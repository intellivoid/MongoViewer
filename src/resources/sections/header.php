<?php
use DynamicalWeb\HTML;
?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="DynamicalWeb Example Template">
<meta name="author" content="Zi Xing Narrakas">

<!-- Bootstrap core CSS -->
<link href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/css/app.css?<?=time()?>" rel="stylesheet">
<link href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/css/dashboard.css" rel="stylesheet">
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

<!-- Favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/apple-touch-icon.png?v=Omry633dkY">
<link rel="icon" type="image/png" sizes="32x32" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/favicon-32x32.png?v=Omry633dkY">
<link rel="icon" type="image/png" sizes="194x194" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/favicon-194x194.png?v=Omry633dkY">
<link rel="icon" type="image/png" sizes="192x192" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/android-chrome-192x192.png?v=Omry633dkY">
<link rel="icon" type="image/png" sizes="16x16" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/favicon-16x16.png?v=Omry633dkY">
<link rel="manifest" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/site.webmanifest?v=Omry633dkY">
<link rel="mask-icon" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/safari-pinned-tab.svg?v=Omry633dkY" color="#343a40">
<link rel="shortcut icon" href="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/favicon.ico?v=Omry633dkY">
<meta name="apple-mobile-web-app-title" content="MongoViewer">
<meta name="application-name" content="MongoViewer">
<meta name="msapplication-TileColor" content="#343a40">
<meta name="msapplication-config" content="/92f3f752-4991-4424-a5f3-43febf0c8d2d/assets/favicon/browserconfig.xml?v=Omry633dkY">
<meta name="theme-color" content="#343a40">
<?PHP HTML::importSection("js_scripts"); ?>
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        var actions = $("#filterTable td:last-child").html();

        // Append table with add row form on add new button click
        $(".add-new").click(function(){
            $(this).attr("disabled", "disabled");
            var index = $("table tbody tr:last-child").index();
            var row = '<tr>' +
            '<td>'+($("#filter_by_base")[0].outerHTML).replace("filter_by_base", "filter_by").replace("hidden", "").replace("disabled", "")+'</td>' +
            '<td><input type="text" class="form-control" name="filter_value[]" id="filter_value"></td>' +
            '<td style="text-align: center">' + actions + '</td>' +
            '</tr>';
            $("table").append(row);
            $("table tbody tr").eq(index + 1).find(".add, .edit").toggle();
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Add row on add button click
        $(document).on("click", ".add", function(){
            var empty = false;

            var input = $(this).parents("tr").find('input[type="text"]');
            input.each(function(){
                if(!$(this).val()){
                    $(this).addClass("error");
                    empty = true;
                } else{
                    $(this).removeClass("error");
                }
            });

            var select = $(this).parents("tr").find('select[class="form-control"]');
            console.log(select[0]);
            if(select.val() === undefined || select.val() === "No Value"){
                select.addClass("error");
                empty = true;
            } else{
                select.removeClass("error");
            }

            $(this).parents("tr").find(".error").first().focus();

            if(!empty){
                select.each(function(){
                    $(this).parent("td").html($(this).val()+"<input value='"+$(this).val()+"' type='hidden' name='filter_by[]'>");
                });

                input.each(function(){
                    $(this).parent("td").html($(this).val()+"<input value='"+$(this).val()+"' type='hidden' name='filter_value[]'>");
                });

                $(this).parents("tr").find(".add, .edit").toggle();
                $(".add-new").removeAttr("disabled");
            }
        });

        // Edit row on edit button click
        $(document).on("click", ".edit", function(){
            $(this).parents("tr").find("td:not(:last-child)").each(function(k, v){
                if(k === 0) {
                    let setValueFB = $(this).text()
                    $(this).html($("#filter_by_base")[0].outerHTML);
                    $(this).val(setValueFB);
                    $(this).contents().children().each(function(){
                        if($(this).val() == setValueFB) {
                            $(this).prop("selected", "selected");
                        }
                    })
                    $(this).contents().prop("id", "filter_by");
                    $(this).contents().prop("name", "filter_by[]");
                    $(this).contents().prop("disabled", false);
                    $(this).contents().prop("hidden", false);
                }
                if(k === 1) {
                    $(this).html('<input type="text" class="form-control" name="filter_value[]" value="' + $(this).text() + '">');
                }
            });

            $(this).parents("tr").find(".add, .edit").toggle();
            $(".add-new").attr("disabled", "disabled");
        });

        // Delete row on delete button click
        $(document).on("click", ".delete", function(){
            $(this).parents("tr").remove();
            $(".add-new").removeAttr("disabled");
            $('.tooltip'). remove();
        });

        $("#filter_by").on("change", function(){
            $(this).val(this.value);
        });
    });
</script>