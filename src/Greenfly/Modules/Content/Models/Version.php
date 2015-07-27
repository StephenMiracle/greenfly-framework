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

    public static function attachLatestActive(&$contents)
    {
        $class = new Static();

        if (is_array($contents)) {

            foreach ($contents as $key => $content) {
                $contents[$key]['version'] = $class->whereRaw('content_name = "' . $content['name'] . '" AND status = 1')->orderBy('updated_at')->first()->toArray();
            }

        }


        return $contents;

    }
}