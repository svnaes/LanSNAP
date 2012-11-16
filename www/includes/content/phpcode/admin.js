
function PC_Submit(form)
{
	var component_id = form.elements.component_id.value;
	//form.elements.php_data.value = pc_codewin.getCode();
	form.elements.php_data.value = editAreaLoader.getValue('pc_codewin');
	new Ajax.Form(form, { onComplete: function() {
		var func = function() { 
			Pico_ReloadComponent(component_id);
		};
		Pico_CloseAP(func);
	} } );
}

function PC_Load()
{
	editAreaLoader.init({
		id: "pc_codewin"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "no"
		,allow_toggle: true
		,word_wrap: true
		,language: "en"
		,syntax: "php"	
	});
}