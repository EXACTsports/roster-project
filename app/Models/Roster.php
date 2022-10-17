<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    // status: 0 - pending 1 - success 2 - failure
    protected $fillable = ['university', 'url', 'sport', 'status'];
}
