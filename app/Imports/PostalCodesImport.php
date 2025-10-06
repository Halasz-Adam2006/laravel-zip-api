<?php

namespace App\Imports;

use App\Models\PostalCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PostalCodesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new PostalCode([
            'iranyitoszam' => $row['Postal Code'],
            'telepules'    => $row['Place Name'],
            'megye'        => $row['County'] ?? null,
        ]);

        
        
    }

}
