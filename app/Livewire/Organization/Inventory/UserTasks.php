<?php

namespace App\Livewire\Organization\Inventory;

use Livewire\Component;
use App\Models\CycleCount;
use App\Models\Location;
use App\Models\Cycle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class UserTasks extends Component
{
    use WithPagination;
    public $selectedLocation = '';
    public $cycleFilter = '';
    public $taskUpdates = [];
    public $search = '';
    public $statusFilter = '';
    // public $tasks = [];
    public $showConfirmModal = false;
    public $taskToConfirm;
    public $taskCount;
    public $variance = 0;
    public $variancePercentage = 0;
    public $note = '';
    public $perPage = 25;
    public $showFutureCycles = false;

    protected $updatesQueryString = ['search', 'cycleFilter'];
    protected $listeners = [
        'cycle-created' => 'handleCycleCreated',
    ];

    public function handleCycleCreated($cycleId)
    {
        // Optional: set the new cycle as default filter
        $this->cycleFilter = $cycleId;

        // Reload tasks immediately
        $this->resetPage();
    }

    public function mount()
    {
        $userId = Auth::id();

        $latestPendingCycle = Cycle::whereIn('id', function ($q) use ($userId) {
            $q->select('cycle_id')
                ->from('cycle_counts')
                ->where('user_id', $userId);
        })
            ->where('status', 'pending')
            // ->whereDate('schedule_date', '<=', now())
            ->orderBy('cycle_name')
            ->first();

        if ($latestPendingCycle) {
            $this->cycleFilter = $latestPendingCycle->id;
            $this->checkIfFutureCycle();
            
            if (!$this->showFutureCycles) {
            $this->loadTasks();
            }
        } else {

            $this->cycleFilter = '';
            $this->showFutureCycles = false;
            $this->loadTasks();
        }
    }

    public function updatedStatusFilter()
    {
        $this->loadTasks();
    }

    // load tasks whenever filters change
    public function updatedSelectedLocation()
    {
        $this->cycleFilter = '';
        $this->loadTasks();
    }

    public function updatedCycleFilter()
    {
        $this->checkIfFutureCycle();
        // $this->loadTasks();
        $this->resetPage();
        if (!$this->showFutureCycles) {
        $this->loadTasks();
    }
    }

    public function updatedSearch()
    {
        // $this->loadTasks();
        $this->resetPage();
    }

     private function checkIfFutureCycle()
{
    if ($this->cycleFilter) {
        $cycle = Cycle::find($this->cycleFilter);
        if ($cycle) {
            // Check if schedule date is in the future
            $this->showFutureCycles = \Carbon\Carbon::parse($cycle->schedule_date)->isFuture();
        } else {
            $this->showFutureCycles = false;
        }
    } else {
        $this->showFutureCycles = false;
    }
}

    public function loadTasks()
    {
        $userId = Auth::id();

        $query = CycleCount::with('product', 'cycle', 'product.category', 'location')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereHas('cycle', function ($q) {
                $q->whereDate('schedule_date', '<=', now());
            });

        \Log::debug('Search term:', ['search' => $this->search]);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->selectedLocation) {
            $query->whereHas('cycle', function ($q) {
                $q->where('location_id', (int) $this->selectedLocation);
            });
        }

        if ($this->cycleFilter) {
            $query->where('cycle_id', (int) $this->cycleFilter);
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';

            $query->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', $searchTerm)
                    ->orWhere('product_code', 'like', $searchTerm);
            });

            Log::debug('Search applied', [
                'term' => $this->search,
                'sql_term' => $searchTerm
            ]);
        }
        return $query->paginate($this->perPage);

        // // $this->tasks = $query->get();
        // $this->tasks = $query->paginate($this->perPage);

        // Log::debug('Search results', [
        //         'search_term' => $this->search,
        //         'tasks_count' => $this->tasks->count(),
        //         'task_products' => $this->tasks->map(function($task) {
        //             return $task->product ? $task->product->product_name : 'No product';
        //         })->toArray()
        //     ]);

        // $this->taskUpdates = [];
        // foreach ($this->tasks as $task) {
        //        $this->taskUpdates[$task->id] = $task->counted_qty !== null ? intval($task->counted_qty) : '';
        // }


        // \Log::debug('Tasks found:', ['count' => $this->tasks->count()]);
    }

    public function getLocationsProperty()
    {
        $userId = Auth::id();

        return Location::whereIn('id', function ($query) use ($userId) {
            $query->select('location_id')
                ->from('cycles')
                ->whereIn('id', function ($q) use ($userId) {
                    $q->select('cycle_id')
                        ->from('cycle_counts')
                        ->where('user_id', $userId);
                });
        })->get();
    }

    public function getCyclesProperty()
    {
        $userId = Auth::id();

        // Get all cycle_ids from the tasks assigned to this user (pending)
        $cycleIds = CycleCount::where('user_id', $userId)
            ->where('status', 'pending')
            ->pluck('cycle_id')
            ->unique();

        // Fetch cycles for these IDs
        return Cycle::whereIn('id', $cycleIds)
            ->where('status', 'pending')
            // ->whereDate('schedule_date', '<=', now())
            ->orderBy('cycle_name')
            ->get();
    }

    public function getDaysUntilStart($scheduleDate)
    {
        $schedule = Carbon::parse($scheduleDate);
        $today = Carbon::today();
        
        return $today->diffInDays($schedule, false); // false means return negative if past
    }


    public function handleUpdateTask($taskId)
    {
        if ($this->showFutureCycles) {
            $this->dispatch('show-notification', 'Cannot update tasks for future cycles!', 'error');
            return;
        }
        $task = CycleCount::find($taskId);
        $countedInput = $this->taskUpdates[$taskId] ?? null;
        $counted = ($countedInput === '' || $countedInput === null) ? null : intval($countedInput);
        $expected = intval($task->expected_qty ?? 0);

        if ($counted === null || $counted === '') {
            $this->dispatch('show-notification', 'Please enter a counted quantity!', 'error');
            return;
        }

        // If no variation, save immediately
        if ($counted === $expected) {
            $task->counted_qty = $counted;
            $task->variance = 0;
            $task->status = 'completed';
            $task->counted_at = now();
            $task->save();

            $this->dispatch('show-notification', 'Task updated successfully!', 'success');
            // $this->loadTasks();
            $this->dispatch('cycleCountUpdated');
            $this->resetPage();
            return;
        }

        // If variation exists, show modal
        $this->taskToConfirm = $taskId;
        $this->taskCount = $counted;
        $this->variance = $counted - $expected;
        $this->variancePercentage = $expected > 0 ? round(($this->variance / $expected) * 100, 2) : 0;
        $this->note = $task->notes ?? '';
        $this->showConfirmModal = true;
    }


    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->selectedLocation = '';
        $this->cycleFilter = '';
        $this->search = '';
        $this->loadTasks();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->loadTasks(); // optional, reload tasks without search filter
    }

    public function saveConfirmedTask()
    {
        if (!$this->taskToConfirm)
            return;

        $task = CycleCount::find($this->taskToConfirm);
        $task->counted_qty = $this->taskCount;
        $task->variance = $this->variance;
        $task->notes = $this->note ?? null;
        $task->status = 'completed';
        $task->counted_at = now();
        $task->save();

        $this->showConfirmModal = false;
        $this->taskToConfirm = null;
        $this->note = '';

        // $this->loadTasks();
        $this->resetPage();
        $this->dispatch('cycleCountUpdated');
        $this->dispatch('show-notification', 'Task updated successfully!', 'success');
    }

    public function render()
    {
        $this->checkIfFutureCycle();
         
        $tasks = collect([]);
        
        // Only load tasks if NOT viewing a future cycle
        if (!$this->showFutureCycles) {
            $tasks = $this->loadTasks();
        }
       
        $this->taskUpdates = [];
        foreach ($tasks as $task) {
            $this->taskUpdates[$task->id] = $task->counted_qty !== null ? intval($task->counted_qty) : '';
        }

        return view('livewire.organization.inventory.user-tasks', [
            'locations' => $this->locations,
            'cycles' => $this->cycles,
            'tasks' => $tasks,
        ])->layout('layouts.app');
    }
}