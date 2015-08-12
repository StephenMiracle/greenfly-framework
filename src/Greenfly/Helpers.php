<?php
/**
 * Created by PhpStorm.
 * User: scraig
 * Date: 8/12/2015
 * Time: 3:50 PM
 */

namespace Greenfly;


trait Helpers {


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


}