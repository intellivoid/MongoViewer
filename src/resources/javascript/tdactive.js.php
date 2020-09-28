$(function(){
    $("td").click(function(){
        if($(this).hasClass("td-active")) {
            $(this).removeClass("td-active");
        } else {
            $(this).addClass("td-active");
        }
    })
});