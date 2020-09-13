<?php

    /** @noinspection PhpConstantReassignmentInspection */

    namespace DynamicalWeb;

    use Exception;

    /**
     * Functions for handling languages
     *
     * Class Language
     * @package DynamicalWeb
     */
    class Language
    {

        /**
         * Defines the current language configuration for the session
         *
         * @throws Exception
         */
        public static function loadLanguage()
        {
            if(file_exists(APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'languages') == false)
            {
                throw new Exception('The directory "languages" was not found in resources');
            }

            $LanguageDirectory = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'languages';

            if(file_exists($LanguageDirectory . DIRECTORY_SEPARATOR . APP_PRIMARY_LANGUAGE . '.json') == false)
            {
                throw new Exception('The primary language file "' . APP_PRIMARY_LANGUAGE . '" was not found in resources->languages');
            }

            define('APP_FALLBACK_LANGUAGE_FILE', $LanguageDirectory . DIRECTORY_SEPARATOR . APP_PRIMARY_LANGUAGE . '.json', false);

            if(isset($_COOKIE['language']) == false)
            {
                setcookie('language', APP_PRIMARY_LANGUAGE);
                define('APP_SELECTED_LANGUAGE', APP_PRIMARY_LANGUAGE, false);
                define('APP_SELECTED_LANGUAGE_FILE', $LanguageDirectory . DIRECTORY_SEPARATOR . APP_PRIMARY_LANGUAGE . '.json', false);
            }
            else
            {
                $FormattedCode = strtolower(stripslashes($_COOKIE['language']));
                if(file_exists($LanguageDirectory . DIRECTORY_SEPARATOR . $FormattedCode . '.json') == true)
                {
                    define('APP_SELECTED_LANGUAGE', $FormattedCode, false);
                    define('APP_SELECTED_LANGUAGE_FILE', $LanguageDirectory . DIRECTORY_SEPARATOR . $FormattedCode . '.json', false);
                }
                else
                {
                    define('APP_SELECTED_LANGUAGE', APP_PRIMARY_LANGUAGE, false);
                    define('APP_SELECTED_LANGUAGE_FILE', $LanguageDirectory . DIRECTORY_SEPARATOR . APP_PRIMARY_LANGUAGE . '.json', false);
                }
            }

            self::defineLanguageVariables();
        }

        /**
         * Changes the language
         *
         * @param string $language
         * @throws Exception
         */
        public static function changeLanguage(string $language)
        {
            $LanguageDirectory = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'languages';
            $FormattedCode = strtolower(stripslashes($language));

            if(file_exists($LanguageDirectory . DIRECTORY_SEPARATOR . $FormattedCode . '.json') == false)
            {
                return;
            }

            setCookie('language', $FormattedCode);
        }

        /**
         * Defines the language variables
         */
        public static function defineLanguageVariables()
        {
            $SelectedLanguage = json_decode(file_get_contents(APP_SELECTED_LANGUAGE_FILE), true);
            define('APP_LANGUAGE_ISO_639', $SelectedLanguage['language']['iso_639-1'], false);
        }

        /**
         * Loads the language contents for the requested page
         *
         * @param string $pageName
         */
        public static function loadPage(string $pageName)
        {
            $SelectedLanguage = json_decode(file_get_contents(APP_SELECTED_LANGUAGE_FILE), true);
            $FallbackLanguage = json_decode(file_get_contents(APP_FALLBACK_LANGUAGE_FILE), true);

            $SelectedAvailable = true;

            if(isset($SelectedLanguage['pages'][$pageName]) == false)
            {
                if(isset($FallbackLanguage['pages'][$pageName]) == false)
                {
                    return;
                }

                $SelectedAvailable = false;
            }

            if($SelectedAvailable == false)
            {
                foreach($FallbackLanguage['pages'][$pageName] as $Variable => $Value)
                {
                    define("TEXT_$Variable", $Value, false);
                }

                return;
            }

            foreach($SelectedLanguage['pages'][$pageName] as $Variable => $Value)
            {
                define("TEXT_$Variable", $Value, false);
            }

            foreach($FallbackLanguage['pages'][$pageName] as $Variable => $Value)
            {
                if(defined("TEXT_$Variable") == false)
                {
                    define("TEXT_$Variable", $Value, false);
                }
            }
        }

        /**
         * Defines all language variables for a section rather than a page
         *
         * @param string $sectionName
         */
        public static function loadSection(string $sectionName)
        {
            $SelectedLanguage = json_decode(file_get_contents(APP_SELECTED_LANGUAGE_FILE), true);
            $FallbackLanguage = json_decode(file_get_contents(APP_FALLBACK_LANGUAGE_FILE), true);

            $SelectedAvailable = true;

            if(isset($SelectedLanguage['sections'][$sectionName]) == false)
            {
                if(isset($FallbackLanguage['sections'][$sectionName]) == false)
                {
                    return;
                }

                $SelectedAvailable = false;
            }

            if($SelectedAvailable == false)
            {
                foreach($FallbackLanguage['sections'][$sectionName] as $Variable => $Value)
                {
                    define("TEXT_$Variable", $Value, false);
                }

                return;
            }

            foreach($SelectedLanguage['sections'][$sectionName] as $Variable => $Value)
            {
                define("TEXT_$Variable", $Value, false);
            }

            foreach($FallbackLanguage['sections'][$sectionName] as $Variable => $Value)
            {
                if(defined("TEXT_$Variable") == false)
                {
                    define("TEXT_$Variable", $Value, false);
                }
            }
        }
    }