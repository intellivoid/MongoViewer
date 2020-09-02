<?PHP
    use DynamicalWeb\DynamicalWeb;
    use DynamicalWeb\HTML;
    use Example\ExampleLibrary;
?>
<footer class="footer">
    <div class="container">
        <span class="text-muted"><?PHP HTML::print(TEXT_FOOTER_TEXT); ?></span>
        <span class="text-muted">
            <?PHP
                /** @var ExampleLibrary $ExampleLibrary */
                $ExampleLibrary = DynamicalWeb::getMemoryObject('example_library');

                $ExampleLibrary->getPrintFunctions()->SayName('Samantha Smith');
                $ExampleLibrary->getPrintFunctions()->sayAge(24);
            ?>
        </span>
    </div>
</footer>