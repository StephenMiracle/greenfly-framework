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
            $table->integer('type_id')->unsigned();
            $table->timestamps();
        });

        Capsule::schema()->create('versions', function ($table) {
            $table->increments('id');
            $table->text('name');
            $table->text('data');
            $table->integer('content_id')->unsigned();
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
            $table->integer('taxonomy_id')->unsigned();
            $table->timestamps();
        });

        Capsule::schema()->create('tag_version', function ($table) {
            $table->increments('id');
            $table->integer('tag_id')->unsigned();
            $table->integer('version_id')->unsigned();
        });

        Capsule::schema()->create('taxonomy_types', function ($table) {
            $table->increments('id');
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->timestamps();
        });
    }

}

