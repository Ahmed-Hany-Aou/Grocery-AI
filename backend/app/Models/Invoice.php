<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'type', 'supplier_name', 
        'total_amount', 'status', 'image_url', 'processed_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
