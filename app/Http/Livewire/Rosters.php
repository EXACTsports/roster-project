<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;
use App\Models\Athlete;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RostersImport;
use App\Spiders\EXACTRosterSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Rosters extends Component
{
    use WithFileUploads;
    public $rosters = [], $file, $loadData = false, $selectedAthletes = [], $selectedRoster = null, $first_id = -1, $end_id = -1;

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

        // get the next id
        $next = Roster::where('id', '>', $id)->orderBy('id')->first();
        $next_id = -1;
        if($next) {
            $next_id = $next->id;
        }

        // check status(if status is not zero, done)
        if($roster->status != 0) {
            if($next_id != -1) {
                $this->dispatchBrowserEvent('scrap', ['id' => $next_id]);
            }
            return;
        }

        if($next_id == $this->end_id) {
            dd('Finish scrapping 10 rosters!');
            return;
        }

        $url = $roster->url;

        $result = Roach::collectSpider(
            EXACTRosterSpider::class, 
            new Overrides(startUrls: [$url]),
        );

        if(empty($result)) {
            $this->emit('failure', ['status' => 'Not found']);

            $roster = Roster::find($id);
            $roster->status = 2;
            $roster->save();

            if($next_id != -1) {
                $this->dispatchBrowserEvent('scrap', ['id' => $next_id]);
            }
            return;
        }

        $result = $result[0]->all();

        // check status
        if($result['status'] == 'Not found')
        {
            $this->emit('failure', ['status' => 'Not found']);
            $roster = Roster::find($id);
            $roster->status = 2;
            $roster->save();

            if($next_id != -1) {
                $this->dispatchBrowserEvent('scrap', ['id' => $next_id]);
            }
            return;
        }

        // fix image url
        foreach ($result['athletes'] as $key => $athlete) {
            if($athlete['image_url'] == 'undefined') continue;
            $parse = parse_url($athlete['image_url']);
            if(!array_key_exists('host', $parse)) {
                $parse = parse_url($url);
                $result['athletes'][$key]['image_url'] = "https://" . $parse['host'] . $athlete['image_url'];
            }
        }

        $this->emit('success', ['status' => $result['status']]);

        // save athlete info
        foreach ($result['athletes'] as $key => $athlete) {
            $new = new Athlete;

            $new->roster_id = $id;
            $new->name = $athlete['name'];
            $new->image_url = $athlete['image_url'];
            $new->position = $athlete['position'];
            $new->year = $athlete['year'];
            $new->home_town = $athlete['home_town'];
            $new->extra = json_encode([]);

            $new->save();
        }

        // update status of this roster
        $roster = Roster::find($id);
        $roster->status = 1;
        $roster->save();

        if($next_id != -1) {
            $this->dispatchBrowserEvent('scrap', ['id' => $next_id]);
        }
    }

    public function scrapAll()
    {
        $this->first_id = Roster::orderBy('id')->where('status', '==', 0)->get()->first()->id;
        $this->end_id = $this->first_id + 11;
        $this->dispatchBrowserEvent('scrap', ['id' => $this->first_id]);
    }

    public function view($id)
    {
        $this->selectedAthletes = Athlete::where('roster_id', $id)->get();
        $this->selectedRoster = Roster::find($id);
        $this->dispatchBrowserEvent('athelete');
    }
}
