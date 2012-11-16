/*
 * admin.js .. should not show up unless you are logged in as moderator or higher
 */
 
 /**
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/
 * Version : 2.01.B
 * By Binny V A
 * License : BSD
 */
shortcut = {
	'all_shortcuts':{},//All the shortcuts are stored in this array
	'add': function(shortcut_combination,callback,opt) {
		//Provide a set of default options
		var default_options = {
			'type':'keydown',
			'propagate':false,
			'disable_in_input':false,
			'target':document,
			'keycode':false
		}
		if(!opt) opt = default_options;
		else {
			for(var dfo in default_options) {
				if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
			}
		}

		var ele = opt.target;
		if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
		var ths = this;
		shortcut_combination = shortcut_combination.toLowerCase();

		//The function to be called at keypress
		var func = function(e) {
			e = e || window.event;
			
			if(opt['disable_in_input']) { //Don't enable shortcut keys in Input, Textarea fields
				var element;
				if(e.target) element=e.target;
				else if(e.srcElement) element=e.srcElement;
				if(element.nodeType==3) element=element.parentNode;

				if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') return;
			}
	
			//Find Which key is pressed
			if (e.keyCode) code = e.keyCode;
			else if (e.which) code = e.which;
			var character = String.fromCharCode(code).toLowerCase();
			
			if(code == 188) character=","; //If the user presses , when the type is onkeydown
			if(code == 190) character="."; //If the user presses , when the type is onkeydown

			var keys = shortcut_combination.split("+");
			//Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
			var kp = 0;
			
			//Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
			var shift_nums = {
				"`":"~",
				"1":"!",
				"2":"@",
				"3":"#",
				"4":"$",
				"5":"%",
				"6":"^",
				"7":"&",
				"8":"*",
				"9":"(",
				"0":")",
				"-":"_",
				"=":"+",
				";":":",
				"'":"\"",
				",":"<",
				".":">",
				"/":"?",
				"\\":"|"
			}
			//Special Keys - and their codes
			var special_keys = {
				'esc':27,
				'escape':27,
				'tab':9,
				'space':32,
				'return':13,
				'enter':13,
				'backspace':8,
	
				'scrolllock':145,
				'scroll_lock':145,
				'scroll':145,
				'capslock':20,
				'caps_lock':20,
				'caps':20,
				'numlock':144,
				'num_lock':144,
				'num':144,
				
				'pause':19,
				'break':19,
				
				'insert':45,
				'home':36,
				'delete':46,
				'end':35,
				
				'pageup':33,
				'page_up':33,
				'pu':33,
	
				'pagedown':34,
				'page_down':34,
				'pd':34,
	
				'left':37,
				'up':38,
				'right':39,
				'down':40,
	
				'f1':112,
				'f2':113,
				'f3':114,
				'f4':115,
				'f5':116,
				'f6':117,
				'f7':118,
				'f8':119,
				'f9':120,
				'f10':121,
				'f11':122,
				'f12':123
			}
	
			var modifiers = { 
				shift: { wanted:false, pressed:false},
				ctrl : { wanted:false, pressed:false},
				alt  : { wanted:false, pressed:false},
				meta : { wanted:false, pressed:false}	//Meta is Mac specific
			};
                        
			if(e.ctrlKey)	modifiers.ctrl.pressed = true;
			if(e.shiftKey)	modifiers.shift.pressed = true;
			if(e.altKey)	modifiers.alt.pressed = true;
			if(e.metaKey)   modifiers.meta.pressed = true;
                        
			for(var i=0; k=keys[i],i<keys.length; i++) {
				//Modifiers
				if(k == 'ctrl' || k == 'control') {
					kp++;
					modifiers.ctrl.wanted = true;

				} else if(k == 'shift') {
					kp++;
					modifiers.shift.wanted = true;

				} else if(k == 'alt') {
					kp++;
					modifiers.alt.wanted = true;
				} else if(k == 'meta') {
					kp++;
					modifiers.meta.wanted = true;
				} else if(k.length > 1) { //If it is a special key
					if(special_keys[k] == code) kp++;
					
				} else if(opt['keycode']) {
					if(opt['keycode'] == code) kp++;

				} else { //The special keys did not match
					if(character == k) kp++;
					else {
						if(shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
							character = shift_nums[character]; 
							if(character == k) kp++;
						}
					}
				}
			}
			
			if(kp == keys.length && 
						modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
						modifiers.shift.pressed == modifiers.shift.wanted &&
						modifiers.alt.pressed == modifiers.alt.wanted &&
						modifiers.meta.pressed == modifiers.meta.wanted) {
				callback(e);
	
				if(!opt['propagate']) { //Stop the event
					//e.cancelBubble is supported by IE - this will kill the bubbling process.
					e.cancelBubble = true;
					e.returnValue = false;
	
					//e.stopPropagation works in Firefox.
					if (e.stopPropagation) {
						e.stopPropagation();
						e.preventDefault();
					}
					return false;
				}
			}
		}
		this.all_shortcuts[shortcut_combination] = {
			'callback':func, 
			'target':ele, 
			'event': opt['type']
		};
		//Attach the function with the event
		if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
		else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
		else ele['on'+opt['type']] = func;
	},

	//Remove the shortcut - just specify the shortcut and I will remove the binding
	'remove':function(shortcut_combination) {
		shortcut_combination = shortcut_combination.toLowerCase();
		var binding = this.all_shortcuts[shortcut_combination];
		delete(this.all_shortcuts[shortcut_combination])
		if(!binding) return;
		var type = binding['event'];
		var ele = binding['target'];
		var callback = binding['callback'];

		if(ele.detachEvent) ele.detachEvent('on'+type, callback);
		else if(ele.removeEventListener) ele.removeEventListener(type, callback, false);
		else ele['on'+type] = false;
	}
}

function Navigate(obj)
{
	window.location = url(obj.value);
}

function Pico_ManageUsers()
{
	Pico_DisplayAP(url('includes/ap_manage_users.php'), 'Manage Users', 650, 400);
}

function Pico_EditGroup(group_id)
{
	Pico_DisplayAP(url('includes/ap_edit_group.php?group_id='+group_id), 'Edit Group', 450, 400);
}

function Pico_DeleteGroup(group_id)
{
	if (confirm('Are you sure you want to delete this group?'))
	{
		var target_url = url('includes/ap_actions.php?ap_action=delete_group&group_id='+group_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Pico_ManageGroups();
		} } );
	}
}

function Pico_ManageGroups()
{
	Pico_DisplayAP(url('includes/ap_groups.php'), 'Manage Groups', 450, 400);
}

function Pico_UserProfiles(edit_id)
{
	edit_id = (edit_id != null) ? edit_id : 0;
	Pico_DisplayAP(url('includes/ap_user_profiles.php?edit='+edit_id), 'User Profiles', 800, 500);
}

function Pico_UserProfileFields(profile_id, edit_id)
{
	edit_id = (edit_id == null) ? 0 : edit_id;
	Pico_DisplayAP(url('includes/ap_user_profile_fields.php?profile_id='+profile_id+'&edit='+edit_id), 'Profile Fields', 800, 500);
}

function Pico_UserProfileMoveField(profile_id, field_id, direction)
{
	var target_url = url('includes/ap_actions.php?ap_action=move_profile_field&field_id='+field_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		Pico_UserProfileFields(profile_id)
	} } );
}

function Pico_UserProfileDeleteField(profile_id, field_id)
{
	if (confirm('Are you sure you want to delete this field? All corresponding data stored will be lost.'))
	{
		var target_url = url('includes/ap_actions.php?ap_action=delete_profile_field&field_id='+field_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Pico_UserProfileFields(profile_id)
		} } );
	}
}

function Pico_AddUserProfile(form)
{
	new Ajax.Form(form, { onComplete: function() {
		Pico_UserProfiles();
	}} );
}

function Pico_DeleteUserProfile(profile_id)
{
	if (confirm('Are you sure you want to delete this user profile?'))
	{
		var target_url = url('includes/ap_actions.php?ap_action=delete_user_profile&profile_id='+profile_id);
		new Ajax.Request(target_url, { onComplete: function(t) {
			if (t.responseText.length > 0)
			{
				alert(t.responseText);
			}
			else
			{
				Pico_UserProfiles();
			}
		} } );
	}
}

function Pico_AddProfileField(form)
{
	var profile_id = form.elements.profile_id.value;
	new Ajax.Form(form, { onComplete: function() {
		Pico_UserProfileFields(profile_id);
	}} );
}

function Pico_PaymentSettings()
{
	Pico_DisplayAP(url('includes/ap_payment_settings.php'), 'Payment Settings', 800, 500);
}

function Pico_PaymentSettingsUpdate(form)
{
	new Ajax.Form(form, { onComplete: function () {
		alert('Settings Saved');
	} } );
}

function Pico_SubmitEditGroup(form)
{
	var source = form.elements.current_users;
	
	for (var i=0; i<source.options.length; i++)
	{
		source.options[i].selected = true;
	}
	
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			Pico_ManageGroups();
		}
	} } );
}

function Pico_AddGroup(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			Pico_ManageGroups();
		}
	} } );
}

function Pico_SelectDelete(source_id)
{
	var source = document.getElementById(source_id);
	while (source.selectedIndex != -1)
	{
		source.options[source.selectedIndex] = null;
	}
}

function Pico_SelectAddOption(dest, text, value)
{
	var optn = document.createElement("OPTION");
	optn.text = text;
	optn.value = value;
	dest.options.add(optn);
}

function Pico_SelectAdd(source_id, dest_id)
{
	var source = document.getElementById(source_id);
	var dest   = document.getElementById(dest_id);

	while (source.selectedIndex != -1)
	{
		text = source.options[source.selectedIndex].text;
		value = source.options[source.selectedIndex].value;
		
		source.options[source.selectedIndex].selected = false;
		Pico_SelectAddOption(dest, text, value);
	}
}

function Pico_DeleteUser(user_id)
{
	if (confirm('Are you sure you want to delete this user?'))
	{
		var target_url = url('includes/ap_actions.php?ap_action=delete_user&user_id='+user_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Pico_ManageUsers();
		} } );
	}
}

function Pico_AddUser(user_id)
{
	var extra = '';
	var title = 'Add New User';
	
	if (user_id != null)
	{
		extra = '?edit='+user_id;
		title = 'Edit User';
	}

	Pico_DisplayAP(url('includes/ap_users.php'+extra), title, 375, 275);
}

function Pico_SH_AP_Refresh()
{
	var target_url = url('includes/ap_site_heirarchy.php?show=1');
	new Ajax.Updater('site_heirarchy_display', target_url);
	//document.getElementById('site_heirarchy_display').innerHTML = t.responseText;
	var form = document.getElementById('add_sh_form');
	form.style.display = 'none';
	
	var target_url = url('includes/ap_site_heirarchy.php?show=2');
	new Ajax.Updater('site_heirarchy_form', target_url);
}

function Pico_SHSubmit(form)
{
	new Ajax.Form(form, { onComplete: function() {
		Pico_SH_AP_Refresh();
	} } );
}

function Pico_SHAddItem(parent_id)
{
	var form = document.getElementById('add_sh_form');
	form.elements.parent.value = parent_id;
	form.style.display = 'block';
}

function Pico_SHMoveItem(item_id, direction)
{
	var target_url = url('includes/ap_actions.php?ap_action=sh_item_move&id='+item_id+'&direction='+direction);
	new Ajax.Request(target_url, { onComplete: function() {
		Pico_SH_AP_Refresh();
	} } );
}

function Pico_SHHideItem(item_id)
{
	var target_url = url('includes/ap_actions.php?ap_action=sh_hide_delete&id='+item_id);
	new Ajax.Request(target_url, { onComplete: function() {
		Pico_SH_AP_Refresh();
	} } );
}

function Pico_SHUnlinkItem(item_id)
{
	var target_url = url('includes/ap_actions.php?ap_action=sh_unlink_delete&id='+item_id);
	new Ajax.Request(target_url, { onComplete: function() {
		Pico_SH_AP_Refresh();
	} } );
}

function Pico_SHDeleteItem(item_id)
{
	if (confirm('Are you sure you want to delete this item and all of its sub items?'))
	{
		var target_url = url('includes/ap_actions.php?ap_action=sh_item_delete&id='+item_id);
		new Ajax.Request(target_url, { onComplete: function() {
			Pico_SH_AP_Refresh();
		} } );
	}
}

function Pico_SiteHeirarchy()
{
	Pico_DisplayAP(url('includes/ap_site_heirarchy.php'), 'Site Heirarchy', 800, 500);
}

function Pico_ClonePage()
{
	Pico_DisplayAP(url('includes/ap_clone_page.php?page_id='+CURRENT_PAGE), 'Clone Page', 400, 150);
}

function Pico_BulkClonePage()
{
	Pico_DisplayAP(url('includes/ap_clone_page.php?bulk=1&page_id='+CURRENT_PAGE), 'Bulk Add Pages', 350, 200);
}

function Pico_DeletePage()
{
	Pico_DisplayAP(url('includes/ap_delete_page.php?page_id='+CURRENT_PAGE), 'Delete Page', 300, 150);
}

function Pico_EditPage()
{
	Pico_DisplayAP(url('includes/ap_pages.php?edit='+CURRENT_PAGE), 'Edit Page', 400, 500);
}

function Pico_AddPage()
{
	Pico_DisplayAP(url('includes/ap_pages.php'), 'Add Page', 400, 500);
}

function Pico_FTPSettings()
{
	Pico_DisplayAP(url('includes/ap_ftp.php'), 'FTP Settings', 200, 200);
}

function Pico_Update()
{
	Pico_DisplayAP(url('includes/ap_update.php'), 'Update Pico', 500, 300);
}

function Pico_SaveFTP(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		alert(t.responseText);
	} } );
}

function Pico_ShowPanel(panel)
{
	var panels = new Array();
	panels[0] = 'lap_pages';
	panels[1] = 'lap_content';
	panels[2] = 'lap_users';
	
	for (var x=0; x < 3; x++)
	{
		var obj = document.getElementById(panels[x]);
		if (obj.style.display == 'block')
		{
			var func = function() {
				obj.style.display = 'none';
				Pico_ShowPanel(panel);
			};
			Pico_FadeOut(obj, 100, func);
			return;
		}
	}
	
	var panel_obj = document.getElementById(panel);
	panel_obj.style.display = 'block';
	
	Pico_FadeIn(panel_obj, 0);
}

function Pico_SubmitAndCloseAP(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		Pico_CloseAP()
	} } );
}

function Pico_DeletePageSubmit(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		var fade_complete = function() {
			window.location = url(''); // should send us to the home page
		}
		var ap = document.getElementById('action_panel');
		Pico_FadeOut(ap, 100, fade_complete);
	} } );
}

function Pico_AddUserSubmit(form)
{
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function(t) {
		Pico_ManageUsers();
	} } );
}

function Pico_AddPageSubmit(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		var fade_complete = function() {
			window.location = url(t.responseText);
		}
		var ap = document.getElementById('action_panel');
		Pico_FadeOut(ap, 100, fade_complete);
	} } );
}

function Pico_CheckPage(page_name, post_action)
{
	var form = document.getElementById('page_form');
	page_name = urlencode(page_name);
	var target_url = url('includes/ap_actions.php?ap_action=check_page&page_name='+page_name+'&current_page='+CURRENT_PAGE+'&post_action='+post_action);
	new Ajax.Request(target_url, { onComplete: function(t) {
		var obj = document.getElementById('page_indicator');
		Pico_VerifyField(obj, t.responseText, function() { PicoFormCheck(form); } );
	}} );
}

function Pico_VerifyField(obj, result, complete_func)
{
	if (obj != null)
	{
		if (result == 'GOOD')
		{
			obj.style.background = 'url('+url('includes/icons/ok.png')+') center no-repeat';
			obj.title = 'OK!';
		}
		else
		{
			obj.style.background = 'url('+url('includes/icons/warning.png')+') center no-repeat';
			obj.title = 'ERROR!';
		}
	}
	
	if (complete_func != null)
	{
		complete_func();
	}
}

function Pico_VerifyContentDescription(obj)
{
	if (obj.value.length > 5)
	{
		result = 'GOOD';
	}
	else
	{
		result = 'BAD';
	}
	var indicator = document.getElementById('content_description_indicator');
	var form      = document.getElementById('add_content_form');
	Pico_VerifyField(indicator, result, function() { PicoFormCheck(form); });
}

function Pico_VerifyUsername()
{
	var form = document.getElementById('user_form');
	var username = urlencode(form.elements.username.value);
	var target_url = url('includes/ap_actions.php?ap_action=check_user&username='+username);
	new Ajax.Request(target_url, { onComplete: function(t) {
		var obj = document.getElementById('username_indicator');
		Pico_VerifyField(obj, t.responseText, function() { PicoFormCheck(form); });
	}} );
}

function PicoFormCheck(form)
{
	var fields = getElementsByClassName('indicator', '*');
	
	var good = 0;
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		if (el.title == 'OK!')
		{
			good++;
		}
	}
	
	if (good == fields.length)
	{
		form.elements.submitbtn.disabled = false;
	}
	else
	{
		form.elements.submitbtn.disabled = true;
	}
}

function Pico_VerifyPassword()
{
	var obj = document.getElementById('password_indicator');
	var form = document.getElementById('user_form');
	var ok = 'BAD';
	if (form.elements.password.value.length > 5)
	{
		ok = 'GOOD';
	}
	Pico_VerifyField(obj, ok, function() { PicoFormCheck(form); });
}

function Pico_VerifyConfirm(direct)
{
	var obj = document.getElementById('confirm_indicator');
	var form = document.getElementById('user_form');
	var ok = 'BAD';
	if (form.elements.password.value == form.elements.confirm.value)
	{
		ok = 'GOOD';
	}
	Pico_VerifyField(obj, ok, function() { PicoFormCheck(form); });
	
	if ((form.elements.email_address.value.length > 0) && (direct == true))
	{
		Pico_VerifyEmail(); // this is here in case when we are editing a user and not changing the e-mail address we can continue
	}
}

function Pico_VerifyEmail(direct)
{
	var obj = document.getElementById('email_indicator');
	var form = document.getElementById('user_form');
	var ok = 'BAD';

	var email  = form.elements.email_address.value;
	var regexp = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	
	if (regexp.test(email))
	{
		ok = 'GOOD';
	}
	Pico_VerifyField(obj, ok, function() { PicoFormCheck(form); });
	
	if ((form.elements.email_address.value.length > 0) && (direct == true))
	{
		Pico_VerifyPassword(); // this is here in case when we are editing a user and not changing the password we can continue
		Pico_VerifyConfirm(); 
	}
}

function Pico_AddContent(location, page_id, type)
{
	var target_url = url('includes/content_add.php?location='+location+'&page_id='+page_id+'&ru='+urlencode(REQUEST_URI)+'&type='+type);
	Pico_DisplayAP(target_url, 'Add Content', 400, 300);
}

function Pico_ClearEditModes(ignore)
{
	if ( (pico_is_in_add == 1) && (ignore != 'add') )
	{
		Pico_ToggleAddContent();
	}
	if ( (pico_is_in_edit == 1) && (ignore != 'edit') )
	{
		Pico_ToggleEditContent();
	}
	if ( (pico_is_in_delete == 1) && (ignore != 'delete') )
	{
		Pico_ToggleDeleteContent();
	}
	if ( (pico_is_in_move == 1) && (ignore != 'move') )
	{
		Pico_ToggleMoveContent();
	}
}

function Pico_RemoveContainer(id)
{
	var remove_obj = document.getElementById(id);
	if (remove_obj != null)
	{
		var parent  = remove_obj.parentNode;
		var i       = parent.firstChild;
		do
		{
			if (i.id == remove_obj.id)
			{
				parent.removeChild(i);
				return true;
			}
			var i = i=i.nextSibling;
		} while (i != null);
	}
	return false;
}

function Pico_DeleteContentSubmit(form)
{
	if (confirm('Are you sure you want to continue?'))
	{
		var component_id = form.elements.component_id.value;
		
		new Ajax.Form(form, { onComplete: function() {
			// remove the component from the page
			var container = 'box_' + component_id;
			Pico_RemoveContainer(container);
			Pico_CloseAP();
			Pico_ClearEditModes('none');
		}} );
	}
}

function Pico_MoveClick(destination)
{
	var node_id = destination.id;
	//var destination_id = node_id.split('_').pop(); // TEMP!
	var destination_id = node_id;
	//alert(destination_id);
	
	var target_url = url('includes/ap_actions.php?ap_action=move_content&component_id='+pico_move_source+'&destination='+destination_id+'&page_id='+CURRENT_PAGE);
	//alert(target_url);
	
	new Ajax.Request(target_url, { onComplete: function(t) {
		// refresh the window, this part will have to be AJAX-ified later
		
		var fields = getElementsByClassName('pico_move', 'div');
		for (x=0; x<fields.length; x++)
		{
			var el = fields[x];
			el.style.display = 'none';
			el.onclick = function() { };
		}
		
		var containers = t.responseText.split('|');
		for (x=0; x<containers.length; x++)
		{
			var i = containers[x];
			Pico_ReloadColumn(i);
		}
	} } );
}

function Pico_MoveContent(obj)
{
	var node_id = obj.parentNode.id;
	var component_id = parseFloat(node_id.split('_').pop());
	pico_move_source = component_id;
	Pico_ClearEditModes('none');
	
	var fields = getElementsByClassName('pico_move', 'div');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		el.style.display = 'block';
		el.onclick = function() {
			Pico_MoveClick(this)
		}
	}
}

function Pico_DeleteContent(obj)
{
	var node_id = obj.parentNode.id;
	var component_id = parseFloat(node_id.split('_').pop());
	
	var target_url = url('includes/content_delete.php?component_id='+component_id+'&page_id='+CURRENT_PAGE);
	
	Pico_DisplayAP(target_url, 'Delete Content', 300, 200);
}

function Pico_PrepEdit()
{
	//CodePressRun(); // initializes all the codepress windows
	
	var on_edit_obj = document.getElementById('on_edit_load');
	if (on_edit_obj != null)
	{
		var on_edit = on_edit_obj.value;
		eval(on_edit);
	}
}

function Pico_EditContentId(component_id, request_uri, page_id)
{
	var target_url = url('includes/ap_actions.php?ap_action=load_edit&component_id='+component_id+'&page_id='+page_id+'&ru='+urlencode(request_uri));
	
	var complete_func = function() {
		Pico_PrepEdit();
	};
	
	Pico_DisplayAP(target_url, 'Edit Content', 800, 500, complete_func);
}

function Pico_EditContent(obj, request_uri, page_id)
{
	var node_id = obj.parentNode.id;
	var component_id = parseFloat(node_id.split('_').pop());
	var target_url = url('includes/ap_actions.php?ap_action=load_edit&component_id='+component_id+'&page_id='+page_id+'&ru='+urlencode(request_uri));
	
	var complete_func = function() {
		Pico_PrepEdit();
	};
	
	Pico_DisplayAP(target_url, 'Edit Content', 800, 500, complete_func);
}

function Pico_ToggleEditContent()
{
	Pico_ClearEditModes('edit');
	var fields = getElementsByClassName('content_box_bg', 'div');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		
		if (pico_is_in_edit == 0)
		{
			//var parent_id = el.parentNode.id;
			var bg_style = 'url('+url('site/images/overlay3.png')+')';
			var onclick_func = function() { Pico_EditContent(this, REQUEST_URI, CURRENT_PAGE) };
			var border = '1px blue solid';
			var cursor = 'pointer';
			var padding = '3px';
		}
		else
		{
			var border = 'none';
			var bg_style = '';
			var cursor = 'default';
			var onclick_func = function() { };
			var padding='0px';
		}
		
		el.style.padding = padding;
		el.style.border = border;
		el.style.background = bg_style;
		el.onclick = onclick_func;
		el.style.cursor = cursor;
	}
	
	if (pico_is_in_edit == 0)
	{
		pico_is_in_edit = 1;
	}
	else
	{
		pico_is_in_edit = 0;
	}
}

function Pico_ToggleMoveContent()
{
	Pico_ClearEditModes('move');
	var fields = getElementsByClassName('content_box_bg', 'div');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		
		if (pico_is_in_move == 0)
		{
			//var parent_id = el.parentNode.id;
			var bg_style = 'url('+url('site/images/overlay3.png')+')';
			var onclick_func = function() { Pico_MoveContent(this) };
			var border = '1px yellow solid';
			var cursor = 'pointer';
			var padding = '3px';
		}
		else
		{
			var border = 'none';
			var bg_style = '';
			var cursor = 'default';
			var onclick_func = function() { };
			var padding='0px';
		}
		
		el.style.padding = padding;
		el.style.border = border;
		el.style.background = bg_style;
		el.onclick = onclick_func;
		el.style.cursor = cursor;
	}
	
	if (pico_is_in_move == 0)
	{
		pico_is_in_move = 1;
	}
	else
	{
		pico_is_in_move = 0;
	}
}

function Pico_ToggleDeleteContent()
{
	Pico_ClearEditModes('delete');
	var fields = getElementsByClassName('content_box_bg', 'div');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		
		if (pico_is_in_delete == 0)
		{
			//var parent_id = el.parentNode.id;
			var bg_style = 'url('+url('site/images/overlay3.png')+')';
			var onclick_func = function() { Pico_DeleteContent(this) };
			var border = '1px red solid';
			var cursor = 'pointer';
			var padding = '3px';
		}
		else
		{
			var border = 'none';
			var bg_style = '';
			var cursor = 'default';
			var onclick_func = function() { };
			var padding='0px';
		}
		
		el.style.padding = padding;
		el.style.border = border;
		el.style.background = bg_style;
		el.onclick = onclick_func;
		el.style.cursor = cursor;
	}
	
	if (pico_is_in_delete == 0)
	{
		pico_is_in_delete = 1;
	}
	else
	{
		pico_is_in_delete = 0;
	}
}

function Pico_ToggleAddContent(type)
{
	Pico_ClearEditModes('add');
	var fields = getElementsByClassName('content_div_bg', 'div');
	
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		
		if (pico_is_in_add == 0)
		{
			//var parent_id = el.parentNode.id;
			var bg_style = 'url('+url('site/images/overlay3.png')+')';
			//var onclick_func = function() { Pico_AddContent(this, REQUEST_URI, CURRENT_PAGE) };

			var onclick_func = function() { Pico_AddContent(this.parentNode.id, CURRENT_PAGE, type) };
			var border = '1px green solid';
			var cursor = 'pointer';
			var padding = '3px';
		}
		else
		{
			var border = 'none';
			var bg_style = '';
			var cursor = 'default';
			var onclick_func = function() { };
			var padding='0px';
		}
		
		el.style.padding = padding;
		el.style.border = border;
		el.style.background = bg_style;
		el.onclick = onclick_func;
		el.style.cursor = cursor;
	}
	
	if (pico_is_in_add == 0)
	{
		pico_is_in_add = 1;
	}
	else
	{
		pico_is_in_add = 0;
	}
}

function Pico_SelectContent(obj)
{
	if (obj.value.length == 0)
	{
		var result = 'BAD';
		// fadeout
		var fields = getElementsByClassName('content_description', 'div');
		for (var x=0; x<fields.length; x++)
		{
			var el = fields[x];
			if (el.style.display == 'block')
			{
				var func = function() {
					el.style.display = 'none';
				}
				Pico_FadeOut(el, 100, func);
			}
		}
	}
	else
	{
		var result = 'GOOD';
		var newobj = document.getElementById('content_description_'+obj.value);
		var fields = getElementsByClassName('content_description', 'div');
		var check  = false;
		for (var x=0; x<fields.length; x++)
		{
			var el = fields[x];
			if (el.style.display == 'block')
			{
				// we have something to hide
				check = true;
				var func = function() {
					el.style.display = 'none';
					set_opacity(newobj, 0);
					newobj.style.display = 'block';
					Pico_FadeIn(newobj, 0);
				}
				Pico_FadeOut(el, 100, func);
				return;
			}
		}
		if (check == false)
		{
			// fade in we have not display anything yet
			set_opacity(newobj, 0);
			newobj.style.display = 'block';
			Pico_FadeIn(newobj, 0);
		}
	}
	var indicator = document.getElementById('content_type_indicator');
	var form      = document.getElementById('add_content_form');
	Pico_VerifyField(indicator, result, function() { PicoFormCheck(form); });
}

function Pico_AddContentSubmit(form)
{
	form.elements.submitbtn.disabled = true;
	new Ajax.Form(form, { onComplete: function(t) {
		// we're getting back HTML for editing, or to continue with some install... so just take return HTML and fade it in
		var obj = document.getElementById('ap_content');
		var func = function() {
			var func2 = function() {
				obj.innerHTML = t.responseText;
				var component_id = document.getElementById('js_component_id').value;
				var location     = document.getElementById('js_location').value;
				var target_url   = url('includes/ap_actions.php?ap_action=get_scripts&component_id='+component_id);
				new Ajax.Request(target_url, { onComplete: function(d) {
					var files = d.responseText.split('|');
					for (i=0; i<files.length; i++)
					{
						loadjscssfile(files[i]);
					}
				}});
				Pico_PrepEdit();
				Pico_FadeIn(obj, 0);
				
				var content_container = document.getElementById(location);
				
				var index = content_container.firstChild.childNodes.length - 1;
				var tmp   = content_container.firstChild.childNodes[index];
				
				content_container.firstChild.removeChild(tmp);
			
				var newdiv = document.createElement('DIV');
				newdiv.setAttribute('id', 'box_'+component_id);
				content_container.firstChild.appendChild(newdiv);
				content_container.firstChild.appendChild(tmp);
				
				Pico_ReloadComponent(component_id);
			}
			var ap             = document.getElementById('action_panel');
			var current_width  = parseFloat(ap.style.width);
			var current_height = parseFloat(ap.style.height);
			Pico_ResizeAP(current_width, current_height, 800, 500, 1, func2);
		}
		Pico_FadeOut(obj, 100, func);
	} } );
}

function Pico_LoadCssEditor()
{
	editAreaLoader.init({
		id: "ta_css_edit"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "no"
		,allow_toggle: true
		,word_wrap: true
		,language: "en"
		,syntax: "css"	
	});
}

function Pico_LoadJsEditor()
{
	editAreaLoader.init({
		id: "ta_js_edit"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "no"
		,allow_toggle: true
		,word_wrap: true
		,language: "en"
		,syntax: "js"	
	});
}

function Pico_COShow(container, click_obj, local_func)
{
	var obj = document.getElementById(container);
	
	if (click_obj != null)
	{
		// this highlights the current menu item the user has clicked on
		var ul = document.getElementById('component_options');
		var i  = ul.firstChild;
		do
		{
			i.style.fontWeight = 'normal';
			var i = i=i.nextSibling;
		} while (i != null);
		click_obj.style.fontWeight = 'bold';
	}
	
	// see if anything is visable
	//var main = document.getElementById('co_main');
	
	var fields = getElementsByClassName('co_hidden', 'div');
	for (x=0; x<fields.length; x++)
	{
		var el = fields[x];
		if (el.style.display != 'none')
		{
			var func = function() { 
				el.style.display = 'none';
				Pico_COShow(container, null, local_func);
			};
			Pico_FadeOut(el, 100, func);
			return;
		}
	}
	
	set_opacity(obj, 0);
	obj.style.display = 'block';
	
	if (local_func != null) { local_func(); }
	
	Pico_FadeIn(obj, 0);
}

function Pico_ReloadColumn(column, complete_func)
{
	// we will find the location with the given component id and the current page, as there can only be 1
	Pico_ClearEditModes('none');
	var target_url = url('includes/ap_actions.php?ap_action=reload_column&column='+urlencode(column)+'&page_id='+CURRENT_PAGE+'&ru='+urlencode(REQUEST_URI));
	
	new Ajax.Updater(column, target_url, { onComplete: function() {
		if (complete_func != null)
		{
			complete_func();
		}
	} });
}

function Pico_ReloadComponent(component_id)
{
	// page
	Pico_ClearEditModes('none');
	var target_url = url('includes/ap_actions.php?ap_action=reload_container&component_id='+component_id+'&page_id='+CURRENT_PAGE+'&ru='+urlencode(REQUEST_URI));
	new Ajax.Updater('box_'+component_id, target_url);
}

function Pico_SaveCSS(form)
{
	//form.elements.css.value = ta_css_edit.getCode();
	form.elements.css.value = editAreaLoader.getValue('ta_css_edit');
	new Ajax.Form(form, { onComplete: function() {
		updateStylesheets();
		alert('CSS Saved');
	}} );
}

function Pico_SaveJS(form)
{
	//form.elements.js.value = ta_js_edit.getCode();
	form.elements.js.value = editAreaLoader.getValue('ta_js_edit');
	new Ajax.Form(form, { onComplete: function() {
		// reload JS? we had this problem before did not work out so well
		alert('Javascript Saved');
	}} );
}

function Pico_UpdateComponent(form)
{
	new Ajax.Form(form, { onComplete: function() {
		alert('Settings Saved');
	}} );
}

function Pico_BulkAddContent()
{
	var target_url = url('includes/content_add_bulk.php?page_id='+CURRENT_PAGE);
	Pico_DisplayAP(target_url, 'Add Content', 400, 600);
}

function Pico_BulkAddSubmit(form)
{
	new Ajax.Form(form, { onComplete: function() {
		alert('Bulk Add Complete');
		Pico_CloseAP();
	} } );
}

shortcut.add("Ctrl+Shift+E",function() {
	Pico_ToggleEditContent();
});


var ap_lockout = 0;
var pico_is_in_add = 0;
var pico_is_in_edit = 0;
var pico_is_in_delete = 0;
var pico_is_in_move = 0;
var pico_move_source = 0;