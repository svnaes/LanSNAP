
function LS_UpdateSettings(form)
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
	}} );
}

function LS_UpdateTickets(form)
{
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			var target_url = url('includes/content/lansnap/tickets.php?reload=1');
			new Ajax.Updater('co_tickets', target_url);
		}
	}} );
}

function LS_DeleteTicket(ticket_id)
{
	if (confirm('Are you sure you want to delete this ticket?'))
	{
		var target_url = url('includes/content/lansnap/submit.php?page_action=delete_ticket&ticket_id='+ticket_id);
		new Ajax.Request(target_url, { onComplete: function () {
			var target_url = url('includes/content/lansnap/tickets.php?reload=1');
			new Ajax.Updater('co_tickets', target_url);
		}} );
	}
}

function LS_LoadSurvey(component_id, edit_id)
{
	edit_id = (edit_id == null) ? 0 : edit_id;
	var target_url = url('includes/content/lansnap/survey.php?reload=1&component_id='+component_id+'&edit_id='+edit_id);
	new Ajax.Updater('co_survey', target_url);
}

function LS_Survey(form)
{
	var component_id = form.elements.component_id.value;
	new Ajax.Form(form, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			LS_LoadSurvey(component_id);
		}
	} } );
}

function LS_DeleteSurveyQuestion(component_id, question_id)
{
	if (confirm('Are you sure you want to delete this question?'))
	{
		var target_url = url('includes/content/lansnap/submit.php?page_action=delete_question&question_id='+question_id);
		new Ajax.Request(target_url, { onComplete: function() {
			LS_LoadSurvey(component_id);
		} } );
	}
}

function LS_ExternalDB(component_id)
{
	var target_url = url('includes/content/lansnap/external_db.php?component_id='+component_id);
	new Ajax.Updater('co_main', target_url);
}

function LS_EditHome(component_id)
{
	var target_url = url('includes/content/lansnap/edit.php?reload=1&component_id='+component_id);
	new Ajax.Updater('co_main', target_url);
}

function LS_ImportLanfestCSV(filename)
{
	var target_url = url('includes/content/lansnap/submit.php?page_action=import_lanfest&filename='+urlencode(filename));
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			alert('File Imported');
		}
	} } );
}

function LS_BindAllIPs(component_id, bind)
{
	alert('This will take some time please do not click again');
	var target_url = url('includes/content/lansnap/submit.php?page_action=bind_all_ips&component_id='+component_id+'&bind='+bind);
	new Ajax.Request(target_url, { onComplete: function(t) {
		if (t.responseText.length > 0)
		{
			alert(t.responseText);
		}
		else
		{
			alert('IPs Bound');
		}
	} } );
}