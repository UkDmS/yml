jQuery( document ).ready(function() {
    jQuery('#reload').click(function(e){
        e.preventDefault();
            jQuery.ajax({ type: "POST",
                url: "/wp-content/plugins/yml/yml.php" });
            });
});