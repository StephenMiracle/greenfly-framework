<?php

namespace Greenfly;
use Phlyty\App as RouteSystem;
use Greenfly\Database;
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

    public $route;

    public function __construct($config)
    {
        Database::connect($config[self::SITE_CONFIG_KEY][self::DATABASE_CONFIG_KEY]);
        $this->route = new RouteSystem();
    }

    public function runDocument ($jsonDocument)
    {
        $document = json_decode($jsonDocument, 1);

        if ($document) {
            $this->parseDocument($document);
        }

        $this->route->run();

    }

    protected function parseDocument($document)
    {
        if (isset($document['get'])) {

            foreach ($document['get'] as $route => $content) {
                $this->getRoute($route, $content);
            }

        }

        if (isset($document['post'])) {

            foreach ($document['post'] as $route => $content) {
                $this->postRoute($route, $content);
            }

        }

        if (isset($document['put'])) {

            foreach ($document['put'] as $route => $content) {
                $this->putRoute($route, $content);
            }

        }

        if (isset($document['delete'])) {

            foreach ($document['delete'] as $route => $content) {
                $this->deleteRoute($route, $content);
            }

        }
    }


    public function getRoute ($route, $content)
    {

        $this->route->get($route, function ($router) use ($content) {

            if (is_array($content)) {

                foreach ($router->request()->getQuery()->toArray() as $key => $queryItem) {
                    $content['config']['params'][$key] = $queryItem;
                }

                foreach ($router->params()->getParams() as $key => $urlParam) {
                    $content['config']['params'][$key] = $urlParam;
                }

                if (!isset($content['config'])) {
                    $content['config'] = [];
                }

                return call_user_func($content['callback'], $content['config']);

            }

        });

    }

    public function postRoute ($route, $content)
    {

    }

    public function putRoute ($route, $config)
    {

    }

    public function deleteRoute($route, $config)
    {

    }

}
