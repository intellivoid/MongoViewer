<?php
    use DynamicalWeb\DynamicalWeb;
?>
<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="<?PHP DynamicalWeb::getRoute("index", [], true); ?>">
        <img style="max-width:24px; margin-top: 2px; margin-right: 5px;" src="/assets/images/logo.svg"> MongoViewer
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>