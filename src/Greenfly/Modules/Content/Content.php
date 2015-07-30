<?php
/**
 * Created by PhpStorm.
 * User: scraig
 * Date: 6/26/2015
 * Time: 8:25 PM
 */

namespace Greenfly\Modules\Content;


use Greenfly\Module;
use Greenfly\Modules\Content\Models\Content as ContentModel;
use Greenfly\Modules\Content\Models\Type as TypeModel;
use Greenfly\Modules\Content\Models\Version as VersionModel;
use Greenfly\Modules\Content\Models\Taxonomy as TaxonomyModel;
use Greenfly\Modules\Content\Models\Tag as TagModel;
use Greenfly\Modules\Model;

class Content extends Module
{

    const EXCEPTION_MESSAGE = 'Caught Exception: ';
    const CONFIG_MODEL_KEY = 'model';
    const CONFIG_METHOD_KEY = 'method';
    const CONFIG_PARAMETERS_KEY = 'params';
    const SECTIONS_KEY = 'sections';
    const RENDER_KEY = 'render';
    const DATA_KEY = 'data';
    const VIEW_KEY = 'view';
    const VERSION_KEY = 'version';
    const SINGLE_TAG_KEY = 'tag';
    const PLURAL_TAG_KEY = 'tags';
    const TYPE_ID_KEY = 'type_id';
    const NAME_KEY = 'name';
    const CONTENTS_KEY = 'contents';
    const CONTENT_NAME_KEY = 'content_name';
    const TYPE_NAME_KEY = 'type_name';

    protected $contentModel;
    protected $typeModel;
    protected $versionModel;
    public $viewsList = 'views';
    public $renderObject = 'renderer';

    public function __construct()
    {
        $this->content = new ContentModel();
        $this->type = new TypeModel();
        $this->version = new VersionModel();
        $this->taxonomy = new TaxonomyModel();
        $this->tag = new TagModel();
    }

    public static function index()
    {
        return ContentModel::where(static::TYPE_ID_KEY, '=', 1)->with(static::VERSION_KEY)->take(20)->get()->toArray();
    }


    public static function sections (array $config)
    {
        $class = new Static();

        foreach ($config[static::SECTIONS_KEY] as $section => $content) {
            $class->runSection($content, $config[static::TEMPLATE_KEY], $config[static::PARAMS_KEY]);
        }

    }

    protected function runSection ($content, $template, $additionalVars = [])
    {

        if (is_string($content)) {
            echo $template->render($content, $additionalVars);
        } elseif (isset($content[static::CONFIG_CALLBACK_KEY])) {
            $content[static::CONFIG_KEY][static::TEMPLATE_KEY] = $template;
            $content[static::CONFIG_KEY][static::PARAMS_KEY] = array_merge($content[static::CONFIG_KEY][static::PARAMS_KEY], $additionalVars);
            call_user_func($content[static::CONFIG_CALLBACK_KEY], $content[static::CONFIG_KEY]);
        } elseif (isset($content[static::RENDER_WITH_DATA_KEY])) {
            $this->renderWithData($content, $template, [$additionalVars, $content[static::RENDER_WITH_DATA_KEY]]);
        }

    }

    /**
     * get content based on tags and render through template view
     * @param array $config
     */
    public static function contentByTags(array $config)
    {
        $class = new Static();
        $params = $class->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY], $class->getContentByTags($config)]);
        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);
    }

    /**
     * When you want to get content based on tags and return as array
     * @param $config
     * @return array
     * @throws \Exception
     */
    public function getContentByTags($config)
    {
        $vars = [];

        foreach ($config[static::PARAMS_KEY][static::PLURAL_TAG_KEY] as $tagSelector) {
            $tag = TagModel::where(static::NAME_KEY, '=',$tagSelector[static::NAME_KEY])->first();
            $content = $tag->contents;
            $vars[static::SINGLE_TAG_KEY] = $tag->toArray();
            VersionModel::AttachlatestActive($vars[static::SINGLE_TAG_KEY][static::CONTENTS_KEY]);
        }

        return $vars;

    }



    public static function single (array $config)
    {
        $class = new Static();
        $params = $class->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY], $class->getSingleVersion($config)]);
        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);
    }

    /**
     * When you want to get a content piece with related item via static method
     * @param array $config
     */
    public static function contentWithRelated (array $config)
    {
        $class = new Static();
        $content = $class->getContentWithRelated($config);
        $params = $class->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY], $content]);
        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);
    }

    /**
     * get content with related content
     * @param array $config
     * @return array
     */
    public function getContentWithRelated (array $config)
    {

        $contentPiece = isset($config[static::PARAMS_KEY][static::TYPE_NAME_KEY]) ? ContentModel::where([static::NAME_KEY => $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY], static::TYPE_NAME_KEY => $config[static::PARAMS_KEY][static::TYPE_NAME_KEY]]) : ContentModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY]);
        $contentPiece = ContentModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();
        $tags = $contentPiece->tags;

        foreach ($tags as $tag) {
            isset($config[static::PARAMS_KEY][static::TYPE_NAME_KEY]) ? $tag->getContentWithVersion($config[static::PARAMS_KEY][static::CONTENT_NAME_KEY], $config[static::PARAMS_KEY][static::TYPE_NAME_KEY]) :  $tag->getContentWithVersion();
        }

        $version = $contentPiece->latestVersion();
        $contentArray = $contentPiece->toArray();
        $contentArray[static::VERSION_KEY] = $version;
        return $contentArray;



    }

    public function getSingleVersion($config)
    {

        $versionParams = '';
        $i = 0;

        if (!is_string($config[static::CONFIG_PARAMETERS_KEY][static::VERSION_KEY])) {

            foreach ($config[static::CONFIG_PARAMETERS_KEY][static::VERSION_KEY] as $col => $val) {
                $versionParams .= $i == 0 ?  $col . ' = "' . $val . '"' : ' AND ' . $col . ' = "' . $val . '"';
                $i++;
            }

        }

        $version = VersionModel::whereRaw($versionParams)->first();
        $content = $version->content;
        $version = $version->toArray();
        $version[static::DATA_KEY] = json_decode($version[static::DATA_KEY], 1);
        return [static::VERSION_KEY => $version];
    }

    public static function contentItem($config)
    {
        $version = VersionModel::where(static::CONTENT_NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();
        $content = $version->content;
        $config[static::PARAMS_KEY][static::VERSION_KEY] = $version->toArray();
        $config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY] = json_decode($config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY], 1);
        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY],$config[static::PARAMS_KEY]);
    }



    public function storeType(array $typeContent)
    {
        $this->updateOrCreateRow($this->type, $typeContent);
    }

    public function storeContent(array $contentDetails)
    {
        $this->updateOrCreateRow($this->content, $contentDetails);
    }

    private function updateOrCreateRow(Model $model, array $itemDetails)
    {
        try {
            $item = $model::find($itemDetails['id']);

            if (count($item) > 0) {
                $item->update($itemDetails);
            } else {
                $model::create($itemDetails);
            }

        } catch (\Exception $e) {
            echo static::EXCEPTION_MESSAGE . $e->getMessage();
        }
    }

}