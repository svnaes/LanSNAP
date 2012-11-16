
function MG_FileUploaded(filename)
{
	//alert(filename + " has been uploaded");
	MG_AddFile(filename);
}

function MG_Install(form)
{
	form.elements.submitbtn.disabled = true;
	var column = form.elements.location.value;
	var component_id = form.elements.component_id.value;
	
	new Ajax.Form(form, { onComplete: function(t) {
		var func = function() {
			Pico_EditContentId(component_id, REQUEST_URI, CURRENT_PAGE);
		};
		Pico_ReloadColumn(column, func);
	}} );
}

function MG_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function() {
		Pico_ReloadComponent(form.elements.component_id.value);
		alert('Gallery Options Updated');
		form.elements.submitbtn.disabled = false;
	}} );
}

function MG_AddCategory(form)
{
	var component_id = form.elements.component_id.value;
	var instance_id  = document.getElementById('instance_id').value;
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		MG_ShowCategories();
	}} );
}

function MG_Updatefile(form)
{
	form.elements.submitbtn.disabled = true;
	
	if (CKEDITOR.instances.image_description)
	{
		form.elements.description.value = CKEDITOR.instances.image_description.getData();
	}
	
	new Ajax.Form(form, { onComplete: function() {
		MG_ReloadImages();
	}} );
}

function MG_AddFile(filename)
{
	var instance_id   = document.getElementById('instance_id').value;
	var component_id  = document.getElementById('component_id').value;
	var category_id   = document.getElementById('category_id').value;
	var target_url = url('includes/content/media/submit.php?page_action=add&filename='+urlencode(filename)+'&instance_id='+instance_id+'&component_id='+component_id+'&category_id='+category_id);
	
	new Ajax.Request(target_url);
}

function MG_LoadImages(instance_id, component_id, category_id)
{
	var target_url  = url('includes/content/media/image_list.php?instance_id='+instance_id+'&component_id='+component_id+'&category_id='+category_id);
	new Ajax.Updater('mg_preview_window', target_url);
}

function MG_ReloadImages()
{
	// this function is here so we don't have to mess with the flash
	MG_EditImageClose();
	
	var instance_id  = document.getElementById('instance_id').value;
	var component_id = document.getElementById('component_id').value;
	var category_id  = document.getElementById('category_id').value;
	var target_url  = url('includes/content/media/image_list.php?instance_id='+instance_id+'&component_id='+component_id+'&category_id='+category_id);
	new Ajax.Updater('mg_preview_window', target_url);
}

function MG_RefreshImages()
{
	// we put a 2 second delay in so that we can make sure any uploaded images have time to process
	
	var func = function()
	{
		MG_ReloadImages();
	}
	
	func.delay(2);
}

function MG_MoveImage(image_id, direction)
{
	var instance_id = document.getElementById('instance_id').value;
	var target_url  = url('includes/content/media/submit.php?page_action=move&image_id='+image_id+'&instance_id='+instance_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		MG_ReloadImages();
	} } );
}

function MG_DeleteImage(image_id)
{
	if (confirm('Are you sure you want to delete this file from the gallery?'))
	{
		var instance_id = document.getElementById('instance_id').value;
		var target_url  = url('includes/content/media/submit.php?page_action=delete&image_id='+image_id+'&instance_id='+instance_id);
		new Ajax.Request(target_url, { onComplete: function() {
			MG_ReloadImages();
		} } );
	}
}

function MG_EditImageClose()
{
	if (CKEDITOR.instances.image_description)
	{
		CKEDITOR.instances.image_description.destroy();
	}
}

function MG_EditImage(image_id)
{
	var component_id = document.getElementById('component_id').value;
	var target_url   = url('includes/content/media/submit.php?page_action=edit&image_id='+image_id+'&component_id='+component_id);
	new Ajax.Updater('mg_preview_window', target_url, { onComplete: function() {
		// image_description
		var obj = document.getElementById('image_description');
		if (obj != null)
		{
			CKEDITOR.replace('image_description', { height: 275 });
		}
	}});
}

function MG_MoveCategory(category, direction)
{
	var target_url  = url('includes/content/media/submit.php?page_action=move_category&category='+category+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		MG_ShowCategories();
	} } );
}

function MG_EditCategory(category)
{
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	var target_url = url('includes/content/media/gallery_categories.php?component_id='+component_id+'&edit='+category+'&instance_id='+instance_id);
	new Ajax.Updater('mg_preview_window', target_url);
}

function MG_DeleteCategory(category)
{
	if (confirm('Are you sure you want to delete this category?'))
	{
		var target_url = url('includes/content/media/submit.php?page_action=delete_category&category='+category);
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				alert(t.responseText);
			}
			else
			{
				MG_ShowCategories();
			}
		} });
	}
}

function MG_CustomThumbnail(filename)
{
	var component_id = document.getElementById('component_id').value;
	var image_id = document.getElementById('image_id').value;
	var target_url = url('includes/content/media/submit.php?page_action=custom_thumbnail&file='+urlencode(filename)+'&image_id='+image_id+'&component_id='+component_id);
	new Ajax.Request(target_url, { onComplete: function() {
		// done
	} });
	
}

function MG_PreviewStyle(obj)
{
	document.getElementById('mg_preview_style').innerHTML = 'Loading Preview...';
	if (obj.value.length > 0)
	{
		var target_url = url('includes/content/media/preview.php?viewtype='+obj.value);
		new Ajax.Updater('mg_preview_style', target_url);
	}
}

function MG_DestroyCK()
{
	if (CKEDITOR.instances.category_html)
	{
		CKEDITOR.instances.category_html.destroy();
	}
}

function MG_Close()
{
	MG_DestroyCK();
	MG_EditImageClose();
	var component_id = document.getElementById('component_id').value;
	Pico_ReloadComponent(component_id);
}

function MG_ShowCategories()
{
	var component_id = document.getElementById('component_id').value;
	var instance_id  = document.getElementById('instance_id').value;
	var target_url = url('includes/content/media/gallery_categories.php?component_id='+component_id+'&instance_id='+instance_id);
	new Ajax.Updater('mg_preview_window', target_url);
}

function MG_EditHTML(category_id)
{
	var target_url = url('includes/content/media/category_html.php?category_id='+category_id);
	new Ajax.Updater('mg_preview_window', target_url, { onComplete: function() {
		// ckeditor
		CKEDITOR.replace('category_html', { height: 250 });
	} });
}

function MG_UpdateCategoryHTML(form)
{
	var data = CKEDITOR.instances.category_html.getData();
	form.elements.html.value = data;
	new Ajax.Form(form, { onComplete: function() {
		MG_DestroyCK();
		MG_ShowCategories();
	} } );
}

function MG_FileTop(image_id)
{
	var target_url = url('includes/content/media/submit.php?page_action=top_file&image_id='+image_id);
	new Ajax.Request(target_url, { onComplete: function() {
		MG_ReloadImages();
	} } );
}