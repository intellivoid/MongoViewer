<?php

    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\Runtime;
    use MongoDB\Client;

    Runtime::import("MongoDB_Driver");

    if(isset(DynamicalWeb::$globalObjects["mongo_client"]) == false)
    {
        $Configuration = DynamicalWeb::getConfiguration("mongodb");
        $MongoDB_Client = new Client(
            "mongodb://" . $Configuration['Host'] . ":" . $Configuration['Port'],
            array(
                "username" => $Configuration['Username'],
                "password" => $Configuration['Password']
            )
        );

        DynamicalWeb::setMemoryObject("mongo_client", $MongoDB_Client);
    }