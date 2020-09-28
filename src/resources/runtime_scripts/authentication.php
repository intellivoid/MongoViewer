<?php

    $AuthenticationConfiguration = \DynamicalWeb\DynamicalWeb::getConfiguration("authentication");

    if((bool)$AuthenticationConfiguration["require_authentication"] == true)
    {
        if (!isset($_SERVER['PHP_AUTH_USER']))
        {
            header('WWW-Authenticate: Basic realm="DynamicalWeb Authentication"');
            header('HTTP/1.0 401 Unauthorized');

            \DynamicalWeb\Page::staticResponse(
                "DynamicalWeb", "Authentication Required",
                "Authentication is required in order to access this resource"
            );
            exit(0);
        }
        else
        {
            $Authenticated = true;

            if(hash("sha256", $_SERVER['PHP_AUTH_USER']) !== hash("sha256", $AuthenticationConfiguration["username"]))
            {
                $Authenticated = false;
            }

            if(hash("sha256", $_SERVER['PHP_AUTH_PW']) !== hash("sha256", $AuthenticationConfiguration["password"]))
            {
                $Authenticated = false;
            }

            if($Authenticated == false)
            {
                header('WWW-Authenticate: Basic realm="DynamicalWeb Authentication"');
                header('HTTP/1.0 401 Unauthorized');

                \DynamicalWeb\Page::staticResponse(
                    "DynamicalWeb", "Incorrect Credentials",
                    "Authentication is required in order to access this resource"
                );
                exit(0);
            }
        }
    }

