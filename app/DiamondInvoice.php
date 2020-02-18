<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiamondInvoice extends Model
{
    //
    protected $guarded = [];
    public $table = "diamond_invoices";
    protected $primaryKey = 'id';
}
