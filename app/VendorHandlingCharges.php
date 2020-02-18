<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorHandlingCharges extends Model
{
    //
    protected $guarded = [];
    public $table = "vendor_handling_charges";
    protected $primaryKey = 'id';
}
