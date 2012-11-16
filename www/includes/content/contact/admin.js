
function CF_UpdateSettings(form)
{
	var component_id = form.elements.component_id.value;
	
	form.elements.complete_message.value = CKEDITOR.instances.complete_message_editor.getData();
	form.elements.preview_message.value = CKEDITOR.instances.preview_message_editor.getData();
	CF_CloseForm();
	
	new Ajax.Form(form, { onComplete: function() {
		
		Pico_ReloadComponent(component_id);
		alert('Settings Saved');
		var target_url = url('includes/content/contact/edit.php?reload=1&component_id='+component_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			CF_LoadForm();
		} } );
		//Pico_CloseAP(func);
	} } );
}

function CF_AddField(form)
{
	var component_id = form.elements.component_id.value;
	var obj = document.getElementById('cf_fields');
	
	var func = function() {
		new Ajax.Form(form, { onComplete: function() {
			Pico_ReloadComponent(component_id);
			var update_url = url('includes/content/contact/submit.php?page_action=update_fields&component_id='+component_id);
			new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
				Pico_FadeIn(obj, 0);
			} });
		} } );
	}
	Pico_FadeOut(obj, 100, func);
}

function CF_EditField(field_id, component_id)
{
	var update_url = url('includes/content/contact/submit.php?page_action=edit_field&field_id='+field_id+'&component_id='+component_id);
	new Ajax.Updater('cf_fields', update_url);
}

function CF_DeleteField(field_id, component_id)
{
	var obj = document.getElementById('cf_fields');
	if (confirm('Are you sure you want to delete this field'))
	{
		var func = function() {
			var target_url = url('includes/content/contact/submit.php?page_action=delete_field&field_id='+field_id+'&component_id='+component_id);
			new Ajax.Request(target_url, { onComplete: function() {
				var update_url = url('includes/content/contact/submit.php?page_action=update_fields&component_id='+component_id);
				new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
					Pico_FadeIn(obj, 0);
				} });
			} });
		}
		Pico_FadeOut(obj, 100, func);
	}
}

function CF_MoveField(field_id, component_id, direction)
{
	var obj = document.getElementById('cf_fields');
	
	var func = function() {
		var target_url = url('includes/content/contact/submit.php?page_action=move_field&field_id='+field_id+'&component_id='+component_id+'&direction='+direction);
		new Ajax.Request(target_url, { onComplete: function() {
			var update_url = url('includes/content/contact/submit.php?page_action=update_fields&component_id='+component_id);
			new Ajax.Updater('cf_fields', update_url, { onComplete: function() {
				Pico_FadeIn(obj, 0);
			} });
		} });
	}
	Pico_FadeOut(obj, 100, func);
}

function CF_Toggle(container)
{
	var obj = document.getElementById(container);
	if (obj.style.display == 'none')
	{
		obj.style.display = 'block';
	}
	else
	{
		obj.style.display = 'none';
	}
}

function CF_SetDownloadFile(filename)
{
	alert(filename + ' uploaded');
}

function CF_DeleteAllFiles(component_id)
{
	if (confirm('Are you sure you want to delete files associated with this form? Doing so will result in this form being a standard contact form until a new file is uploaded'))
	{
		var target_url = url('includes/content/contact/submit.php?page_action=clear_download&component_id='+component_id);
		new Ajax.Request(target_url, { onComplete: function() {
			alert('File removed');
		} } );
	}
}

function CF_SubmitButton(filename)
{
	var obj = document.getElementById('contact_submit_button');
	obj.value = filename;
}

function CF_DeleteHistory(component_id)
{
	if (confirm('Are you sure you want to delete the contact form history? This cannot be undone'))
	{
		var target_url = url('includes/content/contact/submit.php?page_action=clear_history&component_id='+component_id);
		new Ajax.Request(target_url, { onComplete: function() {
			var target_url = url('includes/content/contact/history.php?reload=1&component_id='+component_id);
			new Ajax.Updater('cf_history', target_url);
		} } );
	}
}

function CF_LoadDirectyFields(component_id, dir_component_id)
{
	var target_url = url('includes/content/contact/submit.php?page_action=get_directory_fields&component_id='+component_id+'&dir_component_id='+dir_component_id);
	new Ajax.Updater('cf-directory-field', target_url);
}

function CF_LoadForm()
{
	CKEDITOR.replace('complete_message_editor', { height: 275 });
	CKEDITOR.replace('preview_message_editor', { height: 275 });
}

function CF_CloseForm()
{
	if (CKEDITOR.instances.complete_message_editor)
	{
		CKEDITOR.instances.complete_message_editor.destroy();
	}
	if (CKEDITOR.instances.preview_message_editor)
	{
		CKEDITOR.instances.preview_message_editor.destroy();
	}
}