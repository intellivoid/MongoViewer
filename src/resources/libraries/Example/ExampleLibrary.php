<?php


    namespace Example;

    use Example\Classes\PrintFunctions;

    include_once(__DIR__ . DIRECTORY_SEPARATOR  . 'Classes' . DIRECTORY_SEPARATOR . 'PrintFunctions.php');

    /**
     * Class ExampleLibrary
     * @package Example
     */
    class ExampleLibrary
    {
        /**
         * @var PrintFunctions
         */
        private $PrintFunctions;

        /**
         * ExampleLibrary constructor.
         */
        public function __construct()
        {
            $this->PrintFunctions = new PrintFunctions();
        }

        /**
         * @return PrintFunctions
         */
        public function getPrintFunctions(): PrintFunctions
        {
            return $this->PrintFunctions;
        }
    }