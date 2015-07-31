<?php

namespace Greenfly;
use Phlyty\App as RouteSystem;
use Greenfly\Database;
use Greenfly\Template;
use Exception;
/**
 * Application container
 *
 * @category   Greenfly
 * @package    Greenfly
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

    public $route;
    public $siteVariables;
    protected $template;

    public function __construct($config)
    {
        Database::connect($config[self::SITE_CONFIG_KEY][self::DATABASE_CONFIG_KEY]);
        $this->template = new Template($config[self::SITE_CONFIG_KEY]);
        $this->route = new RouteSystem();
        $this->siteVariables = $config[self::SITE_CONFIG_KEY][self::VARIABLES_KEY];
    }

    public function runDocument ($jsonDocument)
    {
        $document = json_decode($jsonDocument, 1);



        if ($document) {
            $this->parseDocument($document);
        } else {
            throw new \Exception('Either missing JSON file or JSON file has errors');
        }

        $this->route->run();

    }

    protected function parseDocument($document)
    {
        if (isset($document[self::DOCUMENT_GET_KEY])) {

            foreach ($document[self::DOCUMENT_GET_KEY] as $route => $content) {
                 $this->getRoute($route, $content);

            }

        }

        if (isset($document[self::DOCUMENT_POST_KEY])) {

            foreach ($document[self::DOCUMENT_POST_KEY] as $route => $content) {
                $this->postRoute($route, $content);
            }



        }

        if (isset($document[self::DOCUMENT_PUT_KEY])) {

            foreach ($document[self::DOCUMENT_PUT_KEY] as $route => $content) {
                $this->putRoute($route, $content);
            }

        }

        if (isset($document[self::DOCUMENT_DELETE_KEY])) {

            foreach ($document[self::DOCUMENT_DELETE_KEY] as $route => $content) {
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

        if (isset($content[self::CONFIG_CALLBACK_KEY])) {


            if (!isset($content[self::CONFIG_KEY][self::PARAMS_KEY])) {
                $content[self::CONFIG_KEY][self::PARAMS_KEY] = [];
            }

             $this->connectArrays($content[self::CONFIG_KEY][self::PARAMS_KEY], [$this->siteVariables, $httpMethods, $params]);
            $content[self::CONFIG_KEY][self::TEMPLATE_KEY] = $this->template;

            return call_user_func($content[self::CONFIG_CALLBACK_KEY], $content[self::CONFIG_KEY]);

        } elseif(is_string($content)) {

            $params = [];

            $this->connectArrays($params, [$this->siteVariables, $httpMethods]);
            echo $this->template->render($content, $params);


        } elseif (isset($content[self::RENDER_WITH_DATA_KEY])) {
            $this->renderWithData($content, $this->template, [$this->siteVariables, $httpMethods, $content[self::RENDER_WITH_DATA_KEY]]);
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
