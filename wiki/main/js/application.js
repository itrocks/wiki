$(window).load(function() {
	$(window).resize(function() {
		var $body_margins = parseInt($("body").css("margin-top"))
			+ parseInt($("body").css("margin-bottom"))
			+ parseInt($("body").css("padding-top"))
			+ parseInt($("body").css("padding-bottom"));
		var $windows = $(window).height() - $body_margins;
		var $center = $(".application.center");
		var $top = $(".application.top").height()
			+ parseInt($(".application.top").css("margin-top"))
			+ parseInt($(".application.top").css("margin-bottom"))
			+ parseInt($(".application.top").css("padding-top"))
			+ parseInt($(".application.top").css("padding-bottom"));
		var $content = parseInt($center.css("margin-top"))
			+ parseInt($center.css("margin-bottom"))
			+ parseInt($center.css("padding-top"))
			+ parseInt($center.css("padding-bottom"))
			+ $top;
		$center.children().each(function() {
			$content += $(this).height();
			$content += parseInt($(this).css("margin-top"))
				+ parseInt($(this).css("margin-bottom"))
				+ parseInt($(this).css("padding-top"))
				+ parseInt($(this).css("padding-bottom"));
			$content += parseInt($(this).css("border-left-width")) * 2;
		});
		$("body").height(($windows > $content ? $windows : $content));
	});
	$(window).resize();

	$("body").build(function()
	{
		var $this = $(this);
		this.xtarget({ url_append: "as_widget=1" });
		// modifiable objects
		this.in(".modifiable .value").dblclick(function()
		{
			//noinspection JSUnresolvedVariable
			var app = window.app;
			var $div = $(this).closest(".modifiable");
			if (!$div.closest("form").length) {
				var url = $div.attr("id").lLastParse("/") + "/edit";
				var xhr = $.ajax({
					url: app.uri_base + url + "?as_widget=1&PHPSESSID=" + app.PHPSESSID,
					error: function() { alert("error opening form, please retry"); },
					success: function(data, status, xhr)
					{
						var $target = $(xhr.target);
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
			}
		});

		// tab controls
		$this.find(".tabber").tabber();

		// window objects brought to front
		$this.find("div.window").mousedown(function()
		{
			$(this).css("z-index", ++zindex_counter);
		});

	});

});
