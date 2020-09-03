<?php

    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use DynamicalWeb\Page;
    use MongoDB\Client;

    if(isset($_GET["db"]) == false)
    {
        Page::staticResponse(
            "MongoViewer Error", "Missing parameter", "The parameter 'db' is missing"
        );
    }

    if(isset($_GET["col"]) == false)
    {
        Page::staticResponse(
            "MongoViewer Error", "Missing parameter", "The parameter 'col' is missing"
        );
    }

    HTML::importScript("create_connection");

    /** @var Client $MongoClient */
    $MongoClient = DynamicalWeb::getMemoryObject("mongo_client");

    try
    {
        $Database = $MongoClient->selectDatabase($_GET["db"]);
    }
    catch(Exception $exception)
    {
        Page::staticResponse(
            "MongoViewer Error", "Select Database Error", $exception->getMessage()
        );
    }

    try
    {
        $Collection = $Database->selectCollection($_GET["col"]);
    }
    catch(Exception $exception)
    {
        Page::staticResponse(
            "MongoViewer Error", "Select Collection Error", $exception->getMessage()
        );
    }

    DynamicalWeb::setMemoryObject("database", $Database);
    DynamicalWeb::setMemoryObject("collection", $Collection);