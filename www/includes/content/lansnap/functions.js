
function LS_SeatTooltip(obj, component_id, seat_id) {
	var tooltip = document.getElementById('tooltip_' + seat_id);
	tooltip.innerHTML = 'Loading...';
	
	var target_url = url('includes/content/lansnap/mod_tooltip_info.php?component_id='+component_id+'&seat_id='+seat_id);
	new Ajax.Updater('tooltip_' + seat_id, target_url);
	tooltip.style.display = 'block';
	tooltip.onclick = function() { return false; }
	obj.onclick = null;
}

function LS_LoadSeatTooltip(component_id)
{
	var elements = getElementsByClassName('seat_taken');
	for (var x=0; x<elements.length; x++)
	{
		var el = elements[x];
		
		el.onclick = function() {
			var id = this.id;
			var parts = id.split('_');
			var seat_id = parts[1];
			
			LS_SeatTooltip(this, component_id, seat_id);
		}
		
		/*el.onmouseout = function() {
			this.tooltip.style.display = 'none';
		}*/
	}
}

function LS_CloseTooltip(component_id, seat_id)
{
	var obj     = document.getElementById('seat_' + seat_id);
	var tooltip = document.getElementById('tooltip_' + seat_id);
	
	tooltip.style.display = 'none';
	
	var func = function() {
		obj.onclick = function() {
			LS_SeatTooltip(this, component_id, seat_id);
		}
	}
	
	// just a delay so our triggers dont slam into each other
	setTimeout(func, 500);
}

function LS_UpdateSeat(user_id)
{
	var seat_id = document.getElementById('seat_number').value;
	
	var target_url = url('includes/content/lansnap/mod_tooltip_info.php?page_action=change_seat&user_id='+user_id+'&new_seat='+seat_id);
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length == 0)
		{
			window.location = window.location; // reload page
		}
		else
		{
			alert(t.responseText);
		}
	} } );
}