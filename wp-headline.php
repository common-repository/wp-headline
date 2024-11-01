<?php
/*
Plugin Name: WP Headline
Author URI: http://coding-bereich.de/
Description: Adds Headline BBCodes to WordPress. Use: [hl=1-5]Headline[/hl] or [h=1-5]Headline[/h] 
Version: 1.0.2
Author: Thomas Heck
Plugin URI: http://coding-bereich.de/2010/03/31/allgemein/wordpress-plugin-wp-headline.html
*/


class WPHeadline
{
	var $_headlines = array();
	
	function WPHeadline()
	{
		add_filter('the_content', array($this, 'onTheContent'));
		add_action('wp_print_styles', array($this, 'onPrintStyles'));
	}
	
	function onPrintStyles()
	{
		$stylePath = '/wp-headline/style.css';
		
		if( !file_exists(WP_PLUGIN_DIR . $stylePath) )
			return;
			
		wp_register_style('wp-headline-style', WP_PLUGIN_URL . $stylePath);
		wp_enqueue_style('wp-headline-style');
	}
	
	function onTheContent($content='')
	{
		if( is_feed() )
			return $content;
			
		$content = preg_replace_callback('/\[h(?:\s*=\s*([1-5])\s*)?\](.*)\[\/h\]/imS', array($this, 'onH'), $content);
		$content = preg_replace_callback('/\[hl(?:\s*=\s*([1-5])\s*)?\](.*)\[\/hl\]/imS', array($this, 'onHL'), $content);
		
		if( count($this->_headlines) > 0 )
		{
			$head = '<ul class="contentlist">';
			foreach($this->_headlines as $name=>$h)
				$head .= '<li class="contentlistlevel' . $h['level'] . '" style="padding-left: ' . (($h['level']-1)*10) . 'px;"><a href="#' . $name . '">' . $h['title'] . '</a></li>';
			$head .='</ul>';
			
			$content = $head . $content;
		}
		
		return $content;
	}
	
	function onH($in)
	{
		$h = 1;
		if( !empty($in[1]) )
			$h = $in[1];
		$title = $in[2];
				
		
		return '<h' . ($h+1) . '>' . $title . '</h' . ($h+1) . '>';
	}
	
	function onHL($in)
	{
		$h = 1;
		if( !empty($in[1]) )
			$h = $in[1];
		$title = $in[2];
		
		$name = preg_replace('/\s+/mS', '_', $title);
		$name = str_replace(array('ö', 'ä', 'ü', 'ß'), array('oe', 'ae', 'ue', 'ss'), $name);
		$name = preg_replace('/[^a-zA-Z0-9_]/umS', '', $name);
		$name = strtolower($name);
		
		for($i=1;;++$i)
		{
			$tmpName = $i===1?$name:$name.'-'.$i;
			if( isset($this->_headlines[$tmpName]) )
				continue;
			$name = $tmpName;
			break;
		}
		$this->_headlines[$name] = array('title'=>$title, 'level'=>$h);
		
		return '<h' . ($h+1) . ' id="' . $name . '" class="contentlistlevel' . $h . '"><a href="#'.  $name .'">' . $title . '</a></h' . ($h+1) . '>';
	}
}

$wpHeadLine = new WPHeadLine();
?>
