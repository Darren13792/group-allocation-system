<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function group_students(): HasMany
    {
        return $this->hasMany(GroupStudent::class, 'group_id');
    }

    public function group_supervisors(): HasMany
    {
        return $this->hasMany(GroupSupervisor::class, 'group_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

}
