/* plaintext js functions */

function PT_Submit(form)
{
	new Ajax.Form(form, { onComplete: function(t) { 
		var ap = document.getElementById('action_panel');
		var func = function() { 
			var component_id = t.responseText;
			Pico_ReloadComponent(component_id);
			//window.location = window.location; // refresh the page
		};
		Pico_CloseAP(func);
	} });
}