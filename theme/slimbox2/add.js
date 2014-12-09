jQuery.extend(jQuery.colorbox.settings, {
    opacity:0.7,
    title: function() { return ($(this).data('title')) ? $(this).data('title') : this.title; },
    next: jQuery.colorbox.settings.next+'<span class="cboxPrevNextBgColor"></span><span><img src="'+theme_url+'images/Next.png"></span>',
    previous: jQuery.colorbox.settings.previous+'<span class="cboxPrevNextBgColor"></span><span><img src="'+theme_url+'images/Prev.png"></span>',
    onComplete: function(e) {
        var tmp_content = $('#cboxLoadedContent');
        $('#cboxTitle').css({float:"none",top:-(e.h)+"px"}).slideUp(0);
        $('#cboxPrevious').css({
                "padding-bottom":e.h+"px",
                left:tmp_content.css("border-left-width")
            });
        $('#cboxNext').css({
                "padding-bottom":e.h+"px",
                right:tmp_content.css("border-right-width")
            });
        $('#cboxContent').trigger('mouseenter');
    },
    onOpen: function(e) {
        $('#cboxContent').on({
            mouseenter: function(event) {
                var not = $(event.target).attr("id");
                if(not == "cboxClose" || not == "cboxSlideshow" || not == "cboxCurrent")
                    return;
                if($('#cboxTitle').text().length)
                    $('#cboxTitle').stop(false,true).slideDown(jQuery.colorbox.settings.speed);
            },
            mouseleave: function(event) {
                $('#cboxTitle').stop(false,true).slideUp(jQuery.colorbox.settings.speed);
            }
        });
    },
    onClosed: function(e) {
        $('#cboxContent').off('mouseenter mouseleave');
    }
});
