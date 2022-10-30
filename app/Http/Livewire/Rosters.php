<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Roster;
use App\Models\Athlete;
use App\Models\Mapping;
use App\Models\Analysis;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RostersImport;
use App\Imports\MappingsImport;
use App\Spiders\EXACTRosterSpider;
use App\Spiders\OpendorseLinkSpider;
use App\Spiders\OpendorseDetailSpider;
use App\Spiders\GoogleTwitterSpider;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Rosters extends Component
{
    use WithFileUploads;

    public $rosters = []; // roster list
    public $file; // external roster excel file
    public $loadData = false; // check loaded
    public $selectedAthletes = []; // selected athletes data by each roster
    public $selectedRoster = null; // selected roster id
    public $first_id = -1; // start roster id to start scrap
    public $end_id = -1; // end roster id to end scrap
    public $mappings_cnt = 0; // mappings amount
    public $mappings_file; // external mappings excel file

    /**
        *  init
        * 
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function init()
    {
        // set loaded true
        $this->loadData = true;
    }

    /**
        *  render
        * 
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return view roster
    */
    public function render()
    {
        // check load state and get all rosters
        if($this->loadData == true)
        {
            $this->rosters = Roster::all();
        }
        return view('livewire.rosters');
    }

    /**
        *  updatedFile - once someone select file in the form, will get called automatically
        * 
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function updatedFile()
    {
        // imports roster data from external excel file
        Excel::import(new RostersImport, $this->file->store('temp'));
        $this->rosters = Roster::all();
        if(count($this->rosters))
            $this->dispatchBrowserEvent('draw-datatable');
    }

    /**
        *  updatedFile - once someone select file in the form, will get called automatically
        * 
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function updatedMappingsFile()
    {
        // imports mappings data from external excel file
        Excel::import(new MappingsImport, $this->mappings_file->store('mapping_temp'));
        $this->mappings_cnt = Mapping::all()->count();
    }

    /**
        *  scrap - scrap roster one by one, also scrap social links of each athlete. And save data in DB
        * 
        *  @param roster_id $id
        *
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
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

        // save athlete info and analysis info
        $analysis = new Analysis;

        $freshman = 0;
        $sophomores = 0;
        $juniors = 0;
        $seniors = 0;
        $total_height = 0.0;
        $total_members = 0;
        $number_by_state = [];
        $number_by_position = [];

        foreach ($result['athletes'] as $key => $athlete) {
            // seach mappings and fix the values by thme
            // --- position
            $label_position = "sport-" . strtolower(trim(explode("(", $roster->sport)[0])) . "-position-" . $athlete['position'];
            $mapping_position = Mapping::where('label', $label_position)->firstOr(function() {
                return null;
            });
            $mapping_position = $mapping_position == null ? $athlete['position'] : $mapping_position->mapping;

            if(key_exists($mapping_position, $number_by_position)) {
                $number_by_position[$mapping_position]++;
            } else {
                $number_by_position[$mapping_position] = 1;
            }

            // --- home town
            $explode_homes = explode(",", $athlete['home_town']);
            $label_hometown = "state-" . str_replace(" ", "-", trim($explode_homes[count($explode_homes) - 1]));
            $mapping_hometown = Mapping::where('label', $label_hometown)->firstOr(function() {
                return null;
            });
            $mapping_hometown = $mapping_hometown == null ? $athlete['home_town'] : $mapping_hometown->mapping;

            if(key_exists($mapping_hometown, $number_by_state)) {
                $number_by_state[$mapping_hometown]++;
            } else {
                $number_by_state[$mapping_hometown] = 1;
            }

            // --- year
            $label_year = "class-" . $athlete['year'];
            $mapping_year = Mapping::where('label', $label_year)->firstOr(function() {
                return null;
            });
            $mapping_year = $mapping_year == null ? $athlete['year'] : $mapping_year->mapping;

            // --- height - 6'23" => 6-23, 4-12
            $mapping_height = str_replace("'", "-", trim($athlete['height'], '"'));

            // --- hometown city
            $mapping_city = $explode_homes[0];

            // --- profile link
            $parse = parse_url($url);
            $mapping_profile_link = "https://" . $parse["host"] . $athlete["profile_link"];

            $new = new Athlete;

            $new->roster_id = $id;
            $new->name = $athlete['name'];
            $new->image_url = $athlete['image_url'];
            $new->position = $mapping_position;
            $new->year = $mapping_year;
            $new->home_town = $mapping_hometown;
            $new->height = $mapping_height; 
            $new->high_school = $athlete['high_school'];
            $new->city = $mapping_city;
            $new->previous_school = $athlete['previous_school'];
            $new->profile_link = $mapping_profile_link;
            $new->jersey = $athlete["jersey"];
            $new->extra = json_encode([]);

            $new->save();

            // analysis data
            // --- year
            switch($mapping_year) {
                case "Freshman": $freshman++; break;
                case "Sophomore": $sophomores++; break;
                case "Senior": $seniors++; break;
                case "Junior": $juniors++; break;
            }

            // --- increase total height with inches
            $explodes_height = explode("-", $mapping_height);
            $total_height += $explodes_height[0] * 12 + $explodes_height[1];

            // --- increase total members
            $total_members++;

            // scrap social links
            // --- scrap twitter
            // $this->googleScrap($roster->university, $roster->sport, $new->id);
            // --- scrap opendorse
            // $this->scrapOpendorse($new->id);
        }
        $total_feet = intdiv($total_height, 12);
        $remain_inches = $total_height % 12;
        $avg_feet = intdiv($total_feet, $total_members);
        $avg_inches = (($total_feet % $total_members) * 12 + $remain_inches) / $total_members;

        // save analysis data
        $analysis->roster_id = $id;
        $analysis->freshman = $freshman;
        $analysis->sophomores = $sophomores;
        $analysis->seniors = $seniors;
        $analysis->juniors = $juniors;
        $analysis->avg_height = $avg_feet . "-" . $avg_inches;
        $analysis->number_by_state = json_encode($number_by_state);
        $analysis->number_by_position = json_encode($number_by_position);

        dd($analysis);

        $analysis->save();

        // update status of this roster
        $roster = Roster::find($id);
        $roster->status = 1;
        $roster->save();

        if($next_id != -1) {
            $this->dispatchBrowserEvent('scrap', ['id' => $next_id]);
        }
    }

    /**
        *  scrapAll - bootstrap scrapping
        * 
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function scrapAll()
    {
        // check first roster's id to be done
        $this->first_id = Roster::orderBy('id')->where('status', '==', 0)->get()->first()->id;
        $this->end_id = $this->first_id + 5; // TODO: fix the amount of rosters to be done
        $this->dispatchBrowserEvent('scrap', ['id' => $this->first_id]);
    }

    /**
        *  view - View modal which contains athlete data of each roster
        * 
        *  @param roster_id $id
        *
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function view($id)
    {
        $this->selectedAthletes = Athlete::where('roster_id', $id)->get();
        $this->selectedRoster = Roster::find($id);
        $this->dispatchBrowserEvent('athelete');
    }

    /**
        *  view - View modal which contains mappings data
        *
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function viewMappingsModal()
    {
        $this->mappings_cnt = Mapping::all()->count();
        $this->dispatchBrowserEvent('mappings');
    }

    /**
        *  scrapOpendorse - scrap opendorse link of each athlete by google search engine
        * 
        *  @param athlete_id $id
        *
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
    public function scrapOpendorse($athlete_id)
    {   
        $athlete = Athlete::find($athlete_id);
        $url = "https://www.google.com/search?q=opendorse+" . str_replace(" ", "+", $athlete->name);

        $result = Roach::collectSpider(
            OpendorseLinkSpider::class, 
            new Overrides(startUrls: [$url]),
        );

        if(empty($result)) {
            return;
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

        // detect detail
        if($isFound) {
            $athlete->opendorse = $opendorseURL;

            $athlete->save();

            $result = Roach::collectSpider(
                OpendorseDetailSpider::class, 
                new Overrides(startUrls: [$opendorseURL]),
            );

            if(empty($result)) {
                return;
            }

            $result = $result[0]->all();

            foreach ($result["social"] as $key => $social) {
                switch($key) {
                    case "instagram": 
                        if($social)
                            $athlete->instagram = $social;
                        break;
                    case "twitter": 
                        if($social)
                            $athlete->twitter = $social; 
                        break;
                }
            }

            $athlete->save();
        } else {
            return;
        }
    }

    /**
        *  googleScrap - scrap other social links(twitter, instagram) of each athlete by google search engine
        * 
        *  @param university
        *  @param sport
        *  @param athlete
        *
        *  @throws 
        *  @author hardcommitoneself <hardcommitoneself@gmail.com>
        *  @return void
    */
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
                $twitter = substr($candidate["twitter_id"], 1);
                break;
            }
        }

        if($isTwitterFound) {
            // dd("Twitter found", $twitter);
            $athlete->twitter = "https://twitter.com/" . $twitter;

            $athlete->save();
        } else {
            // dd("Not found");
            return;
        }
    }

    public function test()
    {
        $this->scrap(25609);
    }
}
