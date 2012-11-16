
function EN_Signup(form)
{
	if (form.elements.submitbtn) {
		form.elements.submitbtn.disabled = true;
	}
	
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length == 0)
		{
			alert('Signup Complete!');
		}
		else
		{
			if (form.elements.submitbtn) {
				form.elements.submitbtn.disabled = false;
			}
			alert('Error: ' + t.responseText);
		}
	} } );
}