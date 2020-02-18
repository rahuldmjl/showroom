<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceLogs extends Model
{
    protected $guarded = [];
    public $table = 'invoice_logs';
    protected $primaryKey = 'id';
}
