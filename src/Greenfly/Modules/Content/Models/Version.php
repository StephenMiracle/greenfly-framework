<?php

namespace Greenfly\Modules\Content\Models;

use Greenfly\Modules\Model;

class Version extends Model
{
    protected $fillable = ['data', 'content_name', 'name', 'published_at', 'expired_at'];


    public function content()
    {
        $content = $this->belongsTo('Greenfly\Modules\Content\Models\Content', 'content_name', 'name');

        return $content;
    }

    public function tags()
    {
        return $this->belongsToMany('Greenfly\Modules\Content\Models\Tag', 'name', 'name');
    }

    /**
     * This method allows you to attach the latest Content Version to an array that has an active status of 1
     * @param $contents
     * @return array
     * @throws \Exception
     */
    public static function attachLatestActive(&$contents)
    {
        $class = new Static();

        if (is_array($contents)) {

            foreach ($contents as $key => $content) {
                $version = $class->where(['content_name' => $content['name'], 'status' => 1])->orderBy('updated_at')->first();

                if ($version) {
                    $version = $version->toArray();
                } else {
                    throw new \Exception('missing or incorrect row for ' . $content['name']);
                }

                $version['data'] = json_decode($version['data'], true);
                $contents[$key]['version'] = $version;
            }

        }


        return $contents;

    }
}