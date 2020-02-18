<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorCharges extends Model
{
    //
    protected $guarded = [];
    public $table = "vendor_charges";
    protected $primaryKey = 'id';
}
