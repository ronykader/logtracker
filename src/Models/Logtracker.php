<?php

namespace Obd\Logtracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logtracker extends Model
{
    use HasFactory;
    protected $table = 'logtrackers';

    public $timestamps = false;
    public $dates = ['log_date'];
    protected $appends = ['dateHumanize','json_data'];

    private $userInstance = "\App\Models\User";

    public function __construct() {
        $userInstance = ''; // Will be dynamic for package
        if(!empty($userInstance)) $this->userInstance = $userInstance;
    }

    public function getDateHumanizeAttribute()
    {
        return $this->log_date->diffForHumans();
    }

    public function getJsonDataAttribute()
    {
        return json_decode($this->data,true);
    }

    public function user()
    {
        return $this->belongsTo($this->userInstance);
    }

}
