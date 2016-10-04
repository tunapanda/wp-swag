jQuery(document).ready(function($) {

	// Make the metaboxes work.
	$(".handlediv").click(function() {
		console.log("here...");
		$(this).closest(".postbox").toggleClass('closed');
	});

	// Initialize the date time picker for timestamp fields.
	$('.wpcrud-timestamp').datetimepicker({
		format: 'Y-m-d H:i'
	});

	// Initialize media-image references.
	var fileFrame;
	var mediaImageId;
	$('.wpcrud-media-image-link').on('click', function(event) {
		mediaImageId = $(this).attr("media-image-id");
		event.preventDefault();

		if (!fileFrame) {
			fileFrame = wp.media.frames.file_frame = wp.media({
				title: "Select Media Library Image", //$(this).data( 'File upload' ),
				button: {
					text: "Select",
				},
				multiple: false
			});

			// When an image is selected, run a callback.
			fileFrame.on('select', function() {
				attachment = fileFrame.state().get('selection').first().toJSON();
				$("#" + mediaImageId + "-image").attr("src", attachment.url);
				$("#" + mediaImageId).val(attachment.url);
			});
		}

		fileFrame.options.title = "asdfasdf";

		console.log(fileFrame);

		// Finally, open the modal
		fileFrame.open();
	});

	// Initialize button to remove media references.
	$('.wpcrud-media-image-delete-button').on('click', function() {
		var mediaImageId = $(this).attr("media-image-id");
		$("#" + mediaImageId + "-image").attr("src", EMPTY_IMAGE_URL);
		$("#" + mediaImageId).val("");
		$(this).hide();
	});

	$('.wpcrud-media-image-holder').mouseenter(function() {
		var mediaImageId = $(this).attr("media-image-id");
		if ($("#" + mediaImageId).val())
			$('#' + mediaImageId + "-delete-button").fadeIn();
	});

	$('.wpcrud-media-image-holder').mouseleave(function() {
		var mediaImageId = $(this).attr("media-image-id");
		$('#' + mediaImageId + "-delete-button").fadeOut();
	});
});