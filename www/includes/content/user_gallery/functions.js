
function UG_FileUploaded(filename, component_id)
{
	var filename = urlencode(filename);
	var target_url = url('includes/content/user_gallery/submit.php?page_action=upload_file&filename='+filename+'&component_id='+component_id);
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
	} } );
}

function UG_FilesUploaded()
{
	var func = function() {
		window.location = window.location;
	}
	
	setTimeout(func, 3000);
}