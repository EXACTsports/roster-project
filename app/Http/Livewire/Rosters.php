<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RostersImport;
use App\Spiders\EXACTRosterSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Rosters extends Component
{
    use WithFileUploads;
    public $rosters = [], $file, $loadData = false, $loading = false, $selectedID = 0;

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

    public function scrap($id)
    {
        $roster = Roster::find($id);

        $url = $roster->url;

        $this->loading = true;
        $this->selectedID = $id;
        $athletes = Roach::collectSpider(
            EXACTRosterSpider::class, 
            new Overrides(startUrls: [$url]),
        )[0]->all();

        // fix image url
        foreach ($athletes as $key => $athelte) {
            $parse = parse_url($athelte['image_url']);
            if(!array_key_exists('host', $parse)) {
                $parse = parse_url($url);
                $athletes[$key]['image_url'] = "https://" . $parse['host'] . $athelte['image_url'];
            }
        }

        dd($athletes);
    }

    public function checkRegex()
    {
    }
}
