<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'taxonomy_id'];

    public function versions()
    {
        return $this->belongsToMany('Greenfly\Modules\Content\Models\Version');
    }

    public function taxonomy()
    {
        return $this->belongsTo('Greenfly\Modules\Content\Models\Taxonomy');
    }
}