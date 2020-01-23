<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoogleProject extends Model
{
    protected $fillable = [
        'name',
        'project_id',
        'service_account_json',
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }
}
