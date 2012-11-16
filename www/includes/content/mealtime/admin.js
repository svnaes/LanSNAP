
function MT_Submit(form)
{
	var component_id = form.elements.component_id.value;
	var instance_id = form.elements.instance_id.value;
	
	new Ajax.Form(form, { onComplete: function() {
		var target_url = url('includes/content/mealtime/edit.php?reload=1&instance_id='+instance_id+'&component_id='+component_id);
		new Ajax.Updater('co_main', target_url);
	} } );
}