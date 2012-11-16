function US_UpdateSettings(form)
{
	new Ajax.Form(form, { onComplete: function(){
		alert('Setting Saved');
	}});
}

function US_AddPayment(form)
{
	var component_id = form.elements.component_id.value;
	var group_id     = form.elements.group_id.value;
	new Ajax.Form(form, { onComplete: function(){
		US_PaymentSettings(component_id, group_id);
	}});
}

function US_PaymentSettings(component_id, group_id, edit_id)
{
	edit_id = (edit_id == null) ? 0 : edit_id;
	var target_url = url('includes/content/user_signup/group_payment_settings.php?component_id='+component_id+'&group_id='+group_id+'&edit_id='+edit_id);
	new Ajax.Updater('co_main', target_url);
}

function US_DeletePayment(component_id, group_id, entry_id)
{
	if (confirm('Are you sure you want to delete this entry?'))
	{
		var target_url = url('includes/content/user_signup/submit.php?page_action=delete_payment_entry&entry_id='+entry_id);
		new Ajax.Request(target_url, { onComplete: function() {
			US_PaymentSettings(component_id, group_id)
		}} );
	}
}

function US_ReloadMain(component_id)
{
	var target_url = url('includes/content/user_signup/edit.php?reload=1&component_id='+component_id);
	new Ajax.Updater('co_main', target_url);
}

