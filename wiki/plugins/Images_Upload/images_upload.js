$(document).ready(function() {
	$(document).on("click", ".close",function()
	{
		$(this).closest("#images_upload_window").html("");
	});

	$(document).on("dblclick", ".image_uploaded",function()
	{
		insert("[/" + $(this).attr("alt") + "]");
	});

});

function insert(text)
{
	$("#main").find('textarea').each( function(){
		$(this).val($(this).val() + text);
	});
}
