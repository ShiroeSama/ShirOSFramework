<?php

    /**
     * --------------------------------------------------------------------------
     *   @Copyright : License MIT 2017
     *
     *   @Author : Alexandre Caillot
     *   @WebSite : https://www.shiros.fr
     *
     *   @File : RequestBuilder.php
     *   @Created_at : 26/03/2018
     *   @Update_at : 26/03/2018
     * --------------------------------------------------------------------------
     */

    namespace Luna\Component\HTTP\Request;

    class RequestBuilder
    {
        public static function create(): Request
        {
            return new Request($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER, []);
        }
    }
?>