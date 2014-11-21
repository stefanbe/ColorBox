jQuery.extend(jQuery.colorbox.settings, {
    title: function() { return ($(this).data('title')) ? $(this).data('title') : this.title; }
});