<div id="page_container">
	<div id="logo"></div>
	<div id="bar_top"></div>

	<table border="0" cellpadding="0" cellspacing="0" id="main_layout">
	<tr>
		<td valign="top" class="outer small"></td>
		<td valign="top" class="inner">
			<?=ContentDiv('left_content')?>
		</td>
		<td valign="top" class="outer large">
			<?=ContentDiv('top_content')?>
			<?=ContentDiv('upper_content')?>
			<?=ContentDiv('upper_content_right')?>
			<div class="clear"></div>
			
			<table border="0" cellpadding="0" cellspacing="0" id="bottom_boxes">
			<tr>
				<td valign="top" class="a"><?=ContentDiv('box1')?><div class="shadow"></div></td>
				<td valign="top" class="b"><?=ContentDiv('box2')?><div class="shadow"></div></td>
				<td valign="top" class="a"><?=ContentDiv('box3')?><div class="shadow"></div></td>
			</tr>
			</table>
			
			<?=ContentDiv('lower_content')?>
		</td>
	</tr>
	</table>

	<div id="bar_bottom"></div>
</div>