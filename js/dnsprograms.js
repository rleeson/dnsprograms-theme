// Call to load the program image into a modal
jQuery('a.modal-link').click(function (e) {
	jQuery('#image-modal #modal-label').text(jQuery(this).attr('title'));
    jQuery('#image-modal img#modal-image').attr('src', jQuery(this).attr('data-image-url'));
});