<?php


    namespace Example\Classes;

    /**
     * Class PrintFunctions
     * @package Example\Classes
     */
    class PrintFunctions
    {
        /**
         * @param string $name
         */
        public function SayName(string $name)
        {
            print("Hello, $name");
            return;
        }

        /**
         * @param int $age
         */
        public function sayAge(int $age)
        {
            print("I am $age years old");
            return;
        }
    }