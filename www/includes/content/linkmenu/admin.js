
function LM_LinkSubmit(form)
{
	var source = form.elements.current_links;
	var component_id = form.elements.component_id.value;
	for (var i=0; i<source.options.length; i++)
	{
		source.options[i].selected = true;
	}
	new Ajax.Form(form, { onComplete: function() {
		var func = function() {
			Pico_ReloadComponent(component_id);
		};
		Pico_CloseAP(func);
	} });
}

function LM_SelectDown()
{
	var source = document.getElementById('current_links');

	var max_index = source.options.length - 1;
	for (var i = max_index; i >= 0; i--) 
	{
		if (source.options[i].selected)
		{
			// move swap it with the one above it
			if ((i+1) <= max_index)
			{
				tmp_value = source.options[i+1].value;
				tmp_text  = source.options[i+1].text;
				
				source.options[i+1].value = source.options[i].value;
				source.options[i+1].text  = source.options[i].text;
				
				source.options[i].value = tmp_value;
				source.options[i].text  = tmp_text;
				
				source.options[i].selected = false;
				source.options[i+1].selected = true;
			}
		}
	}
}

function LM_SelectUp()
{
	var source = document.getElementById('current_links');

	for (var i = 0; i < source.options.length; i++) 
	{
		if (source.options[i].selected)
		{
			// move swap it with the one above it
			if ((i-1) >= 0)
			{
				tmp_value = source.options[i-1].value;
				tmp_text  = source.options[i-1].text;
				
				source.options[i-1].value = source.options[i].value;
				source.options[i-1].text  = source.options[i].text;
				
				source.options[i].value = tmp_value;
				source.options[i].text  = tmp_text;
				
				source.options[i].selected = false;
				source.options[i-1].selected = true;
			}
		}
	}
}

function LM_SelectDelete()
{
	var source = document.getElementById('current_links');
	while (source.selectedIndex != -1)
	{
		source.options[source.selectedIndex] = null;
	}
}

function LM_SelectAddOption(dest, text, value)
{
	var optn = document.createElement("OPTION");
	optn.text = text;
	optn.value = value;
	dest.options.add(optn);
}

function LM_SelectAdd()
{
	var form = document.getElementById('linkmenu_form');
	var source = form.elements.available_links;
	var dest = document.getElementById('current_links');
	while (source.selectedIndex != -1)
	{
		text = source.options[source.selectedIndex].text;
		value = source.options[source.selectedIndex].value;
		
		if (form.elements.is_tabbed.checked)
		{
			text  =  text + ' (tabbed)';
			value = 't_' + value;
		}
		
		source.options[source.selectedIndex].selected = false;
		LM_SelectAddOption(dest, text, value);
	}
}

function LM_UpdateEdit()
{
	var form = document.getElementById('linkmenu_form');
	var component_id = form.elements.component_id.value;
	var instance_id  = form.elements.instance_id.value;
	var target_url = url('includes/content/linkmenu/submit.php?page_action=get_edit&component_id='+component_id+'&instance_id='+instance_id);
	new Ajax.Updater('co_main', target_url);
}

function LM_AddLink(form)
{
	var obj = document.getElementById('external_links');
	
	var func = function() {
		new Ajax.Form(form, { onComplete: function() {
			var target_url = url('includes/content/linkmenu/submit.php?page_action=get_links');
			new Ajax.Updater('external_links', target_url, { onComplete: function() {
				LM_UpdateEdit();
				Pico_FadeIn(obj, 0);
			} });
		}} );
	}
	
	Pico_FadeOut(obj, 100, func);
}

function LM_DeleteLink(link_id)
{
	if (confirm('Are you sure you want to delete this link?'))
	{
		var obj = document.getElementById('external_links');
		var func = function() {
			var target_url = url('includes/content/linkmenu/submit.php?page_action=delete_link&link_id='+link_id);
			new Ajax.Updater('external_links', target_url, { onComplete: function() {
				LM_UpdateEdit();
				Pico_FadeIn(obj, 0);
			} });
		}
		Pico_FadeOut(obj, 100, func);
	}
}

function LM_EditLink(link_id)
{
	var obj = document.getElementById('external_links');
	var func = function() {
		var target_url = url('includes/content/linkmenu/submit.php?page_action=get_links&edit_link='+link_id);
		new Ajax.Updater('external_links', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		} });
	}
	Pico_FadeOut(obj, 100, func);
}

function LM_UpdateSettings(form)
{
	form.elements.submit_btn.disabled = true;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submit_btn.disabled = false;
		var component_id = document.getElementById('component_id').value;
		Pico_ReloadComponent(component_id);
	}} );
}

function LM_FontUploaded(filename)
{
	var el = document.getElementById('font_file');
	el.value = filename;
}