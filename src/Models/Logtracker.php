<?php

namespace Obd\Logtracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logtracker extends Model
{
    use HasFactory;
    protected $table = 'logtrackers';

    public $timestamps = false;
    protected $casts = [
        'log_date' => 'datetime',
        'data' => 'array',
        'new_data' => 'array',
    ];
    protected $appends = ['dateHumanize'];

    private $userInstance = "\App\Models\User";

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $userInstance = config('logtracker.user_model', "\App\Models\User");
        if(!empty($userInstance)) $this->userInstance = $userInstance;
    }

    public function getDateHumanizeAttribute()
    {
        return $this->log_date ? $this->log_date->diffForHumans() : '—';
    }

    public function user()
    {
        return $this->belongsTo($this->userInstance);
    }

}
