<?php

namespace Greenfly;
use Phlyty\App as RouteSystem;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\Exception\FatalThrowableError as Error;
/**
 * Application container
 *
 * @category   Greenfly
 * @package    Greenfly
 *
 * @todo update PUT and DELETE methods for use.
 * @todo update documentation
 */
class App
{




    const DATABASE_CONFIG_KEY = 'database';

    const SITE_CONFIG_KEY = 'site';

    const CONFIG_CALLBACK_KEY = 'callback';

    const DOCUMENT_GET_KEY = 'get';

    const DOCUMENT_POST_KEY = 'post';

    const DOCUMENT_PUT_KEY = 'put';

    const DOCUMENT_DELETE_KEY = 'delete';

    const CONFIG_KEY = 'config';

    const PARAMS_KEY = 'params';

    const VARIABLES_KEY = 'variables';

    const TEMPLATE_KEY = 'template';

    const RENDER_WITH_DATA_KEY = 'renderData'; // Use instead of Callback if you just want to dump known data attributes into render file

    const VIEW_KEY = 'view';

    const ERROR_404_KEY = '404';

    const ENVIRONMENT_KEY = 'environment';

    const PRODUCTION_ENVIRONMENT = 'production';

    const THEME_DIRECTORY_KEY = 'theme_directory';

    const CACHE_DIRECTORY_KEY = 'theme_cache_directory';




    public $route;

    public $siteVariables;

    protected $template;


    public function __construct($config)

    {



        if (isset($config[static::SITE_CONFIG_KEY][static::ENVIRONMENT_KEY]) && $config[static::SITE_CONFIG_KEY][static::ENVIRONMENT_KEY] !== static::PRODUCTION_ENVIRONMENT) {


            Debug::enable();


        }


        Database::connect($config[static::SITE_CONFIG_KEY][static::DATABASE_CONFIG_KEY]);

        $this->template = new Template($config[static::SITE_CONFIG_KEY][static::THEME_DIRECTORY_KEY], $config[static::SITE_CONFIG_KEY][static::CACHE_DIRECTORY_KEY]);

        $this->route = new RouteSystem();

        $this->siteVariables = $config[static::SITE_CONFIG_KEY][static::VARIABLES_KEY];

    }





    public function runDocument ($jsonDocument)
        
    {


        $document = json_decode($jsonDocument, 1);

        try {


            if ($document) {


                $this->parseDocument($document);


            } else {


                throw new Exception('Either missing JSON file or JSON file has errors'); die();


            }


            $this->route->run();


        } catch (Exception $e) {


            echo $e; die;

        }


    }




    protected function parseDocument($document)

    {


        if (isset($document[static::DOCUMENT_GET_KEY])) {


            foreach ($document[static::DOCUMENT_GET_KEY] as $route => $content) {


                 $this->getRoute($route, $content);


            }


        }

        if (isset($document[static::DOCUMENT_POST_KEY])) {


            foreach ($document[static::DOCUMENT_POST_KEY] as $route => $content) {


                $this->postRoute($route, $content);


            }



        }

        if (isset($document[static::DOCUMENT_PUT_KEY])) {


            foreach ($document[static::DOCUMENT_PUT_KEY] as $route => $content) {


                $this->putRoute($route, $content);


            }


        }

        if (isset($document[static::DOCUMENT_DELETE_KEY])) {


            foreach ($document[static::DOCUMENT_DELETE_KEY] as $route => $content) {


                $this->deleteRoute($route, $content);


            }


        }


        $this->route->events()->attach('404', function ($e) use($document) {


            echo $this->template->render($document[static::ERROR_404_KEY], $this->siteVariables);


        });


    }




    protected function connectArrays(array &$array, array $attachments)

    {

        foreach ($attachments as $key => $value) {


           $array = array_merge($array, $value);


        }


        return $array;
    }




    private function runRoute($content, array $httpMethods, array $params)

    {


        if (isset($content[static::CONFIG_CALLBACK_KEY])) {


            if (!isset($content[static::CONFIG_KEY][static::PARAMS_KEY])) {


                $content[static::CONFIG_KEY][static::PARAMS_KEY] = [];


            }


             $this->connectArrays($content[static::CONFIG_KEY][static::PARAMS_KEY], [$this->siteVariables, $httpMethods, $params]);


            $content[static::CONFIG_KEY][static::TEMPLATE_KEY] = $this->template;


            return call_user_func($content[static::CONFIG_CALLBACK_KEY], $content[static::CONFIG_KEY]);



        } elseif(is_string($content)) {


            $params = [];


            $this->connectArrays($params, [$this->siteVariables, $httpMethods]);

            echo $this->template->render($content, $params);


        } elseif (isset($content[static::RENDER_WITH_DATA_KEY])) {


            $this->renderWithData($content, $this->template, [$this->siteVariables, $httpMethods, $content[static::RENDER_WITH_DATA_KEY]]);


        }


    }




    protected function renderWithData($content, $template, $additionalVars = [])

    {
        $params = [];
        $this->connectArrays($params, $additionalVars);
        echo $template->render($content[static::VIEW_KEY], $params);
    }




    public function getRoute ($route, $content)

    {


        $this->route->get($route, function ($router) use ($content) {


            return $this->runRoute($content, $router->request()->getQuery()->toArray(), $router->params()->getParams());


        });


    }




    public function postRoute ($route, $content)

    {


        $this->route->post($route, function ($router) use ($content) {


            return $this->runRoute($content, $router->request()->getPost()->toArray(), $router->params()->getParams());


        });


    }




    public function putRoute ($route, $config)

    {

    }




    public function deleteRoute($route, $config)

    {

    }

}
