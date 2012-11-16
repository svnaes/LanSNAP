<?php
/* upload.class.php
 *
 * for external uploading to pico
 * 
 */
 
class Uploader
{
	var $upload_path;       // path to upload handler
	var $file_callback;     // javascript callback for single file
	var $complete_callback; // javascript callback for all upload completed
	var $filetypes;         // files allowed in upload path
	var $filetypetext;      // what to call the uploaded file types
	var $text_color;        // color of the stage text
	var $bg_color;          // color of the stage text
	
	function Uploader($upload_path, $file_callback = '', $complete_callback = '', $filetypes = '', $filetypetext = 'All Files', $text_color = 'ffffff', $bg_color = '000000')
	{
		$this->upload_path       = $upload_path;
		$this->filetypes         = $filetypes;
		$this->filetypetext      = $filetypetext;
		$this->file_callback     = $file_callback;
		$this->complete_callback = $complete_callback;
		$this->text_color        = '0x' . $text_color;
		$this->bg_color          = '#' . $bg_color;
	}
	
	function Output()
	{
		$swf_path = PICO_BASEPATH . 'includes/uploader/Uploader.swf';
		
		$upload_path       = $this->upload_path;
		$filetypes         = $this->filetypes;
		$filetypetext      = $this->filetypetext;
		$file_callback     = $this->file_callback;
		$complete_callback = $this->complete_callback;
		$text_color        = $this->text_color;
		$bg_color          = $this->bg_color;
		
		
		$extensions = explode(',', $filetypes);
		$good_extensions = array();
		if ( (sizeof($extensions) > 0) and (strlen($filetypes) > 0) )
		{
			foreach ($extensions as $e)
			{
				list ($foo, $ext) = explode('.', $e);
				$ext = trim(strtolower($ext));
				$good_extensions[] = '*.' . $ext;
			}
		}
		else
		{
			$good_extensions[] = '*.*';
		}
		$ext_text = implode(';', $good_extensions);
		
		$flashvars = array();
		$flashvars['uploadURL']         = $upload_path;
		$flashvars['text_color']        = $text_color;
		$flashvars['allowed_types']     = $filetypetext . '|' . $ext_text;
		$flashvars['complete_callback'] = $complete_callback;
		$flashvars['file_callback']     = $file_callback;
		
		$entries = array();
		foreach ($flashvars as $key=>$val)
		{
			$entries[] = $key . '=' . $val;
		}
		
		$fv_text = implode('&', $entries);
		
		$bgfv = ($bg_color == '#trans') ? 'wmode' : 'bgcolor';
		$bgcv = ($bg_color == '#trans') ? 'transparent' : $bg_color;

		
		$html = <<<HTML
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="290" height="38" id="Uploader" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="$swf_path" />
	<param name="quality" value="high" />
	<param name="$bgfv" value="$bgcv" />
	<param name="flashvars" value="$fv_text" />
	<embed src="$swf_path" flashvars="$fv_text" quality="high" $bgfv="$bgcv" width="290" height="38" name="Uploader" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
HTML;
		return $html;
	}
}