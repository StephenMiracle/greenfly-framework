<?php

/**

 * Created by PhpStorm.

 * User: scraig

 * Date: 7/1/2015

 * Time: 12:19 AM

 */



namespace Greenfly;




class Template {


    const RENDER_CONFIG_VIEW = 'view';

    const RENDER_CONFIG_PARAMS = 'params';



    public $twigTemplatePath;

    public $twigCachePath;

    public $twig;



    public function __construct($themeDirectory, $cacheDirectory)

    {

        \Twig_Autoloader::register();



        $loader = new \Twig_Loader_Filesystem($themeDirectory);

        $this->twig = new \Twig_Environment($loader, array(

            'cache' => $cacheDirectory,

            'debug' => true

        ));

        $this->twig->addExtension(new \Twig_Extension_Debug());

    }



    public function render($view, array $params = [])

    {

        return $this->twig->render($view, $params);

    }



}