<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Status extends Component
{
    // listeners
    protected $listeners = ['success', 'failure'];

    public $success_percent = 20;
    public $failure_percent = 10;

    public function render()
    {
        return view('livewire.status');
    }

    public function success()
    {
        $this->success_percent++;
    }

    public function failure()
    {
        $this->failure_percent++;
    }
}
