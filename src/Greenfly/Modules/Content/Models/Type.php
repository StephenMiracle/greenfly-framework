<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Type extends Model
{
    protected $fillable = ['name', 'data'];

    public function content()
    {
        return $this->hasMany('Greenfly\Modules\Content\Models\Type\Content');
    }
}