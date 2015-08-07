#Greenfly Framework

This is the kernel behind the Greenfly site generator. It is recommended that you use the standard Greenfly package
to get started as it creates a project that gets you set up quickly.

## Documentation

The documentation is currently under construction.


## Installation

### Composer

Create a new project with Greenfly using composer as this is the recommended route

```bash

Composer create-project Greenfly/Greenfly

```



##Basic Usage:
The Greenfly Content Management Framework is built to be easily extendable and flexible to meet your needs. All you need
is a JSON file that contains your page tree and a php config file to get started. Each page can reference either a view
file that can use the Twig template engine or use any class static method. The framework comes with the standard content
module but you can customize to meet your own needs whether extending the Greenfly module systen or using your own classes.


###Documents.json file
The documents.json file is where you will store the list of your site pages and what you want to do with them. It must
follow a certain object structure that flows as such:

```js

{

    "HTTP METHOD" : {

        "PAGE URL" :  ..

    }

}

```


For Example, the following would produce an acceptable documents.json file.


```js

{

    "get" : {

        "/" : "home.html",

        "/about" : {

            "renderData": {"mainClass": "sampleClass "},

            "view": "about.html"

        },

        "/:type_name/:content_name" : {

            "callback" : "Greenfly\\Modules\\Content\\Content" : "contentWithRelated",

            "config" : {

                "params" : {

                    "take" : 20

                }

                "render" : {

                    "view" : "article.html",

                    "data" : { "itemClass" : "class1 class2" }

                }

            }


        }

    }

}

```


The above documents.json file would produce the following page structure:


- [yoursite.com]/                     ==>     will render home.html with any site variables from the config file.

- [yoursite.com]/about                ===>    Renders about.html with site variables plus items in the
                                            renderData json object.

- [yoursite.com]/article/my-article   ===>    Calls the contentWithRelated method in the associated class and pass
                                            type_name, content_name and take as parameters for the method. It will
                                            follow by passing additional variables from the database plus the data json
                                            object in the associated render attribute to the article.html file.


###Site.php config file
The site.php config file is a php array that gets passed through Greenfly when starting the site. It contains
all of the necessary items to boot up and get the framework working correctly. There are a few keys that are required in
the site to function.

```php

$config = [

    'site' => [

        'variables' => [                                    // variables that get passed to your html template files
            'siteName' => 'My New Site',
            'siteDescription' => 'a super amazing site ,
            'siteUrl' => http://mysite.com,
            'pageTitle' => 'my awesome website',
            'metaDescription' => 'check out my new site',
            'metaKeywords' => ''
        ],
        'database' => [                                         // The database information
              'driver'    => 'mysql',
              'database'  => 'pursesbliss',
              'username'  => 'root',
              'password'  => '',
              'charset'   => 'utf8',
              'collation' => 'utf8_unicode_ci',
              'prefix'    => '',
              'host'      => 'localhost'
                          ],
        'theme_directory' => '../themes',                       // the location where you will place your template files
        'theme_cache_directory' => '../cache'                   // the cache directory
    ]
];

```


###public/index.php file

```php

use Greenfly\App as App;

include '../vendor/autoload.php';
include '../config/site.php';

$jsonDoc = file_get_contents('../documents.json');


$app = new App($config);
$app->runDocument($jsonDoc);

```


###themes/standard/home.html

```html

{{dump()}}                               // will dump all variables allowed to be used in this file

<div>
<h1>Welcome to {{siteName}}.</h1>        // will show the siteName variable

<p>{{siteDescription}} </p>             // will show the siteDescription variable

Please enjoy
</div>

```


###http parameters

All get and post parameters get pushed into the class method and / or rendered view file that can be accessed. You can
see the following as an example.

http://mysite.com/about?cat_owner=Lary&cat_name=Killa

Sample view file:

```html

<p>The owner of {{cat_name}} is {{cat_owner}}</p>

```


Sample class callback method:

```php
Class MyModule {

    public static function catOwner ($config)

    {

        $catOwner = $config['params']['cat_owner'];

        $catName = $config['params']['cat_name'];

        echo $catName . 'is owned by: ' . $catOwner . ' whom is very odd.';

    }

}
```

##Summary
I hope you enjoy using Greenfly. This content framework really filled in the gap for me where the popular CMS' were too
inflexible and time-consuming to fit my needs but the application frameworks were too robust and feature-rich beyond
what I need. I don't think this CMF is for everyone or all sites. I think its useful for building interactive
content websites and business websites. I'd recommend sticking with Wordpress if you want to create a heavy blog / article
website or use a larger MVC framework such as Laravel if you need a more in-depth application.

Greenfly is designed for professional web designers and developers whom want to quickly and easily create great
websites for their clients.