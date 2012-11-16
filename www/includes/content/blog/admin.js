
function Blog_Home()
{
	var component_id = document.getElementById('component_id').value;
	
	var obj = document.getElementById('co_main');
	var target_url = url('includes/content/blog/edit.php?refresh=1&component_id='+component_id);
	
	var func = function() {
		Blog_Close();
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			Pico_FadeIn(obj, 0);
		}} );
	}
	
	Pico_FadeOut(obj, 100, func);
}

function Blog_UpdateLastSaves(entry_id)
{
	if (CKEDITOR.instances.last_user_saved_post)
	{
		CKEDITOR.instances.last_user_saved_post.destroy();
	}
	if (CKEDITOR.instances.last_auto_saved_post)
	{
		CKEDITOR.instances.last_auto_saved_post.destroy();
	}
	
	var target_url = url('includes/content/blog/blog_restore.php?refresh=1&entry_id='+entry_id);
	new Ajax.Updater('blog_restore', target_url, { onComplete: function() {
		CKEDITOR.replace('last_user_saved_post', { height: 275 });
		CKEDITOR.replace('last_auto_saved_post', { height: 275 });
	} });
}

function Blog_Save()
{
	var form = document.getElementById('blog_content_form');
	form.elements.blog_entry_text.value = CKEDITOR.instances.blog_story.getData();
	new Ajax.Form(form, { onComplete: function(t) {
		var obj = document.getElementById('blog_status');
		obj.innerHTML = t.responseText;
		var obj2 = document.getElementById('blog_status2');
		obj2.innerHTML = t.responseText;
		
		var func = function() {
			obj.innerHTML = '';
			obj2.innerHTML = '';
			Blog_UpdateLastSaves(form.elements.entry_id.value);
		}
		setTimeout(func, 5000);
		form.elements.page_action.value = 'edit_story';
	} } );
}

function Blog_Publish()
{
	var form = document.getElementById('blog_content_form');
	form.elements.page_action.value = 'publish';
	Blog_Save();
}

function Blog_AutoSave()
{
	var form = document.getElementById('blog_content_form');
	if (form)
	{
		form.elements.page_action.value = 'draft';
		form.elements.blog_entry_text.value = CKEDITOR.instances.blog_story.getData();
		new Ajax.Form(form, { onComplete: function(t) {
			var obj = document.getElementById('blog_status');
			obj.innerHTML = t.responseText;
			var func = function() {
				obj.innerHTML = '';
				Blog_UpdateLastSaves(form.elements.entry_id.value);
			}
			setTimeout(func, 5000);
			
			var func2 = function() {
				Blog_AutoSave()
			}
			setTimeout(func2, 30000);
		} } );
		form.elements.page_action.value = 'edit_story';
	}
}

function Blog_Close()
{
	if (CKEDITOR.instances.blog_story)
	{
		CKEDITOR.instances.blog_story.destroy();
	}
	if (CKEDITOR.instances.last_user_saved_post)
	{
		CKEDITOR.instances.last_user_saved_post.destroy();
	}
	if (CKEDITOR.instances.last_auto_saved_post)
	{
		CKEDITOR.instances.last_auto_saved_post.destroy();
	}
}

function Blog_NewStory(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		var response = t.responseText;
		
		var result = response.split('|');
		if (result[0] == 0)
		{
			// success, edit the story
			var entry_id = result[1];
			Blog_EditStory(entry_id);
		}
		else
		{
			// something bad
			var error = result[1];
			alert(error);
		}
	} });
}

function Blog_TabActivate(container)
{
	var fields = getElementsByClassName('tabbed_content', '*');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'none';
	}
	document.getElementById(container).style.display = 'block';
}

function Blog_EditStory(entry_id)
{
	var obj = document.getElementById('co_main');
	var target_url = url('includes/content/blog/blog_entry.php?id='+entry_id);
	
	var func = function() {
		new Ajax.Updater('co_main', target_url, { onComplete: function() {
			CKEDITOR.replace('blog_story', { height: 275 });
			CKEDITOR.replace('last_user_saved_post', { height: 275 });
			CKEDITOR.replace('last_auto_saved_post', { height: 275 });
			Pico_FadeIn(obj, 0);
			
			var func = function() {
				Blog_AutoSave();
			}
			setTimeout(func, 30000);
		}} );
	}
	
	Pico_FadeOut(obj, 100, func);
}

function Blog_DeleteEntry(entry_id)
{
	if (confirm('Are you sure you want to delete this entry?'))
	{
		var target_url = url('includes/content/blog/submit.php?page_action=delete_entry&entry_id='+entry_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Blog_Home();
		} } );
	}
}

function Blog_AddCategory()
{
	var component_id = document.getElementById('component_id').value;
	var new_category = prompt('Enter new category name');
	
	if ( (new_category != null) && (new_category.length > 0) )
	{
		var target_url = url('includes/content/blog/submit.php?page_action=add_category&component_id='+component_id+'&category='+urlencode(new_category));
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				// error
				alert(t.responseText);
			}
			else
			{
				Blog_ReloadCategory(component_id, new_category);
			}
		} } );
	}
}

function Blog_RenameCategory()
{
	var component_id = document.getElementById('component_id').value;
	var obj = document.getElementById('blog_cat_'+component_id);
	
	var index = obj.selectedIndex;
	if (obj.options[index].value != 0)
	{
		var category_id = obj.options[index].value;
		var text = obj.options[index].text;
		var new_category = prompt('Enter new category name', text);
		
		if ( (new_category != null) && (new_category.length > 0) )
		{
			var target_url = url('includes/content/blog/submit.php?page_action=edit_category&category='+urlencode(new_category)+'&category_id='+category_id);
			new Ajax.Request(target_url, { onComplete: function(t) {
				if (t.responseText.length > 0)
				{
					// error
					alert(t.responseText);
				}
				else
				{
					Blog_ReloadCategory(component_id, new_category);
				}
			} } );
		}
	}
}

function Blog_DeleteCategory()
{
	var component_id = document.getElementById('component_id').value;
	var obj = document.getElementById('blog_cat_'+component_id);
	
	var index = obj.selectedIndex;
	if (obj.options[index].value != 0)
	{
		var category_id = obj.options[index].value;
		if (confirm('Are you sure you want to delete this category?'))
		{
			var target_url = url('includes/content/blog/submit.php?page_action=delete_category&category_id='+category_id);
			new Ajax.Request(target_url, { onComplete: function(t) {
				if (t.responseText.length > 0)
				{
					// error
					alert(t.responseText);
				}
				else
				{
					Blog_ReloadCategory(component_id);
				}
			} } );
		}
	}
}

function Blog_ReloadCategory(component_id, selected)
{
	selected = (typeof selected == "undefined") ? '' : selected;
	
	// blog_category
	var target_url = url('includes/content/blog/submit.php?page_action=reload_category&component_id='+component_id);
	new Ajax.Updater('blog_category', target_url, { onComplete: function() {
		if (selected.length > 0)
		{
			// select the category we are passing in
			var obj = document.getElementById('blog_cat_'+component_id);
			
			var size = obj.options.length;
			for (x=0; x<size; x++)
			{
				var text = obj.options[x].text;
				if (text == selected)
				{
					obj.selectedIndex = x;
					return;
				}
			}
		}
	} });
}

function Blog_UpdateOptions(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function() {
		Pico_ReloadComponent(component_id);
		alert('Settings Updated');
	} } );
}

function Blog_ImageUploaded(filename)
{
	//alert(filename);
	var entry_id = document.getElementById('blog_entry_id').value;
	var target_url = url('includes/content/blog/submit.php?page_action=blog_image&entry_id='+entry_id+'&filename='+urlencode(filename));
	
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			// error
			alert(t.responseText);
		}
		else
		{
			var obj = document.getElementById('blog_image');
			var target_url = url('includes/content/blog/blog_image.php?refresh=1&entry_id='+entry_id);
			
			var func = function() {
				new Ajax.Updater('blog_image', target_url, { onComplete: function() {
					Pico_FadeIn(obj, 0);
				}} );
			}
			
			Pico_FadeOut(obj, 100, func);
		}
	} } );
}

function Blog_DeleteImage()
{
	var entry_id = document.getElementById('blog_entry_id').value;
	if (confirm('Are you sure you want to remove this image from your post?'))
	{
		var target_url = url('includes/content/blog/submit.php?page_action=delete_blog_image&entry_id='+entry_id);
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				// error
				alert(t.responseText);
			}
			else
			{
				var obj = document.getElementById('blog_image');
				var target_url = url('includes/content/blog/blog_image.php?refresh=1&entry_id='+entry_id);
				
				var func = function() {
					new Ajax.Updater('blog_image', target_url, { onComplete: function() {
						Pico_FadeIn(obj, 0);
					}} );
				}
				
				Pico_FadeOut(obj, 100, func);
			}
		} } );
	}
}

function Blog_DeleteComment(entry_id, comment_id)
{
	if (confirm('Are you sure you want to delete this comment?'))
	{
		var target_url = url('includes/content/blog/submit.php?page_action=delete_comment&comment_id='+comment_id);
		new Ajax.Request(target_url, { onComplete: function() {
			var obj = document.getElementById('blog_admin_comments');
			
			var func = function() {
				var target_url = url('includes/content/blog/blog_comments.php?refresh=1&entry_id='+entry_id);
				new Ajax.Updater('blog_admin_comments', target_url, { onComplete: function() {
					Pico_FadeIn(obj, 0);
				}} );
			}
			Pico_FadeOut(obj, 100, func);
		} } );
	}
}

function Blog_ApproveComment(entry_id, comment_id)
{
	var target_url = url('includes/content/blog/submit.php?page_action=approve_comment&comment_id='+comment_id);
	new Ajax.Request(target_url, { onComplete: function() {
		var obj = document.getElementById('blog_admin_comments');
		
		var func = function() {
			var target_url = url('includes/content/blog/blog_comments.php?refresh=1&entry_id='+entry_id);
			new Ajax.Updater('blog_admin_comments', target_url, { onComplete: function() {
				Pico_FadeIn(obj, 0);
			}} );
		}
		Pico_FadeOut(obj, 100, func);
	} } );
}

/*


function Blog_Install(form)
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

function Blog_UpdateCategoryDrop(component_id)
{
	var selected = document.getElementById('add_blog_form').elements.category.value;
	var category_url = url('includes/content/blog/submit.php?page_action=get_category_drop&component_id='+component_id+'&selected='+selected);
	new Ajax.Updater('choose_category', category_url);
}



function Blog_StoryImage(entry_id)
{
	var target_url = url('includes/content/blog/storyimage.php?entry_id='+entry_id);
	new Ajax.Updater('blog_entries', target_url);
}

function Blog_DeleteComment(post_id, comment_id)
{
	if (confirm('Are you sure you want to delete this comment?'))
	{
		var target_url = url('includes/content/blog/submit.php?page_action=delete_comment&comment_id='+comment_id);
		new Ajax.Request(target_url, { onComplete: function() {
			alert('Comment Deleted');
			//window.location = window.location;
			Blog_ReloadComments(post_id);
		} } );
	}
}*/