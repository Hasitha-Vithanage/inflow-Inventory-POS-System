<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'sale_id',
        'channel',
        'status',
        'error_message',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
