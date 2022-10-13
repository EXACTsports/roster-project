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

        $result = Roach::collectSpider(
            EXACTRosterSpider::class, 
            new Overrides(startUrls: [$url]),
        );

        if(empty($result)) {
            dd('empty');
        }

        $result = $result[0]->all();

        // check status
        if($result['status'] == 'Not found')
        {
            dd($result);
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
    }

    public function checkRegex()
    {
        // $pattern = '/(.*?year.?)$|(.*?yr.?)$|(.*?cl.?)$|(.*?class.?)$/i';

        // dd(preg_match($pattern, 'cl.'));

        dd(is_numeric('1'));

        // $str = "Pos.: GK";

        // dd(count(explode(":", $str)));
    }

    public function scrapAll()
    {
        dd('all');
    }
}
