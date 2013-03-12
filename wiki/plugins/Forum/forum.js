$(window).load(function() {
	$(document).die(".need_confirm");
	$(document).on("click", ".need_confirm",function(event, noValidation)
	{
		if(!noValidation)
		{
			return false;
		}
		return false;
	});
});
