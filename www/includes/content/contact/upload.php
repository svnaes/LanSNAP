<?php
$component_id = $_GET['component_id'];
$storage = 'storage/' . $component_id . '/';

if (file_exists($storage))
{
	// see if there are any files in this folder, if so, delete
	if (is_dir($storage))
	{
		if ($dh = opendir($storage))
		{
			while (($file = readdir($dh)) !== false)
			{
				$full_file = $storage . $file;
				if (filetype($full_file) == 'file')
				{
					unlink($full_file);
				}
			}
			closedir($dh);
		}
	}
}
else
{
	mkdir($storage);
	chmod($storage, 0777);
}


$file = basename($_FILES['Filedata']['name']);
if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $storage . $file ))
{
	echo "File uploaded successfully.";
}
else
{
	echo "An error occured.";
}
?>