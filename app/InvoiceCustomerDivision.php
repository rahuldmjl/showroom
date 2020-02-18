<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceCustomerDivision extends Model
{
    protected $guarded = [];
    public $table = 'invoice_customer_division';
    protected $primaryKey = 'id';
}
