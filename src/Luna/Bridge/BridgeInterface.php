<?php

    /**
     * --------------------------------------------------------------------------
     *   @Copyright : License MIT 2017
     *
     *   @Author : Alexandre Caillot
     *   @WebSite : https://www.shiros.fr
     *
     *   @File : RouterInterface.php
     *   @Created_at : 14/03/2018
     *   @Update_at : 14/03/2018
     * --------------------------------------------------------------------------
     */

    namespace Luna\Bridge;

    interface BridgeInterface
    {
        /**
         * Make bridge between the app and the framework
         */
        public function bridge();

        /**
         * Allow to take some information about a bridge
         * Like method to class for an Handler
         *
         * @param string $key
         */
        public function getParameters(string $key);
    }
?>