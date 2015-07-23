<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Taxonomy extends Model
{
    protected $fillable = ['name'];

    public function content()
    {
        return $this->hasMany('Greenfly\Modules\Content\Models\Type');
    }

    public function tags()
    {
        return $this->hasMany('Greenfly\Modules\Content\Models\Tag');
    }
}