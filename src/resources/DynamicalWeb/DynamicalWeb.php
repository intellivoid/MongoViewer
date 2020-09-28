<?php

    namespace DynamicalWeb;

    use Exception;

    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Actions.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Client.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'HTML.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Javascript.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'JSMin.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Language.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'MarkdownParser.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Page.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Request.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Router.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Runtime.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'Utilities.php');

    /**
     * Main DynamicalWeb Library
     *
     * Class DynamicalWeb
     * @package DynamicalWeb
     */
    class DynamicalWeb
    {
        /**
         * An array of already loaded libraries
         *
         * @var array
         */
        public static $loadedLibraries = [];

        /**
         * An array of objects that are temporarily stored in memory
         *
         * @var array
         */
        public static $globalObjects = [];

        /**
         * An array of variables that are temporarily stored in memory
         *
         * @var array
         */
        public static $globalVariables = [];

        /**
         * @var Router
         */
        public static $router;

        /**
         * @throws Exception
         */
        public static function initalize()
        {
            DynamicalWeb::defineVariables();
            Runtime::runEventScripts('on_request');
            self::processRequest();
            Runtime::runEventScripts('after_request');
        }

        /**
         * Defines the important variables for DynamicalWeb
         */
        public static function defineVariables()
        {
            $ClientIP = Client::getClientIP();
            if($ClientIP == "::1")
            {
                $ClientIP = "127.0.0.1";
            }

            define("CLIENT_REMOTE_HOST", $ClientIP);
            define("CLIENT_USER_AGENT", Client::getUserAgentRaw());

            try
            {
                $UserAgentParsed = Utilities::parse_user_agent(CLIENT_USER_AGENT);
            }
            catch(Exception $exception)
            {
                $UserAgentParsed = array();
            }

            if($UserAgentParsed['platform'])
            {
                define("CLIENT_PLATFORM", $UserAgentParsed['platform']);
            }
            else
            {
                define("CLIENT_PLATFORM", 'Unknown');
            }

            if($UserAgentParsed['browser'])
            {
                define("CLIENT_BROWSER", $UserAgentParsed['browser']);
            }
            else
            {
                define("CLIENT_BROWSER", 'Unknown');
            }

            if($UserAgentParsed['version'])
            {
                define("CLIENT_VERSION", $UserAgentParsed['version']);
            }
            else
            {
                define("CLIENT_VERSION", 'Unknown');
            }

            $ServerInformation = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'dynamicalweb.json');
            $ServerInformation = json_decode($ServerInformation, true);

            define("DYNAMICAL_WEB_AUTHOR", $ServerInformation['AUTHOR']);
            define("DYNAMICAL_WEB_COMPANY", $ServerInformation['COMPANY']);
            define("DYNAMICAL_WEB_VERSION", $ServerInformation['VERSION']);
        }

        /**
         * Returns a defined variable, returns null if it doesn't exist
         *
         * @param string $var
         * @return mixed|null
         */
        public static function getDefinedVariable(string $var)
        {
            if(defined($var))
            {
                return constant($var);
            }

            return null;
        }

        /**
         * Returns an array of "system" defined variables created by DynamicalWeb
         *
         * @return array
         */
        public static function getDefinedVariables()
        {
            return array(
                'DYNAMICAL_WEB_AUTHOR' => self::getDefinedVariable('DYNAMICAL_WEB_AUTHOR'),
                'DYNAMICAL_WEB_COMPANY' => self::getDefinedVariable('DYNAMICAL_WEB_COMPANY'),
                'DYNAMICAL_WEB_VERSION' => self::getDefinedVariable('DYNAMICAL_WEB_VERSION'),
                'CLIENT_REMOTE_HOST' => self::getDefinedVariable('CLIENT_REMOTE_HOST'),
                'CLIENT_USER_AGENT' => self::getDefinedVariable('CLIENT_USER_AGENT'),
                'CLIENT_PLATFORM' => self::getDefinedVariable('CLIENT_PLATFORM'),
                'CLIENT_BROWSER' => self::getDefinedVariable('CLIENT_BROWSER'),
                'CLIENT_VERSION' => self::getDefinedVariable('CLIENT_VERSION'),
                'APP_HOME_PAGE' => self::getDefinedVariable('APP_HOME_PAGE'),
                'APP_PRIMARY_LANGUAGE' => self::getDefinedVariable('APP_PRIMARY_LANGUAGE'),
                'APP_RESOURCES_DIRECTORY' => self::getDefinedVariable('APP_RESOURCES_DIRECTORY'),
                'APP_CURRENT_PAGE' => self::getDefinedVariable('APP_CURRENT_PAGE'),
                'APP_CURRENT_PAGE_DIRECTORY' => self::getDefinedVariable('APP_CURRENT_PAGE_DIRECTORY'),
                'APP_SELECTED_LANGUAGE' => self::getDefinedVariable('APP_SELECTED_LANGUAGE'),
                'APP_SELECTED_LANGUAGE_FILE' => self::getDefinedVariable('APP_SELECTED_LANGUAGE_FILE'),
                'APP_FALLBACK_LANGUAGE_FILE' => self::getDefinedVariable('APP_FALLBACK_LANGUAGE_FILE'),
                'APP_LANGUAGE_ISO_639' => self::getDefinedVariable('APP_LANGUAGE_ISO_639')
            );
        }

        /**
         * Loads the application resources
         *
         * @param string $resourcesDirectory
         * @throws Exception
         */
        public static function loadApplication(string $resourcesDirectory)
        {
            if(file_exists($resourcesDirectory . DIRECTORY_SEPARATOR . 'configuration.json') == false)
            {
                throw new Exception('The file "configuration.json" was not found in resources');
            }

            $Configuration = json_decode(file_get_contents($resourcesDirectory . DIRECTORY_SEPARATOR . 'configuration.json'), true);

            if(count($Configuration['router']) == 0)
            {
		var_dump($Configuration);
                throw new Exception('No pages has been defined');
            }

            define('APP_HOME_PAGE', $Configuration['router'][0]['path'], false);
            define('APP_PRIMARY_LANGUAGE', $Configuration['primary_language'], false);
            define('APP_RESOURCES_DIRECTORY', $resourcesDirectory, false);

            Language::loadLanguage();
            Runtime::importPpm();
            Runtime::runEventScripts('initialize'); // Run events at initialize

            self::mapRoutes();
        }

        /**
         * Routes all available routes
         *
         * @throws Exception
         */
        public static function mapRoutes()
        {
            self::$router = new Router();

            self::$router->map('GET|POST', '/change_language', function(){
                if(isset($_GET['language']))
                {
                    try
                    {
                        Language::changeLanguage($_GET['language']);
                    }
                    catch (Exception $e)
                    {
                        Page::staticResponse('DynamicalWeb Error', 'DynamicalWeb Internal Server Error', $e->getMessage());
                    }
                }
                Actions::redirect(APP_HOME_PAGE);
            }, 'change_language');

            self::$router->map('GET', '/compiled_assets/js/[a:resource].js', function($resource){
                Javascript::loadResource($resource, false);
            }, 'resources_js');

            if(Page::exists('500'))
            {
                self::$router->map('GET|POST', '/error', function(){
                    Page::load('500');
                }, '500');
            }
            else
            {
                self::$router->map('GET|POST', '/error', function(){
                    Page::staticResponse(
                        'Internal Server Error', 'Server Error',
                        'There was an unexpected error while trying to handle your request'
                    );
                }, '500');
            }

            self::$router->map('GET', '/compiled_assets/js/[a:resource].min.js', function($resource){
                Javascript::loadResource($resource, true);
            }, 'resources_min.js');

            $configuration = self::getWebConfiguration();
            foreach($configuration['router'] as $Route)
            {
                self::$router->map('GET|POST', $Route['path'], function() use ($Route){
                    Page::load($Route['page']);
                }, $Route['page']);
            }
        }

        /**
         * Processes the request
         *
         * @throws Exception
         */
        public static function processRequest()
        {
            $configuration = self::getWebConfiguration();
            $match = DynamicalWeb::$router->match();

            // call closure or throw 404 status
            if(is_array($match) && is_callable( $match['target']))
            {
                try
                {
                    call_user_func_array($match['target'], $match['params']);
                }
                catch(Exception $exception)
                {
                    self::handleException($exception, (bool)$configuration['debugging_mode']);
                }
            }
            else
            {
                self::handleNotFound();
            }
        }

        /** @noinspection PhpDocMissingThrowsInspection */
        /**
         * Generates a route for the requested page
         *
         * @param string $page
         * @param array $parameters
         * @param bool $print
         * @return string
         */
        public static function getRoute(string $page, array $parameters = [], bool $print = false): string
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $url = self::$router->generate($page);
            if(count($parameters) > 0)
            {
                $url .= '?' . http_build_query($parameters);
            }

            if($print)
            {
                HTML::print($url, false);
            }

            return $url;
        }

        /**
         * Handles a 404 not found error
         *
         * @throws Exception
         */
        public static function handleNotFound()
        {
            http_response_code(404);
            if(Page::exists('404') == true)
            {
                Page::load('404');
            }
            else
            {
                Page::staticResponse(
                    'Not Found',
                    '404 Not Found',
                    'The page you were looking for was not found'
                );
            }

            exit();
        }

        /**
         * Handles the exception
         *
         * @param Exception $exception
         * @param bool $debug
         * @throws Exception
         */
        public static function handleException(Exception $exception, bool $debug = false)
        {
            http_response_code(500);

            if($debug == true)
            {
                $Body = "Debugging information regarding the exception can be found below<br/><br/><hr/>\n";

                $Body .= "<h2>Exception Details</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r($exception, true);
                $Body .= "</pre>\n<hr/>";

                $Body .= "<h2>Dynamic Object Memory</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r(DynamicalWeb::$globalObjects, true);
                $Body .= "</pre>\n<hr/>";

                $Body .= "<h2>Dynamic Variable Memory</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r(DynamicalWeb::$globalVariables, true);
                $Body .= "</pre>\n<hr/>";

                $Body .= "<h2>Dynamic Router Memory</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r(DynamicalWeb::$router, true);
                $Body .= "</pre>\n<hr/>";

                $Body .= "<h2>Loaded Libraries</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r(DynamicalWeb::$loadedLibraries, true);
                $Body .= "</pre>\n<hr/>";

                $Body .= "<h2>DynamicalWeb Details</h2>\n";
                $Body .= "<pre>";
                $Body .= print_r(self::getDefinedVariables(), true);
                $Body .= "</pre>";

                $Body = str_ireplace('.php', '.bin', $Body);
                $Body = str_ireplace('.json', '.ziproto', $Body);

                Page::staticResponse('Internal Server Error', 'Server Error', $Body);
            }
            else
            {
                Actions::redirect(DynamicalWeb::getRoute('500'));
            }

            exit();
        }

        /**
         * Gets the current web configuration
         *
         * @return array
         */
        public static function getWebConfiguration(): array
        {
            $ConfigurationFile = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'configuration.json';
            $Contents = file_get_contents($ConfigurationFile);
            return json_decode($Contents, true);
        }

        /**
         * Imports and loads a custom library server-sided
         *
         * This function will be removed in the future, use
         * Runtime instead to import runtime scripts/libraries
         *
         * @param string $libraryName
         * @param string $libraryDirectory
         * @param string $libraryLoader
         * @deprecated Use Runtime's import function instead
         * @throws Exception
         */
        public static function loadLibrary(string $libraryName, string $libraryDirectory, string $libraryLoader)
        {
            if(file_exists(APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . $libraryDirectory) == false)
            {
                throw new Exception(sprintf("The requested library \"%s\" cannot be loaded because the directory was not found", $libraryName));
            }

            if(file_exists(APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . $libraryDirectory . DIRECTORY_SEPARATOR . $libraryLoader) == false)
            {
                throw new Exception(sprintf("The requested library \"%s\" cannot be loaded because the loader was not found", $libraryName));
            }

            /** @noinspection PhpIncludeInspection */
            include_once(sprintf("%s%slibraries%s%s%s%s", APP_RESOURCES_DIRECTORY, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $libraryDirectory, DIRECTORY_SEPARATOR, $libraryLoader));
        }

        /**
         * Returns an existing configuration
         *
         * @param string $configuration_name
         * @return array
         * @throws Exception
         */
        public static function getConfiguration(string $configuration_name): array
        {
            $file = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . $configuration_name . '.json';

            if(file_exists($file) == false)
            {
                throw new Exception("The requested configuration '$configuration_name' does not exist in the shared resources folder");
            }

            return json_decode(file_get_contents($file), true);
        }

        /**
         * Sets an object to memory, and returns the object that's stored in memory
         *
         * @param string $variable_name
         * @param $object
         * @return mixed
         */
        public static function setMemoryObject(string $variable_name, $object)
        {
            DynamicalWeb::$globalObjects[$variable_name] = $object;
            return DynamicalWeb::$globalObjects[$variable_name];
        }

        /**
         * Gets an object from memory, if not set then it will return null
         *
         * @param string $variable_name
         * @return mixed|null
         */
        public static function getMemoryObject(string $variable_name)
        {
            if(isset(DynamicalWeb::$globalObjects[$variable_name]) == false)
            {
                return null;
            }

            return DynamicalWeb::$globalObjects[$variable_name];
        }

        /**
         * Sets a global string variable and returns the value from memory
         *
         * @param string $name
         * @param string $value
         * @return string
         */
        public static function setString(string $name, string $value): string
        {
            DynamicalWeb::$globalVariables['db 0x77'][$name] = $value;
            return DynamicalWeb::$globalVariables['db 0x77'][$name];
        }

        /**
         * Returns an existing global string variable
         *
         * @param string $name
         * @return string
         * @throws Exception
         */
        public static function getString(string $name): string
        {
            if(isset(DynamicalWeb::$globalVariables['db 0x77'][$name]) == false)
            {
                throw new Exception('"' . $name . '" is not defined in globalObjects[db 0x77]');
            }

            return DynamicalWeb::$globalVariables['db 0x77'][$name];
        }

        /**
         * Sets a global integer variable and returns the value from memory
         *
         * @param string $name
         * @param int $value
         * @return int
         */
        public static function setInt32(string $name, int $value): int
        {
            DynamicalWeb::$globalVariables['db 0x26'][$name] = $value;
            return DynamicalWeb::$globalVariables['db 0x26'][$name];
        }

        /**
         * returns an existing global integer variable
         *
         * @param string $name
         * @return int
         * @throws Exception
         */
        public static function getInt32(string $name): int
        {
            if(isset(DynamicalWeb::$globalVariables['db 0x26'][$name]) == false)
            {
                throw new Exception('"' . $name . '" is not defined in globalObjects[db 0x26]');
            }

            return DynamicalWeb::$globalVariables['db 0x26'][$name];
        }

        /**
         * Sets a global float variable and returns the value from memory
         *
         * @param string $name
         * @param float $value
         * @return float
         */
        public static function setFloat(string $name, float $value): float
        {
            DynamicalWeb::$globalVariables['db 0x29'][$name] = $value;
            return DynamicalWeb::$globalVariables['db 0x29'][$name];
        }

        /**
         * Returns an existing global float variable
         *
         * @param string $name
         * @return float
         * @throws Exception
         */
        public static function getFloat(string $name): float
        {
            if(isset(DynamicalWeb::$globalVariables['db 0x29'][$name]) == false)
            {
                throw new Exception('"' . $name . '" is not defined in globalObjects[db 0x29]');
            }

            return DynamicalWeb::$globalVariables['db 0x29'][$name];
        }

        /**
         * Sets a global boolean variable and returns the value from memory
         *
         * @param string $name
         * @param bool $value
         * @return bool
         */
        public static function setBoolean(string $name, bool $value): bool
        {
            DynamicalWeb::$globalVariables['db 0x43'][$name] = (int)$value;
            return (bool)DynamicalWeb::$globalVariables['db 0x43'][$name];
        }

        /**
         * Returns an existing global boolean variable
         *
         * @param string $name
         * @return bool
         * @throws Exception
         */
        public static function getBoolean(string $name): bool
        {
            if(isset(DynamicalWeb::$globalVariables['db 0x43'][$name]) == false)
            {
                throw new Exception('"' . $name . '" is not defined in globalObjects[db 0x43]');
            }

            return (bool)DynamicalWeb::$globalVariables['db 0x43'][$name];
        }

        /**
         * Sets a global array variable and returns the value from memory
         *
         * @param string $name
         * @param array $value
         * @return array
         */
        public static function setArray(string $name, array $value): array
        {
            DynamicalWeb::$globalVariables['db 0x83'][$name] = $value;
            return DynamicalWeb::$globalVariables['db 0x83'][$name];
        }

        /**
         * Returns an existing global array variable
         *
         * @param string $name
         * @return bool
         * @throws Exception
         */
        public static function getArray(string $name): array
        {
            if(isset(DynamicalWeb::$globalVariables['db 0x83'][$name]) == false)
            {
                throw new Exception('"' . $name . '" is not defined in globalObjects[db 0x83]');
            }

            return DynamicalWeb::$globalVariables['db 0x83'][$name];
        }
    }
