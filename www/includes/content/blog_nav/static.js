
function Blog_ShowLink(obj)
{
	var index = obj.selectedIndex;
	if (index != 0)
	{
		window.location = obj.options[index].value;
	}
}