<?php


    namespace DynamicalWeb;


    /**
     * Class Request
     * @package DynamicalWeb
     */
    class Request
    {
        /**
         * Returns the request method that was used
         *
         * @return string
         */
        public static function getRequestMethod(): string
        {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        /**
         * Gets a POST parameter if it's set
         *
         * @param string $value
         * @return string
         */
        public static function getPostParameter(string $value): string
        {
            if(isset($_POST[$value]))
            {
                return $_POST[$value];
            }

            return null;
        }

        /**
         * Gets a GET parameter if it's set
         *
         * @param string $value
         * @return string
         */
        public static function getGetParameter(string $value): string
        {
            if(isset($_GET[$value]))
            {
                return $_GET[$value];
            }

            return null;
        }

        /**
         * Returns a POST/GET Parameter
         *
         * @param string $value
         * @return string
         */
        public static function getParameter(string $value): string
        {
            if(self::getGetParameter($value) !== null)
            {
                return self::getGetParameter($value);
            }

            if(self::getPostParameter($value) !== null)
            {
                return self::getPostParameter($value);
            }

            return null;
        }
    }