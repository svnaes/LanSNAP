function UP_Submit(form)
{
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		alert('Settings saved');
	} } );
}