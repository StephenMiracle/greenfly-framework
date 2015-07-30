<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Content extends Model
{
    protected $fillable = ['name', 'type_name'];

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

    public function tags()
    {
        return $this->belongsToMany('Greenfly\Modules\Content\Models\Tag');
    }

    /**
     * returns the latest version of a content piece as an array.
     * @return array
     */
    public function latestVersion()
    {
        $version = Version::where('content_name', $this->getAttributeValue('name'))->orderBy('published_date', 'DESC')->first()->toArray();
        $version['data'] = json_decode($version['data']);
        return $version;
    }


}
