<?php
    /**
     * DynamicalWeb Bootstrap v2.0.0.1
     */

    // Load the application resources
    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\Page;

    require __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'DynamicalWeb' . DIRECTORY_SEPARATOR . 'DynamicalWeb.php';

    try
    {
        DynamicalWeb::loadApplication(__DIR__ . DIRECTORY_SEPARATOR . 'resources');
    }
    catch (Exception $e)
    {
        Page::staticResponse('DynamicalWeb Error', 'DynamicalWeb Internal Server Error', $e->getMessage());
        exit();
    }

    try
    {
        DynamicalWeb::initalize();
    }
    catch (Exception $e)
    {
        Page::staticResponse("DynamicalWeb", "DynamicalWeb Fatal Error", $e->getMessage());
    }