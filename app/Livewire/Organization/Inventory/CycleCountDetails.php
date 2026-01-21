<?php

namespace App\Livewire\Organization\Inventory;

use Livewire\Component;
use App\Models\Cycle;
use App\Models\User;
use App\Models\CycleCount;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CycleCountDetails extends Component
{
    public Cycle $cycle;
    public $cycleCounts = [];

    // Modal states
    public $rejectModalOpen = false;
    public $deleteModalOpen = false;

    public $selectedCategories = [];
    public $categories = [];
    public $confirmCycleName = '';
    public $selectedUser = '';
    public $usersByCycleOrganization = [];
    public $selectedAction = '';
    public $productCodeConfirm = '';
    public $selectedProductId = null;
    public $users = [];
    public $productName;
    public $productCode;
    public $validationError;
    public $selectedCycleId;

    // Array to hold updated quantities for cycle counts
    public $stats = [];
    protected StockService $stockService;
    protected $listeners = ['openCycleActionModal' => 'openCycleActionModal'];


    public function boot(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function mount(Cycle $cycle)
    {
        $user = Auth::user();

        if ((int) $user->role_id !== 2) {
            $this->dispatch(
                'show-notification',
                'You do not have permission to view this page!',
                'error'
            );
            return redirect()->route('dashboard');
        }

        // Load cycle with related counts, products, categories, and users
        $this->cycle = $cycle->load([
            'cycleCounts.product.category',
            'cycleCounts.user'
        ]);

        // Initialize cycleCounts array for inputs
        foreach ($this->cycle->cycleCounts as $count) {

            Log::info('Cycle Count Loaded', [
                'id' => $count->id,
                'product' => $count->product->product_name ?? 'N/A',
                'expected_qty' => $count->expected_qty,
                'counted_qty' => $count->counted_qty,
                'admin_updated_qty' => $count->admin_updated_qty
            ]);
            $this->cycleCounts[$count->id] = [
                'updated_qty' => $count->admin_updated_qty ?? null
            ];
        }
        $this->calculateStats();
        $this->dispatch('cycle-loaded', cycleId: $cycle->id);

        Log::info('Loaded Cycle Details:', $this->cycle->toArray());

        $this->usersByCycleOrganization = User::where('organization_id', $cycle->organization_id)
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function reassignTask($cycleCountId, $newUserId)
    {
        if (!$newUserId) {
            $this->dispatch('show-notification', 'Please select a valid user.', 'warning');
            return;
        }

        $count = CycleCount::find($cycleCountId);

        if (!$count || $count->status !== 'pending') {
            $this->dispatch('show-notification', 'Cannot reassign completed task.', 'error');
            return;
        }
        // Store the old user ID for logging
        $oldUserId = $count->user_id;


        // Update the user
        $count->user_id = $newUserId;
        $count->save();

        Log::info('Cycle count reassigned', [
            'cycle_count_id' => $cycleCountId,
            'old_user_id' => $oldUserId,
            'new_user_id' => $newUserId,
            'cycle_id' => $count->cycle_id
        ]);

        // Refresh the data
        $this->cycle->refresh();
        $this->cycle->load('cycleCounts.product.category', 'cycleCounts.user');

        $this->dispatch('show-notification', 'Task reassigned successfully.', 'success');
        $this->dispatch('cycleCountListUpdated');
    }

    #[On('openCycleActionModal')]
    public function openCycleActionModal($cycleCountId)
    {
        Log::info('🔹 openCycleActionModal triggered in parent', ['cycleCountId' => $cycleCountId]);

        // Fetch the cycle count with its product details
        $cycleCount = \App\Models\CycleCount::with('product')->find($cycleCountId);

        if (!$cycleCount) {
            Log::warning(' CycleCount not found', ['id' => $cycleCountId]);
            $this->dispatch('show-notification', 'Cycle count not found.', 'error');
            return;
        }

        // Set modal-related properties
        $this->selectedCycleId = $cycleCountId;
        $this->productName = $cycleCount->product->product_name ?? 'N/A'; // Use your correct column name
        $this->productCode = $cycleCount->product->product_code ?? 'N/A';

        // Load organization users only
        $this->users = \App\Models\User::where('organization_id', Auth::user()->organization_id)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        // Reset previous selections
        $this->reset(['selectedAction', 'selectedUser', 'productCodeConfirm']);

        Log::info('🔹 Dispatching open-modal event for cycle_action_modal', [
            'productName' => $this->productName,
            'productCode' => $this->productCode,
            'users_count' => $this->users->count()
        ]);

        // Finally open the modal
        $this->dispatch('open-modal', 'cycle_action_modal');
    }


    public function closeActionModal()
    {
        Log::info('🔹 Closing cycle_action_modal');
        // Send the event in the format your modal expects (just the name string)
        $this->dispatch('close-modal', 'cycle_action_modal');
    }
    public function performCycleAction()
    {
        Log::info('▶️ performCycleAction triggered', [
            'selectedAction' => $this->selectedAction,
            'cycleId' => $this->selectedCycleId,
            'enteredCode' => $this->productCodeConfirm,
            'selectedUser' => $this->selectedUser,
        ]);

        $cycle = \App\Models\CycleCount::with('product')->find($this->selectedCycleId);

        if (!$cycle) {
            Log::warning(' Cycle count not found', ['id' => $this->selectedCycleId]);
            $this->addError('productCodeConfirm', 'Cycle count not found.');
            return;
        }

        $actualCode = $cycle->product->product_code ?? null;

        // 🔹 Validate product code for reset/reject
        if (in_array($this->selectedAction, ['reset', 'reject'])) {
            if ($this->productCodeConfirm != $actualCode) {
                Log::warning(' Entered product code does not match', [
                    'entered' => $this->productCodeConfirm,
                    'actual' => $actualCode,
                ]);
                $this->addError('productCodeConfirm', 'Entered product code does not match.');
                return;
            }
        }

        switch ($this->selectedAction) {
            case 'reset':
                // Already reset state check
                if (
                    ($cycle->variance == 0 || $cycle->variance === null) &&
                    ($cycle->counted_qty == 0 || $cycle->counted_qty === null) &&
                    $cycle->status === 'pending'
                ) {
                    Log::info('ℹ Product already in reset state', ['id' => $cycle->id]);
                    $this->dispatch('show-notification', 'Product is already in reset state.', 'warning');
                    $this->dispatch('close-modal', 'cycle_action_modal');
                    return;
                }

                $cycle->update([
                    'variance' => 0,
                    'counted_qty' => 0,
                    'status' => 'pending',
                ]);

                Log::info('Product reset successfully', ['cycle_id' => $cycle->id]);
                $this->dispatch('show-notification', 'Product reset successfully!', 'success');
                break;

            case 'reject':
                $cycle->delete();
                Log::info('Product rejected and deleted', ['cycle_id' => $this->selectedCycleId]);
                $this->dispatch('show-notification', 'Product removed successfully!', 'success');
                break;

            case 'reassign':
                if (!$this->selectedUser) {
                    $this->addError('selectedUser', 'Please select a user.');
                    return;
                }

                $cycle->update(['user_id' => $this->selectedUser]);
                Log::info('👤 Product reassigned successfully', [
                    'cycle_id' => $this->selectedCycleId,
                    'new_user_id' => $this->selectedUser
                ]);

                $this->dispatch('show-notification', 'Product reassigned successfully!', 'success');
                break;

            default:
                $this->addError('selectedAction', 'Please choose a valid action.');
                return;
        }

        //Close modal and refresh grid
        $this->dispatch('close-modal', 'cycle_action_modal');
        $this->dispatch('cycleCountListUpdated');
    }

    private function calculateStats()
    {
        $user = Auth::user();
        $cycleCounts = $this->cycle->cycleCounts;

        $totalTasks = $cycleCounts->count();
        $completedTasks = $cycleCounts->where('status', 'completed')->count();
        $pendingTasks = $totalTasks - $completedTasks;

        $this->stats = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'completion_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
            'all_completed' => $pendingTasks === 0 && $totalTasks > 0
        ];
    }

    public function updateCount($countId)
    {
        $count = CycleCount::find($countId);
        if (!$count)
            return;

        $inputValue = $this->cycleCounts[$countId]['updated_qty'] ?? null;

        // Convert to integer, but keep existing value if input is empty/null
        if ($inputValue === null || $inputValue === '') {
            // If input is empty, keep the current counted_qty
            $adminQty = $count->counted_qty;
        } else {
            // If input has value, convert to integer
            $adminQty = (int) $inputValue;
        }

        // Update counted_qty, admin_updated_qty, and user_id
        $count->admin_updated_qty = $adminQty;
        $count->counted_qty = $adminQty;
        $count->user_id = Auth::id();

        // Optionally recalc variance
        $count->variance = ($count->counted_qty !== null && $count->expected_qty !== null)
            ? $count->counted_qty - $count->expected_qty
            : null;
        $count->status = $adminQty !== null ? 'completed' : $count->status;

        // Log after update
        Log::info('After Admin Update', [
            'count_id' => $count->id,
            'product' => $count->product->product_name ?? 'N/A',
            'expected_qty' => $count->expected_qty,
            'counted_qty' => $count->counted_qty,
            'admin_updated_qty' => $count->admin_updated_qty,
            'user_id' => $count->user_id,
            'variance' => $count->variance
        ]);

        $count->save();

        $this->cycleCounts[$countId]['updated_qty'] = $adminQty;

        $this->cycle->refresh();
        $this->cycle->load('cycleCounts.product.category', 'cycleCounts.user');


        $this->calculateStats();

        $this->dispatch('show-notification', 'Cycle count has updated successfully!', 'success');

        // Refresh cycle counts
        $this->cycle->load('cycleCounts.product.category', 'cycleCounts.user');
    }

    public function resetCount($countId)
    {
        $count = CycleCount::find($countId);
        if (!$count)
            return;

        // Reset admin updates and counted quantity
        $count->admin_updated_qty = null;
        $count->counted_qty = null;
        $count->variance = null;
        $count->status = 'pending';

        $count->save();

        // Reset frontend input
        $this->cycleCounts[$countId]['updated_qty'] = null;

        $this->cycle->refresh();
        $this->cycle->load('cycleCounts.product.category', 'cycleCounts.user');

        // Recalculate stats live
        $this->calculateStats();

        $this->dispatch(
            'show-notification',
            'Cycle count has been reset for user correction!',
            'success'
        );

        // Refresh cycle counts
        $this->cycle->load('cycleCounts.product.category', 'cycleCounts.user');
    }



    public function closeCycle()
    {
        // Check if all tasks are completed
        if (!$this->stats['all_completed']) {
            $this->dispatch(
                'show-notification',
                'Cannot close cycle. There are pending tasks!',
                'error'
            );
            return;
        }

        try {
            foreach ($this->cycle->cycleCounts as $count) {
                if (!$count->product) {
                    Log::warning("CycleCount {$count->id} has no product linked.");
                    continue;
                }

                $this->stockService->updateStock(
                    $count->product->id,
                    $this->cycle->location_id,
                    [
                        'quantity' => $count->counted_qty ?? 0,
                        'unit' => $count->product->unit->first()->unit->id ?? null,
                        'batch_number' => $count->batch_number ?? null,
                        'expiry_date' => $count->expiry_date ?? null,
                    ]
                );
            }

            // Update cycle status to closed
            $this->cycle->update([
                'status' => 'closed',
                'ended_at' => now()
            ]);

            $this->cycle->refresh();
            $this->calculateStats();

            $this->dispatch(
                'show-notification',
                "Cycle {$this->cycle->cycle_name} approved successfully!",
                'success'
            );

        } catch (\Exception $e) {
            Log::error("Cycle close failed: " . $e->getMessage());
            $this->dispatch(
                'show-notification',
                'Failed to close cycle: ' . $e->getMessage(),
                'error'
            );
        }
    }

    public function openDeleteModal($id)
    {
        $this->selectedCycleId = $id;
        $this->cycle = \App\Models\Cycle::find($id);
        $this->confirmCycleName = '';
        $this->dispatch('open-modal', 'delete_cycle_modal');
    }


    public function closeDeleteModal()
    {
        $this->dispatch('close-modal', 'delete_cycle_modal');
        $this->confirmCycleName = '';
        $this->resetErrorBag();
    }

    public function openRejectModal()
    {
        $this->categories = $this->cycle->cycleCounts
            ->filter(fn($count) => $count->product && $count->product->category)
            ->map(fn($count) => [
                'id' => $count->product->category->id,
                'name' => $count->product->category->category_name,
            ])
            ->unique('id')
            ->values()
            ->toArray();

        $this->dispatch('open-modal', 'reset_categories_modal');
    }

    public function closeRejectModal()
    {
        $this->dispatch('close-modal', 'reset_categories_modal');
        $this->selectedCategories = [];
    }

    public function rejectSelected()
    {
        Log::info('rejectSelected method called', [
            'selected_categories' => $this->selectedCategories,
            'count' => count($this->selectedCategories)
        ]);

        if (empty($this->selectedCategories)) {
            $this->dispatch('show-notification', 'Please select at least one category!', 'error');
            return;
        }

        // Convert selected categories to integers to ensure proper comparison
        $selectedCategoryIds = array_map('intval', $this->selectedCategories);

        Log::info('Processing categories', ['category_ids' => $selectedCategoryIds]);

        // Get all counts for products under selected categories
        $counts = $this->cycle->cycleCounts()
            ->whereHas('product.category', function ($q) use ($selectedCategoryIds) {
                $q->whereIn('id', $selectedCategoryIds);
            })
            ->get();

        Log::info('Found cycle counts to reset', ['count' => $counts->count()]);

        if ($counts->count() === 0) {
            $this->dispatch('show-notification', 'No items found for selected categories!', 'warning');
            return;
        }

        foreach ($counts as $count) {
            $count->update([
                'admin_updated_qty' => null,
                'counted_qty' => null,
                'variance' => null,
                'status' => 'pending'
            ]);

            Log::info('Reset cycle count', [
                'cycle_count_id' => $count->id,
                'product_id' => $count->product_id,
                'category_id' => $count->product->category->id ?? null
            ]);
        }

        // Refresh cycle + stats
        $this->cycle->refresh();
        $this->calculateStats();

        $this->dispatch('show-notification', 'Selected categories reset successfully. Users need to recount!', 'success');

        $this->closeRejectModal();
        $this->selectedCategories = [];

        // Dispatch events to refresh any related components
        $this->dispatch('pg:eventRefresh-cycle-count-list-ybwibg-table');
        $this->dispatch('cycleCountListUpdated');
        $this->dispatch('cycleCountUpdated');

        Log::info('Reset categories completed successfully');
    }

    public function deleteCycle()
    {
        $enteredName = trim($this->confirmCycleName ?? '');
        $cycleName = trim($this->cycle->cycle_name ?? '');

        if ($enteredName !== $cycleName) {
            $this->addError('confirmCycleName', 'Cycle name does not match.');
            return; // stops execution, error shows below input
        }

        try {
            $this->cycle->delete();
            $this->closeDeleteModal();
            $this->dispatch('show-notification', 'Cycle deleted successfully.', 'success');
            return redirect()->route('organization.settings.cycle_counts');
        } catch (\Exception $e) {
            $this->dispatch('show-notification', 'Failed to delete cycle: ' . $e->getMessage(), 'error');
        }
    }


    public function render()
    {
        return view('livewire.organization.inventory.cycle-count-details')
            ->layout('layouts.app');
    }
}