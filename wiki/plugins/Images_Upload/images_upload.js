$(document).ready(function() {
	$(document).on("click", ".close",function(event){
		$(this).closest("#images_upload_window").html("");
	});

 /*	$(document).on("submit", "#images_upload_form", function(){
		//noinspection JSUnresolvedVariable
		var app = window.app;
		var $div = $(this).closest(".window");
		var url = "/Images_Upload/upload";
		var xhr = $.ajax({
			url: app.uri_base + url + "?as_widget=1&PHPSESSID=" + app.PHPSESSID,
			type: 'POST',
			data: { "wmdVal": $("#wmd-preview").html() , "name": $("#name").val() , "title": $("#title").val() },
			data: $("#upload_image_field").files.serialize(),
			error: function() { alert("error"); },
			success: function(data, status, xhr)
			{
				var $target = $("#images_upload_window");
				$target.html(data);
				$target.children().build();
				var input = $target.find("[name='" + xhr.focus + "']");
				if (!input.length) {
					input = $target.find("[name='id_" + xhr.focus + "']").next("input");
				}
				input.focus();
			}
		});
		xhr.focus = $(this).closest(".field").attr("id");
		xhr.target = $div.parent();
	});*/
});
