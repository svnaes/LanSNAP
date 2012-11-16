
function TWTR_UpdateOptions(form)
{
	form.elements.submitbtn.disabled = true;
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function() {
		form.elements.submitbtn.disabled = false;
		Pico_ReloadComponent(component_id);
		alert('Settings Updated');
	}} );
}