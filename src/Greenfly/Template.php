<?php


namespace Greenfly;




class Template {


    const RENDER_CONFIG_VIEW = 'view';

    const RENDER_CONFIG_PARAMS = 'params';



    public $twigTemplatePath;

    public $twigCachePath;

    public $twig;



    public function __construct($themeDirectory, $cacheDirectory)

    {


        $loader = new \Twig_Loader_Filesystem($themeDirectory);

        $this->twig = new \Twig_Environment($loader, array(

            'cache' => $cacheDirectory,

            'debug' => true

        ));

        $this->twig->addExtension(new \Twig_Extension_Debug());


    }


    /**
     * return the content with the view and data.
     *
     * @param string $view the theme file to display.
     *
     * @param array $params
     *
     * @return string
     */
    public function render($view, array $params = [])

    {

        return $this->twig->render($view, $params);

    }



}