<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\PotentialClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PotentialUsersManager extends Component
{
    public $userIdToDelete = null;
    public $showDeletModal = false;

    #[On('remove-potential-user')]
    public function openDeleteModal($rowId)
    {
        $this->userIdToDelete = $rowId;
        $this->dispatch('open-modal', 'delete-user-modal');
    }

    public function closeDeleteModal()
    {
        $this->dispatch('close-modal', 'delete-user-modal');
        $this->userIdToDelete = null;
    }

    public function confirmDelete()
    {
        if (!$this->userIdToDelete) {
            return;
        }

        try {
            DB::beginTransaction();
            
            $potentialClient = PotentialClient::find($this->userIdToDelete);
            if ($potentialClient) {
                $potentialClient->delete();
                $this->dispatch('show-notification', 'User deleted successfully!', 'success');
                $this->dispatch('pg:eventRefresh-potential-user-wozssd-table');
            } else {
                $this->dispatch('show-notification', 'User not found!', 'error');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-notification', 'Error while deleting user: ' . $e->getMessage(), 'error');
        } finally {
            $this->closeDeleteModal();
        }
    }

    public function render()
    {
        return view('livewire.admin.potential-users-manager');
    }
}