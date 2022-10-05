<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RostersImport;

class Rosters extends Component
{
    use WithFileUploads;
    public $rosters = [], $file, $loadData = false, $loading = false;

    public function init()
    {
        $this->loadData = true;
    }

    public function render()
    {
        if($this->loadData == true)
        {
            $this->rosters = Roster::all();
            if(count($this->rosters))
                $this->dispatchBrowserEvent('draw-datatable');
        }
        return view('livewire.rosters');
    }

    public function updatedFile()
    {
        $this->loading = true;
        Excel::import(new RostersImport, $this->file->store('temp'));
        $this->rosters = Roster::all();
        if(count($this->rosters))
            $this->dispatchBrowserEvent('draw-datatable');
        $this->loading = false;
    }
}
