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

use \Exception;



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

    const TAKE_KEY = 'take';

    const OFFSET_KEY = 'offset';

    const TAXONOMY_NAME_KEY = 'taxonomy_name';

    const PLURAL_TAXONOMY_KEY = 'taxonomies';

    const SINGLE_TAXONOMY_KEY = 'taxonomy';

    const PLURAL_TYPE_KEY = 'types';

    const SINGLE_TYPE_KEY = 'type';

    const TYPE_NAME_EXCEPTION_MESSAGE = 'Parameter "<strong>type_name</strong>" must be either a string or an array';


    protected $contentModel;

    protected $typeModel;

    protected $versionModel;

    public $viewsList = 'views';

    public $renderObject = 'renderer';








    public static function index()

    {

        return ContentModel::where(static::TYPE_ID_KEY, '=', 1)->with(static::VERSION_KEY)->take(20)->get()->toArray();

    }




    public static function taxonomiesWithTagsAndTypes(array $config)

    {
        $class = new Static();

        $taxonomies = TaxonomyModel::all();


        foreach ($taxonomies as $taxonomy) {


            try {


                $taxonomy->tags = TagModel::where(static::TAXONOMY_NAME_KEY, $taxonomy->name)->get()->toArray();

                $taxonomy->types;


            } catch (\Exception $e) {


                echo $e; die;


            }


        }

        $config[static::PARAMS_KEY][static::PLURAL_TAXONOMY_KEY] = $taxonomies->toArray();

        $class->renderContent($config);


    }



    public static function typesWithContentAndTaxonomies (array $config)

    {


        $class = new Static();

        $types = $class->typeQueryBuilder($config[static::PARAMS_KEY]);


        foreach ($types as $type) {


            try {


                $type->contents = ContentModel::where(static::TYPE_NAME_KEY, $type->name)->get()->toArray();
                $type->taxonomies;


            } catch (Exception $e) {


                echo $e; die;


            }


        }

        $config[static::PARAMS_KEY][static::PLURAL_TYPE_KEY] = $types->toArray();

        $class->renderContent($config);



    }




    protected function typeQueryBuilder ($params)

    {


        $query = TypeModel::all();


        if (isset($params[static::TYPE_NAME_KEY])) {


            if (is_string($params[static::TYPE_NAME_KEY])) {


                $query = $query->where(static::NAME_KEY, $params[static::TYPE_NAME_KEY]);


            } elseif (is_array($params[static::TYPE_NAME_KEY])) {


                foreach ($params[static::TYPE_NAME_KEY] as $name) {


                    $query = $query->where(static::TYPE_NAME_KEY, $name);


                }


            } else {

                throw new Exception (static::TYPE_NAME_EXCEPTION_MESSAGE);

            }


        }


        if (isset($params[static::TAKE_KEY])) {

            $query = $query->take($params[static::TAKE_KEY]);

        }


        return $query;


    }




    public static function contentArchive(array $config)

    {

        $class = new Static();

        $contentQuery = $class->contentQueryBuilder($config[static::PARAMS_KEY]);


        foreach ($contentQuery as $content) {


            try {

                $content->tags;
                $content->versions = $content->versions()->toArray();

            } catch (\Exception $e) {

                echo $e; die;

            }

        }

        $config[static::PARAMS_KEY][static::CONTENTS_KEY] = $contentQuery->toArray();


        $class->renderContent($config);

    }




    protected function renderContent(array $config)

    {


        try {

            $params = $this->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY]]);

            echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);

        } catch (\Exception $e) {

            echo $e; die;

        }


    }




    protected function contentQueryBuilder (array $params)

    {


        $contentQuery = ContentModel::all();


        if (isset($params[static::TYPE_NAME_KEY])) {

            $contentQuery = $contentQuery->where(static::TYPE_NAME_KEY, $params[static::TYPE_NAME_KEY]);

        }


        if (isset($params[static::TAKE_KEY])) {

            $contentQuery = $contentQuery->take($params[static::TAKE_KEY]);

        }




        return $contentQuery;


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



            if (isset($config[static::PARAMS_KEY]['take'])) {

                $tag->take = $config[static::PARAMS_KEY]['take'];

            }



            $content = $tag->contents;

            $vars[static::SINGLE_TAG_KEY] = $tag->toArray();

            VersionModel::AttachlatestActive($vars[static::SINGLE_TAG_KEY][static::CONTENTS_KEY]);

        } 



        return $vars;



    }


    /**
     * @param array $config
     */
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


         $contentPiece = ContentModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();

        $tags = $contentPiece->tags;

        $take = isset($config[static::PARAMS_KEY][static::TAKE_KEY]) ? $config[static::PARAMS_KEY][static::TAKE_KEY] : 0;

        $offset = isset($config[static::PARAMS_KEY][static::OFFSET_KEY]) ? $config[static::PARAMS_KEY][static::OFFSET_KEY] : 0;


        foreach ($tags as $tag) {

            if (!isset($config[static::PARAMS_KEY][static::TYPE_NAME_KEY])) {

                $config[static::PARAMS_KEY][static::TYPE_NAME_KEY] = '';

            }

            $tag->getContentWithVersion($config[static::PARAMS_KEY][static::CONTENT_NAME_KEY], $config[static::PARAMS_KEY][static::TYPE_NAME_KEY], $take, $offset);

        }


        $version = $contentPiece->latestVersion();

        $contentArray = $contentPiece->toArray();

        $contentArray[static::VERSION_KEY] = $version;

        return $contentArray;


    }


    /**
     * @param $config
     * @return array
     */
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




    public static function contentItemWithAllVersions($config)

    {
        $content = ContentModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();

        $content->tags;

        $content->versions = $content->versions()->toArray();

        $config[static::PARAMS_KEY][static::CONTENTS_KEY] = $content->toArray();



        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY],$config[static::PARAMS_KEY]);

    }

    public static function contentItem($config)

    {

        $version = VersionModel::where(static::CONTENT_NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();

        $content = $version->content;

        $config[static::PARAMS_KEY][static::VERSION_KEY] = $version->toArray();

        $config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY] = json_decode($config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY], 1);

        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY],$config[static::PARAMS_KEY]);

    }




}