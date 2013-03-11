$(window).load(function() {
	$(document).on("click", ".need_confirm",function(event, noValidation)
	{
		if(!noValidation)
		{
			var currentElement = jQuery(this)
			jQuery.alerts.okButton = currentElement.attr('value');
			jQuery.alerts.cancelButton = '${message(code:&quot;dialog.confirm.cancelButton&quot;)}';

			jConfirm('${message(code:&quot;dialog.confirm&quot;)}',
				'${message(code:&quot;dialog.confirm.title&quot;)}', function(r) {
					if(r)
					{
						currentElement.trigger('click',true);
					}
					else
					{
						return false;
					}

				});
			return false;
		}
		$(this).href();
	});
});
