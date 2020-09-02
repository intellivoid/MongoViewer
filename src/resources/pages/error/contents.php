<?PHP
    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\Runtime;
    use Example\ExampleLibrary;

    Runtime::import('Example');
    $ExampleLibrary = DynamicalWeb::setMemoryObject('example_library', new ExampleLibrary());

    throw new Exception('This is a fake error');
?>
