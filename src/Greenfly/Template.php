<?php
/**
 * Created by PhpStorm.
 * User: scraig
 * Date: 7/1/2015
 * Time: 12:19 AM
 */

namespace Greenfly;

use Exception;

class Template {

    const THEME_DIRECTORY_KEY = 'theme_directory';
    const CACHE_DIRECTORY_KEY = 'theme_cache_directory';
    const RENDER_CONFIG_VIEW = 'view';
    const RENDER_CONFIG_PARAMS = 'params';

    public $twigTemplatePath;
    public $twigCachePath;
    public $twig;

    public function __construct(array $config = [])
    {
        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem($config[self::THEME_DIRECTORY_KEY]);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $config[SELF::CACHE_DIRECTORY_KEY],
            'debug' => true
        ));
        $this->twig->addExtension(new \Twig_Extension_Debug());
    }

    public function render(array $config)
    {

        if (!isset($config[SELF::RENDER_CONFIG_VIEW])) {
            throw new Exception('Missing necessary config key - ');
        }

        if (!isset($config[SELF::RENDER_CONFIG_PARAMS])) {
            $config[SELF::RENDER_CONFIG_PARAMS] = [];
        }

        return $this->twig->render($config[SELF::RENDER_CONFIG_VIEW], $config[SELF::RENDER_CONFIG_PARAMS]);
    }

}