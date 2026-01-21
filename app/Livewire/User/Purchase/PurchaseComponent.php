<?php

namespace App\Livewire\User\Purchase;

use App\Livewire\User\Settings\CategoriesComponent;
use App\Models\BatchInventory;
use App\Models\Edi855;
use App\Models\PurchaseOrderDetail;
use Carbon\Carbon;
use App\Models\Edi856;
use App\Models\Location;
use App\Models\PoReceipt;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\StockCount;
use App\Services\AckServices;
use App\Services\InvoiceServices;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Component;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Illuminate\Support\Facades\Log;

class PurchaseComponent extends Component
{

    use WithFileUploads;
    public $selectedTab = 'purchase';
    public $orderType = 'organization';
    public $user = '';
    public $organization_id = null;
    public $viewPurchaseOrder = false;
    public $searchPurchaseOrder = null;
    public $purchaseOrderList = [];
    public $showModal = false;
    public $purchaseOrder = null;
    public $receivedQuantities = [];
    public $location_id;

    public $message = '';
    public $notes = '';
    public $images;
    public $existingImage;
    public $letterCount = 0;
    protected $queryString = ['location_id'];

    public $initialLocation;

    public $locations = [];

    public $biological_products = [];

    public $selectedLocation = [];

    public $previewUrl, $selectedPurchaseOrder;

    public $edi855data = [];
    public $edi856data = [];

    // New properties for product cancellation
    public $showCancelProductModal = false;
    public $productToCancel = null;
    public $expectedProductCode = '';
    public $confirmProductCode = '';
    public $cancelProductError = '';
    public $cancelNote = '';

    protected $rules = [
        'receivedQuantities.*' => 'numeric|min:0',
        'images.*' => 'image|max:10240',
        'batchDetails.*.expiry_date' => 'required_if:has_expiry,true|regex:/^\d{2}-\d{4}$/',
        'productLots.*.*.expiry_date' => 'required_if:quantity,>0|regex:/^\d{2}-\d{4}$/',
    ];
    protected $messages = [
        'receivedQuantities.*.numeric' => 'Received quantity must be a number',
        'receivedQuantities.*.min' => 'Received quantity cannot be negative',
        'batchDetails.*.expiry_date.regex' => 'Invalid expiry date format. Use MM-YYYY (e.g., 06-2026)',
        'productLots.*.*.expiry_date.regex' => 'Invalid expiry date format. Use MM-YYYY (e.g., 06-2026)',
        'batchDetails.*.expiry_date.required_if' => 'Expiry date is required',
        'productLots.*.*.expiry_date.required_if' => 'Expiry date is required when quantity is provided',
    ];
    public $showBiologicalModal = false;
    public $generatedBarcodes = [];
    public $showBarcode = false;
    public $purchasedProductId, $batchInventory = [];
    public $batchDetails = [];

    public $purchaseOrderId;

    public $chart_number = [];

    public $productLots = [];
    public $showMultipleLots = [];

    public $visibleHistoryRows = [];

    public $purchasedProducts = [];
    protected InvoiceServices $invoiceService;
    protected AckServices $ackServices;
    protected StockService $stockService;

    public function boot(InvoiceServices $invoiceService, AckServices $ackServices, StockService $stockService)
    {
        $this->invoiceService = $invoiceService;
        $this->ackServices = $ackServices;
        $this->stockService = $stockService;
    }

    // Cancel functions
    public function openCancelProductModal($productId, $productCode)
    {
        $this->productToCancel = $productId;
        $this->expectedProductCode = $productCode;
        $this->confirmProductCode = '';
        $this->cancelProductError = '';
        $this->showCancelProductModal = true;
    }
    /**
     * Close the cancel product modal
     */
    public function closeCancelProductModal()
    {
        $this->showCancelProductModal = false;
        $this->productToCancel = null;
        $this->expectedProductCode = '';
        $this->confirmProductCode = '';
        $this->cancelProductError = '';
    } /**
      * Confirm and cancel the product
      */

    public function getPurchasedProductsProperty()
    {
        if (!$this->purchaseOrder) {
            return collect();
        }

        return $this->purchasedProducts
            ->filter(function ($product) {
                return $product->product_status != 'canceled';
            });
    }
    public function confirmCancelProduct()
    {
        // Reset error
        $this->cancelProductError = '';

        // Validate product code input
        if (empty($this->confirmProductCode)) {
            $this->cancelProductError = 'Please enter the product code to confirm.';
            return;
        }

        // Validate product code input
        if (empty($this->cancelNote)) {
            $this->cancelProductError = 'Please enter the cancellation note.';
            return;
        }

        // Check if product code matches
        if (trim($this->confirmProductCode) !== trim($this->expectedProductCode)) {
            $this->cancelProductError = 'Product code does not match. Please enter the correct product code.';
            return;
        }

        try {
            // Find the product detail
            $productDetail = PurchaseOrderDetail::findOrFail($this->productToCancel);

            // Update the product status to canceled
            $productDetail->update([
                'product_status' => 'canceled',
                'canceled_by' => auth()->user()->id,
                'cancelation_note' => $this->cancelNote
            ]);

            // Close the modal
            $this->closeCancelProductModal();
            // Show success message
            session()->flash('success', 'Product has been canceled successfully.');
            // Refresh the purchase order data
            $this->purchaseOrder->refresh();
            // Update PO status based on remaining products
            $this->updatePurchaseOrderStatus();

        } catch (\Exception $e) {
            $this->cancelProductError = 'An error occurred while canceling the product. Please try again.';
            \Log::error('Product cancellation error: ' . $e->getMessage());
        }
    }

    public function setOrderType($type)
    {
        $this->orderType = $type;
        $this->fetchPurchaseData();
    }

    public function mount()
    {

        $this->locations = Location::where('org_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($this->location_id) {
            $this->selectedLocation = $this->location_id;
        } else {
            $this->selectedLocation = auth()->user()->location_id ?? null;
        }
        $this->fetchPurchaseData();
    }

    public function getReceiptNotesProperty()
    {
        if (!$this->purchaseOrder || empty($this->purchaseOrder->notes)) {
            return collect();
        }

        // $this->purchaseOrder->notes is already cast to array
        return collect($this->purchaseOrder->notes)
            ->map(function ($note) {
                return [
                    'user' => optional(\App\Models\User::find($note['user']))->name ?? 'Unknown',
                    'notes' => $note['notes'] ?? '',
                    'datetime' => $note['datetime'] ?? null,
                ];
            })
            ->sortByDesc('datetime')
            ->values();
    }
    public function getReceiptImagesProperty()
    {
        if (!$this->purchaseOrder || empty($this->purchaseOrder->packing_slips)) {
            return collect();
        }

        // Each packing slip entry looks like ['user' => ..., 'images' => [...], 'datetime' => ...]
        return collect($this->purchaseOrder->packing_slips)
            ->map(function ($entry) {
                return [
                    'user' => optional(\App\Models\User::find($entry['user']))->name ?? 'Unknown',
                    'images' => $entry['images'] ?? [],
                    'datetime' => $entry['datetime'] ?? null,
                ];
            })
            ->sortByDesc('datetime')
            ->values();
    }

    public function checkLetterLimit($maxLetters)
    {
        $text = trim($this->notes);
        $textWithoutSpaces = preg_replace('/\s+/', '', $text);
        $this->letterCount = mb_strlen($textWithoutSpaces);

        if ($this->letterCount > $maxLetters) {
            $this->notes = mb_substr($text, 0, $maxLetters);
            $this->letterCount = $maxLetters;
            $this->addError('notes', "Notes cannot exceed $maxLetters letters.");
        } else {
            $this->resetErrorBag('notes');
        }
    }

    public function toggleHistory($index)
    {
        if (in_array($index, $this->visibleHistoryRows)) {
            // Remove from visible array (hide)
            $this->visibleHistoryRows = array_filter($this->visibleHistoryRows, function ($item) use ($index) {
                return $item !== $index;
            });
        } else {
            // Add to visible array (show)
            $this->visibleHistoryRows[] = $index;
        }
    }

    public function isHistoryVisible($index)
    {
        return in_array($index, $this->visibleHistoryRows);
    }

    public function fetchPurchaseData()
    {
        $this->user = auth()->user();
        $this->organization_id = $this->user->organization_id;

        $query = PurchaseOrder::with([
            'purchaseSupplier:id,supplier_name,supplier_email',
            'purchaseLocation:id,name'
        ])
            ->whereHas('purchaseSupplier')
            ->where('organization_id', $this->organization_id)
            ->where('purchase_orders.status', '!=', 'completed')
            ->where('purchase_orders.status', '!=', 'canceled');
        if ($this->orderType == 'organization') {
            $query->where('purchase_orders.external_order', false);
        } else {
            $query->where('purchase_orders.external_order', true);
        }

        if ($this->selectedLocation) {
            $query->where('location_id', $this->selectedLocation);
        }

        if ($this->searchPurchaseOrder) {
            $query->where(function ($q) {
                $q->where('purchase_order_number', 'LIKE', "%{$this->searchPurchaseOrder}%")
                    ->orWhere('merge_id', 'LIKE', "%{$this->searchPurchaseOrder}%")
                    ->orWhereHas('purchaseSupplier', function ($supplierQuery) {
                        $supplierQuery->where('supplier_name', 'LIKE', "%{$this->searchPurchaseOrder}%");
                    });
            });
        }

        $this->purchaseOrderList = $query->get();
    }


    #[On('rowClicked')]
    public function fetchPoModal($id)
    {
        $this->fetchPurchaseData();
        $this->viewPurchaseOrder = true;
        $this->selectPo($id);
    }
    public function selectPo($id)
    {
        // Fetch purchase order separately to avoid conflicts with joins
        $this->purchaseOrder = PurchaseOrder::with(['purchasedProducts.product', 'purchasedProducts.unit', 'purchaseLocation'])
            ->where('purchase_orders.id', $id)
            ->leftJoin('bill_to_locations', function ($join) {
                $join->on('purchase_orders.bill_to_location_id', '=', 'bill_to_locations.location_id')
                    ->on('purchase_orders.supplier_id', '=', 'bill_to_locations.supplier_id');
            })
            ->select('purchase_orders.*', 'bill_to_locations.bill_to')
            ->first();

    }
    public function receiveProduct($id)
    {
        $this->reset([
            'biological_products',
            'generatedBarcodes',
            'batchDetails',
            'chart_number',
            'showBarcode',
            'batchInventory',
            'productLots',
            'showMultipleLots',
            'notes',
            'images'
        ]);
        $user = auth()->user();
        $role = $user->role;
        if (!$role?->hasPermission('receive_orders') && $user->role_id > 2) {
            $this->dispatch('show-notification', "You dont have permission to receive Purchase orders.", 'error');
            return;
        }

        $this->purchaseOrder = PurchaseOrder::with([
            'purchasedProducts' => fn($q) =>
                $q->where('product_status', '!=', 'canceled'),
            'purchasedProducts.product',
            'purchasedProducts.unit',
            'purchaseLocation',
        ])
            ->where('id', $id)
            ->whereHas(
                'purchasedProducts',
                fn($q) =>
                $q->where('product_status', '!=', 'canceled')
            )
            ->first();

        $this->purchasedProducts = $this->purchaseOrder?->purchasedProducts ?? collect();



        if (!$this->purchaseOrder) {
            $this->dispatch('show-notification', "No Purchase Order selected.", 'error');
            return;
        }

        $this->initializeReceivedQuantities();
        $this->dispatch('open-modal', 'receive_product_model');
    }
    private function initializeReceivedQuantities()
    {
        foreach ($this->purchasedProducts as $product) {
            $this->receivedQuantities[$product->id] = $product->quantity - $product->received_quantity;
            if ($product->product->has_expiry_date) {
                $this->batchDetails[$product->product->id] = [
                    'batch_number' => '',
                    'expiry_date' => ''
                ];
            }
        }
    }

    public function addLot($productId)
    {
        $product = $this->purchasedProducts->find($productId);

        if (!$product)
            return;

        if (!isset($this->productLots[$productId])) {
            // Initialize with existing single lot data if available
            $existingBatch = $this->batchDetails[$product->product->id] ?? null;
            $this->productLots[$productId] = [];

            if ($existingBatch && ($existingBatch['batch_number'] || $existingBatch['expiry_date'])) {
                $this->productLots[$productId][] = [
                    'batch_number' => $existingBatch['batch_number'],
                    'expiry_date' => $existingBatch['expiry_date'],
                    'quantity' => $this->receivedQuantities[$productId] ?? 0
                ];
            } else {
                $this->productLots[$productId][] = [
                    'batch_number' => '',
                    'expiry_date' => '',
                    'quantity' => $this->receivedQuantities[$productId] ?? 0
                ];
            }
        }
        // Add new lot
        $this->productLots[$productId][] = [
            'batch_number' => '',
            'expiry_date' => '',
            'quantity' => 0
        ];

        // Clear the single batch details since we're now using multiple lots
        unset($this->batchDetails[$product->product->id]);
        $this->receivedQuantities[$productId] = 0; // Reset since we're now tracking via lots
    }

    // Remove lot for a product
    public function removeLot($productId, $lotIndex)
    {
        if (isset($this->productLots[$productId][$lotIndex])) {
            unset($this->productLots[$productId][$lotIndex]);
            // Re-index array
            $this->productLots[$productId] = array_values($this->productLots[$productId]);

            // If only one lot remains, convert back to single lot mode
            if (count($this->productLots[$productId]) <= 1) {
                $product = $this->purchasedProducts->find($productId);
                if ($product && count($this->productLots[$productId]) == 1) {
                    $remainingLot = $this->productLots[$productId][0];
                    $this->batchDetails[$product->product->id] = [
                        'batch_number' => $remainingLot['batch_number'],
                        'expiry_date' => $remainingLot['expiry_date']
                    ];
                    $this->receivedQuantities[$productId] = $remainingLot['quantity'];
                    unset($this->productLots[$productId]);
                }
            }
        }
    }

    private function initializeProductLots()
    {
        foreach ($this->purchasedProducts as $product) {
            // Check if product requires lot numbers (you can modify this condition based on your business logic)
            $requiresLot = $this->productRequiresLot($product->product);

            if ($requiresLot) {
                // Initialize with one lot entry
                $this->productLots[$product->id] = [
                    [
                        'batch_number' => '',
                        'expiry_date' => '',
                        'quantity' => $product->quantity - $product->received_quantity
                    ]
                ];
            }
        }
    }
    private function productRequiresLot($product)
    {
        // Add your logic here - this could be based on product category, type, or a specific field
        // Example: Check if product is biological or has expiry tracking enabled
        return $product->categories()->whereRaw('LOWER(category_name) = ?', ['biological'])->exists()
            || $product->has_expiry_date;
    }
    public function incrementQuantity($productId, $lotIndex = null)
    {
        if ($lotIndex !== null && isset($this->productLots[$productId][$lotIndex])) {
            $this->productLots[$productId][$lotIndex]['quantity']++;
        } else {
            $this->receivedQuantities[$productId]++;
        }
    }

    public function decrementQuantity($productId, $lotIndex = null)
    {
        if ($lotIndex !== null && isset($this->productLots[$productId][$lotIndex])) {
            if ($this->productLots[$productId][$lotIndex]['quantity'] > 0) {
                $this->productLots[$productId][$lotIndex]['quantity']--;
            } else {
                $this->dispatch('show-notification', "Lot quantity cannot be less than 0.", 'error');
            }
        } else {
            if ($this->receivedQuantities[$productId] > 0) {
                $this->receivedQuantities[$productId]--;
            } else {
                $this->dispatch('show-notification', "Receiving quantity cannot be less than 0.", 'error');
            }
        }
    }

    private function validateReceivingData()
    {
        $this->resetErrorBag(['productLots', 'receivedQuantities']);
        if ($this->productLots) {
            foreach ($this->productLots as $productId => $lots) {
                $purchasedProduct = $this->purchasedProducts->find($productId);
                if (!$purchasedProduct)
                    continue;

                $totalLotQuantity = array_sum(array_column($lots, 'quantity'));
                $maxAllowed = $purchasedProduct->quantity - $purchasedProduct->received_quantity;
                logger('  Validate lot quantities first ');
                logger($purchasedProduct);
                if ($totalLotQuantity > $maxAllowed) {
                    $this->addError("productLots.{$productId}", 'Total quantities cannot exceed ordered quantity');
                    // DB::rollBack();
                    return false;
                }

                // Validate that lots have required information
                foreach ($lots as $index => $lot) {
                    if ($lot['quantity'] > 0 && empty($lot['batch_number'])) {
                        $this->addError("productLots.{$productId}.{$index}.batch_number", 'Batch number is required');
                        // DB::rollBack();
                        return false;
                    }
                    if ($lot['quantity'] > 0 && empty($lot['expiry_date'])) {
                        $this->addError("productLots.{$productId}.{$index}.expiry_date", 'Date is required');
                        // DB::rollBack();
                        return false;
                    }
                    logger('About to validate format for: ' . ($lot['expiry_date'] ?? 'NULL')); // ADD THIS


                    if ($lot['quantity'] > 0 && !empty($lot['expiry_date'])) {
                        if (!preg_match('/^\d{2}-\d{4}$/', $lot['expiry_date'])) {
                            $this->addError("productLots.{$productId}.{$index}.expiry_date", 'Invalid format. Use MM-YYYY (e.g., 06-2026)');
                            // DB::rollBack();
                            return false;
                        }
                        // Validate month range (01-12)
                        $parts = explode('-', $lot['expiry_date']);
                        $month = (int) $parts[0];
                        $year = (int) $parts[1];
                        if ($month < 1 || $month > 12) {
                            $this->addError("productLots.{$productId}.{$index}.expiry_date", 'Month must be between 01 and 12');
                            // DB::rollBack();
                            return false;
                        }
                        // Validate that the expiry date is not in the past
                        $currentMonth = (int) date('m');
                        $currentYear = (int) date('Y');

                        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
                            $this->addError("productLots.{$productId}.{$index}.expiry_date", 'Expiry date cannot be in the past');
                            // DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        } else {
            //valiadating products without lots
            foreach ($this->receivedQuantities as $productId => $receivedQty) {
                if ($receivedQty > 0) {
                    $purchasedProduct = $this->purchasedProducts->find($productId);
                    if (!$purchasedProduct) {
                        logger("Purchased product not found for ID: {$productId}");
                        continue; // Just skip instead of erroring
                    }

                    // USE product_id NOT productId (like original working code)
                    $expiryDateRaw = $this->batchDetails[$purchasedProduct->product_id]['expiry_date'] ?? null;
                    $batchNumber = $this->batchDetails[$purchasedProduct->product_id]['batch_number'] ?? null;
                    logger('  else Validate lot quantities first ');
                    logger($purchasedProduct);

                    if ($purchasedProduct->product->has_expiry_date != '0') {
                        if (empty($batchNumber) || trim($batchNumber) === '' || empty($expiryDateRaw)) {
                            $this->addError("receivedQuantities.{$productId}", 'Batch/Lot number and expiry date is required for all products');
                            return false; // Changed from DB::rollBack()
                        }
                        if (!preg_match('/^\d{2}-\d{4}$/', $expiryDateRaw)) {
                            $this->addError("batchDetails.{$purchasedProduct->product_id}.expiry_date", 'Invalid expiry date format. Use MM-YYYY (e.g., 06-2026)');
                            return false; // Changed from DB::rollBack()
                        }
                        $parts = explode('-', $expiryDateRaw);
                        $month = (int) $parts[0];
                        $year = (int) $parts[1];
                        if ($month < 1 || $month > 12) {
                            $this->addError("batchDetails.{$purchasedProduct->product_id}.expiry_date", 'Month must be between 01 and 12');
                            //    DB::rollBack();
                            return false;
                        }
                        // Validate that the expiry date is not in the past
                        $currentMonth = (int) date('m');
                        $currentYear = (int) date('Y');

                        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
                            $this->addError("batchDetails.{$purchasedProduct->product_id}.expiry_date", 'Expiry date cannot be in the past');
                            //    DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }

        return true; // Return true if validation passes
    }
    public function updateReceiveQuantity()
    {
        // $this->validate();

        try {

            $this->validate();

            // Step 2: Custom validation
            $validationResult = $this->validateReceivingData();

            if (!$validationResult || $this->getErrorBag()->isNotEmpty()) {
                $errors = $this->getErrorBag()->toArray();
                logger('Validation errors found:', $errors);

                // Show first error to user
                $firstError = collect($errors)->flatten()->first();
                $this->dispatch('show-notification', $firstError, 'error');
                return; // Exit early - no transaction started yet
            }
            DB::beginTransaction();

            // Process image uploads and store paths
            $imagePaths = [];
            if ($this->images) {
                foreach ($this->images as $image) {
                    // Store image and get path
                    $path = $image->store('packing-slips', 'public');
                    $imagePaths[] = $path;
                }
            }

            foreach ($this->receivedQuantities as $productId => $receivedQty) {
                // Skip if this product has multiple lots

                if (isset($this->productLots[$productId]) || !$this->purchasedProducts->find($productId)) {
                    //    logger("Skipping product ID {$productId} - has lots or not found in PO");
                    continue;
                }

                $purchasedProduct = $this->purchasedProducts->find($productId);
                if (!$purchasedProduct || $receivedQty <= 0)
                    continue;

                $previouslyReceivedQuantity = $purchasedProduct->received_quantity;
                if ($receivedQty > ($purchasedProduct->quantity - $purchasedProduct->received_quantity)) {
                    $this->addError("receivedQuantities.{$productId}", 'Received quantity cannot exceed ordered quantity');
                    DB::rollBack();
                    return;
                }

                logger(' Process regular quantities (non-lot products or single lot products ');
                logger($purchasedProduct);
                $purchasedProduct->update([
                    'received_quantity' => $receivedQty + $purchasedProduct->received_quantity,
                ]);
                // Audit log
                $auditService = app(\App\Services\PurchaseOrderAuditService::class);
                $auditService->logProductReceiving(
                    $this->purchaseOrder,
                    $purchasedProduct->product_id,
                    $previouslyReceivedQuantity,
                    $purchasedProduct->received_quantity
                );

                // Update stock
                // $this->updateProductStock($purchasedProduct->product_id, $purchasedProduct->unit_id, $receivedQty, $this->purchaseOrder->location_id);

                // Handle single batch details
                $batchNumber = $this->batchDetails[$purchasedProduct->product_id]['batch_number'] ?? null;
                $expiryDateRaw = $this->batchDetails[$purchasedProduct->product_id]['expiry_date'] ?? null;

                // if ($expiryDateRaw && preg_match('/^\d{4}-\d{2}$/', $expiryDateRaw)) {
                if ($expiryDateRaw) {
                    if (preg_match('/^\d{2}-\d{4}$/', $expiryDateRaw)) {
                        list($month, $year) = explode('-', $expiryDateRaw);
                        // $expiryDate = $expiryDateRaw . '-01';
                        $expiryDate = $year . '-' . $month . '-01';
                    } elseif (preg_match('/^\d{4}-\d{2}$/', $expiryDateRaw)) {
                        $expiryDate = $expiryDateRaw . '-01';
                    } else {
                        $expiryDate = null;
                    }
                } else {

                    $expiryDate = null;
                }
                logger('batch number is ' . $batchNumber . ' expiry date is ' . $expiryDate);
                logger($purchasedProduct);
                PoReceipt::create([
                    'purchase_order_id' => $purchasedProduct->purchase_order_id,
                    'product_id' => $purchasedProduct->product_id,
                    'ordered_qty' => $purchasedProduct->quantity,
                    'received_qty' => $receivedQty,
                    'received_by' => auth()->user()->id,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'date_received' => now(),
                ]);


                $batch = $this->stockService->addStock(
                    $purchasedProduct->product_id,
                    $this->purchaseOrder->location_id,
                    $batchNumber,
                    $expiryDate,
                    $receivedQty,
                    $purchasedProduct->unit_id
                );


                // Check if biological product
                $biological = Product::where('id', $purchasedProduct->product_id)
                    ->whereHas('categories', function ($query) {
                        $query->whereRaw('LOWER(category_name) = ?', ['biological']);
                    })
                    ->first();

                if ($biological) {
                    $this->biological_products[] = [
                        'product_id' => $biological->id,
                        'product_code' => $biological->product_code,
                        'product_name' => $biological->product_name,
                    ];
                }
            }

            // Process lot-based quantities
            foreach ($this->productLots as $productId => $lots) {
                logger('eneter lot based');
                $purchasedProduct = $this->purchasedProducts->find($productId);
                if (!$purchasedProduct)
                    continue;

                logger('  Process lot-based quantities ');
                logger($purchasedProduct);

                $totalReceived = 0;
                $previouslyReceivedQuantity = $purchasedProduct->received_quantity;

                foreach ($lots as $lot) {
                    if ($lot['quantity'] <= 0)
                        continue;

                    $totalReceived += $lot['quantity'];

                    $expiryDateRaw = $lot['expiry_date'] ?? null;
                    // if ($expiryDateRaw && preg_match('/^\d{4}-\d{2}$/', $expiryDateRaw)) {
                    //     $expiryDate = $expiryDateRaw . '-01';
                    // } else {
                    //     $expiryDate = $expiryDateRaw;
                    // }
                    if ($expiryDateRaw) {
                        // Convert MM-YYYY to YYYY-MM-01 for database storage
                        if (preg_match('/^\d{2}-\d{4}$/', $expiryDateRaw)) {
                            list($month, $year) = explode('-', $expiryDateRaw);
                            $expiryDate = $year . '-' . $month . '-01';
                        } elseif (preg_match('/^\d{4}-\d{2}$/', $expiryDateRaw)) {
                            $expiryDate = $expiryDateRaw . '-01';
                        } else {
                            $expiryDate = null;
                        }
                    } else {
                        $expiryDate = null;
                    }
                    // $convertedQuantity = $this->convertQuantityToBaseUnit($purchasedProduct->product_id, $purchasedProduct->unit_id, $lot['quantity']);
                    // // Create or update batch inventory
                    // $existingBatch = StockCount::where('product_id', $purchasedProduct->product_id)
                    //     ->where('batch_number', $lot['batch_number'])
                    //     ->where('expiry_date', $expiryDate)

                    //     ->where('location_id', $this->purchaseOrder->location_id)
                    //     ->where('organization_id', auth()->user()->organization_id)
                    //     ->first();

                    // if ($existingBatch) {
                    //     $existingBatch->on_hand_quantity += $lot['quantity'];
                    //     $existingBatch->save();
                    // } else {
                    //     StockCount::create([
                    //         'product_id' => $purchasedProduct->product_id,
                    //         'batch_number' => $lot['batch_number'],
                    //         'expiry_date' => $expiryDate,
                    //         'on_hand_quantity' => $convertedQuantity,
                    //         'organization_id' => auth()->user()->organization_id,
                    //         'location_id' => $this->purchaseOrder->location_id,
                    //     ]);
                    // }
                    $batch = $this->stockService->addStock(
                        $purchasedProduct->product_id,
                        $this->purchaseOrder->location_id,
                        $lot['batch_number'],
                        $expiryDate,
                        $lot['quantity'],
                        $purchasedProduct->unit_id
                    );
                    PoReceipt::create([
                        'purchase_order_id' => $purchasedProduct->purchase_order_id,
                        'product_id' => $purchasedProduct->product_id,
                        'ordered_qty' => $purchasedProduct->quantity,
                        'received_qty' => $lot['quantity'],
                        'received_by' => auth()->user()->id,
                        'batch_number' => $lot['batch_number'],
                        'expiry_date' => $expiryDate,
                        'date_received' => now(),
                    ]);
                }

                if ($totalReceived > 0) {
                    // Update purchased product received quantity
                    $purchasedProduct->update([
                        'received_quantity' => $totalReceived + $purchasedProduct->received_quantity,
                    ]);



                    // Audit log for lot-based products
                    $auditService = app(\App\Services\PurchaseOrderAuditService::class);
                    $auditService->logProductReceiving(
                        $this->purchaseOrder,
                        $purchasedProduct->product_id,
                        $previouslyReceivedQuantity,
                        $purchasedProduct->received_quantity
                    );

                    // Check if biological product
                    $biological = Product::where('id', $purchasedProduct->product_id)
                        ->whereHas('categories', function ($query) {
                            $query->whereRaw('LOWER(category_name) = ?', ['biological']);
                        })
                        ->first();

                    if ($biological) {
                        $this->biological_products[] = [
                            'product_id' => $biological->id,
                            'product_code' => $biological->product_code,
                            'product_name' => $biological->product_name,
                        ];
                    }
                }
            }

            // Check if purchase order is fully received
            $allProductsReceived = true;
            foreach ($this->purchasedProducts as $product) {
                if ($product->received_quantity < $product->quantity) {
                    $allProductsReceived = false;
                    break;
                }
            }

            // Update purchase order status if all products are fully received
            if ($allProductsReceived && $this->purchaseOrder->status !== 'completed') {
                $this->purchaseOrder->update([
                    'status' => 'completed',
                    'received_date' => now()
                ]);
            } elseif (!$allProductsReceived && $this->purchaseOrder->status === 'ordered') {
                $this->purchaseOrder->update([
                    'status' => 'partial'
                ]);
            }

            $newNote = [
                'user' => auth()->user()->id,
                'notes' => $this->notes ?? null,
                'datetime' => Carbon::now()->toDateTimeString(),
            ];

            $newImages = [];

            if (!empty($imagePaths)) {
                $newImages = [
                    'user' => auth()->user()->id,
                    'images' => $imagePaths,
                    'datetime' => Carbon::now()->toDateTimeString(),
                ];
                //    Log::info('New images array created:', $newImages);
            } else {
                Log::warning('No image paths found — skipping image creation.');
            }

            $existingNotes = $this->purchaseOrder->notes ?? [];
            $existingImages = $this->purchaseOrder->packing_slips ?? [];
            $existingNotes[] = $newNote;
            if (!empty($newImages)) {
                $existingImages[] = $newImages;
            } else {
                Log::warning('Skipped adding empty image array.');
            }
            $this->purchaseOrder->update([
                'notes' => $existingNotes,
                'packing_slips' => $existingImages,
            ]);


            DB::commit();
            $this->receivedQuantities = [];
            $this->productLots = [];
            $this->batchDetails = [];
            $this->notes = '';
            $this->images = [];
            $this->showModal = false;
            $this->purchaseOrder->load('purchasedProducts.product', 'purchasedProducts.unit');
            $this->dispatch('show-notification', 'Products received successfully!', 'success');
            $this->fetchPurchaseData();
            $this->dispatch('close-modal', 'receive_product_model');

        } catch (\Throwable $e) {
            // Only rollback if transaction was started
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            logger('=== END updateReceiveQuantity - ERROR: ' . $e->getMessage() . ' ===');
            $this->dispatch('show-notification', 'Failed to receive products: ' . $e->getMessage(), 'error');
        }
    }
    // private function updateProductStock($productId, $unitId, $quantity, $location)
    // {
    //     try {
    //         logger('inside updateProduct stock function');
    //         logger('product id ' . $productId . ' unit id is ' . $unitId . ' qty is ' . $quantity . ' locaion is ' . $location);
    //         $stockCount = StockCount::firstOrNew([
    //             'product_id' => $productId,
    //             'location_id' => $location,
    //             'organization_id' => auth()->user()->organization_id,
    //         ]);

    //         $convertedQuantity = $this->convertQuantityToBaseUnit($productId, $unitId, $quantity);

    //         $stockCount->on_hand_quantity += $convertedQuantity;
    //         $stockCount->save();

    //         return true;
    //     } catch (\Exception $e) {
    //         \Log::error('Stock update failed: ' . $e->getMessage());
    //         throw new \Exception('Failed to update stock: ' . $e->getMessage());
    //     }
    // }

    // Helper method to handle unit conversions
    // private function convertQuantityToBaseUnit($productId, $unitId, $quantity)
    // {
    //     // Get the product's base unit and conversion
    //     $productUnit = ProductUnit::where('product_id', $productId)
    //         ->where('unit_id', $unitId)
    //         ->first();

    //     if (!$productUnit) {
    //         throw new \Exception('Unit conversion not found for product ' . $productId . 'and unit is ' . $unitId);
    //     }
    //     if ($productUnit->is_base_unit) {
    //         return $quantity;
    //     }
    //     return $quantity * $productUnit->conversion_factor;
    // }

    private function updatePurchaseOrderStatus()
    {
        // Reload fresh relations
        $this->purchaseOrder->load('purchasedProducts');

        // Ignore canceled products
        $activeProducts = $this->purchaseOrder->purchasedProducts
            ->where('product_status', '!=', 'canceled');

        // If all products are canceled
        if ($activeProducts->isEmpty()) {
            $this->purchaseOrder->update(['status' => 'canceled']);
            return;
        }

        // Check received conditions
        $allReceived = $activeProducts->every(function ($product) {
            return $product->received_quantity >= $product->quantity;
        });

        $anyReceived = $activeProducts->some(function ($product) {
            return $product->received_quantity > 0;
        });

        $status = $allReceived
            ? 'completed'      // or 'completed'
            : ($anyReceived ? 'partial' : 'pending');

        $this->purchaseOrder->update(['status' => $status]);
    }


    private function resetForm()
    {
        $this->receivedQuantities = [];
        $this->showModal = false;
    }

    // Invoice and ack preview related functions starts
    public function previewInvoice($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        $result = $this->invoiceService->getPreviewUrl($purchaseOrder);

        if ($result['success']) {
            $this->selectedPurchaseOrder = $result['purchase_order'];
            $this->previewUrl = $result['url'];
            $this->dispatch('open-modal', 'preview_modal');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function previewAck($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        $result = $this->ackServices->getPreviewUrl($purchaseOrder);
        if ($result['success']) {
            $this->selectedPurchaseOrder = $result['purchase_order'];
            $this->previewUrl = $result['url'];
            $this->dispatch('open-modal', 'preview_modal');
        } else {
            session()->flash('error', $result['message']);
        }
    }
    public function previewEdi855($order)
    {
        $this->edi855data = Edi855::where('purchase_order', $order)->get();
        // logger($this->edi855data);
        $this->dispatch('open-modal', 'preview_edi855_modal');
    }

    // public function previewEdi856($order)
    // {
    //     $this->edi856data = Edi856::where('poNumber', $order)->get();
    //     $this->dispatch('open-modal', 'preview_edi856_modal');
    // }

    public function closePreview()
    {
        $this->dispatch('close-modal', 'preview_modal');
    }
    // Invoice and ack preview related functions ends
    // Search PO filter functions starts
    public function clearSearch()
    {
        $this->searchPurchaseOrder = '';
        $this->fetchPurchaseData();
    }
    public function updatedsearchPurchaseOrder()
    {
        $this->fetchPurchaseData();
    }
    public function updatedSelectedLocation()
    {
        $user = auth()->user();
        $role = $user->role;
        if ($role?->hasPermission('all_purchase') || $user->role_id <= 2) {
            $this->fetchPurchaseData();
        }
    }
    // Cancel order
    public function cancelOrderConfirmation($id)
    {
        $order = PurchaseOrder::find($id);
        $this->purchaseOrderId = $id;
        if ($order && $order->status == 1) {
            $this->message = "Order is placed to supplier, do you still want to cancel it?";
        } else {
            $this->message = "Are you sure you want to cancel this order?";
        }
        $this->dispatch('open-modal', 'cancel-po-confirmation');
    }

    public function cancelOrder()
    {
        $order = PurchaseOrder::find($this->purchaseOrderId);
        // If not placed, mark as placed first
        if ($order->is_order_placed == 0) {
            $order->is_order_placed = 1;
        }

        $order->status = 'canceled';
        $order->save();

        // Close modal + reset properties
        $this->dispatch('close-modal', 'cancel-po-confirmation');
        $this->reset(['purchaseOrderId', 'message']);
        $this->fetchPurchaseData();

        return redirect()->route('purchase.index');

    }



    public function render()
    {
        return view('livewire.user.purchase.purchase-component');
    }

    //print biological code , not required for now

    // public function cancelBarcodeModal()
    // {
    //     $this->showBiologicalModal = false;
    //     $this->reset(['biological_products', 'generatedBarcodes', 'showBarcode']);
    //     $this->dispatch('close-modal', 'biological_product_modal');
    // }

    // public function printBiologicalBarcodes()
    // {
    //     if (empty($this->biological_products)) {
    //          $this->dispatch('show-notification','No biological products to generate barcodes for.', 'error');
    //         return;
    //     }

    //     $generator = new BarcodeGeneratorSVG();

    //     // Initialize chart_number property if it doesn't exist
    //     if (!isset($this->chart_number)) {
    //         $this->chart_number = [];
    //     }

    //     foreach ($this->biological_products as $product) {
    //         // Generate product barcode
    //         $this->generatedBarcodes[$product['product_id']] = $generator->getBarcode(
    //             $product['product_code'],
    //                 $generator::TYPE_CODE_128
    //         );

    //         // Generate chart number barcode
    //         $productId = $product['product_id'];
    //         $chartNumber = $this->chart_number[$productId] ?? '';

    //         if (!empty($chartNumber)) {
    //             $chartBarcodeKey = $chartNumber . $productId;
    //             $this->generatedBarcodes[$chartBarcodeKey] = $generator->getBarcode(
    //                 $chartNumber,
    //                     $generator::TYPE_CODE_128
    //             );
    //         }
    //     }

    //     $this->showBarcode = true;
    //     $this->dispatch('printBarcodes');
    // }
}