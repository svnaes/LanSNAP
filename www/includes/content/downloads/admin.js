
function DL_FileUploaded(filename)
{
	var instance_id = document.getElementById('instance_id').value;
	var target_url = url('includes/content/downloads/submit.php?page_action=add&filename='+urlencode(filename)+'&instance_id='+instance_id);
	new Ajax.Request(target_url, { onComplete: function() {
		// complete
		var target_url = url('includes/content/downloads/file_list.php?refresh=1&instance_id='+instance_id);
		new Ajax.Updater('file_list', target_url);
	} } );
}

function DL_DeleteFile(file_id)
{
	if (confirm('Are you sure you want to delete this file?'))
	{
		var instance_id = document.getElementById('instance_id').value;
		var target_url = url('includes/content/downloads/submit.php?page_action=delete&file_id='+file_id+'&instance_id='+instance_id);
		new Ajax.Request(target_url, { onComplete: function() {
			// complete
			var target_url = url('includes/content/downloads/file_list.php?refresh=1&instance_id='+instance_id);
			new Ajax.Updater('file_list', target_url);
		} } );
	}
}

function DL_MoveFile(file_id, direction)
{
	var instance_id = document.getElementById('instance_id').value;
	var target_url = url('includes/content/downloads/submit.php?page_action=move&file_id='+file_id+'&instance_id='+instance_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		// complete
		var target_url = url('includes/content/downloads/file_list.php?refresh=1&instance_id='+instance_id);
		new Ajax.Updater('file_list', target_url);
	} } );
}

function DL_FilesUploaded()
{
	//alert('done');
}

function DL_Close()
{
	var component_id = document.getElementById('component_id').value;
	var func = function() { 
		var component_id = t.responseText;
		Pico_ReloadComponent(component_id);
		//window.location = window.location; // refresh the page
	};
	Pico_CloseAP(func);
}

function DL_ChangeDesc(file_id)
{
	var instance_id = document.getElementById('instance_id').value;
	
	var dojb = document.getElementById('desc_'+file_id);
	var desc = dojb.innerHTML;
	
	var new_desc = prompt('Please enter a new description', desc);
	if (new_desc != null)
	{
		var description = urlencode(new_desc);
		var target_url = url('includes/content/downloads/submit.php?page_action=description&file_id='+file_id+'&instance_id='+instance_id+'&description='+description);
		new Ajax.Request(target_url, { onComplete: function(t) {
			dojb.innerHTML = t.responseText;
		} } )
	}
}

function DL_EditDescription(file_id)
{
	var target_url = url('includes/content/downloads/html_description.php?id='+file_id);
	new Ajax.Updater('file_list', target_url, { onComplete: function () {
		// load CK editor
		CKEDITOR.replace('dl_html_desc', { height: 250 });
		document.getElementById('file_upload').style.display = 'none';
		document.getElementById('file_list').style.height = '430px';
		
	} });
}

function DL_HTML_Submit(form)
{
	// get CK data
	form.elements.html_description.value = CKEDITOR.instances.dl_html_desc.getData();
	CKEDITOR.instances.dl_html_desc.destroy();
	// put in var
	new Ajax.Form(form, { onComplete: function() {
		// reload list
		var instance_id = document.getElementById('instance_id').value;
		var target_url = url('includes/content/downloads/file_list.php?instance_id='+instance_id);
		new Ajax.Updater('file_list', target_url, { onComplete: function () {
			document.getElementById('file_upload').style.display = 'block';
			document.getElementById('file_list').style.height = '330px';
		} });
	} } );
}