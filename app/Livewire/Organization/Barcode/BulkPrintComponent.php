<?php

namespace App\Livewire\Organization\Barcode;

use App\Models\Mycatalog;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class BulkPrintComponent extends Component
{
    public $products = [];
    public $selectedProducts = [];
    public $search = '';
    public $selectedCategory = '';
    public $categories = [];
    public $printByCategory = false;

    public function mount()
    {
        $this->categories = Category::where('is_active', true)
        ->where('organization_id', auth()->user()->organization_id)
        ->whereHas('products', function ($query) {
            $query->where('is_active', true)
                  ->where('organization_id', auth()->user()->organization_id);
        })
        ->orderBy('category_name')
        ->get(['id', 'category_name'])
        ->toArray();


        $catalogs = Mycatalog::join('products', 'mycatalogs.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->where('products.organization_id', auth()->user()->organization_id)
            ->distinct('products.id')
            ->get([
                'products.id',
                'products.product_name',
                'products.product_code',
                'products.category_id',
                'categories.category_name'
            ]);

        Log::info('Sample catalog rows', $catalogs->take(5)->toArray());

        $this->products = $catalogs->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'category_id' => $product->category_id,
                'category_name' => $product->category_name ?? '',
            ];
        })->toArray();
    }

    public function getFilteredProductsProperty()
    {
        $filtered = collect($this->products)->filter(function ($product) {
            // Filter by search term
            $matchesSearch = empty($this->search) ||
                str_contains(strtolower($product['name']), strtolower($this->search)) ||
                str_contains(strtolower($product['code']), strtolower($this->search));

            // Filter by category
            $matchesCategory = empty($this->selectedCategory) ||
                $product['category_id'] == $this->selectedCategory;

            return $matchesSearch && $matchesCategory;
        })->values()->toArray();
        return $filtered;
    }

    public function getAvailableProductsProperty()
    {
        $selectedIds = collect($this->selectedProducts)->pluck('id')->toArray();

        return collect($this->filteredProducts)->filter(function ($product) use ($selectedIds) {
            return !in_array($product['id'], $selectedIds);
        })->values()->toArray();
        return $filtered;
    }

    public function addToSelected($productId)
    {
        $product = collect($this->products)->firstWhere('id', $productId);

        if ($product && !collect($this->selectedProducts)->contains('id', $productId)) {
            $this->selectedProducts[] = $product;
        }
    }

    public function removeFromSelected($productId)
    {
        $this->selectedProducts = collect($this->selectedProducts)
            ->reject(function ($product) use ($productId) {
                return $product['id'] == $productId;
            })
            ->values()
            ->toArray();
    }

    public function clearSelected()
    {
        $this->selectedProducts = [];
    }

    public function selectAll()
    {
        $this->selectedProducts = $this->filteredProducts;
    }

    public function printSelected()
    {
        logger('reached at the printing component');
        if (empty($this->selectedProducts)) {
            logger('no products found at the component. returning....');

            session()->flash('error', 'Please select at least one product to print.');
            return;
        }

        // Store selected products in session for printing
        session()->put('products_to_print', $this->selectedProducts);

        // Flash success message
        session()->flash('success', 'Products prepared for printing!');

        // Redirect to print page
        return redirect()->route('barcode.bulk-print');
    }

    public function render()
    {
        return view('livewire.organization.barcode.bulk-print-component');
    }
}