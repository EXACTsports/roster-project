<?php

namespace App\Imports;

use App\Models\Roster;
use Maatwebsite\Excel\Concerns\ToModel;

class RostersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // remove the first column
        if($row[6] == 'URL_to_Scrape' || $row[2] == null) return;

        return new Roster([
            'university' => $row[2],
            'url' => $row[6],
            'sport' => $row[5],
            'status' => 0
        ]);
    }
}
