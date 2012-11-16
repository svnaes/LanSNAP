<?php

define('ICONTACT_BASE_URL', 'https://app.icontact.com');

function IContactGetAccountID($key, $user, $pass)
{
	// Build iContact authentication
  $headers = array(
  'Accept: text/xml',
  'Content-Type: text/xml',
  'Api-Version: 2.0',
  'Api-AppId: ' . $key,
  'Api-Username: ' . $user,
  'Api-Password: ' . $pass
  );
  
  	
	$ch=curl_init(ICONTACT_BASE_URL."/icp/a/");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$buf = curl_exec($ch);
	curl_close($ch);
	$xml = simplexml_load_string($buf);
	$account_id = (string) $xml->accounts->account->accountId;
	
	if (!is_numeric($account_id))
	{
		return 0;
	}
	else
	{
		return $account_id;
	}
}

function IContactLogin($account_id, $key, $user, $pass, &$client_folder_id)
{
  // Build iContact authentication
  $headers = array(
  'Accept: text/xml',
  'Content-Type: text/xml',
  'Api-Version: 2.0',
  'Api-AppId: ' . $key,
  'Api-Username: ' . $user,
  'Api-Password: ' . $pass
  );

  // Connect to iContact to retrieve the client folder id
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract client folder id from response
  $client_folder_id = "";
  if (($pos=strpos($buf,"<clientFolderId>"))!==false)
  {
    $client_folder_id = substr($buf, strlen("<clientFolderId>")+$pos);
    if (($pos=strpos($client_folder_id,"<"))!==false)
    {
      $client_folder_id = substr($client_folder_id, 0, $pos);
    }
  }

  // If we have a non empty client_folder_id,
  // then everything worked well
  $result = ($client_folder_id+0 > 0);

  // Return result
  return $result;
}


function IContactSubscribe($account_id, $key, $user, $pass, $email, $list_id, &$result_str)
{
  // Get client folder id
  if (!IContactLogin($account_id, $key, $user, $pass, &$client_folder_id))
  {
    $result_str = "Failed retrieving client_folder_id for '$user'";
    return 0;
  }

  // Build iContact authentication
  $headers = array(
  'Accept: text/xml',
  'Content-Type: text/xml',
  'Api-Version: 2.0',
  'Api-AppId: ' . $key,
  'Api-Username: ' . $user,
  'Api-Password: ' . $pass
  );

  // Find contact_id for the given 'email'
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/$client_folder_id/contacts/?email=".URLEncode($email));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract contactId from response
  $contact_id = "";
  if (($pos=strpos($buf,"<contactId>"))!==false)
  {
    $contact_id = substr($buf, $pos+strlen("<contactId>"));
    if (($pos=strpos($contact_id,"<"))!==false)
    {
      $contact_id = substr($contact_id,0,$pos);
    }
  }

  // If we don't have a contactId, can't add subscription
  if (empty($contact_id))
  { 
    $result_str = "Failed finding a contact with the email address of '$email'";
    return 0;
  }

  // Build subscription record
  $data = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n<subscriptions>\r\n";
  $data.= "<subscription>\r\n";
  $data.= "<contactId>$contact_id</contactId>\r\n";
  $data.= "<listId>$list_id</listId>\r\n";
  $data.= "<status>normal</status>\r\n";
  $data.= "</subscription>\r\n</subscriptions>";

  // Add subscription
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/$client_folder_id/subscriptions/");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract subscriptionID from response
  $subscription_id = "";
  if (($pos=strpos($buf,"<subscriptionId>"))!==false)
  {
    $subscription_id = substr($buf, $pos+strlen("<subscriptionId>"));
    if (($pos=strpos($subscription_id,"<"))!==false)
    {
      $subscription_id = substr($subscription_id,0,$pos);
    }
  }

  // If we have a subscription id OR this subscription already existed, we're good
  $result = !empty($subscription_id) || strpos($buf,"could not be updated")!==false;

  // Set result string
  $result_str = ($result ? "Updated subscription $subscription_id" : $buf);

  // Return result
  return $result;
}

function IContactAddContact($account_id, $key, $user, $pass, $email, $firstname, $lastname,
&$result_str)
{
  // Get client folder id
  if (!IContactLogin($account_id, $key, $user, $pass, &$client_folder_id))
  {
    $result_str = "Failed retrieving client_folder_id for '$user'";
    return 0;
  }
 
  // Build iContact authentication
  $headers = array(
  'Accept: text/xml',
  'Content-Type: text/xml',
  'Api-Version: 2.0',
  'Api-AppId: ' . $key,
  'Api-Username: ' . $user,
  'Api-Password: ' . $pass
  );
         
  // Build contact record
  $data = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n<contacts>\r\n";
  $data.= "<contact>\r\n";
  $data.= "<email>$email</email>\r\n";
  $data.= "<firstName>$firstname</firstName>\r\n";
  $data.= "<lastName>$lastname</lastName>\r\n";
  $data.= "<status>normal</status>\r\n";
  $data.= "</contact>\r\n</contacts>";

  // Add contact
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/$client_folder_id/contacts/");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract contactId from response
  $contact_id = "";
  if (($pos=strpos($buf,"<contactId>"))!==false)
  {
    $contact_id = substr($buf, $pos+strlen("<contactId>"));
    if (($pos=strpos($contact_id,"<"))!==false)
    {
      $contact_id = substr($contact_id,0,$pos);
    }
  }

  // If we have a contact id, we're good
  $result = !empty($contact_id);

  // Set result string
  $result_str = ($result ? "Added new contact $contact_id" : $buf);

  // Return result
  return $result;
}

function IContactUnsubscribe($account_id, $key, $user, $pass, $email, $list_id, &$result_str)
{
  // Get client folder id
  if (!IContactLogin($account_id, $key, $user, $pass, &$client_folder_id))
  {
    $result_str = "Failed retrieving client_folder_id for '$user'";
    return 0;
  }

  // Build iContact authentication
  $headers = array(
  'Accept: text/xml',
  'Content-Type: text/xml',
  'Api-Version: 2.0',
  'Api-AppId: ' . $key,
  'Api-Username: ' . $user,
  'Api-Password: ' . $pass
  );

  // Find contact_id for the given 'email'
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/$client_folder_id/contacts/?email=".URLEncode($email));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract contactId from response
  $contact_id = "";
  if (($pos=strpos($buf,"<contactId>"))!==false)
  {
    $contact_id = substr($buf, $pos+strlen("<contactId>"));
    if (($pos=strpos($contact_id,"<"))!==false)
    {
      $contact_id = substr($contact_id,0,$pos);
    }
  }

  // If we don't have a contactId, can't add subscription
  if (empty($contact_id))
  {
    $result_str = "Failed finding a contact with the email address of '$email'";
    return 0;
  }

  // Build subscription record
  $data = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n<subscriptions>\r\n";
  $data.= "<subscription>\r\n";
  $data.= "<contactId>$contact_id</contactId>\r\n";
  $data.= "<listId>$list_id</listId>\r\n";
  $data.= "<status>unsubscribed</status>\r\n";
  $data.= "</subscription>\r\n</subscriptions>";

  // Add subscription
  $ch=curl_init(ICONTACT_BASE_URL."/icp/a/$account_id/c/$client_folder_id/subscriptions/");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $buf = curl_exec($ch);
  curl_close($ch);

  // Extract subscriptionID from response
  $subscription_id = "";
  if (($pos=strpos($buf,"<subscriptionId>"))!==false)
  {
    $subscription_id = substr($buf, $pos+strlen("<subscriptionId>"));
    if (($pos=strpos($subscription_id,"<"))!==false)
    {
      $subscription_id = substr($subscription_id,0,$pos);
    }
  }

  // If we have a subscription id OR this subscription already unsubscribed, we're good
  $result = !empty($subscription_id) || strpos($buf,"could not be updated")!==false;

  // Set result string
  $result_str = ($result ? "Updated subscription $subscription_id" : $buf);

  // Return result
  return $result;
} 
?>