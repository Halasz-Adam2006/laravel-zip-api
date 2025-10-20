<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iranyitoszamok extends Model
{
    use HasFactory;

    // Map to the existing postal_codes table so tests populate the same data the API uses
    protected $table = 'postal_codes';

    protected $fillable = [
        'zip',
        'city',
        'county',
    ];
}
