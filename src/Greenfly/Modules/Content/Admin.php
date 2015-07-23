<?php
/**
 * Created by PhpStorm.
 * User: scraig
 * Date: 7/8/2015
 * Time: 5:36 PM
 */

namespace Greenfly\Modules\Content;



class Admin extends Content {

    const CONTENT_TYPES_KEY = 'types';
    const CONTENT_TYPE_KEY = 'type';
    const TYPE_DATA_KEY = 'data';
    const TYPE_PROPERTY_KEY = 'property';
    const TYPE_DATA_NAME_KEY = 'name';
    const CONTENTS_KEY = 'contents';
    const VERSIONS_KEY = 'versions';
    const SINGLE_CONTENT_KEY = 'content';
    const SINGLE_TYPE_KEY = 'type';
    const SINGLE_VERSION_KEY = 'version';
    const ID_KEY = 'id';
    const CONTENT_ID_KEY = 'contentID';


    public static function index()
    {
        $class = new Static();

        $content[SELF::CONTENT_TYPES_KEY] = $class->type->all()->toArray();
        $content[SELF::CONTENTS_KEY] = $class->getContentsWithVersions();
        return $content;
    }

    public function getContentsWithVersions()
    {
        $contents = $this->content->all()->toArray();

        foreach ($contents as $key => $content) {
            $contents[$key][SELF::VERSIONS_KEY] = $this->content->find($content['id'])->versions->toArray();
        }

        return $contents;

    }

    public static function editType($id)
    {
        $class = new Static();

        $type = $class->type->find($id);

        if ($type) {
            $content[SELF::CONTENT_TYPE_KEY] = $type->toArray();
            $class->decodeTypeDataJson($content[SELF::CONTENT_TYPE_KEY]);
            return $content;
        }

    }

    public static function editContent($id)
    {
        $class = new Static();

        $contentItem = $class->content->find($id);

        if ($contentItem) {
            $content[SELF::SINGLE_CONTENT_KEY] = $contentItem->toArray();
            $content[SELF::SINGLE_CONTENT_KEY][SELF::SINGLE_TYPE_KEY] = $contentItem->type->toArray();
        }

        $content[SELF::CONTENT_TYPES_KEY] = $class->type->all()->toArray();
        return $content;


    }

    public static function editVersion(array $details)
    {
        $class = new Static();

        $versionItem = $class->version->find($details[SELF::ID_KEY]);
        $contentItem = $class->content->find($details[SELF::CONTENT_ID_KEY]);

        if ($versionItem) {
            $content[SELF::SINGLE_VERSION_KEY] = $versionItem->toArray();
        }

        if ($contentItem) {
            $type = $contentItem->type->toArray();
            $class->decodeTypeDataJson($type);
            $content[SELF::SINGLE_CONTENT_KEY] = $contentItem->toArray();
            $content[SELF::SINGLE_TYPE_KEY] = $type;
        }

        return $content;

    }

    public static function updateType(array $values)
    {
        $class = new Static();
        // @todo validate array
        $class->removeEmptyDataProperties($values);
        $class->encodeTypeDataJson($values);
        $class->storeType($values);
    }

    public static function updateVersion(array $values)
    {
        $class = new Static();
        // @todo validate array
        $class->removeEmptyDataProperties($values);
        //$values['data'] = json_encode($values['data']);
        //$class->encodeTypeDataJson($values);
        die(var_dump($values['data']));
        $class->storeVersion($values);
    }

    public static function updateContent(array $content)
    {
        $class = new Static();
        $class->storeContent($content);
    }

    public function removeEmptyDataProperties(&$content)
    {
        foreach ($content[SELF::TYPE_DATA_KEY] as $key => $item) {

            if (empty($item[SELF::TYPE_DATA_NAME_KEY])) {
                unset($content[SELF::TYPE_DATA_KEY][$key]);
            }

        }

    }

    public function decodeTypeDataJson(&$content)
    {
        $content[SELF::TYPE_DATA_KEY] = json_decode($content[SELF::TYPE_DATA_KEY], true);
    }

    public function encodeTypeDataJson(array &$values)
    {
        $values[SELF::TYPE_DATA_KEY] = json_encode($values[SELF::TYPE_DATA_KEY]);
    }



}