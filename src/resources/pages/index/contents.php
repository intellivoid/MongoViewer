<?PHP
    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use DynamicalWeb\Javascript;
    use DynamicalWeb\Runtime;

?>
<!doctype html>
<html lang="<?PHP HTML::print(APP_LANGUAGE_ISO_639); ?>">
    <head>
        <?PHP HTML::importSection('header'); ?>
        <title><?PHP HTML::print("MongoViewer"); ?></title>
    </head>

    <body>

        <header>
            <?PHP HTML::importSection('navigation'); ?>
        </header>

        <main role="main" class="container">
            <h1 class="mt-5">Header</h1>
        </main>

        <?PHP HTML::importSection('footer'); ?>
        <?PHP HTML::importSection('js_scripts'); ?>

    </body>
</html>
