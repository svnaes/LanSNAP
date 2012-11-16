
function LT_Submit(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			var target_url = url('includes/content/tournaments/edit.php?reload=1&component_id='+component_id);
			new Ajax.Updater('co_main', target_url);
		}
	} } );
}

function LT_EditTourny(component_id, edit_id)
{
	var target_url = url('includes/content/tournaments/edit.php?reload=1&edit_id='+edit_id+'&component_id='+component_id);
	new Ajax.Updater('co_main', target_url);
}

function LT_DeleteTourny(component_id, tourny_id)
{
	if (confirm('Are you sure you want to delete this tournament?'))
	{
		var target_url = url('includes/content/tournaments/submit.php?page_action=delete_tourny&tourny_id='+tourny_id);
		new Ajax.Request(target_url, { onComplete: function() {
			var target_url = url('includes/content/tournaments/edit.php?reload=1&component_id='+component_id);
			new Ajax.Updater('co_main', target_url);
		}} );
	}
}