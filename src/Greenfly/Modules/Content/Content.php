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
        return ContentModel::where('type_id', '=', 1)->with('versions')->take(20)->get()->toArray();
    }


    public static function sections (array $config)
    {
        $class = new Static();

        foreach ($config[self::SECTIONS_KEY] as $section => $content) {
            $class->runSection($content, $config[self::TEMPLATE_KEY], $config[self::PARAMS_KEY]);
        }

    }

    protected function runSection ($content, $template, $additionalVars = [])
    {

        if (is_string($content)) {
            echo $template->render($content, $additionalVars);
        } elseif (isset($content[self::CONFIG_CALLBACK_KEY])) {
            $content[self::CONFIG_KEY][self::TEMPLATE_KEY] = $template;
            $content[self::CONFIG_KEY][self::PARAMS_KEY] = array_merge($content[self::CONFIG_KEY][self::PARAMS_KEY], $additionalVars);
            call_user_func($content[self::CONFIG_CALLBACK_KEY], $content[self::CONFIG_KEY]);
        }

    }

    public static function contentByTags(array $config)
    {
        $class = new Static();
        $params = $class->connectArrays($config[self::PARAMS_KEY], [$config[self::RENDER_KEY][self::DATA_KEY], $class->getContentByTags($config)]);
        echo $config[self::TEMPLATE_KEY]->render($config[self::RENDER_KEY][self::VIEW_KEY], $params);
    }

    public function getContentByTags($config)
    {
        $vars = [];

        foreach ($config['params']['tags'] as $tagSelector) {
            $tag = TagModel::whereRaw('name = "' . $tagSelector['name'] . '" AND taxonomy_name = "' . $tagSelector['taxonomy_name'] . '"')->first();
            $content = $tag->contents;
            $vars['tag'] = $tag->toArray();
            VersionModel::AttachlatestActive($vars['tag']['contents']);
        }

        return $vars;

    }



    public static function single (array $config)
    {
        $class = new Static();
        $params = $class->connectArrays($config[self::PARAMS_KEY], [$config[self::RENDER_KEY][self::DATA_KEY], $class->getSingleVersion($config)]);
        echo $config[self::TEMPLATE_KEY]->render($config[self::RENDER_KEY][self::VIEW_KEY], $params);
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
        $version['data'] = json_decode($version['data'], 1);
        return ['version' => $version];
    }

    public static function contentItem($config)
    {
        $version = VersionModel::where('content_name', $config['params']['content_name'])->first();
        $content = $version->content;
        $config['params']['version'] = $version->toArray();
        $config['params'][self::VERSION_KEY][self::DATA_KEY] = json_decode($config['params'][self::VERSION_KEY][self::DATA_KEY], 1);
        echo $config[self::TEMPLATE_KEY]->render($config[self::RENDER_KEY][self::VIEW_KEY],$config['params']);
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
            echo SELF::EXCEPTION_MESSAGE . $e->getMessage();
        }
    }

}