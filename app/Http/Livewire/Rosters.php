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
    public $rosters = [], $file, $loadData = false, $selectedID = -1;

    public function init()
    {
        $this->loadData = true;
    }

    public function render()
    {
        if($this->loadData == true)
        {
            $this->rosters = Roster::all();
        }
        return view('livewire.rosters');
    }

    public function updatedFile()
    {
        Excel::import(new RostersImport, $this->file->store('temp'));
        $this->rosters = Roster::all();
        if(count($this->rosters))
            $this->dispatchBrowserEvent('draw-datatable');
    }

    public function scrap($id)
    {
        $roster = Roster::find($id);

        $url = $roster->url;

        $this->selectedID = $id;

        $result = Roach::collectSpider(
            EXACTRosterSpider::class, 
            new Overrides(startUrls: [$url]),
        );

        if(empty($result)) {
            $this->emit('failure', ['status' => 'Not found']);
            return;
        }

        $result = $result[0]->all();

        // check status
        if($result['status'] == 'Not found')
        {
            $this->emit('failure', ['status' => 'Not found']);
            return;
        }

        // fix image url
        foreach ($result['athletes'] as $key => $athelte) {
            if($athelte['image_url'] == 'undefined') continue;
            $parse = parse_url($athelte['image_url']);
            if(!array_key_exists('host', $parse)) {
                $parse = parse_url($url);
                $result['athletes'][$key]['image_url'] = "https://" . $parse['host'] . $athelte['image_url'];
            }
        }

        dd($result);
        $this->emit('success', $result);
    }

    public function scrapAll()
    {
        $this->dispatchBrowserEvent('scrap', ['id' => '20083']);
    }
}
