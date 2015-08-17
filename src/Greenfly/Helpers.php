<?php
/**
 * Created by PhpStorm.
 * User: scraig
 * Date: 8/12/2015
 * Time: 3:50 PM
 */

namespace Greenfly;


trait Helpers {


    private $configCallbackKey = 'callback';

    private $configKey = 'config';

    private $templateKey = 'template';

    private $renderWithDataKey = 'renderData';

    private $paramsKey = 'params';

    private $viewKey = 'view';

    /**
     * connects one or more arrays to a parent array.
     *
     * @param array $array
     * @param array $attachments
     * @return array
     *
     */
    public static function connectArrays(array &$array, array $attachments)

    {

        foreach ($attachments as $key => $value) {


            $array = array_merge($array, $value);


        }


        return $array;
    }




    protected function runContent($content, Template $template, array $additionalVars = [])

    {


        if (is_string($content)) {


            echo $template->render($content, $additionalVars);


        } elseif (isset($content[$this->configCallbackKey])) {


            $content[$this->configKey][$this->templateKey] = $template;

            $content[$this->configKey][$this->paramsKey] = isset($content[$this->configKey][$this->paramsKey]) ? array_merge($content[$this->configKey][$this->paramsKey], $additionalVars) :  $additionalVars;


            try {


                call_user_func($content[$this->configCallbackKey], $content[$this->configKey]);


            } catch (\Exception $e) {

                dd($e);

            }


        } elseif (isset($content[$this->renderWithDataKey])) {


            $this->renderWithData($content[$this->viewKey], $template, [$additionalVars, $content[$this->renderWithDataKey]]);


        }


    }



    /**
     * render a file with additional variables without having to use a callback. Useful when you  know the specific variables you'd like pass.
     *
     * @param string $view
     * @param template $template
     * @param array $additionalVars
     *
     */
    protected function renderWithData($view, template $template, array $additionalVars = [])

    {


        $params = [];

        $this->connectArrays($params, $additionalVars);

        echo $template->render($view, $params);


    }



}