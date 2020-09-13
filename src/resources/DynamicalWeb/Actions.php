<?php


    namespace DynamicalWeb;

    /**
     * Class Actions
     * @package DynamicalWeb
     */
    class Actions
    {
        /**
         * Redirects the client to another location, this only
         * works if the server hasn't sent any data back yet
         *
         * Using this function will terminate the process
         *
         * @param string $location
         */
        public static function redirect(string $location)
        {
            header("Location: $location");
            exit();
        }

        /**
         * Same as redirect but with a delay, this function will
         * terminate the process
         *
         * @param string $location
         * @param int $time
         */
        public static function delayed_redirect(string $location, int $time)
        {
            header('Refresh: ' . $time . ' URL=' . $location);
            exit();
        }
    }