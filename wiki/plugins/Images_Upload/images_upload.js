$(document).ready(function() {
	$(document).on("click", ".close",function(event){
		$(this).closest("#images_upload_window").html("");
	});
});
