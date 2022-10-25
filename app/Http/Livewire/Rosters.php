<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;
use App\Models\Athlete;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RostersImport;
use App\Spiders\EXACTRosterSpider;
use App\Spiders\OpendorseLinkSpider;
use App\Spiders\OpendorseDetailSpider;
use App\Spiders\GoogleTwitterSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Rosters extends Component
{
    use WithFileUploads;

    public $rosters = [];
    public $file;
    public $loadData = false;
    public $selectedAthletes = [];
    public $selectedRoster = null;
    public $first_id = -1;
    public $end_id = -1;

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

        if($roster == null) {
            dd("End");
        }

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
            $new->height = $athlete['height'];
            $new->high_school = $athlete['high_school'];
            $new->extra = json_encode([]);

            $new->save();

            // scrap social links
            $this->googleScrap($roster->university, $roster->sport, $new->id);
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
        $this->end_id = $this->first_id + 50;
        $this->dispatchBrowserEvent('scrap', ['id' => $this->first_id]);
    }

    public function view($id)
    {
        $this->selectedAthletes = Athlete::where('roster_id', $id)->get();
        $this->selectedRoster = Roster::find($id);
        $this->dispatchBrowserEvent('athelete');
    }

    public function scrapOpendorse($athlete_id)
    {   
        $athlete = Athlete::find($athlete_id);
        $url = "https://www.google.com/search?q=opendorse+" . str_replace(" ", "+", $athlete->name);

        $result = Roach::collectSpider(
            OpendorseLinkSpider::class, 
            new Overrides(startUrls: [$url]),
        );

        if(empty($result)) {
            dd("empty");
        }

        $result = $result[0]->all();
        $isFound = false;
        $opendorseURL = "";

        foreach ($result["names"] as $key => $name) {
            $percent = 0;
            similar_text($name, $athlete->name, $percent);

            if($percent > 90) {
                $isFound = true;
                $opendorseURL = $result["urls"][$key];
                break;
            }
        }
        
        // dd($isFound, $opendorseURL);

        // detect detail
        if($isFound) {
            $result = Roach::collectSpider(
                OpendorseDetailSpider::class, 
                new Overrides(startUrls: [$opendorseURL]),
            );
        } else {
            dd("Not found!");
        }
    }

    public function googleScrap($university, $sport, $athlete_id)
    {
        $athlete = Athlete::find($athlete_id);
        $name = $athlete->name;

        // Scrap twitter id
        $twitter_url = "https://www.google.com/search?q=twitter+" . str_replace(" ", "+", $athlete->name) . "+" . str_replace(" ", "+", $university) . "+" . trim(explode("(", $sport)[0]) . "'";
    
        $result = Roach::collectSpider(
            GoogleTwitterSpider::class, 
            new Overrides(startUrls: [$twitter_url]),
        );

        if(empty($result)) {
            return;
        }

        $result = $result[0]->all();

        $isTwitterFound = false;
        $twitter = null;

        if(empty($result)) {
            return;
        }

        foreach ($result as $key => $candidate) {
            // check similarity
            $isMatchName = false;
            $isMatchUniversity = false;
            $isMatchSport = false;
            // --- check name
            $percent_name = 0;
            $percent_twitter_id = 0;
            
            similar_text(strtolower($candidate["name"]), strtolower($name), $percent_name);
            similar_text(trim(strtolower($candidate["twitter_id"]), "@"), strtolower($name), $percent_twitter_id);

            if($percent_name > 90 || $percent_twitter_id > 80) {
                $isMatchName = true;
            }

            // --- check university
            $abbreviation = preg_replace("/[a-z& ]/", "", $university);
            $university = strtolower($university);
            $university = str_replace("university", "", $university);
            $university = str_replace("college", "", $university);
            $university = trim($university);
            
            if(strpos(strtolower($candidate["description"]), $university) != false || strpos($candidate["description"], $abbreviation) != false) {
                $isMatchUniversity = true;
            }

            // --- check sport
            $sport = strtolower(trim(explode("(", $sport)[0]));
            if(strpos(strtolower($candidate["description"]), $sport) != false) {
                $isMatchSport = true;
            }

            // check
            if($isMatchName && ($isMatchUniversity || $isMatchSport)) {
                $isTwitterFound = true;
                $twitter = $candidate["twitter_id"];
                break;
            }
        }

        if($isTwitterFound) {
            // dd("Twitter found", $twitter);
            $athlete->twitter = $twitter;

            $athlete->save();
        } else {
            // dd("Not found");
            return;
        }
    }

    public function test()
    {
    }
}
