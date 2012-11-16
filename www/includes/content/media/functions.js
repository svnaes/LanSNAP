
function MG_ShowGridImage(image_id)
{
	var fields = getElementsByClassName('grid_image', '*');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	
	var obj = document.getElementById('grid'+image_id);
	obj.style.display = 'block';
}

function MG_ShowProjectCategory(component_id, category_id, image_count)
{
	if (image_count == null)
	{
		image_count = 0;
	}
	var container = 'gallery_description_' + component_id;
	var target_url = url('includes/content/media/galleries/project/show_category.php?category='+category_id+'&component_id='+component_id+'&image_count='+image_count+'&alias='+urlencode(CURRENT_ALIAS));
	new Ajax.Updater(container, target_url);
}

function MG_Active(obj)
{
	var fields = getElementsByClassName('click catlist');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.firstChild.className = '';
	}
	obj.className = 'active';
}

function JScriptG_ShowImage(image_id, click)
{
	var click = (click != null) ? click : 0;
	if (click == 1)
	{
		JscriptG_DoRotateImage = 0;
	}
	
	var fields = getElementsByClassName('jscript_image', '*');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	
	var obj = document.getElementById('jscript_'+image_id);
	
	set_opacity(obj, 0);
	obj.style.display = 'block';
	JScriptG_FadeIn(obj, 0);
}

function JScriptG_FadeIn(obj, alpha, complete_function)
{
	var new_alpha = alpha + 5;
	if (new_alpha > 100) { new_alpha = 100; }
	set_opacity(obj, new_alpha);
	
	if (new_alpha != 100)
	{
		var func = function() { JScriptG_FadeIn(obj, new_alpha, complete_function); };
		setTimeout(func, 25);
	}
	else
	{
		if (complete_function != null)
		{
			complete_function();
		}
	}
}

function JScriptG_ShowThumbNext()
{
	var page_number = parseInt(document.getElementById('jscript_page_no').value);
	var num = page_number+1;
	var new_obj = document.getElementById('jscript_thumbrow_'+num);
	if (new_obj != null)
	{
		document.getElementById('jscript_page_no').value = num;
		
		var fields = getElementsByClassName('jscript_thumbrow', '*');
		for (x=0; x<fields.length; x++)
		{
			var el = fields[x];
			el.style.display = 'none';
		}
	
		set_opacity(new_obj, 0);
		new_obj.style.display = 'block';
		JScriptG_FadeIn(new_obj, 0);
	}
}


function JScriptG_ShowThumbPrevious()
{
	var page_number = parseInt(document.getElementById('jscript_page_no').value);
	var num = page_number-1;
	var new_obj = document.getElementById('jscript_thumbrow_'+num);
	if (new_obj != null)
	{
		document.getElementById('jscript_page_no').value = num;
		
		
		var fields = getElementsByClassName('jscript_thumbrow', '*');
		for (x=0; x<fields.length; x++)
		{
			var el = fields[x];
			el.style.display = 'none';
		}
	
		set_opacity(new_obj, 0);
		new_obj.style.display = 'block';
		JScriptG_FadeIn(new_obj, 0);
	}
}

function JscriptG_RotateImage(index)
{
	var obj = document.getElementById('jscript_image_id_'+index);
	if (obj == null)
	{
		if (index != 1)
		{
			index = 0;
			obj = document.getElementById('jscript_image_id_'+index); // wrap around to the first image
		}
		else
		{
			return;
		}
	}
	
	var func = function() {
		var fade_id = obj.value;
		//alert('go!');
		
		if (JscriptG_DoRotateImage == 1)
		{
			JScriptG_ShowImage(fade_id);
			JscriptG_RotateImage(index+1);
		}
	}
	
	setTimeout(func, 5000);
}

var func = function()
{
	var obj = document.getElementById('jscript_page_no');
	if (obj != null)
	{
		obj.value = 0;
		JscriptG_RotateImage(1);
	}
}

var JscriptG_DoRotateImage = 1;

add_load_event(func);