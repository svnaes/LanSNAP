
function MP3_FileUploaded(filename)
{
	var extension = filename.split('.').pop().toLowerCase();
	var form = document.getElementById('mp3form');
	if (extension == 'mp3')
	{
		form.elements.music_file.value = filename;
		form.submitbtn.disabled = false;
	}
	else if (extension == 'jpg')
	{
		form.elements.image_file.value = filename;
	}
	else
	{
		var target_url = url('includes/content/music/submit.php?page_action=purge&filename='+urlencode(filename));
		new Ajax.Request(target_url);
		alert('Invalid extension: '  + extension + ' ... Please upload only MP3 and JPG files');
	}
	
}

function MP3_Submit(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = form.elements.component_id.value;
	var instance_id = form.elements.instance_id.value;
	
	new Ajax.Form(form, { onComplete: function(t) {
		form.reset();
		form.elements.submitbtn.disabled = false;
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
			
		}
		else
		{
			// refresh entries
			var target_url = url('includes/content/music/entries.php?reload=true&component_id='+component_id+'&instance_id='+instance_id);
			new Ajax.Updater('mp3_entries', target_url);
			
			// show the entries
			var obj1 = document.getElementById('co_main');
			var obj2 = document.getElementById('mp3_entries');
			
			var func = function() {
				obj1.style.display = 'none';
				obj2.style.display = 'block';
				Pico_FadeIn(obj2, 0);
			}
			Pico_FadeOut(obj1, 100, func);
			
			// update music player component on page
			Pico_ReloadComponent(component_id);
		}
	} } );
}

function MP3_Move(entry, direction)
{
	var component_id = document.getElementById('component_id').value;
	var target_url = url('includes/content/music/submit.php?page_action=move&direction='+direction+'&entry='+entry);
	new Ajax.Request(target_url, { onComplete: function() {
		var target_url = url('includes/content/music/entries.php?reload=true&component_id='+component_id);
		new Ajax.Updater('mp3_entries', target_url);
	} } );
}

function MP3_Delete(entry)
{
	if (confirm('Are you sure you want to delete this song?'))
	{
		var component_id = document.getElementById('component_id').value;
		var instance_id  = document.getElementById('instance_id').value;
		var target_url = url('includes/content/music/submit.php?page_action=delete&entry='+entry);
		new Ajax.Request(target_url, { onComplete: function() {
			var target_url = url('includes/content/music/entries.php?reload=true&component_id='+component_id+'&instance_id='+instance_id);
			new Ajax.Updater('mp3_entries', target_url);
		} } );
	}
}

function MP3_Update(obj, entry, field)
{
	var new_text = prompt('Update Text', obj.innerHTML);
	if ( (new_text != null) && (new_text.length > 0) )
	{
		var component_id = document.getElementById('component_id').value;
		var instance_id = document.getElementById('instance_id').value;
		var target_url = url('includes/content/music/submit.php?page_action=update&entry='+entry+'&text='+urlencode(new_text)+'&field='+field);
		new Ajax.Request(target_url, { onComplete: function() {
			var target_url = url('includes/content/music/entries.php?reload=true&component_id='+component_id+'&instance_id='+instance_id);
			new Ajax.Updater('mp3_entries', target_url);
		} } );
	}
}

function MP3_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		Pico_ReloadComponent(component_id);
		var target_url = url('includes/content/music/settings.php?reload=true&component_id='+component_id);
		
		new Ajax.Updater('mp3_settings', target_url);
		alert('Settings Updated');
	}} );
}

function MP3_Close()
{
	var component_id = document.getElementById('component_id').value;
	Pico_ReloadComponent(component_id);
}