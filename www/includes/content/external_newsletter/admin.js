
function EN_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = document.getElementById('component_id').value;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		
		Pico_ReloadComponent(component_id);
		var target_url = url('includes/content/external_newsletter/edit.php?refresh=1&component_id='+component_id);
		new Ajax.Updater('co_main', target_url);
	} } );
}

function EN_SubmitButton(filename)
{
	var obj = document.getElementById('newsletter_submit_button');
	obj.value = filename;
}