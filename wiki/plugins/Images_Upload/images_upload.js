$(document).ready(function() {
	$(document).on('click', '.close',function()
	{
		$(this).closest('#images_upload_window').html('');
	});

	$(document).on('click', '.image_uploaded',function()
	{
		insert('[' + $(this).attr('alt') + ']');
		$(this).closest('#images_upload_window').html('');
	});

});

function insert(text)
{
	$('.window.page textarea').each( function(){
		$(this).val($(this).val() + text);
	});
}
