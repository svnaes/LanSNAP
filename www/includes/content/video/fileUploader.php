<?php
$storage = 'upload/';

$file = strtolower(basename($_FILES['Filedata']['name']));
if ( move_uploaded_file( $_FILES['Filedata']['tmp_name'] , $storage.''.$file ) ) {
	echo "File uploaded successfully.";
}
else{
	echo "An error occured.";
}
?>