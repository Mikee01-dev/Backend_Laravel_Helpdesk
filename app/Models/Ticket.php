<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'title',
        'description',
        'attachment',
        'status',
        'priority',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class)->latest();
    }
};