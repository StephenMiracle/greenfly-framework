<?php



namespace Greenfly\Modules\Content;





use Greenfly\Helpers;

use Greenfly\Module;

use Greenfly\Modules\Content\Models\Content as ContentModel;

use Greenfly\Modules\Content\Models\Type as TypeModel;

use Greenfly\Modules\Content\Models\Version as VersionModel;

use Greenfly\Modules\Content\Models\Taxonomy as TaxonomyModel;

use Greenfly\Modules\Content\Models\Tag as TagModel;

use Symfony\Component\Config\Definition\Exception;

use Greenfly\Template;


/**
 * Class Content
 * @package Greenfly\Modules\Content
 *
 * @todo add documentation.
 *
 * @todo refactor out unnecessary variables and constants.
 */
class Content

{

    use Helpers;

    const EXCEPTION_MESSAGE = 'Caught Exception: ';

    const CONFIG_PARAMETERS_KEY = 'params';

    const SECTIONS_KEY = 'sections';

    const RENDER_KEY = 'render';

    const DATA_KEY = 'data';

    const VIEW_KEY = 'view';

    const VERSION_KEY = 'version';

    const SINGLE_TAG_KEY = 'tag';

    const PLURAL_TAG_KEY = 'tags';

    const PARAMS_KEY = 'params';

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

    const TEMPLATE_KEY = 'template';

    const CONFIG_CALLBACK_KEY = 'callback';

    const CONFIG_KEY = 'config';

    const SHUFFLE_KEY = 'shuffle';



    public $type;

    public $types;

    public $taxonomy;

    public $taxonomies;

    public $taxonomy_name;

    public $offset;

    public $type_name;

    public $content_name;

    public $contents;

    public $params;

    public $sections;

    public $render;

    public $data;

    public $view;

    public $version;

    public $tag;

    public $tags;

    public $type_id;




    /**
     * @param array $config
     *
     * @todo add documentation.
     */
    public function __construct(array $config = [])

    {


        try {


            if (!empty($config)) {


                $this->setupFromConfig($config);


            }


        } catch (Exception $e) {


            echo $e; die();


        }



    }


    /**
     * @param array $config the options to run the desired content callback methods.
     *
     * @todo add documentation.
     *
     */
    protected function setupFromConfig(array $config)

    {


        $this->setParams($this->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY]]));

        $this->setTemplate($config[static::TEMPLATE_KEY]);

        $this->setView($config[static::RENDER_KEY][static::VIEW_KEY]);


    }




    /**
     * @param string $view the view file to render.
     */
    protected function setView($view)

    {


        $this->view = $view;


    }




    /**
     * @param Template $template the template engine to render
     */
    protected function setTemplate(Template $template)

    {


        $this->template = $template;


    }




    /**
     * @param array $params the parameters that the method & view need in order to render.
     */
    protected function setParams (array $params)

    {


        $this->params = $params;


    }




    /**
     * @param array $config
     *
     * @todo add documentation.
     */
    public static function taxonomiesWithTagsAndTypes(array $config)

    {

        $class = new Static($config);

        $taxonomies = TaxonomyModel::all();


        foreach ($taxonomies as $taxonomy) {


            try {


                $taxonomy->tags = TagModel::where(static::TAXONOMY_NAME_KEY, $taxonomy->name)->get()->toArray();

                $taxonomy->types;


            } catch (\Exception $e) {


                echo $e; die;


            }


        }

        $class->params[static::PLURAL_TAXONOMY_KEY] = $taxonomies->toArray();


        $class->renderObjectContent();


    }




    /**
     * @param array $config
     * @throws Exception
     *
     * @todo add documentation.
     */
    public static function typesWithContentAndTaxonomies (array $config)

    {


        $class = new Static($config);

        $take = isset ($class->params[static::TAKE_KEY]) ? $class->params[static::TAKE_KEY] : null;

        $typesList = isset ($class->params[static::PLURAL_TYPE_KEY]) ? $class->params[static::PLURAL_TYPE_KEY] : '';

        $types = $class->typeQueryBuilder($typesList, $take);


        foreach ($types as $type) {


            try {


                $type->contents = ContentModel::where(static::TYPE_NAME_KEY, $type->name)->get()->toArray();
                $type->taxonomies;


            } catch (Exception $e) {


                echo $e; die;


            }


        }


        $class->params[static::PLURAL_TYPE_KEY] = $types->toArray();

        $class->renderObjectContent();


    }


    /**
     * @param string $types
     * @param null $take
     * @param null $offset
     * @return \Illuminate\Database\Eloquent\Collection|static|static[]
     * @throws Exception
     *
     * @todo May need to refactor out. I don't like this method but couldn't figure out a better way to handle.
     */
    protected function typeQueryBuilder ($types = '', $take = null, $offset = null)

    {


        $query = TypeModel::all();


        if (empty($types)) {


            if (is_string($types)) {


                $query = $query->where(static::NAME_KEY, $types);


            } elseif (is_array($types)) {


                foreach ($types as $name) {


                    $query = $query->where(static::TYPE_NAME_KEY, $name);


                }


            } else {


                throw new Exception (static::TYPE_NAME_EXCEPTION_MESSAGE);


            }


        }


        if ($take) {


            $query = $query->take($take);


        }


        return $query;


    }


    /**
     * @param array $config
     *
     * @todo add documentation.
     */
    public static function contentArchive(array $config)

    {


        $class = new Static($config);

        $typeName = isset($class->params[static::TYPE_NAME_KEY]) ? $class->params[static::TYPE_NAME_KEY] : '';

        $take = isset($class->params[static::TAKE_KEY]) ? $class->params[static::TAKE_KEY] : 0;

        $offset = isset($class->params[static::OFFSET_KEY]) ? $class->params[static::OFFSET_KEY] : 0;

        $shuffle = isset($class->params[static::SHUFFLE_KEY]) ? $class->params[static::SHUFFLE_KEY] : 0;

        $contentQuery = $class->contentQueryBuilder($typeName, $take, $offset, $shuffle);


        foreach ($contentQuery as $content) {


            try {


                $content->tags;
                $content->version = $content->versions()->first()->toArray();


            } catch (\Exception $e) {


                echo $e; die;


            }


        }

        $class->params[static::CONTENTS_KEY] = $contentQuery->toArray();

        $class->renderObjectContent();


    }


    /**
     * Method to echo view response via the class.
     */
    protected function renderObjectContent()

    {


        try {


            echo $this->template->render($this->view, $this->params);


        } catch (\Exception $e) {


            echo $e; die;


        }


    }


    /**
     * renders content from config array.
     *
     * @deprecated Preferred method to render is renderObjectContent.
     *
     * @param array $config
     */
    protected function renderContent(array $config)

    {


        try {


            $params = $this->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY]]);

            echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);


        } catch (\Exception $e) {


            echo $e; die;


        }


    }


    /**
     * build a query for the content.
     *
     * @param string $typeName
     * @param int $take
     * @param int $offset
     * @param bool $shuffle
     * @return \Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @todo may refactor out method as this isn't preferred but couldn't find a better way to handle.
     */
    protected function contentQueryBuilder (array $typeName = [], $take = 0, $offset = 0, $shuffle = null)

    {


        $contentQuery = ContentModel::all();


        if (!empty($typeName)) {


            foreach ($typeName as $type) {


                $contentQuery = ContentModel::where(static::TYPE_NAME_KEY, $type);


                if ($take) {


                    $contentQuery = $contentQuery->take($take);


                }


                if ($offset) {


                    $contentQuery = $contentQuery->offset($offset);


                }



                $contentQuery = $contentQuery->get();


            }


            if ($shuffle) {


                $contentQuery = $contentQuery->shuffle();


            }



        }




        return $contentQuery;


    }


    /**
     * Good for custom pages with different needs. May be cumbersome on resources.
     *
     * @param array $config
     *
     * @paaram array [static::SECTIONS_KEY] each section can contain its own callback or render.
     *
     * @todo add documentation.
     */
    public static function sections (array $config)

    {

        $class = new Static();



        foreach ($config[static::SECTIONS_KEY] as $section => $content) {


            try {

                $class->runContent($content, $config[static::TEMPLATE_KEY], $config[static::PARAMS_KEY]);



            } catch (\Exception $e) {


                dd($e);


            }


        }



    }




    /**
     * get content based on tags and render through template view.
     *
     * @param array $config
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent.
     *
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
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent.
     */
    public function getContentByTags($config)

    {

        $vars = [];


        if (isset($config[static::PARAMS_KEY][static::NAME_KEY])) {


            $tag = TagModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::NAME_KEY])->first();


            if (isset($config[static::PARAMS_KEY]['take'])) {


                $tag->take = $config[static::PARAMS_KEY]['take'];


            }


            $tag->contents = $tag->getContents()->toArray();


            $vars[static::SINGLE_TAG_KEY] = $tag->toArray();

            VersionModel::AttachlatestActive($vars[static::SINGLE_TAG_KEY][static::CONTENTS_KEY]);


        } else {


            foreach ($config[static::PARAMS_KEY][static::PLURAL_TAG_KEY] as $tagSelector) {


                $tag = TagModel::where(static::NAME_KEY, $tagSelector[static::NAME_KEY])->first();


                if (isset($config[static::PARAMS_KEY]['take'])) {


                    $tag->take = $config[static::PARAMS_KEY]['take'];


                }


                $tag->contents = $tag->getContents()->toArray();

                $vars[static::SINGLE_TAG_KEY] = $tag->toArray();

                VersionModel::AttachlatestActive($vars[static::SINGLE_TAG_KEY][static::CONTENTS_KEY]);

            }

        }





        return $vars;



    }


    /**
     * @param array $config
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent.
     * @todo add documentation.
     */
    public static function single (array $config)

    {


        $class = new Static();

        $params = $class->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY], $class->getSingleVersion($config)]);

        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);


    }




    /**
     * When you want to get a content piece with related item via static method.
     *
     * @param array $config
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent
     *
     */
    public static function contentWithRelated (array $config)

    {


            dd($config);
        $class = new Static($config);

        $take = isset ($class->params[static::TAKE_KEY]) ? $class->params[static::TAKE_KEY] : null;

        $offset = isset ($class->params[static::OFFSET_KEY]) ? $class->params[static::OFFSET_KEY] : null;

        $typeName = isset ($class->params[static::TYPE_NAME_KEY]) ? $class->params[static::TYPE_NAME_KEY] : '';

        $shuffle = isset ($class->params[static::SHUFFLE_KEY]) ? $class->params[static::SHUFFLE_KEY] : null;

        $content = $class->getContentWithRelated($class->params[static::CONTENT_NAME_KEY], $typeName, $take, $offset, $shuffle);

        $params = $class->connectArrays($config[static::PARAMS_KEY], [$config[static::RENDER_KEY][static::DATA_KEY], $content]);

        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY], $params);


    }




    /**
     * get content with related content.
     *
     * @param string $contentName the name of the primary content to pull
     *
     * @param string $typeName set the type of content for related content
     *
     * @param int $take get a limited amount of related items to pull
     *
     * @param int offset how many to offset before pulling
     *
     * @param int shuffle randomize the items when returned.
     *
     * @return array
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent.
     * @todo refactor to use specific parameters instead of config array.
     *
     */
    public function getContentWithRelated ($contentName, $typeName = '', $take = 0, $offset = 0, $shuffle = 0)

    {


        try {


            $contentPiece = ContentModel::where(static::NAME_KEY, $contentName)->first();


        } catch (\Exception $e) {


            dd($e);


        }


        $tags = $contentPiece->tags;


        foreach ($tags as $tag) {



            $tag->getContentWithVersion($typeName, $take, $offset, $shuffle, $contentName);


        }



        $version = $contentPiece->latestVersion();

        $contentArray = $contentPiece->toArray();

        $contentArray[static::VERSION_KEY] = $version;

        return $contentArray;


    }


    /**
     * @param $config
     * @return array
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent
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




    /**
     * @param $config
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent
     * @todo add documentation
     */
    public static function contentItemWithAllVersions($config)

    {
        $content = ContentModel::where(static::NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();

        $content->tags;

        $content->versions = $content->versions()->toArray();

        $config[static::PARAMS_KEY][static::CONTENTS_KEY] = $content->toArray();



        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY],$config[static::PARAMS_KEY]);

    }


    /**
     * @param $config
     *
     * @todo refactor method to use new renderObjectContent instead of deprecated renderContent
     * @todo add documentation
     */
    public static function contentItem($config)

    {

        $version = VersionModel::where(static::CONTENT_NAME_KEY, $config[static::PARAMS_KEY][static::CONTENT_NAME_KEY])->first();

        $content = $version->content;

        $config[static::PARAMS_KEY][static::VERSION_KEY] = $version->toArray();

        $config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY] = json_decode($config[static::PARAMS_KEY][static::VERSION_KEY][static::DATA_KEY], 1);

        echo $config[static::TEMPLATE_KEY]->render($config[static::RENDER_KEY][static::VIEW_KEY],$config[static::PARAMS_KEY]);

    }




}