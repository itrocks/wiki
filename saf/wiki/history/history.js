$('document').ready(function()
{
	$('body').build(function()
	{

		/**
		 * Click a 'differences' button : hide detail if loaded
 		 */
		this.inside('.article.history.window .actions>li>a').click(function(event)
		{
			var $p = $(this).closest('li[id]').find('>p');
			if ($p.is(':visible')) {
				event.preventDefault();
				event.stopImmediatePropagation();
				$p.hide().empty();
			}
		});

	});
});
