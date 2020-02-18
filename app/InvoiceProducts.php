<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceProducts extends Model
{
    protected $guarded = [];
    public $table = 'invoice_products';
    protected $primaryKey = 'id';
}
