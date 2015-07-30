<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'type_name'];
    protected $typeName = 'type_name';
    public $typerName;

    public function versions()
    {
        return $this->belongsToMany('Greenfly\Modules\Content\Models\Version');
    }

    public function taxonomy()
    {
        return $this->belongsTo('Greenfly\Modules\Content\Models\Taxonomy');
    }

    public function contents($take = '', $offset = '')
    {
        $contents = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');

        if (!empty($this->typerName)) {
            $contents->where('type_name', $this->typerName);
        }

        if (!empty($this->currentContentName)) {
            $contents->where('name', '!=', $this->currentContentName);
        }
        $contents->orderBy('published_date', 'DESC');
        $contents->take(empty($take) ? 15 : $take);
        $contents->offset(empty($offset) ? 0 : $offset);

        return $contents;
    }

    public function getContentWithVersion($currentContentName = '', $type_name = '')
    {
        $this->currentContentName = $currentContentName;
        $this->typerName = $type_name;
        $contents = $this->contents;

        foreach ($contents as $content) {
            $content->version = $content->latestVersion();
        }

        return $contents;
    }

    public function getContents($take = '', $offset = '')
    {
        $contents = $this->belongsToMany('Greenfly\Modules\Content\Models\Content');

        if (!empty($this->typerName)) {
            $contents->where('type_name', $this->typeName);
        }

        $contents->orderBy('published_date', 'DESC');
        $contents->take(empty($take) ? 15 : $take);
        $contents->offset(empty($offset) ? 0 : $offset);

        return $contents;
    }
}