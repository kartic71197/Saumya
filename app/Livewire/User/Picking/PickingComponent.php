<?php

namespace App\Livewire\User\Picking;

use App\Models\BatchInventory;
use App\Models\BatchPicking;
use App\Models\Location;
use App\Models\Patient;
use App\Models\PickingDetailsModel;
use App\Models\PickingModel;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockCount;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Once;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Picqer\Barcode\BarcodeGeneratorSVG;

class PickingComponent extends Component
{
    use WithPagination;

    public $isPicking = true;
    public $pickingNumber = '';
    public $user;
    public $organization_id;
    public $selectedProduct;
    public $selectedLocation;
    public $selected_location_id;
    public $total = '0';
    public $pickQuantity = 1;
    public $showBiologicalModal = false;
    public $isBiologicalProduct = false;
    public $chart_number = null;
    public $biological_product;

    public $generatedBarcodes;

    public $showSampleProducts = false;

    public string $pickError = '';

    public $alternativeBatch = null;

    protected StockService $stockService;

    public function boot()
    {
        $this->stockService = new StockService();
    }

    public function mount()
    {
        $this->selectedLocation = auth()->user()->location_id ?? null;
    }

    function switchTab($type)
    {
        if ($type == 'picking') {
            $this->isPicking = true;
        } else {
            $this->isPicking = false;
        }
        $this->dispatch('pickingLocationChanged', $this->selectedLocation);
    }

    public function updatedSelectedLocation()
    {
        $this->dispatch('pickingLocationChanged', $this->selectedLocation);
    }
    public function updatedShowSampleProducts()
    {
        $this->dispatch('showSamples', $this->showSampleProducts);
    }
    public function cancelPicking()
    {
        $this->isPicking = false;
        $this->dispatch('close-modal', 'picking_product_modal');
        $this->dispatch('close-modal', 'picking_batch_modal');
        $this->reset(['pickQuantity', 'pickError']);
        $this->dispatch('pg:eventResetPaging-picking-inventory-list-q7yfsl-table');
        $this->dispatch('pg:eventRefresh-picking-inventory-list-q7yfsl-table');
        $this->isPicking = true;
    }
    public function incrementQuantity()
    {

        // Check if we haven't reached the maximum allowed quantity
        if ($this->pickQuantity < $this->selectedProduct->on_hand_quantity) {
            $this->pickQuantity++;
            $this->pickError = '';
        } else {
            $this->pickError = 'Available quantity is only ' . $this->selectedProduct->on_hand_quantity . '. <br>Cant pick more than available quantity.';
            return;
        }

    }

    public function decrementQuantity()
    {
        // Ensure we don't go below zero
        if ($this->pickQuantity > 0) {
            $this->pickQuantity--;
            if ($this->pickQuantity <= $this->selectedProduct->on_hand_quantity) {
                $this->pickError = '';
            }
        }
    }

    public function updatePicking()
    {

        if (!$this->selectedProduct) {
            $this->dispatch('show-notification', 'No product selected!', 'error');
            return;
        }

        if ($this->pickQuantity <= 0) {
            $this->dispatch('show-notification', 'Please enter a valid quantity', 'error');
            return;
        }

        if ($this->pickQuantity > $this->selectedProduct->on_hand_quantity) {
            $this->pickError = 'Available quantity is only ' . $this->selectedProduct->on_hand_quantity . '. <br>Cant pick more than that.';
            return;
        }

        $this->pickError = '';

        if ($this->selectedProduct->product->categories?->category_name == 'biological' && !$this->chart_number) {
            $this->dispatch('show-notification', 'Please enter a valid chart number', 'error');
            return;
        }
        if ($this->selectedProduct->product->categories?->category_name == 'biological') {
            // Create the patient
            Patient::create([
                'chartnumber' => $this->chart_number,
                'organization_id' => auth()->user()->organization_id,
                'drug' => $this->selectedProduct->product->product_name,
                'date_given' => now(),
            ]);
        }

        try {
            DB::beginTransaction();

            $picking = PickingModel::create([
                'picking_number' => $this->pickingNumber,
                'organization_id' => auth()->user()->organization_id,
                'location_id' => $this->selectedProduct->location_id,
                'user_id' => auth()->user()->id,
                'total' => $this->selectedProduct->product->product_price * $this->pickQuantity,
            ]);
            PickingDetailsModel::create([
                'picking_id' => $picking->id,
                'product_id' => $this->selectedProduct->product_id,
                'picking_quantity' => $this->pickQuantity,
                'picking_unit' => $this->selectedProduct->product->units[0]->unit->unit_name,
                'net_unit_price' => $this->selectedProduct->product->cost,
                'sub_total' => $this->selectedProduct->product->cost * $this->pickQuantity,
            ]);

            // $picking = BatchPicking::create([
            //     'picking_number' => $this->pickingNumber,
            //     'location_id' => $this->selectedProduct->location_id,
            //     'batch_id' => $this->selectedProduct->batch_number,
            //     'organization_id' => auth()->user()->organization_id,
            //     'user_id' => auth()->user()->id,
            //     'total' => $this->selectedProduct->product->product_price * $this->pickQuantity,
            //     'product_id' => $this->selectedProduct->product_id,
            //     'picking_quantity' => $this->pickQuantity,
            //     'picking_unit' => $this->selectedProduct->product->units[0]->unit->unit_name,
            //     'net_unit_price' => $this->selectedProduct->product->cost,
            //     'total_amount' => $this->selectedProduct->product->cost * $this->pickQuantity,
            //     'chart_number' => $this->chart_number,
            // ]);



            $auditService = app(\App\Services\PickingAuditService::class);
            $auditService->logPickingCreation(
                $picking,
                $this->selectedProduct->product_id,
                $this->pickQuantity,
                $this->selectedProduct->product->units[0]->unit->unit_name
            );

            $this->stockService->updateStock(
                $this->selectedProduct->product_id,
                $this->selectedProduct->location_id,
                [
                    'quantity' => $this->selectedProduct->on_hand_quantity - $this->pickQuantity,
                    'unit' => $this->selectedProduct->product->units[0]->unit->id,
                    'batch_number' => $this->selectedProduct->batch_number,
                    'expiry_date' => $this->selectedProduct->expiry_date,
                ]
            );

            DB::commit();
            $this->dispatch('close-modal', 'picking_product_modal');
            $this->dispatch('show-notification', 'Product picked successfully!', 'success');
            $this->pickError = '';
            $this->dispatch('pg:eventRefresh-picking-inventory-list-q7yfsl-table');
            // $this->reset(['selectedProduct', 'pickQuantity']);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('show-notification', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    #[On('pickProduct')]
    public function pickProduct($rowId)
    {
        $this->reset(['chart_number', 'biological_product', 'pickError', 'pickQuantity']);
        $user = auth()->user();
        $role = $user->role;
        if (!$role?->hasPermission('pick_products') && $user->role_id > 2) {
            $this->dispatch('show-notification', 'You don\'t have permission to Pick products!', 'error');
            return;
        }

        // Fetch selected product with relationships
        $this->selectedProduct = StockCount::where('id', $rowId)
            ->with([
                'product.categories',
                'location',
                'product.units' => function ($query) {
                    $query->where('is_base_unit', true)->with('unit');
                }
            ])
            ->first();

        // Check if product is biological
        $this->isBiologicalProduct = $this->selectedProduct && 
            strtolower($this->selectedProduct->product->categories?->category_name) === 'biological';

        if ($this->selectedProduct) {
            if ($this->selectedProduct->expiry_date) {
                $this->alternativeBatch = $this->getAlternativeBatch(
                    $this->selectedProduct->product_id,
                    $this->selectedProduct->id,
                    $this->selectedProduct->expiry_date
                );
            } else {
                $this->alternativeBatch = null;

                // \Log::info('pickProduct called', [
                //     'selectedProductId' => $this->selectedProduct->id,
                //     'productName' => $this->selectedProduct->product->product_name ?? null,
                //     'alternativeBatchId' => $this->alternativeBatch?->id,
                //     'alternativeExpiry' => $this->alternativeBatch?->expiry_date,
                // ]);
            }
        }

        $this->pickingNumber = PickingModel::generatePickingNumber();
        $this->dispatch('open-modal', 'picking_product_modal');
    }

    public function redirectToPatientPicking()
{
    if ($this->selectedProduct) {
        // Store the biological product info in session
        session([
            'biological_product_for_picking' => [
                'stock_count_id' => $this->selectedProduct->id,
                'product_id' => $this->selectedProduct->product_id,
                'product_name' => $this->selectedProduct->product->product_name,
                'product_code' => $this->selectedProduct->product->product_code,
            ]
        ]);
        
        // Close modal and redirect to patient page
        $this->dispatch('close-modal', 'picking_product_modal');
        return redirect()->route('patient.index');
    }
}
    // #[On('pickBatchProduct')]
    // public function pickBatchProduct($rowId)
    // {
    //     $this->reset(['chart_number', 'biological_product']);
    //     $user = auth()->user();
    //     $role = $user->role;
    //     if (!$role?->hasPermission('pick_products') && $user->role_id > 2) {
    //         $this->dispatch('show-notification', 'You don\'t have permission to Pick products!', 'error');
    //         return;
    //     }

    //     // Fetch selected product with relationships
    //     $this->selectedProduct = BatchInventory::where('id', $rowId)
    //         ->with([
    //             'product',
    //             'location',
    //             'product.units' => function ($query) {
    //                 $query->where('is_base_unit', true)->with('unit');
    //             }
    //         ])
    //         ->first();

    //     $this->pickingNumber = BatchPicking::generatePickingNumber();
    //     $this->dispatch('open-modal', 'picking_batch_modal');
    // }

    public function updateBatchPicking()
    {

        if (!$this->selectedProduct) {
            $this->dispatch('show-notification', 'No product selected!', 'error');
            return;
        }

        if ($this->pickQuantity <= 0) {
            $this->dispatch('show-notification', 'Please enter a valid quantity', 'error');
            return;
        }

        try {
            DB::beginTransaction();

            $picking = BatchPicking::create([
                'picking_number' => $this->pickingNumber,
                'location_id' => $this->selectedProduct->location_id,
                'batch_id' => $this->selectedProduct->batch_number,
                'organization_id' => auth()->user()->organization_id,
                'user_id' => auth()->user()->id,
                'total' => $this->selectedProduct->product->product_price * $this->pickQuantity,
                'product_id' => $this->selectedProduct->product_id,
                'picking_quantity' => $this->pickQuantity,
                'picking_unit' => $this->selectedProduct->product->units[0]->unit->unit_name,
                'net_unit_price' => $this->selectedProduct->product->cost,
                'total_amount' => $this->selectedProduct->product->cost * $this->pickQuantity,
                'chart_number' => $this->chart_number,
            ]);

            // Create the patient
            Patient::create([
                'chartnumber' => $this->chart_number,
                'organization_id' => auth()->user()->organization_id,
                'drug' => $this->selectedProduct->product->product_name,
                'date_given' => now(),
            ]);

            $auditService = app(\App\Services\BatchPickingAuditService::class);
            $auditService->logPickingCreation(
                $picking,
                $this->selectedProduct->product_id,
                $this->pickQuantity,
                $this->selectedProduct->product->units[0]->unit->unit_name
            );
            $this->selectedProduct->quantity -= $this->pickQuantity;
            $this->selectedProduct->save();

            DB::commit();
            $this->dispatch('close-modal', 'picking_batch_modal');
            $this->dispatch('show-notification', 'Product picked successfully!', 'success');
            $this->reset(['selectedProduct', 'pickQuantity']);
            $this->pickError = '';
            $this->dispatch('pg:eventRefresh-batch-picking-list-ga40i5-table');


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('show-notification', 'Error: ' . $e->getMessage(), 'error');
        }
    }

    public function cancelBarcodeModal()
    {
        $this->showBiologicalModal = false;
        $this->reset(['biological_products', 'generatedBarcodes', 'showBarcode']);
        $this->dispatch('close-modal', 'biological_product_modal');
    }

    public function printBiologicalBarcodes()
    {
        if (empty($this->biological_product)) {
            $this->dispatch('show-notification', 'No biological products to generate barcodes for.', 'warning');
            return;
        }

        $generator = new BarcodeGeneratorSVG();
        // Generate chart number barcode
        $chartNumber = $this->chart_number ?? '';

        if (!empty($chartNumber)) {
            $this->generatedBarcodes = $generator->getBarcode(
                $chartNumber,
                    $generator::TYPE_CODE_128
            );
        }

        $this->dispatch('printChartNumberBarcodes');
        $this->dispatch('redirect-to-patient');
    }
    public function switchBatch($batchId)
    {
        $this->selectedProduct = StockCount::with([
            'product.categories',
            'location',
            'product.units' => fn($q) => $q->where('is_base_unit', true)->with('unit'),
        ])->find($batchId);

        $this->pickQuantity = 1; // reset quantity
        $this->chart_number = null;

        if ($this->selectedProduct && $this->selectedProduct->product) {
            if ($this->selectedProduct->expiry_date) {
                $this->alternativeBatch = $this->getAlternativeBatch(
                    $this->selectedProduct->product_id,
                    $this->selectedProduct->id,
                    $this->selectedProduct->expiry_date
                );
            } else {
                $this->alternativeBatch = null;
            }
            $this->showModal = true;
        } else {
            \Log::warning("switchBatch: No product found for batchId {$batchId}");
        }
    }


    protected function getAlternativeBatch($productId, $currentBatchId, $currentExpiry = null)
    {
        if (!$currentExpiry) {
            return null;
        }

        $query = StockCount::with('product')
            ->where('product_id', $this->selectedProduct->product_id)
            ->where('id', '<>', $this->selectedProduct->id)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('on_hand_quantity', '>', 0);
        if ($currentExpiry) {
            $query->where('expiry_date', '<', $currentExpiry);
        }

        $alt = $query->orderBy('expiry_date', 'asc')->first();

        // \Log::info('getAlternativeBatch called', [
        //     'productId' => $productId,
        //     'currentBatchId' => $currentBatchId,
        //     'currentExpiry' => $currentExpiry,
        //     'alternativeBatchId' => $alt?->id,
        //     'alternativeExpiry' => $alt?->expiry_date,
        //     'productLoaded' => $alt?->product?->product_name,
        // ]);

        return $alt;
    }
    public function render()
    {
        $locations = Location::where('org_id', auth()->user()->organization_id)->where('is_active', true)->orderBy('name')->get();
        return view('livewire.user.picking.picking-component', compact('locations'));
    }
}