<?php



namespace Greenfly;



use Illuminate\Database\Capsule\Manager as Capsule;



class Database

{

    public static function connect($config)

    {

        $capsule = new Capsule();





        $capsule->addConnection($config);



        $capsule->setAsGlobal();

        $capsule->bootEloquent();

    }

}

