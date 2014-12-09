jQuery.extend(jQuery.colorbox.settings, {
    opacity:0.7,
    title: function() {
        if(typeof $(this).data('title') != "undefined")
            return ($(this).data('title')) ? '<span>'+$(this).data('title')+'</span>' : '';
        return (this.title) ? '<span>'+this.title+'</span>' : '';
    },
    next: jQuery.colorbox.settings.next+'<span></span>',
    previous: jQuery.colorbox.settings.previous+'<span></span>',
    onComplete: function(e) {
        $('#cboxSlideshow, #cboxClose').css({visibility:'visible'});
        if($('#cboxTitle').text().length)
            $('#cboxTitle').css({'margin-left':-((e.mw - e.w) / 2)+"px",right:'50%'});
        else
            $('#cboxTitle').css({'margin-left':0,right:0});
    },
    onLoad: function(e) {
        $('#cboxSlideshow, #cboxClose').css({visibility:'hidden'});
    }
});
