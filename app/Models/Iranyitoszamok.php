<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iranyitoszamok extends Model
{
    protected $filable = [
        'zip',
        'city',
        'city_normalized',
        'county',
        'district'
    ];
}
