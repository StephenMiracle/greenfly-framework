<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Content extends Model
{
    protected $fillable = ['name', 'type_id'];

    public function versions()
    {
        return $this->hasMany('Greenfly\Modules\Content\Models\Version');
    }

    public function type()
    {
        return $this->belongsTo('Greenfly\Modules\Content\Models\Type');
    }

    public function taxonomies()
    {
        return $this->hasMany('Greenfly\Modules\Content\Models\Taxonomy');
    }

}
