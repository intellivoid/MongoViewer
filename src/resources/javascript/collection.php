$("#sort").on('change', function(){
    if(this.value == "desc") {
        var url = new URL(window.location.href);
        url.searchParams.set("sort", "desc");
        window.location.href = url.href;
    } else {
        var url = new URL(window.location.href);
        url.searchParams.set("sort", "asc");
        window.location.href = url.href;
    }
});