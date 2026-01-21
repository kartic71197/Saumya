<?php

namespace App\Livewire;

use App\Models\Cycle;
use App\Models\CycleCount;
use Livewire\Component;

class CycleCountIcon extends Component
{
    public $cycleCount = 0;
    public $hasTasks = false;

    protected $listeners = [
        'cycleCountUpdated' => 'updateCycleCount',
        'cycle-created' => 'updateCycleCount',
        'cycle-closed' => 'updateCycleCount',
    ];

    public function mount()
    {
        $this->updateCycleCount();

    }

    public function updateCycleCount()
    {
        $user = auth()->user();

        // Count active cycles for the user's organization

        // $this->cycleCount = CycleCount::where('user_id', $user->id)
        //     // ->where('user_id', $user->id)
        //     ->where('status', 'pending')
        //     ->whereHas('cycle', function($query) {
        //         $query->where('schedule_date', '<=', today()->format('Y-m-d'))
        //               ->where('status', 'pending');
        //     })
        //     ->distinct('cycle_id')
        //     ->count();

        $this->cycleCount = Cycle::where('status', 'pending')
    // ->whereDate('schedule_date', '<=', today())
    ->whereIn('id', function($q) use ($user) {
        $q->select('cycle_id')
          ->from('cycle_counts')
          ->where('user_id', $user->id)
          ->where('status', 'pending'); 
    })
    ->count();

        $this->hasTasks = $this->cycleCount > 0;
    }

    public function goToCycles()
    {
        if (!$this->hasTasks) {
            $this->dispatch('show-notification', 'You do not have any assigned cycle tasks.', 'error');
            return;
        }
    return redirect()->route('cycles.user-tasks');

    }

    public function render()
    {
        return view('livewire.cycle-count-icon', [
            'hasTasks' => $this->hasTasks,
            'cycleCount' => $this->cycleCount,
        ]);
    }
}