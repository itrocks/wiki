$(document).ready(function()
{

	/**
	 * Click a 'differences' button : hide detail if loaded
	 */
	$('body').build('click', 'article.history .actions > li > a', function(event)
	{
		var $p = $(this).closest('li[id]').find('>p');
		if ($p.html().trim()) {
			event.preventDefault();
			event.stopImmediatePropagation();
			$p.empty();
		}
	});

});
