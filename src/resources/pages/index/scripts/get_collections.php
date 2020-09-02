<?php

    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use MongoDB\Client;

    HTML::importScript("create_connection");

    /** @var Client $MongoClient */
    $MongoClient = DynamicalWeb::getMemoryObject("mongo_client");
    $Collections = array();

    foreach($MongoClient->listDatabases() as $database)
    {
        $Collections[$database->getName()] = array();

        foreach($MongoClient->selectDatabase($database->getName())->listCollections() as $collection)
        {
            $Collections[$database->getName()][] = $collection->getName();
        }
    }

    DynamicalWeb::setArray("collections", $Collections);