<?php

namespace App\Imports;

use App\Models\Mapping;
use Maatwebsite\Excel\Concerns\ToModel;

class MappingsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // remove the first column
        if($row[0] == null) return;

        return new Mapping([
            'label' => $row[1],
            'mapping' => $row[2],
        ]);
    }
}
