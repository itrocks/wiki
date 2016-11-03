$('document').ready(function()
{
	$('body').build(function()
	{

		/**
		 * Click the 'show history' button : display the history into a window
		 */
		this.inside('.article.history > a').click(function()
		{
			$(this).closest('.article.history').addClass('window');
		});

		/**
		 * Click a 'differences' button : hide detail if loaded
 		 */
		if (this.parent().is('.article.history.window')) {
			this.inside('.actions > li > a').click(function (event)
			{
				var $p = $(this).closest('li[id]').find('>p');
				if ($p.is(':visible')) {
					event.preventDefault();
					event.stopImmediatePropagation();
					$p.hide().empty();
				}
			});
		}

	});
});
