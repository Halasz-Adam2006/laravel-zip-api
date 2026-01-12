<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    use HasFactory;

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
