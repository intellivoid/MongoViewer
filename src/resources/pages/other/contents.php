<?PHP
    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use DynamicalWeb\Runtime;
    use Example\ExampleLibrary;

?>
<!doctype html>
<html lang="<?PHP HTML::print(APP_LANGUAGE_ISO_639); ?>">
    <head>
        <?PHP HTML::importSection('header'); ?>
        <title>Other Page</title>
    </head>

    <body>

        <header>
            <?PHP HTML::importSection('navigation'); ?>
        </header>

        <main role="main" class="container">
            <h1 class="mt-5">Test</h1>
            <p class="lead">This is another page</p>
        </main>

        <?PHP HTML::importSection('footer'); ?>

        <?PHP HTML::importSection('js_scripts'); ?>

    </body>
</html>
