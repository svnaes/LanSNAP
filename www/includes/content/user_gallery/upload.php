<?php
chdir('../../../');
$storage = 'includes/tmp/';
$file = basename($_FILES['Filedata']['name']);

$ok_extensions = array('jpg', 'jpeg', 'gif', 'png');
$extension = strtolower(array_pop(explode('.', $file)));

if (!in_array($extension, $ok_extensions)) { exit(); }

if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $storage . $file ))
{
	echo "File uploaded successfully.";
}
else
{
	echo "An error occured.";
}
?>