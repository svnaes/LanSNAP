<?php
function CC_SubmitAddress($email, $first_name, $last_name, $list_name, $username, $password)
{
	$post_url      = 'http://api.constantcontact.com/0.1/API_AddSiteVisitor.jsp';
	$values = array();
	
	array_push($values, "loginName=$username");
	array_push($values, "loginPassword=$password");
	array_push($values, "ea=$email");
	array_push($values, "ic=$list_name");
	array_push($values, "First_Name=$first_name");
	array_push($values, "Last_Name=$last_name");

	$curl_post_line = implode('&', $values);
	
    $ch = curl_init();    // initialize curl handle
    curl_setopt($ch, CURLOPT_URL,$post_url); // set url to post to
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
    curl_setopt($ch, CURLOPT_POST, 1); // set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_line); // add POST fields
    $cc_result = curl_exec($ch); // run the whole process
	$info = curl_getinfo($ch);
    curl_close($ch); 

	// close cURL resource, and free up system resources
}
?>