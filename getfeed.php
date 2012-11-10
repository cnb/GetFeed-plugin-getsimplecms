<?php
/****************************************************
*
name: GetFeed
version: 0.2 beta
file name:	getfeed.php
description: Plugin for GetSimple CMS, with several functions to display feed contents
license: GPL
author: Carlos Navarro
uri: http://webs.org.es/getfeed/

note: needs MagpieRSS libraries - http://magpierss.sourceforge.net/

*
*****************************************************/

$thisfile=basename(__FILE__, ".php");
register_plugin(
	$thisfile,
	'GS GetFeed',
	'0.2 beta',
	'Carlos Navarro',
	'http://webs.org.es/getfeed/',
	'Simple aggregator - display RSS feeds'
);

define('GETFEED_DATE_FORMAT', 'M jS, Y'); // default date format

define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_INPUT_ENCODING', 'UTF-8');

define('MAGPIE_CACHE_DIR', GSDATAPATH.'/getfeedcache'); // where to store MagpieRSS cache files
require_once GSPLUGINPATH.'/getfeed/magpierss/rss_fetch.inc';

/* sample function to display a list titles linking to source post */
function getfeed_list_titles($feedurl,$numposts=0) {
	echo '<ul>';
	getfeed_output($feedurl,'<li><a href="{{link}}">{{title}}</a></li>',$numposts);
	echo '</ul>';
}

/* sample function, displays full posts */
function getfeed_echo_posts($feedurl,$numposts=0) {
	getfeed_output($feedurl,'<div id="post-{{pid}}" class="post-wrapper"><h2 class="post-title"><a href="{{link}}">{{title}}</a></h2><span class="post-date">{{date}}</span><p class="post-body">{{description}}</p></div>',$numposts);
}

/* customize feed output */
/* give it a html string with keys {{title}}, {{link}}, {{description}}, {{date}}, {{pid}} */
function getfeed_output($feedurl,$html,$MAXPOSTS=0) {
	$rss = fetch_rss($feedurl);
	$num = count($rss->items);
	if ($MAXPOSTS!=0) {
		if ($num > $MAXPOSTS) {
			$num=$MAXPOSTS;
		}
	}
	for ($i=0; $i<$num; $i++) {
		$item = $rss->items[$i];
		$title = $item['title'];
		$link = $item['link'];
		$desc = $item['description'];
		if (!$desc) {
			$desc = $item['content'];
		}
		$date = $item['date_timestamp'];
		if (!$date) {		
			$date = strtotime($item['dc']['date']);
			if (!$date) {
				$date = strtotime($item['published']);
			}
		}
		if ($date) {
			$date = date(GETFEED_DATE_FORMAT, $date); 
		}
		echo str_replace('{{date}}',$date,
			 str_replace('{{pid}}',$i+1,
			 str_replace('{{description}}',$desc, 
			 str_replace('{{link}}',$link,
			 str_replace('{{title}}',$title,
			 $html)))));
	}
}

?>