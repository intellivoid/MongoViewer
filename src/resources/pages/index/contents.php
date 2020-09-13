<?PHP

    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;

?>
<!doctype html>
<html lang="<?PHP HTML::print(APP_LANGUAGE_ISO_639); ?>">
    <head>
        <?PHP HTML::importSection('header'); ?>
        <title><?PHP HTML::print("MongoViewer"); ?></title>
    </head>

    <body>
        <?PHP HTML::importSection("navigation"); ?>
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                    <div class="sidebar-sticky pt-3">
                        <?PHP
                            HTML::importScript("get_collections");
                            $Collection = DynamicalWeb::getArray("collections");

                            foreach($Collection as $database_name => $collection)
                            {
                                ?>
                                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                                    <span><?PHP HTML::print($database_name); ?></span>
                                </h6>
                                <ul class="nav flex-column mb-2">
                                    <?PHP
                                        sort($Collection[$database_name]);
                                        foreach($Collection[$database_name] as $collection_name)
                                        {
                                            ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?PHP DynamicalWeb::getRoute("view_collection", array("db" => $database_name, "col" => $collection_name), true); ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    <?PHP HTML::print($collection_name); ?>
                                                </a>
                                            </li>
                                            <?PHP
                                        }
                                    ?>
                                </ul>
                                <?PHP
                            }
                        ?>
                    </div>
                </nav>
                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                        <h1 class="h2">Select a collection</h1>
                    </div>
                </main>
            </div>
        </div>
        <?PHP HTML::importSection("js_scripts"); ?>
    </body>
</html>
