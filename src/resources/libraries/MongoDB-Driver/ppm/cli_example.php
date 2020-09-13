<?php

    function getBooleanInput($message)
    {
        print($message . " [Y/n] ");

        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);

        if(trim(strtolower($line)) != 'y')
        {
            return false;
        }

        return true;
    }

    print("Hello!" . PHP_EOL);
    $awesome = getBooleanInput("Are you awesome? ");

    if($awesome)
    {
        print("You answered yes" . PHP_EOL);
    }
    else
    {
        print("You answered no" . PHP_EOL);
    }

    print("Exiting with code 50" . PHP_EOL);
    exit(50);