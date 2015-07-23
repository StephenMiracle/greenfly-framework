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
use Greenfly\Template;

class Content extends Module
{

    const EXCEPTION_MESSAGE = 'Caught Exception: ';
    const CONFIG_MODEL_KEY = 'model';
    const CONFIG_METHOD_KEY = 'method';
    const CONFIG_PARAMETERS_KEY = 'params';

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


    public static function single (array $config)
    {
        $class = new Static();
        $template = new Template();
        echo $template->render(['view' => $config['render']['view'], 'params' => array_merge($config['render']['data'], $class->getSingleVersion($config))]);
    }

    public function getSingleVersion($config)
    {

        $contentParams = '';
        $versionParams = '';

        foreach ($config[static::CONFIG_PARAMETERS_KEY]['content'] as $col => $val) {
        }

        foreach ($config[static::CONFIG_PARAMETERS_KEY]['version'] as $col => $val) {
            $versionParams = ' AND ' . $col . ' = "' . $val . '"';
        }

        $contentPiece = ContentModel::whereRaw('name = "' . $config[static::CONFIG_PARAMETERS_KEY]['name'] . '" ' . $contentParams)->firstOrFail()->toArray();
        $version = VersionModel::whereRaw('content_id = ' . $contentPiece['id'] . $versionParams)->firstOrFail()->toArray();
        $version['data'] = json_decode($version['data'], 1);
        return ['content' => $contentPiece, 'version' => $version];
    }



    protected function jsonToArray($json)
    {
        return json_decode($json);
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