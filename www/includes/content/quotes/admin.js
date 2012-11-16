
function Q_Reload()
{
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	var target_url = url('includes/content/quotes/edit.php?reload=1&component_id='+component_id+'&instance_id='+instance_id);
	new Ajax.Updater('co_main', target_url);
}

function Q_Submit(form)
{
	new Ajax.Form(form, { onComplete: function() {
		//reload edit
		Q_Reload();
	} } );
}

function Q_Edit(quote_id)
{
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	var target_url = url('includes/content/quotes/edit.php?reload=1&component_id='+component_id+'&instance_id='+instance_id+'&edit='+quote_id);
	new Ajax.Updater('co_main', target_url);
}

function Q_Delete(quote_id)
{
	var target_url = url('includes/content/quotes/submit.php?page_action=delete&quote_id='+quote_id);
	if (confirm('Are you sure you want to delete this quote?'))
	{
		new Ajax.Request(target_url, { onComplete: function() {
			Q_Reload();
		} } );
	}
}
