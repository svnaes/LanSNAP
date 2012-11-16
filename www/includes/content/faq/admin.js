
function FAQ_Delete(faq_id)
{
	if (confirm('Are you sure you want to delete this topic?'))
	{
		var component_id = document.getElementById('component_id').value;
		var instance_id  = document.getElementById('instance_id').value;
		
		var target_url = url('includes/content/faq/submit.php?page_action=delete&faq_id='+faq_id);
		new Ajax.Request(target_url, { onComplete: function() {
			FAQ_Reload(component_id, instance_id)
		} });
	}
}

function FAQ_Move(faq_id, direction)
{
	var target_url = url('includes/content/faq/submit.php?page_action=move&direction='+direction+'&faq_id='+faq_id);
	new Ajax.Request(target_url, { onComplete: function() {
		var component_id = document.getElementById('component_id').value;
		var instance_id  = document.getElementById('instance_id').value;
		var target_url = url('includes/content/faq/edit.php?component_id='+component_id+'&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			// instant update
		} });
	} });
}

function FAQ_Edit(faq_id)
{
	var obj = document.getElementById('co_main');
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	
	var func = function() {
		var target_url = url('includes/content/faq/edit.php?edit='+faq_id+'&component_id='+component_id+'&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		} });
	}
	
	Pico_FadeOut(obj, 100, func);
}

function FAQ_Reload(component_id, instance_id)
{
	var obj = document.getElementById('co_main');
	
	var func = function() {
		var target_url = url('includes/content/faq/edit.php?component_id='+component_id+'&instance_id='+instance_id);
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		} });
	}
	
	Pico_FadeOut(obj, 100, func);
}

function FAQ_Submit(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = form.elements.component_id.value;
	var instance_id  = form.elements.instance_id.value;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		FAQ_Reload(component_id, instance_id);
		Pico_ReloadComponent(component_id);
	}} );
}