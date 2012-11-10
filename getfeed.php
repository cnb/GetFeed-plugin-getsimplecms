<?php
/****************************************************
*
name: GetFeed
version: 0.4.1 beta
file name:	getfeed.php
description: Plugin for GetSimple CMS, with several functions to display feed contents
license: GPL
author: Carlos Navarro
uri: http://www.cyberiada.org/cnb/getsimple-plugin-getfeed/

IMPORTANT:
This plugin requires MagpieRSS libraries. Download at http://magpierss.sourceforge.net/
Create a folder called 'magpierss' inside the 'plugins/getfeed' folder, and upload MagpieRSS files there.

*
*****************************************************/

# customization: edit next values:
#
define('GETFEED_DATE_FORMAT', 'M jS, Y'); // default date format
define('MAGPIE_CACHE_DIR', GSDATAOTHERPATH.'/getfeedcache'); // where to store MagpieRSS cache files
#
# do not edit past here (not recommended)

$thisfile=basename(__FILE__, ".php");
register_plugin(
	$thisfile,
	'GetFeed - external RSS reader',
	'0.4 beta',
	'Carlos Navarro',
	'http://www.cyberiada.org/cnb/',
	'Fetch/Read and display RSS/Atom feeds'
);

define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_INPUT_ENCODING', 'UTF-8');

if (file_exists(GSPLUGINPATH.'/getfeed/magpierss/rss_fetch.inc')) {
	require_once GSPLUGINPATH.'/getfeed/magpierss/rss_fetch.inc';
}

/* sample function to display a list of posts titles */
function getfeed_list_titles($feedurl,$numposts=0) {
	echo '<ul>',"\n";
	getfeed_output($feedurl, '<li><a href="{{link}}">{{title}}</a></li>'."\n", $numposts);
	echo '</ul>',"\n";
}

/* sample function, displays full posts with some classes */
function getfeed_echo_posts($feedurl,$numposts=0,$excerptlength=-1) {
	getfeed_output($feedurl, '
	<div id="post-{{pid}}" class="post-wrapper">
		<h2 class="post-title"><a href="{{link}}">{{title}}</a></h2>
		<span class="post-date">{{date}}</span>
		<p class="post-body">{{content}}</p>
	</div>
	'."\n", $numposts, $excerptlength);
}

/* getfeed_output: customize feed output 
 parameters:
	- feed URL
	- html string with keys/tags: {{title}}, {{link}}, {{description}} or {{content}}, {{date}}, {{pid}}
	- maximum number of posts (default or 0 = all)
	- maximum length of content/excerpt (also: -1 = full raw post, 0 = full filtered post)
*/
function getfeed_output($feedurl,$html,$MAXPOSTS=0,$excerptlength=-1) {
	if (!function_exists('fetch_rss')) {
		echo 'GetFeed error: MagpieRSS missing!';
	} else {
		$rss = fetch_rss($feedurl);
		$num = count($rss->items);
		if ($MAXPOSTS!=0) {
			if ($num > $MAXPOSTS) {
				$num=$MAXPOSTS;
			}
		}
		for ($i=0; $i<$num; $i++) {
			$item = $rss->items[$i];
			$title = strip_tags(str_replace('<','&lt;',@$item['title']));
			$link = @$item['link'];
			$desc = @$item['content'];
			if (!$desc) {
				$desc = @$item['description'];
				if (!$desc) {
					$desc = @$item['summary'];
					if (!$desc) {
						$desc = '';
					}
				}
			}
			if ($excerptlength > -1) {
				$desc = strip_tags($desc);
				if ($excerptlength > 0) {
					if ($excerptlength < strlen($desc)) {
						if (function_exists('mb_substr')) { 
							$desc = trim(mb_substr($desc, 0, $excerptlength)) . '...';
						} else {
							$desc = trim(substr($desc, 0, $excerptlength)) . '...';
						}
					}
				}
			}
			$date = @$item['date_timestamp'];
			if (!$date) {		
				$date = strtotime(@$item['dc']['date']);
				if (!$date) {
					$date = strtotime(@$item['published']);
					if (!$date) {
						$date = strtotime(@$item['pubDate']);
						if (!$date) {
							$date = strtotime(@$item['updated']);
						}
					}
				}
			}
			if ($date) {
				$date = date(GETFEED_DATE_FORMAT, $date); 
			} else {
				$date = '';
			}
			echo str_replace('{{date}}',$date,
				 str_replace('{{pid}}',$i+1,
				 str_replace('{{description}}', $desc,
				 str_replace('{{content}}',$desc, 
				 str_replace('{{link}}',$link,
				 str_replace('{{title}}', $title,
				 $html))))));
		}
	}
}
?>