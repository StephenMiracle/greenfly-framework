<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Version extends Model
{
    protected $fillable = ['data', 'content_id', 'name', 'published_at', 'expired_at'];


    public function content()
    {
        return $this->belongsTo('Greenfly\Modules\Content\Models\Content');
    }

    public function tags()
    {
        return $this->belongsToMany('Greenfly\Modules\Content\Models\Tag');
    }
}