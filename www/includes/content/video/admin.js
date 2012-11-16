function VP_FilesUploaded()
{
	// function called when all files are uploaded
}

function VP_FileUploaded(filename)
{
	// function called when file 'filename' is uploaded
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	
	var target_url = url('includes/content/video/submit.php?page_action=upload&filename='+urlencode(filename)+'&component_id='+component_id+'&instance_id='+instance_id);
	new Ajax.Request(target_url, { onComplete: function() {
		alert(filename + ' uploaded and processed');
	} });
}

function VP_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function() {
		alert('Settings Updated');
		form.elements.submitbtn.disabled = false;
	} } );
}