<?php

    /**
     * --------------------------------------------------------------------------
     *   @Copyright : License MIT 2017
     *
     *   @Author : Alexandre Caillot
     *   @WebSite : https://www.shiros.fr
     *
     *   @File : handler.php
     *   @Created_at : 15/03/2018
     *   @Update_at : 15/03/2018
     * --------------------------------------------------------------------------
     */

    /*
    |--------------------------------------------------------------------------
    | Handler Configuration
    |--------------------------------------------------------------------------
    |
    */

    $Handler = [

        /*
        |--------------------------------------------------------------------------
        | Routing Handler
        |--------------------------------------------------------------------------
        |
        */

        'Routing' => [

            /*
            |--------------------------------------------------------------------------
            | Exception Handler
            |--------------------------------------------------------------------------
            |
            |   Structure :
            |       'Exception' => [
            |           'class' => Namespace + Class
            |           'method' => Name of callback | Default : 'onHandlerEvent()'
            |       ]
            |
            */

            'Exception' => NULL
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Return Handler Configuration
    |--------------------------------------------------------------------------
    |
    */

    return $Config;
?>