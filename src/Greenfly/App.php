<?php

namespace Greenfly;



use Phlyty\App as RouteSystem;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Debug\Debug;

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



    use Helpers;

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

    public $config;

    protected $template;


    /**
     * instantiate new Greenfly framework.
     *
     * @param array $config used to set up the application with necessary configuration.
     *
     * <ul>
     * <li><strong>array $config</strong></li>
     *  <li><ul><li>array ['site']
     *      <ul><li>array ['variables']                     required                    include sitewide variables that you'd like to pass to all pages. Examples: "siteTitle", "siteUrl", "siteDescription"
     *          <ul><li>mixed ['variable1']                 optional                    a variable that will be sent to all pages. Can be a string or an array</li></ul></li>
     *      <li>string ['environment']                      optional (recommended).     set the application environment. Setting with anything but "production" will display errors.</li>
     *      <li>array ['database']                          required.                   set necessary options to connect to the database
     *          <ul><li>string ['driver']                   required.                   samples: "mysql", "postgres, "SQLite", "SQL Server"</li>
     *          <li>string ['database']                     required.                   name of database.</li>
     *          <li>string ['user']                         required.                   the user to connect to the database</li>
     *          <li>string ['password']                     required.                   uses to provide password to database connection</li>
     *          <li>string ['host']                         required.                   used to provide host information for database. Ex. "localhost"</li>
     *          <li>string ['charset']                      required.                   set the charset for the tables. * 'utf8' recommended</li>
     *          <li>string ['collation']                    required.                   set the collation for the db. * 'utf8_unicode_ci' recommended</li>
     *          <li>string ['prefix']                       optional.                   used to provide a prefix to the tables. Useful if using same db for several projects. Example: "mywebsite_"</li></ul></li>
 *   *      <li>string ['theme_directory']                  required.                   set the location of where you want to put your template files.</li>
     *      <li>string ['theme_cache_directory']            required.                   set where you would like to store the cache for you template files.</li>
     * </li></ul></li></ul>
     */
    public function __construct(array $config)

    {

        $this->config = $config;

        $this->debugToggle();

        $database = new Database();

        $config[static::SITE_CONFIG_KEY][static::DATABASE_CONFIG_KEY] = $database->connect($config[static::SITE_CONFIG_KEY][static::DATABASE_CONFIG_KEY]);

        $this->template = new Template($config[static::SITE_CONFIG_KEY][static::THEME_DIRECTORY_KEY], $config[static::SITE_CONFIG_KEY][static::CACHE_DIRECTORY_KEY]);

        $this->route = new RouteSystem();

        $this->siteVariables = $config[static::SITE_CONFIG_KEY][static::VARIABLES_KEY];

    }


    protected function debugToggle()
    {


        if (isset($this->config[static::SITE_CONFIG_KEY][static::ENVIRONMENT_KEY]) && $this->config[static::SITE_CONFIG_KEY][static::ENVIRONMENT_KEY] !== static::PRODUCTION_ENVIRONMENT) {


            Debug::enable();


        }


    }


    /**
     * decodes json document based on a correctly formatted jsonDocument to build required routes and responses.
     *
     * @param string $jsonDocument input valid json that will convert to an array to set up your site structure.
     *
     * @example
     *
     */
    public function runDocument ($jsonDocument)
        
    {


        $document = json_decode($jsonDocument, 1);

        try {


            if ($document) {


                $this->build($document);

            } else {


                throw new Exception('Either missing JSON file or JSON file has errors'); die();


            }


            $this->route->run();


        } catch (Exception $e) {


            echo $e; die;

        }


    }


    /**
     * builds the application routes and responses from a properly formatted php array.
     *
     * @param array $document
     */
    public function build(array $document)

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



    /**
     * set-up the request and response events with all variables being sent to the response.
     *
     * @param mixed $content contains the configuration to provide the right response. If is string then returns a view file. If is array and contains "callback" key then returns the given method. If is array and contains ""renderData" key then it will render a view with additional specifically set variables from documents configuration.
     * @param array $httpParams send parameters from http method to view and / or callback.
     * @param array $params send additional params to view and / or callback.
     *
     * @return mixed
     */
    private function runRoute($content, array $httpParams = [], array $params = [])

    {


        if (isset($content[static::CONFIG_CALLBACK_KEY])) {


            if (!isset($content[static::CONFIG_KEY][static::PARAMS_KEY])) {


                $content[static::CONFIG_KEY][static::PARAMS_KEY] = [];


            }


            $this->connectArrays($content[static::CONFIG_KEY][static::PARAMS_KEY], [$this->siteVariables, $httpParams, $params]);


            $content[static::CONFIG_KEY][static::TEMPLATE_KEY] = $this->template;


            return call_user_func($content[static::CONFIG_CALLBACK_KEY], $content[static::CONFIG_KEY]);



        } elseif(is_string($content)) {


            $params = [];


            $this->connectArrays($params, [$this->siteVariables, $httpParams]);

            echo $this->template->render($content, $params);


        } elseif (isset($content[static::RENDER_WITH_DATA_KEY])) {


            $this->renderWithData($content[static::VIEW_KEY], $this->template, [$this->siteVariables, $httpParams, $content[static::RENDER_WITH_DATA_KEY]]);


        }


    }




    /**
     * return the response for the given <em>GET</em> http route.
     *
     * @param string $route The url path that is requested. Hint: Can be made dynamic by setting variables from path by adding a colon. Example: <em>:content_name</em> will create a variable named "content_name" to send to callback and / or view.
     * @param array $content contains the configuration to provide the right response.
     */
    public function getRoute ($route, $content)

    {


        $this->route->get($route, function ($router) use ($content) {


            $array = [];


            $this->runContent($content, $this->template, $this->connectArrays($array, [$router->request()->getQuery()->toArray(), $router->params()->getParams(), $this->siteVariables]));



        });


    }




    /**
     * return the response for the given <em>POST</em> http route.
     *
     * @param string $route The url path that is requested. Hint: Can be made dynamic by setting variables from path by adding a colon. Example: <em>:content_name</em> will create a variable named "content_name" to send to callback and / or view.
     * @param array $content contains the configuration to provide the right response.
     */
    public function postRoute ($route, $content)

    {


        $this->route->post($route, function ($router) use ($content) {


            return $this->runRoute($content, $router->request()->getPost()->toArray(), $router->params()->getParams());


        });


    }




    /**
     * return the response for the given <em>PUT</em> http route.
     *
     * @param string $route The url path that is requested. Hint: Can be made dynamic by setting variables from path by adding a colon. Example: <em>:content_name</em> will create a variable named "content_name" to send to callback and / or view.
     * @param array $content contains the configuration to provide the right response.
     */
    public function putRoute ($route, $content)

    {


        $this->route->put($route, function ($router) use ($content) {


            $putParams = array();

            parse_str($this->getRequest()->getContent(), $putParams);

            return $this->runRoute($content, $putParams, $router->params()->getParams());


    });


    }




    /**
     * return the response for the given <em>DELETE</em> http route.
     *
     * @param string $route The url path that is requested. Hint: Can be made dynamic by setting variables from path by adding a colon. Example: <em>:content_name</em> will create a variable named "content_name" to send to callback and / or view.
     * @param array $content contains the configuration to provide the right response.
     */
    public function deleteRoute($route, $content)

    {


        $this->route->put($route, function ($router) use ($content) {


            $params = array();

            parse_str($this->getRequest()->getContent(), $params);

            return $this->runRoute($content, $params, $router->params()->getParams());


        });


    }

}
