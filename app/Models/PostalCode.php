<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    protected $fillable = [
        'zip',
        'city',
        'county_id',
    ];

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}
