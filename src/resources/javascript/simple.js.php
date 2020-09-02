<?PHP
    use DynamicalWeb\HTML;
?>

function dyn_test()
{
    alert("<?PHP HTML::print("Hello World"); ?>")
}