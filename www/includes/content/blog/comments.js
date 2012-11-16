
function AddComment(form)
{
	var post_id = form.elements.post_id.value;
	//form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function(t) {
		var response = t.responseText;
		var flag = response.substring(0, 1);
		if (flag == '0')
		{
			var inner = response.substring(2);
			form.parentNode.innerHTML = inner;
		}
		else if (flag == '1')
		{
			var error = response.substring(2);
			alert(error);
		}
		else
		{
			var comment_id = response.substring(2);
			Blog_ReloadComments(post_id, comment_id);
			
			var comment_form = document.getElementById('comment_' + post_id);
			comment_form.innerHTML = 'Thank you!';
			
			//form.reset();
			//form.elements.parent.value = '0';
		}
		//form.elements.submitbtn.disabled = false;
	} } );
}


function ToggleComment(post_id)
{
	var obj = document.getElementById('comment_' + post_id);
	if (obj.style.display == 'none')
	{
		obj.style.display = 'block';
	}
	else
	{
		obj.style.display = 'none';
	}
}

function ShowComments(post_id)
{
	var obj = document.getElementById('comment_show_' + post_id);
	if (obj.style.display == 'block')
	{
		obj.style.display = 'none';
	}
	else
	{
		obj.style.display = 'block';
	}
}

function Blog_Reply(post_id, comment_id)
{
	var obj = document.getElementById('comment_' + post_id);
	obj.style.display = 'block';
	
	var form = document.getElementById('comment_form_' + post_id);
	form.elements.parent.value = comment_id;
	form.elements.verify.focus();
	form.elements.name.focus();
}

function Blog_ReloadComments(post_id, comment_id)
{
	var comment_container = 'comment_container_' + post_id;
	var target_url = url('includes/content/blog/comments.php?post_id='+post_id+'&page_action=reload_comments');
	new Ajax.Updater(comment_container, target_url, { onComplete: function() {
		var obj = document.getElementById('comment_' + post_id);
		obj.style.display = 'none';
		
		if (comment_id != null)
		{
			
			//var obj1 = document.getElementById('comment_container_' + post_id);
			var obj2 = document.getElementById('blogcomment_'+comment_id);
			obj2.scrollIntoView();
			//obj.scrollIntoView();
			//alternativeScrollIntoView(obj1, obj2);
		}
	} });
}

function alternativeScrollIntoView(parentDiv, elementIntoDiv)
{
	var principal =parentDiv;
	principal.scrollTop = 0;
	var rects = principal.getClientRects()[0];
	var topFinal = rects.top;
	var bottomFinal = rects.bottom;
	var bottomActual = elementIntoDiv.getClientRects()[0].bottom;
	if (bottomActual == 0)
	{
		return;
	}
	while(bottomActual>bottomFinal||bottomActual<topFinal)
	{
		var direction="down";
		if(bottomActual<topFinal) direction="up";
		principal.doScroll(direction);
		bottomActual=elementIntoDiv.getClientRects()[0].bottom;
	}
}