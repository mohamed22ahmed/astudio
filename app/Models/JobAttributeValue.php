<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAttributeValue extends Model
{
    protected $table = 'job_attribute_values';

    protected $fillable = [
        'job_id',
        'attribute_id',
        'value',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
