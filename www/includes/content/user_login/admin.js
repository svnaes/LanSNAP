function UL_UpdateSettings(form)
{
	new Ajax.Form(form, { onComplete: function(){
		alert('Setting Saved');
	}});
}