
function BlogFeed_Update(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function() {
		var func = function() {
			Pico_ReloadComponent(component_id);
		}
		Pico_CloseAP(func);
	} } );
}