<form method="post" action="" onsubmit="SiteSearch(this); return false">
	<table border="0" cellpadding="0" cellspacing="0" class="search" align="center">
	<tr>
		<td><input type="text" class="searchbox" name="search" value="search the site" onfocus="this.value=''" onsubmit="return false" /></td>
		<td><input type="submit" value="Go" /></td>
	</tr>
	</table>
</form>
<script type="text/javascript">
function SiteSearch(obj)
{
	var target_url = url('search/'+urlencode(obj.elements.search.value));
	window.location = target_url;
}
</script>