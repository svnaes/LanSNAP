<?php

$data   = $db->result('SELECT `additional_info` FROM `'.DB_COMPONENT_TABLE.'` WHERE `component_id`=?', $component_id);
$options = unserialize($data);
if (!is_array($options)) { $options = array(); }
$twitter_table = DB_PREFIX . 'twitter_data';

if (sizeof($options) == 0)
{
	echo "Please set up this component before using";
	return;
}

$twitter_info = $db->assoc('SELECT * FROM `'.$twitter_table.'` WHERE `component_id`=?', $component_id);
if ( (!is_array($twitter_info)) or ((time() - $twitter_info['last_updated']) > 300) )
{
	if ($options['t_search'] == 'yes')
	{
		$url = 'http://search.twitter.com/search.rss?q='.urlencode($options['t_name']);
		//echo $url;
	}
	else
	{
		$url = 'http://twitter.com/statuses/user_timeline/'.$options['t_name'].'.xml?count=25';
	}
	
	
	$c   = curl_init($url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	$src = curl_exec($c);
	curl_close($c);
	
	if ($options['t_search'] == 'yes')
	{
		preg_match_all('/<title>(.*?)<\/title>/', $src, $data);
		$messages = $data[1];
		array_shift($messages);
		
		//echo '<pre>' . print_r($messages, TRUE) . '</pre>';
		
		preg_match_all('/<link>(.*?)<\/link>/', $src, $data);
		$links = $data[1];
		array_shift($links);
		
		//echo '<pre>' . print_r($links, TRUE) . '</pre>';
		
		$info = array(
			'links' => $links,
			'messages' => $messages
		);
		
		//array_shift($matches);
	}
	else
	{
		preg_match_all('/<text>(.*)<\/text>/', $src, $data);
		$info = $data[1];
	}
	
	if (!is_array($twitter_info))
	{
		$db->run('INSERT INTO `'.$twitter_table.'` (`component_id`, `tweets`, `last_updated`) VALUES (?,?,?)',
			$component_id, serialize($info), time()
		);
	}
	else
	{
		$db->run('UPDATE `'.$twitter_table.'` SET `tweets`=?, `last_updated`=? WHERE `component_id`=?', serialize($info), time(), $component_id);
	}
	
	$tweets = $info;
}
else
{
	$tweets = unserialize($twitter_info['tweets']);
}

echo '<div class="title">'.$options['t_title'].'</div>';

if (sizeof($tweets) == 0)
{
	//$tweets[] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a diam at mauris viverra semper.';
}

//echo '<pre>'.print_r($tweets, true).'</pre>';

if (sizeof($tweets) > 0)
{
	$good = 0;
	$counter = 0;
	
	if ($options['t_search'] == 'yes')
	{
		$links  = $tweets['links'];
		$tweets = $tweets['messages'];
	}
	
	foreach ($tweets as $t)
	{
		$status = html_entity_decode($t);
		$text_only = strip_tags($status);
		
		if (substr($text_only, 0, 1) != '@')
		{
			$status = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\">\\0</a>", $status);
			$status = preg_replace("/@([A-Za-z0-9_]+)/ise", "'<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>'", $status);
			$status = preg_replace("/#([A-Za-z0-9_]+)/ise", "'<a href=\"http://twitter.com/#search?q=\\1\" target=\"_blank\">#\\1</a>'", $status);
			
			if ($options['t_search'] == 'yes')
			{
				$link = $links[$counter];
				# http://twitter.com/ddkurcfeld/statuses/28467336474271744
				preg_match('/twitter\.com\/(.*)\/statuses/', $link, $matches);
				$username = $matches[1];
				$status = '<a href="'.$link.'" target="_blank">'.$username.':</a> ' . $status;
			}
			
			echo '<div class="message">'.$status.'</div>';
			
			$good++;
			if ($options['t_num'] == $good)
			{
				break;
			}
		}
		$counter++;
	}
}

echo '<div class="follow"><a href="http://twitter.com/'.$options['t_name'].'" target="_blank">'.$options['t_text'].'</a></div>';


?>