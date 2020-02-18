<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MmToSieve extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public $table = 'mm_to_sieve';
    protected $primaryKey = 'id';
}
