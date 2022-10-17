<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;

class Status extends Component
{
    // listeners
    protected $listeners = ['success', 'failure'];

    public $total = 0;
    public $success_amount = 0;
    public $failure_amount = 0;

    public function render()
    {
        $this->total = Roster::all()->count();
        $this->success_amount = Roster::where('status', 1)->count();
        $this->failure_amount = Roster::where('status', 2)->count();
        return view('livewire.status');
    }

    public function success()
    {
        $this->success_amount++;
    }

    public function failure()
    {
        $this->failure_amount++;
    }
}
