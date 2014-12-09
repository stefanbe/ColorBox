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
        if($('#cboxTitle').text().length) {
            var pa = Math.min(e.w - parseInt($('#cboxTitle span').css("paddingLeft")) * 2,e.w);
            $('#cboxTitle span').css({maxWidth:Math.max((($(window).width() - e.w) / 2) + pa,pa)+"px"});
        }
    }
});
