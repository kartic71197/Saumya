<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlaggedPo extends Model
{
    protected $table = 'flagged_pos';
    protected $fillable = ['id', 'purchase_order', 'created_at', 'email_sent', 'is_inbound_save'];
}
