
var scale = 10;
var mouse_x = 0;
var mouse_y = 0;
var move_obj;
var table_pos = {};
var aIsDown = 0;
var sIsDown = 0;
var dIsDown = 0;
var wIsDown = 0;
var finetune = 1;

var ButtonTypes = {
	'8foot_tall' : {
		'className' : 'banquet_tall',
		'width' : 2.5,
		'height' : 8,
		'numSeats' : 3,
		'orientation' : 'vertical'
	},
	'8foot_tall_2seats' : {
		'className' : 'banquet_tall',
		'width' : 2.5,
		'height' : 8,
		'numSeats' : 2,
		'orientation' : 'vertical'
	},
	'6foot_tall' : {
		'className' : 'banquet_tall',
		'width' : 2.5,
		'height' : 6,
		'numSeats' : 2,
		'orientation' : 'vertical'
	},
	
	'8foot_wide' : {
		'className' : 'banquet_wide',
		'width' : 8,
		'height' : 2.5,
		'numSeats' : 3,
		'orientation' : 'horizontal'
	},
	'8foot_wide_2seats' : {
		'className' : 'banquet_wide',
		'width' : 8,
		'height' : 2.5,
		'numSeats' : 2,
		'orientation' : 'horizontal'
	},
	'6foot_wide' : {
		'className' : 'banquet_wide',
		'width' : 6,
		'height' : 2.5,
		'numSeats' : 2,
		'orientation' : 'horizontal'
	}
}

function ResizeCanvas(width, height)
{
	//var width = document.getElementById('canvas_width').value;
	//var height = document.getElementById('canvas_height').value;
	
	if ( (!isNaN(width)) && (!isNaN(height)) )
	{
		var obj = document.getElementById('canvas');
		var new_width = width * scale;
		var new_height = height * scale;
		obj.style.width = new_width + 'px';
		obj.style.height = new_height + 'px';
		
		// throw down some grid lines
		
		var canvas = document.getElementById('canvas');
		
		var x = scale;
		var counter = 1;
		while (x < new_width)
		{
			var line = document.createElement('div');
			var className = (counter % 5 == 0) ? 'grid_line five' : 'grid_line';
			
			line.className = className;
			line.style.left = x + 'px';
			line.style.top = '0px';
			line.style.bottom = '0px';
			line.style.width = '1px';
			
			canvas.appendChild(line);
			
			x = x+scale;
			counter++;
		}
		
		var y = scale;
		var counter = 1;
		while (y < new_height)
		{
			var line = document.createElement('div');
			var className = (counter % 5 == 0) ? 'grid_line five' : 'grid_line';
			
			line.className = className;
			line.style.top = y + 'px';
			line.style.left = '0px';
			line.style.right = '0px';
			line.style.height = '1px';
			
			canvas.appendChild(line);
			
			y = y+scale;
			counter++;
		}
	}
}

function add_load_event(func)
{
	var oldonload = window.onload;
	if (typeof window.onload != 'function')
	{
		window.onload = func;
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func();
		}
	}
}

var func = function()
{
	var elements = getElementsByClassName('placeable_container');
	for (var x=0; x<elements.length; x++)
	{
		var el = elements[x];
		//alert(el.childNodes[0].nodeValue);
		//alert(el.attributes['name']);
	}
}

//add_load_event(func);

function AddTable(table_type)
{
	var config = ButtonTypes[table_type];
	var obj = document.createElement('div');
	PrepTable(obj, table_type);
	obj.className = 'placeable_container ' + config.className;
	
	var left = (!isNaN(table_pos.x)) ? table_pos.x : 5;
	var top = (!isNaN(table_pos.y)) ? table_pos.y : 5;
	obj.style.left = left + 'px';
	obj.style.top = top + 'px';
	
	
	
	var canvas = document.getElementById('canvas');
	canvas.appendChild(obj);
	table_pos.y = top + obj.offsetHeight; // for next table
}

function save_position(e) {
    e = e || window.event;
    var cursor = {x:0, y:0};
    if (e.pageX || e.pageY) {
        cursor.x = e.pageX;
        cursor.y = e.pageY;
    } 
    else {
        var de = document.documentElement;
        var b = document.body;
        cursor.x = e.clientX + 
            (de.scrollLeft || b.scrollLeft) - (de.clientLeft || 0);
        cursor.y = e.clientY + 
            (de.scrollTop || b.scrollTop) - (de.clientTop || 0);
    }
    
	mouse_x = cursor.x;
	mouse_y = cursor.y;
	
	var canvas = document.getElementById('canvas');
	var objpos = strPos(canvas);
	
	var disp_x = cursor.x - objpos[0];
	var disp_y = cursor.y - objpos[1];
	
	if ( (disp_x <= canvas.clientWidth) && (disp_y <= canvas.clientHeight) && (disp_x > 0) && (disp_y > 0) )
	{
		document.getElementById('xpos').innerHTML = disp_x + ' / ' + (disp_x / scale) + 'ft';
		document.getElementById('ypos').innerHTML = disp_y + ' / ' + (disp_y / scale) + 'ft';
	}
	else
	{
		document.getElementById('xpos').innerHTML = '';
		document.getElementById('ypos').innerHTML = '';
	}
	
	if ((move_obj != null) && (typeof(move_obj) == 'object'))
	{
		// update position of move_obj;
		
		var left = cursor.x - objpos[0];
		var top = cursor.y - objpos[1];
		
		if (left < 5) { left = 5; }
		if (top < 5) { top = 5; }
		
		var max_left = canvas.clientWidth - move_obj.offsetWidth + 5;
		if (left > max_left) { left = max_left; }
		
		var max_top = canvas.clientHeight - move_obj.offsetHeight + 5;
		if (top > max_top) { top = max_top; }
		
		// snappy
		
		var snap = document.getElementById('snaptogrid');
		if (snap.checked)
		{
			left = Math.round((left-5) / scale) * scale;
			top = Math.round((top-5) / scale) * scale;
		}
		else
		{
			left = left - 5;
			top  = top - 5;
		}
		
		move_obj.style.left = (left) + 'px';
		move_obj.style.top = (top) + 'px';
		
		table_pos.x = left;
		table_pos.y = top + move_obj.offsetHeight;
	}
}

function strPos(strobj)
{
	strlft=strobj.offsetLeft;
	strtop=strobj.offsetTop;
	while(strobj.offsetParent!=null)
	{
		strpar=strobj.offsetParent;
		strlft+=strpar.offsetLeft;
		strtop+=strpar.offsetTop;
		strobj=strpar;
	}
	return [strlft,strtop];
}

function SaveLayout(layout_id)
{
	var tables = getElementsByClassName('placeable_container');
	
	var form = document.createElement('form');
	form.setAttribute('id', 'save_form');
	form.setAttribute('method', 'post');
	form.action = 'submit.php';
	
	var table_el = document.createElement('input');
	table_el.setAttribute('type', 'hidden');
	table_el.setAttribute('name', 'layout_id');
	table_el.setAttribute('value', layout_id);
	form.appendChild(table_el);
	
	var counter = 0;
	
	for (x=0; x<tables.length; x++)
	{
		var table = tables[x];
		if (table.style.display != 'none')
		{
			//alert(table.tableType);
			var x_coord = parseInt(table.style.left);
			var y_coord = parseInt(table.style.top);
			
			var table_el = document.createElement('input');
			table_el.setAttribute('type', 'hidden');
			table_el.setAttribute('name', 'tables['+counter+'][type]');
			table_el.setAttribute('value', table.tableType);
			form.appendChild(table_el);
			
			var table_el = document.createElement('input');
			table_el.setAttribute('type', 'hidden');
			table_el.setAttribute('name', 'tables['+counter+'][x]');
			table_el.setAttribute('value', x_coord);
			form.appendChild(table_el);
			
			var table_el = document.createElement('input');
			table_el.setAttribute('type', 'hidden');
			table_el.setAttribute('name', 'tables['+counter+'][y]');
			table_el.setAttribute('value', y_coord);
			form.appendChild(table_el);
			
			var table_id = (table.id != null) ? table.id : '';
			var table_el = document.createElement('input');
			
			table_el.setAttribute('type', 'hidden');
			table_el.setAttribute('name', 'tables['+counter+'][id]');
			table_el.setAttribute('value', table_id);
			form.appendChild(table_el);
			
			counter++;
		}
		
	}
	
	/*
	var fb = document.getElementById('form_box');
	fb.innerHTML = '';
	fb.appendChild(form);
	
	var submitForm = document.getElementById('save_form');
	*/
	
	new Ajax.Form(form, { onComplete: function(t) {
		alert(t.responseText);
	} } );
}

function PrepTable(obj, table_type)
{
	var config = ButtonTypes[table_type];
	var totalWidth = ((config.width * scale)-2);
	var totalHeight = ((config.height * scale)-2);
	
	obj.style.width =  totalWidth + 'px';
	obj.style.height = totalHeight + 'px';
	obj.tableType = table_type;
	
	obj.onclick = function() {
		var amount = document.getElementById('finetune').value;
		if (isNaN(amount)) { amount = 1; }
		amount = amount * scale;
		
		if (wIsDown == 1)
		{
			this.style.top = (parseInt(this.style.top) + amount) + 'px';
		}
		else if (sIsDown == 1)
		{
			this.style.top = (parseInt(this.style.top) - amount) + 'px';
		}
		else if (aIsDown == 1)
		{
			this.style.left = (parseInt(this.style.left) - amount) + 'px';
		}
		else if (dIsDown == 1)
		{
			this.style.left = (parseInt(this.style.left) + amount) + 'px';
		}
		else if (move_obj != this)
		{
			move_obj = this;
		}
		else
		{
			move_obj = null;
		}
	}
	
	for (var x=0; x<config.numSeats; x++)
	{
		var seat = document.createElement('div');
		var className = (x % 2 == 0) ? 'seat a' : 'seat b';
		
		if (config.orientation == 'vertical')
		{
			var seatHeight = (totalHeight/config.numSeats);
			seat.style.width = totalWidth + 'px';
			seat.style.height = seatHeight + 'px';
			seat.style.left = '0px';
			seat.style.top = (x * seatHeight) + 'px';
			seat.className = className;
		}
		else
		{
			var seatWidth = (totalWidth/config.numSeats);
			seat.style.width = seatWidth + 'px';
			seat.style.height = totalHeight + 'px';
			seat.style.top = '0px';
			seat.style.left = (x * seatWidth) + 'px';
			seat.className = className;
		}
		
		obj.appendChild(seat);
	}
	
	var del = document.createElement('div');
	del.className = 'delete';
	del.parent = obj;
	del.onclick = function() {
		if (confirm('Are you sure you want to delete this table?'))
		{
			this.parent.style.display = 'none';
			table_pos.x = 5;
			table_pos.y = 5;
			move_obj = null;
		}
		return false;
	}
	obj.appendChild(del);
}

function LoadTables()
{
	var elements = getElementsByClassName('placeable_container');
	for (var i=0; i<elements.length; i++)
	{
		var obj = elements[i];
		var table_type = obj.innerHTML;
		obj.innerHTML = '';
		PrepTable(obj, table_type);
	}
}

function VerifyFineTune()
{
	var amount = document.getElementById('finetune').value;
	if (isNaN(amount)) 
	{
		document.getElementById('finetune').value = finetune;
	}
	else
	{
		finetune = amount;
	}
}

window.onkeydown = function(e) {
	// w = 87, a = 65, s = 83, d = 68
	if (e.keyCode == 87) {
		wIsDown = 1;
	}
	if (e.keyCode == 65) {
		aIsDown = 1;
	}
	if (e.keyCode == 83) {
		sIsDown = 1;
	}
	if (e.keyCode == 68) {
		dIsDown = 1;
	}
}

document.onkeyup = function(e) {
	if (e.keyCode == 87) {
		wIsDown = 0;
	}
	if (e.keyCode == 65) {
		aIsDown = 0;
	}
	if (e.keyCode == 83) {
		sIsDown = 0;
	}
	if (e.keyCode == 68) {
		dIsDown = 0;
	}
}