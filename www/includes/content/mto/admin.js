
function MTO_Reload(component_id, edit_id)
{
	edit_id = (edit_id != null) ? edit_id : 0;
	var target_url = url('includes/content/mto/edit.php?reload=1&component_id='+component_id+'&edit_id='+edit_id);
	new Ajax.Updater('co_main', target_url);
}

function MTO_Submit(form)
{
	var component_id = form.elements.component_id.value;
	
	new Ajax.Form(form, { onComplete: function() {
		MTO_Reload(component_id);
	} } );
}

function MTO_Delete(component_id, item_id)
{
	if (confirm('Are you sure you want to delete this item?'))
	{
		var target_url = url('includes/content/mto/submit.php?page_action=delete_item&item_id='+item_id);
		new Ajax.Request(target_url, { onComplete: function() {
			MTO_Reload(component_id);
		} } );
	}
}

function MTO_OrderRefresh(component_id)
{
	var target_url = url('includes/content/mto/order-window.php?component_id='+component_id);
	new Ajax.PeriodicalUpdater('order_window', target_url, {
		method: 'get', frequency: 3, decay: 0
	});
}

function MTO_CloseOrder(order_id)
{
	if (confirm('Are you sure you want to CLOSE this order?'))
	{
		var target_url = url('includes/content/mto/submit.php?page_action=close_order&order_id='+order_id);
		new Ajax.Request(target_url, { onComplete: function() {
			// nothing
		}} );
	}
}

function MTO_CancelOrder(order_id)
{
	if (confirm('Are you sure you want to CANCEL this order?'))
	{
		var target_url = url('includes/content/mto/submit.php?page_action=cancel_order&order_id='+order_id);
		new Ajax.Request(target_url, { onComplete: function() {
			// nothing
		}} );
	}
}

function MTO_SaveSettings(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			alert('Settings Saved');
		}
	} } );
}