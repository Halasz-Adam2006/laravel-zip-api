<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iranyitoszamok extends Model
{
    protected $filable = [
        'zip',
        'city',
        'county',
        'district'
    ];
}
