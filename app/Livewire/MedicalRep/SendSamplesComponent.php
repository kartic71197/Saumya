<?php

namespace App\Livewire\MedicalRep;

use App\Models\MedicalRepSales;
use App\Models\MedicalRepSalesProducts;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Location;
use App\Models\Organization;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendSamplesComponent extends Component
{
    public $locationId;
    public $location;
    public $organization;
    public $products = [];
    public $selectedProducts = [];
    public $sale_number = '';
    public $isSaving = false;
    public $searchTerm = '';

    protected $rules = [
        'selectedProducts.*.product_id' => 'required|exists:products,id',
        'selectedProducts.*.quantity' => 'required|numeric|min:0.01',
        'selectedProducts.*.unit_id' => 'required|exists:units,id',
    ];

    protected $messages = [
        'selectedProducts.*.quantity.required' => 'Quantity is required.',
        'selectedProducts.*.quantity.min' => 'Quantity must be greater than 0.',
        'selectedProducts.*.unit_id.required' => 'Unit selection is required.',
    ];

    public function mount($locationId = null)
    {
        $this->locationId = $locationId;
        
        if ($locationId) {
            $this->location = Location::with('organization')->find($locationId);
            $this->organization = $this->location->organization ?? null;
        }

        $this->loadProducts();
        $this->generateSaleNumber();
        $this->initializeSelectedProducts();
    }

    public function loadProducts()
    {
        $query = Product::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->with(['baseUnit.unit', 'units.unit', 'brand']);

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('product_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->products = $query->get()->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'brand' => $product->brand->brand_name ?? 'N/A',
                'price' => $product->price,
                'units' => $product->units->map(function($pu) {
                    return [
                        'id' => $pu->unit->id,
                        'name' => $pu->unit->unit_name,
                        'code' => $pu->unit->unit_code,
                        'conversion_factor' => $pu->conversion_factor,
                        'operator' => $pu->operator,
                        'is_base_unit' => $pu->is_base_unit,
                    ];
                })->toArray(),
                'base_unit_id' => $product->baseUnit->unit->id ?? null,
                'base_unit_name' => $product->baseUnit->unit->unit_name ?? 'Unit',
            ];
        })->toArray();
    }

    public function initializeSelectedProducts()
    {
        // Initialize all products with default values
        foreach ($this->products as $product) {
            $this->selectedProducts[$product['id']] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_code' => $product['code'],
                'quantity' => '',
                'unit_id' => $product['base_unit_id'],
                'unit_name' => $product['base_unit_name'],
                'net_unit_price' => $product['price'],
                'total_price' => 0,
                'units' => $product['units'],
                'is_selected' => false,
            ];
        }
    }

    public function updatedSearchTerm()
    {
        $this->loadProducts();
        $this->initializeSelectedProducts();
    }

    public function updatedSelectedProducts($value, $key)
    {
        $parts = explode('.', $key);
        $productId = (int) $parts[0];
        $field = $parts[1] ?? null;

        if (!isset($this->selectedProducts[$productId])) {
            return;
        }

        // Auto-check if quantity is entered
        if ($field === 'quantity') {
            $quantity = floatval($this->selectedProducts[$productId]['quantity']);
            if ($quantity > 0) {
                $this->selectedProducts[$productId]['is_selected'] = true;
            } else {
                $this->selectedProducts[$productId]['is_selected'] = false;
            }
            $this->calculateRowTotal($productId);
        }

        if ($field === 'unit_id') {
            $this->calculateRowTotal($productId);
        }
    }

    public function toggleProduct($productId)
    {
        if (isset($this->selectedProducts[$productId])) {
            $this->selectedProducts[$productId]['is_selected'] = !$this->selectedProducts[$productId]['is_selected'];
            
            // If unchecked, reset quantity
            if (!$this->selectedProducts[$productId]['is_selected']) {
                $this->selectedProducts[$productId]['quantity'] = '';
                $this->selectedProducts[$productId]['total_price'] = 0;
            } else {
                // If checked and no quantity, set default
                if (empty($this->selectedProducts[$productId]['quantity'])) {
                    $this->selectedProducts[$productId]['quantity'] = 1;
                    $this->calculateRowTotal($productId);
                }
            }
        }
    }

    public function calculateRowTotal($productId)
    {
        if (!isset($this->selectedProducts[$productId])) {
            return;
        }

        $unitId = $this->selectedProducts[$productId]['unit_id'];
        $quantity = floatval($this->selectedProducts[$productId]['quantity'] ?? 0);

        // Get product base price
        $product = Product::find($productId);
        $basePrice = $product ? floatval($product->price) : 0;

        // Get unit conversion data
        $unitData = ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($unitData && !$unitData->is_base_unit) {
            if ($unitData->operator == 'multiply') {
                $unitPrice = $basePrice * $unitData->conversion_factor;
            } elseif ($unitData->operator == 'divide') {
                $unitPrice = $unitData->conversion_factor != 0
                    ? $basePrice / $unitData->conversion_factor
                    : $basePrice;
            } else {
                $unitPrice = $basePrice;
            }
        } else {
            $unitPrice = $basePrice;
        }

        // Update selected unit name
        $selectedUnit = collect($this->selectedProducts[$productId]['units'])
            ->firstWhere('id', $unitId);
        
        if ($selectedUnit) {
            $this->selectedProducts[$productId]['unit_name'] = $selectedUnit['name'];
        }

        $this->selectedProducts[$productId]['net_unit_price'] = round($unitPrice, 2);
        $this->selectedProducts[$productId]['total_price'] = round($quantity * $unitPrice, 2);
    }

    public function getSelectedProductsOnly()
    {
        return collect($this->selectedProducts)
            ->filter(fn($product) => $product['is_selected'] && floatval($product['quantity']) > 0)
            ->values()
            ->toArray();
    }

    public function getTotalQuantity()
    {
        return array_sum(array_map(
            fn($p) => floatval($p['quantity'] ?? 0), 
            $this->getSelectedProductsOnly()
        ));
    }

    public function getTotalPrice()
    {
        return round(array_sum(array_map(
            fn($p) => floatval($p['total_price'] ?? 0), 
            $this->getSelectedProductsOnly()
        )), 2);
    }

    public function generateSaleNumber()
    {
        try {
            $lastSale = MedicalRepSales::latest('id')->first();
            $nextNumber = $lastSale ? $lastSale->id + 1 : 1;
            $this->sale_number = 'SAMPLE-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Error generating sale number: ' . $e->getMessage());
            $this->sale_number = 'SAMPLE-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }
    }

    public function saveSamples()
    {
        $selectedProducts = $this->getSelectedProductsOnly();

        if (empty($selectedProducts)) {
            $this->dispatch('show-notification', 'Please select at least one product with quantity.', 'error');
            return;
        }

        if (!$this->locationId) {
            $this->dispatch('show-notification', 'Location is required.', 'error');
            return;
        }

        $this->isSaving = true;

        try {
            DB::transaction(function () use ($selectedProducts) {
                $saleData = [
                    'sales_number' => $this->sale_number,
                    'medical_rep_id' => Auth::id(),
                    'org_id' => Auth::user()->organization_id,
                    'receiver_org_id' => $this->organization->id,
                    'location_id' => $this->locationId,
                    'total_qty' => $this->getTotalQuantity(),
                    'total_price' => $this->getTotalPrice(),
                    'status' => 'pending',
                    'items' => count($selectedProducts)
                ];

                $sale = MedicalRepSales::create($saleData);

                foreach ($selectedProducts as $productData) {
                    MedicalRepSalesProducts::create([
                        'sales_id' => $sale->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'unit_id' => $productData['unit_id'],
                        'price' => $productData['net_unit_price'],
                        'total' => $productData['total_price'],
                    ]);
                }
            });

            // $this->dispatch('show-notification', 'Samples sent successfully!', 'success');
            return redirect()->route('medical_rep.sales');

        } catch (\Exception $e) {
            Log::error('Error saving samples: ' . $e->getMessage());
            $this->dispatch('show-notification', 'An error occurred while saving. Please try again.', 'error');
        } finally {
            $this->isSaving = false;
        }
    }

    public function render()
    {
        return view('livewire.medical-rep.send-samples-component');
    }
}