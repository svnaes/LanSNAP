
function Browser_Load()
{
	Browser_LoadFolder();
}

function Browser_LoadFolder(folder)
{
	if (folder == null) { folder = '' }
	
	var mode = document.getElementById('browse_mode').value;
	
	var target_url = 'browse_folder.php?&mode='+mode+'&path='+urlencode(folder);
	new Ajax.Updater('browse-folder', target_url);
	
	Browse_LoadPane(folder);
}

function Browser_NewFolder()
{
	var path = document.getElementById('browser_current_path').value;
	var new_folder = document.getElementById('new_folder_name').value;
	
	var target_url = 'browse_actions.php?page_action=new_folder&path='+urlencode(path)+'&folder_name='+urlencode(new_folder);
	new Ajax.Request(target_url, { onComplete: function() {
		document.getElementById('new_folder_name').value = '';
		Browser_LoadFolder(path);
	} } );
}

function Browse_FileUploaded(filename)
{
	var path = document.getElementById('browser_current_path').value;
	var target_url = 'browse_actions.php?page_action=new_file&path='+urlencode(path)+'&filename='+urlencode(filename);
	new Ajax.Request(target_url, { onComplete: function() {
		// do nothing?
	} } );
}

function Browse_FilesUploaded()
{
	// reload browse pane
	var path = document.getElementById('browser_current_path').value;
	var func = function() {
		Browse_LoadPane(path);
	}
	setTimeout(func, 2000);
}

function Browse_LoadPane(path)
{
	var mode = document.getElementById('browse_mode').value;
	var fn   = document.getElementById('fn').value;
	var target_url = 'browse_pane.php?path='+urlencode(path)+'&fn='+fn+'&mode='+mode;
	
	new Ajax.Updater('browse-pane', target_url);
}

function Browse_DeleteFile(filename)
{
	if (confirm('Are you sure you want to delete this file?'))
	{
		var path = document.getElementById('browser_current_path').value;
		var target_url = 'browse_actions.php?page_action=delete_file&path='+urlencode(path)+'&filename='+urlencode(filename);
		new Ajax.Request(target_url, { onComplete: function() {
			// do nothing?
			Browse_LoadPane(path);
		} } );
	}
}

function Browse_BackFromEdit()
{
	var path = document.getElementById('browser_current_path').value;
	Browse_LoadPane(path);
	var mode = document.getElementById('browse_mode').value;
	var target_url = 'browse_upload.php?mode='+mode;
	new Ajax.Updater('browse-upload', target_url);
}

function Browse_EditFile(filename)
{
	var path = document.getElementById('browser_current_path').value;
	
	var final_width  = document.getElementById('final_width').value;
	var final_height = document.getElementById('final_height').value;
	
	var target_url = 'browse_actions.php?page_action=edit_image&path='+urlencode(path)+'&filename='+urlencode(filename);
	
	new Ajax.Updater('browse-pane', target_url, { onComplete: function() {
		// load the controls
		
		var target_url = 'browse_actions.php?page_action=image_controls&path='+urlencode(path)+'&filename='+urlencode(filename)+'&final_width='+final_width+'&final_height='+final_height;
		new Ajax.Updater('browse-upload', target_url);
	} } );
}

function Edit_Calculate(flag)
{
	var check = document.getElementById('ratio_lock');
	
	if (check.checked == true)
	{
		
		var orig_width = document.getElementById('orig_width').value;
		var orig_height = document.getElementById('orig_height').value;
		
		if (flag == 'w')
		{
			// scale by width
			var edit_width = document.getElementById('edit_width').value;
			
			var mod = edit_width / orig_width;
			var new_height = mod * orig_height;
			document.getElementById('edit_height').value = Math.round(new_height);
		}
		else
		{
			// scale by height
			var edit_height = document.getElementById('edit_height').value;
			
			var mod = edit_height / orig_height;
			var new_width = mod * orig_width;
			document.getElementById('edit_width').value = Math.round(new_width);
		}
	}
}

function Edit_GetImageOrCanvas()
{
	var obj1 = document.getElementById('coi1');
	var obj2 = document.getElementById('coi2');
	var obj3 = document.getElementById('coi3');
	
	if (obj1.checked == true)
	{
		return 0;
	}
	else if (obj2.checked == true)
	{
		return 1;
	}
	else
	{
		return 2;
	}
}

function Edit_UpdateSizes()
{
	var option = Edit_GetImageOrCanvas();
	if (option == 0)
	{
		// canvas
		document.getElementById('edit_width').value = document.getElementById('current_canvas_width').value;
		document.getElementById('edit_height').value = document.getElementById('current_canvas_height').value;
	}
	else if(option == 1)
	{
		// image
		document.getElementById('edit_width').value = document.getElementById('current_image_width').value;
		document.getElementById('edit_height').value = document.getElementById('current_image_height').value;
	}
	else
	{
		// both
		document.getElementById('edit_width').value = document.getElementById('current_canvas_width').value;
		document.getElementById('edit_height').value = document.getElementById('current_canvas_height').value;
	}
}

function Edit_ResizePercent(percent)
{
	var orig_width = document.getElementById('edit_width').value;
	var orig_height = document.getElementById('edit_height').value;
	
	var new_width  = Math.round(orig_width * percent);
	var new_height = Math.round(orig_height * percent);
	
	document.getElementById('edit_width').value  = new_width;
	document.getElementById('edit_height').value = new_height;
	
	Edit_Resize();
}
  

function Edit_Resize()
{
	var img = document.getElementById('edit_image');
	var obj = document.getElementById('canvas_or_image');
	var option = Edit_GetImageOrCanvas();
	
	// resize canvas
	var new_width = document.getElementById('edit_width').value;
	var new_height = document.getElementById('edit_height').value;
	
	var canvas = document.getElementById('edit_canvas');
	
	if ((option == 0) || (option == 2))
	{
		// resize canvas
		canvas.style.width = new_width + 'px';
		canvas.style.height = new_height + 'px';
		document.getElementById('current_canvas_width').value = new_width;
		document.getElementById('current_canvas_height').value = new_height;
	}
	
	if ((option == 1) || (option == 2))
	{
		// resize image
		img.width = new_width;
		img.height = new_height;
		
		// update hidden vars
		document.getElementById('current_image_width').value = new_width; 
		document.getElementById('current_image_height').value = new_height;
		
		// resample image
		var old_src = document.getElementById('image_orig_src').value;
		img.src = 'browse_actions.php?page_action=resample&file='+urlencode(old_src)+'&width='+new_width+'&height='+new_height;
	}

	// center the image on the canvas
	
	var canvas_width  = document.getElementById('current_canvas_width').value;
	var canvas_height = document.getElementById('current_canvas_height').value;
	
	var left = Math.round((canvas_width - img.width) / 2);
	var top  = Math.round((canvas_height - img.height) / 2);
	
	img.style.left = left + 'px';
	img.style.top = top + 'px';
	
	// see if save button should be disabled/enabled
	var save_button = document.getElementById('save_button');
	
	
	var final_width  = document.getElementById('final_width').value;
	var final_height = document.getElementById('final_height').value;
	
	if ( ((canvas_width == final_width) || (final_width == 0)) && ((canvas_height == final_height) || (final_height == 0)) )
	{
		save_button.disabled = false;
	}
	else
	{
		save_button.disabled = true;
	}
}

function Edit_Crop()
{
	var obj = document.getElementById('edit_crop');
	// makes a box of given width/height to be dragged over the canvas image so we can get coordinates to make a cropped image
	if (show_crop == 0)
	{
		show_crop = 1;
		var img_width     = parseInt(document.getElementById('current_image_width').value);
		var img_height    = parseInt(document.getElementById('current_image_height').value);
		var canvas_width  = parseInt(document.getElementById('current_canvas_width').value);
		var canvas_height = parseInt(document.getElementById('current_canvas_height').value);
		
		var crop_width = parseInt(document.getElementById('crop_width').value);
		var crop_height = parseInt(document.getElementById('crop_height').value);
		
		if ( (isNaN(crop_width)) || (crop_width == 0) || (crop_width > canvas_width) )
		{
			crop_width = canvas_width;
			document.getElementById('crop_width').value = crop_width;
		}
		
		if ( (isNaN(crop_height)) || (crop_height == 0) || (crop_height > canvas_height) )
		{
			crop_height = canvas_height;
			document.getElementById('crop_height').value = crop_height;
		}
		
		var left = Math.round((canvas_width - crop_width) / 2);
		var top = Math.round((canvas_height - crop_height) / 2);
		
		obj.style.width = (crop_width-2) + 'px'; // -2 for border
		obj.style.height = (crop_height-2) + 'px';
		obj.style.top = top + 'px';
		obj.style.left = left + 'px';
		obj.style.display = 'block';
	}
	else
	{
		show_crop = 0;
		obj.style.display = 'none';
	}
}

function Edit_CropDrag(event)
{
	if (crop_drag == 1)
	{
		// moving crop box
		var obj = document.getElementById('edit_crop');
		
		var coords = Edit_Coords(event);
		var x = coords[0];
		var y = coords[1];
		
		var x_diff = x - start_x;
		var y_diff = y - start_y;
		
		var left = parseInt(obj.style.left);
		var top = parseInt(obj.style.top);
		
		var new_left = start_left + x_diff;
		var new_top = start_top + y_diff;
		
		if (new_left < 0) { new_left = 0; }
		if (new_top < 0) { new_top = 0; }
		
		var canvas_width = parseInt(document.getElementById('current_canvas_width').value);
		var canvas_height = parseInt(document.getElementById('current_canvas_height').value);

		var w = parseInt(obj.style.width);
		var h = parseInt(obj.style.height);
		
		var total_w = new_left + w + 2; // +2 for border
		if (total_w > canvas_width)
		{
			new_left = canvas_width - (w+2);
		}
		
		var total_h = new_top + h + 2;
		if (total_h > canvas_height)
		{
			new_top = canvas_height - (h+2);
		}
		
		obj.style.left = new_left + 'px';
		obj.style.top = new_top + 'px';
	}
	if (image_drag == 1)
	{
		// moving image
		var obj = document.getElementById('edit_image');
		
		var coords = Edit_Coords(event);
		var x = coords[0];
		var y = coords[1];
		
		var x_diff = x - start_x;
		var y_diff = y - start_y;
		
		var left = parseInt(obj.style.left);
		var top = parseInt(obj.style.top);
		
		var new_left = start_left + x_diff;
		var new_top = start_top + y_diff;
		
		var img_width     = parseInt(document.getElementById('current_image_width').value);
		var img_height    = parseInt(document.getElementById('current_image_height').value);
		var canvas_width  = parseInt(document.getElementById('current_canvas_width').value);
		var canvas_height = parseInt(document.getElementById('current_canvas_height').value);
		
		var max_left = 0;
		if (canvas_width > img_width)
		{
			var max_left = canvas_width - img_width;
		}
		
		var max_top = 0;
		if (canvas_height > img_height)
		{
			var max_top = canvas_height - img_height;
		}
		
		if (new_left > max_left) { new_left = max_left; }
		if (new_top > max_top) { new_top = max_top; }
		
		var min_left = canvas_width - img_width;
		var min_top  = canvas_height - img_height;
		if (min_left > 0) { min_left = 0; }
		if (min_top > 0) { min_top = 0; }
		
		if (new_left < min_left) { new_left = min_left; }
		if (new_top < min_top) { new_top = min_top; }
		
		obj.style.left = new_left + 'px';
		obj.style.top = new_top + 'px';
	}
}

function Edit_CropDragToggle(event, obj)
{
	if (crop_drag == 0)
	{
		crop_drag = 1;
		var coords = Edit_Coords(event);
		var x = coords[0];
		var y = coords[1];
		
		var obj = document.getElementById('edit_crop');
		
		obj.style.border = '1px yellow solid';
		start_left = parseInt(obj.style.left);
		start_top = parseInt(obj.style.top);
		
		start_x = x;
		start_y = y;
		
	}
	else
	{
		Edit_CropDragStop();
	}
}

function Edit_CropDragStop()
{
	crop_drag = 0;
	var obj = document.getElementById('edit_crop');
	obj.style.background = 'none';
	obj.style.border = '1px blue solid';
}

function Edit_Coords(event)
{
	if(event.offsetX || event.offsetY) { //For Internet Explorer
		x=event.offsetX;
		y=event.offsetY;
	}
	else { //For FireFox
		x=event.pageX;
		y=event.pageY;
	}
	
	var rArray = [];
	rArray[0] = x;
	rArray[1] = y;
	return rArray;
}

function Edit_ImageDragToggle(event)
{
	if (image_drag == 0)
	{
		image_drag = 1;
		var coords = Edit_Coords(event);
		var x = coords[0];
		var y = coords[1];
		
		var border = document.getElementById('edit_canvas');
		border.style.border = '1px yellow solid';
		
		var obj = document.getElementById('edit_image');
		
		start_left = parseInt(obj.style.left);
		start_top = parseInt(obj.style.top);
		
		start_x = x;
		start_y = y;
	}
	else
	{
		Edit_ImageDragStop();
	}
}

function Edit_ImageDragStop()
{
	image_drag = 0;
	var border = document.getElementById('edit_canvas');
	border.style.border = '1px #000 solid';
}

function Edit_Optimize()
{
	var final_width  = document.getElementById('final_width').value;
	var final_height = document.getElementById('final_height').value;
	
	var orig_width = document.getElementById('orig_width').value;
	var orig_height = document.getElementById('orig_height').value;
	
	var img = document.getElementById('edit_image');
	var canvas = document.getElementById('edit_canvas');
	
	if ((final_width != 0) && (final_height != 0))
	{
		// fixed width and height
		var new_width = final_width;
		var new_height = final_height;
	}
	else if (final_width != 0)
	{
		// fixed width
		var new_width = final_width;
		var mod = new_width / orig_width;
		var new_height = Math.round(orig_height * mod);
	}
	else
	{
		// fixed height
		var new_height = final_width;
		var mod = new_height / orig_height;
		var new_width = Math.round(orig_width * mod);
	}
	
	document.getElementById('edit_width').value = new_width;
	document.getElementById('edit_height').value = new_height;
	
	// resize canvas
	canvas.style.width = new_width + 'px';
	canvas.style.height = new_height + 'px';
	
	document.getElementById('current_canvas_width').value = new_width;
	document.getElementById('current_canvas_height').value = new_height;
	
	// get new image size
	// the image size will end up being proportionate to itself, not to the canvas
	var width_diff = Math.abs(orig_width - new_width);
	var height_diff = Math.abs(orig_height - new_height);
	if (width_diff < height_diff)
	{
		// scale the width down to final_width, and proprotionately resize the height
		var new_image_width = final_width;
		var mod = new_image_width / orig_width;
		var new_image_height = Math.ceil(orig_height * mod);
	}
	else
	{
		// scale the height down to final_height, and proprotionately resize the width
		var new_image_height = final_height;
		var mod = new_image_height / orig_height;
		var new_image_width = Math.ceil(orig_width * mod);
	}
	
	
	// resize img
	img.width = new_image_width;
	img.height = new_image_height;
	
	document.getElementById('current_image_width').value = new_image_width; 
	document.getElementById('current_image_height').value = new_image_height;
	
	// resample image
	var old_src = document.getElementById('image_orig_src').value;
	img.src = 'browse_actions.php?page_action=resample&file='+urlencode(old_src)+'&width='+new_image_width+'&height='+new_image_height;
	
	// center the image on the canvas
	
	var left = Math.round((new_width - new_image_width) / 2);
	var top  = Math.round((new_height - new_image_height) / 2);
	
	img.style.left = left + 'px';
	img.style.top = top + 'px';
	
	obj3 = document.getElementById('coi3');
	obj3.checked = true;
	
	var save_button = document.getElementById('save_button');
	save_button.disabled = false;
}

function Edit_Save(filename)
{
	var canvas_width  = document.getElementById('current_canvas_width').value;
	var canvas_height = document.getElementById('current_canvas_height').value;
	
	var image_width  = document.getElementById('current_image_width').value;
	var image_height = document.getElementById('current_image_height').value;
	
	var path   = document.getElementById('browser_current_path').value;
	var source = document.getElementById('image_orig_src').value;
	
	var img = document.getElementById('edit_image');
	var left = parseInt(img.style.left);
	var top = parseInt(img.style.top);
	
	// make an image with the information above, call a script, it will return the path to the new image
	// add that image to the image queue, the image queue will then make an appropriate thumbnail and place it in the queue box
	
	var target_url = 'browse_actions.php?page_action=get_image_name&path='+urlencode(path)+'&filename='+urlencode(filename);
	new Ajax.Request(target_url, { onComplete: function(t) {
		var new_name_tmp = t.responseText;
		var new_name = prompt('Enter file name for this image', new_name_tmp);
		if ((new_name != null) && (new_name.length > 0))
		{
			var target_url = 'browse_actions.php?page_action=save_image&path='+urlencode(path)+'&filename='+urlencode(filename);
			target_url = target_url + '&canvas_width='+canvas_width+'&canvas_height='+canvas_height;
			target_url = target_url + '&image_width='+image_width+'&image_height='+image_height;
			target_url = target_url + '&source='+urlencode(source)+'&left='+left+'&top='+top;
			target_url = target_url + '&finished_filename='+urlencode(new_name);
			
			new Ajax.Request(target_url, { onComplete: function(t) {
				alert('File Uploaded');
			} } );
		}
	} } );
}

var crop_drag = 0;
var image_drag = 0;
var start_x = 0;
var start_y = 0;
var start_left = 0;
var start_top = 0;
var show_crop = 0;

