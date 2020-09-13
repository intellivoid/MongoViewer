<?php

    namespace DynamicalWeb;

    use Exception;

    /**
     * Basic HTML Utilities for rendering
     *
     * Class HTML
     * @package DynamicalWeb
     */
    class HTML
    {
        /**
         * Prints HTML output
         *
         * @param string $output
         * @param bool $escape_html
         */
        public static function print(string $output, bool $escape_html = true)
        {
            if($escape_html == true)
            {
                $output = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
            }

            print($output);
        }

        /**
         * Imports a file from the sections directory in resources
         *
         * @param string $sectionName
         * @throws Exception
         */
        public static function importSection(string $sectionName)
        {
            $FormattedName = strtolower(stripslashes($sectionName));

            if(file_exists(APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $FormattedName . '.php') == false)
            {
                throw new Exception('The section file "' .  $FormattedName . '.php" was not found');
            }

            Language::loadSection($sectionName);

            /** @noinspection PhpIncludeInspection */
            include_once(APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR . $FormattedName . '.php');
        }

        /**
         * Prints the contents of a markdown file
         *
         * @param string $markdownName
         * @return bool
         * @throws Exception
         */
        public static function importMarkdown(string $markdownName): bool
        {
            $FormattedName = strtolower(stripslashes($markdownName));
            $ResourcesDirectory = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'markdown' . DIRECTORY_SEPARATOR . $FormattedName;

            if(file_exists($ResourcesDirectory) == false)
            {
                throw new Exception('The markdown resources were not found');
            }

            $FallbackFile = $ResourcesDirectory . DIRECTORY_SEPARATOR . strtolower(APP_PRIMARY_LANGUAGE) . '.md';
            $SelectedLanguageFile = $ResourcesDirectory . DIRECTORY_SEPARATOR . strtolower(APP_SELECTED_LANGUAGE) . '.md';

            if(file_exists($SelectedLanguageFile) == false)
            {
                if(file_exists($FallbackFile) == false)
                {
                    throw new Exception('No selected lanaguage or fallback language for this markdown file has been found');
                }
                else
                {
                    $MarkdownParser = new MarkdownParser();
                    print($MarkdownParser->text(file_get_contents($FallbackFile)));
                    return true;
                }
            }
            else
            {
                $MarkdownParser = new MarkdownParser();
                print($MarkdownParser->text(file_get_contents($SelectedLanguageFile)));
                return true;
            }
        }

        /**
         * Imports a HTML Sections
         *
         * @param string $resourceName
         * @throws Exception
         */
        public static function importHTML(string $resourceName)
        {
            $FormattedName = strtolower(stripslashes($resourceName));

            $LocalResource = APP_CURRENT_PAGE_DIRECTORY . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $FormattedName . '.php';
            $SharedResource = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $FormattedName . '.php';

            if(file_exists($LocalResource) == false)
            {
                if(file_exists($SharedResource) == false)
                {
                    throw new Exception('The resource file "' . $FormattedName . '.php" was not found in either local resources or shared resources');
                }
                else
                {
                    /** @noinspection PhpIncludeInspection */
                    include_once($SharedResource);
                    return ;
                }
            }
            else
            {
                /** @noinspection PhpIncludeInspection */
                include_once($LocalResource);
            }
        }

        /**
         * Imports a script from local resources or shared resources
         *
         * @param string $scriptName
         * @throws Exception
         */
        public static function importScript(string $scriptName)
        {
            $FormattedName = strtolower(stripslashes($scriptName));

            $LocalResource = APP_CURRENT_PAGE_DIRECTORY . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $FormattedName . '.php';
            $SharedResource = APP_RESOURCES_DIRECTORY . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $FormattedName . '.php';

            if(file_exists($LocalResource) == false)
            {
                if(file_exists($SharedResource) == false)
                {
                    throw new Exception('The resource file "' . $FormattedName . '.php" was not found in either local resources or shared resources');
                }
                else
                {
                    /** @noinspection PhpIncludeInspection */
                    include_once($SharedResource);
                    return ;
                }
            }
            else
            {
                /** @noinspection PhpIncludeInspection */
                include_once($LocalResource);
            }
        }
    }
