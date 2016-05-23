function getChanges(target,oldID,newId){
	var request = '../History/changes/Old/'+oldID;
	if (newId){
		request +='/New/'+newId;
	}
	$.ajax(request)
		.done(function(data){
			target.parent().children('p').html(data);
		});
}

$('document').ready(function()
{
	window.zindex_counter = 0;

	$('body').build(function()
	{

		this.xtarget({
			url_append:      'as_widget',
			draggable_blank: '.window>h2',
			popup_element:   'section',
			success:         function() { $(this).autofocus(); },
			history: {
				condition: '.window>h2',
				title:     '.window>h2'
			}
		});

		// can enter tab characters into textarea
		this.inside('textarea').presstab();

		// messages is draggable
		this.inside('#messages').draggable();

		// tab controls
		this.inside('.tabber').tabber();

		// draggable objects brought to front on mousedown
		this.inside('.ui-draggable').mousedown(function()
		{
			$(this).css('z-index', ++window.zindex_counter);
		});

		// minimize menu
		this.inside('.menu.output').minimize();

	});

	// focus first form element
	$(this).autofocus();
});
