<?php

namespace Greenfly\Modules\Content;

use \Illuminate\Database\Capsule\Manager as Capsule;


class Installer {


    public static function start ()
    {

        Capsule::schema()->create('types', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->text('data');
            $table->timestamps();
        });

        Capsule::schema()->create('contents', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type_name');
            $table->timestamps(); 
        });

        Capsule::schema()->create('versions', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->text('data');
            $table->string('content_name');						$table->string('locale');
            $table->timestamps();
            $table->timestamp('published_date');
            $table->integer('status');
        });


        Capsule::schema()->create('taxonomies', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Capsule::schema()->create('tags', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('taxonomy_name');
            $table->timestamps();
        });

        Capsule::schema()->create('content_tag', function ($table) {
            $table->increments('id');			            $table->integer('content_id')->unsigned();
            $table->integer('tag_id')->unsigned();
        });
    }

}

