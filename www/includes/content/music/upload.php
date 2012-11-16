<?php
$storage = 'upload/';

$file = basename($_FILES['Filedata']['name']);

$extension = strtolower(array_pop(explode('.', $file)));

/*
if ($extension == 'jpg')
{
	require_once('../media/functions.php');
	@make_new_image($_FILES['Filedata']['tmp_name'], $storage . $file, 95, 75);
}
else
{*/
	if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $storage . $file ))
	{
		echo "File uploaded successfully.";
	}
	else
	{
		echo "An error occured.";
	}
//}
?>
