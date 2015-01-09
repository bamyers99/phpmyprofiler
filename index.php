<?php
/* phpMyProfiler
 * Copyright (C) 2004 by Tim Reckmann [www.reckmann.org] & Powerplant [www.powerplant.de]
 * Copyright (C) 2005-2015 The phpMyProfiler project
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
*/

#$start = microtime(true);

define('_PMP_REL_PATH', '.');

$pmp_module = 'index';
$pmp_db = null;

if (file_exists('installation/')) {
	header("Location:installation/");
	exit();
}
else {
	require_once('config.inc.php');
	require_once('include/functions.php');
	require_once('include/pmp_Smarty.class.php');
	require_once('include/smallDVD.class.php');
	require_once('custom_media.inc.php');

	dbconnect_pdo();

	$pmp_collections = get_collections();

	// Valid pages
	$valildpages = [
		'contact'			=> 'contact.php',
		'coverlist' 		=> 'coverlist.php',
		'credits'			=> 'credits.php',
		'filmprofile'		=> 'filmprofile.php',
		'guestbook' 		=> 'guestbook.php',
		'news'				=> 'news.php',
		'peoplelist'		=> 'peoplelist.php',
		'picturelist'		=> 'picturelist.php',
		'review'			=> 'review.php',
		'search'			=> 'search.php',
		'searchperson'		=> 'searchperson.php',
		'start'				=> 'start.php',
		'statistics'	 	=> 'statistics.php',
		'statisticsdetail'	=> 'statisticsdetail.php',
		'watched'			=> 'watched.php'
	];

	// Try to turn on page compression
	if ($pmp_compression && extension_loaded('zlib')) {
		if (!ob_start("ob_gzhandler")) {
			 ob_start();
		}
	}
	else {
		ob_start();
	}

	// Sent header
	header('Content-type: text/html; charset=utf-8');
	header($ExpStr);

	if (!isset($_GET['ajax_call']))  {
		$smarty = new pmp_Smarty;
		$smarty->loadFilter('output', 'trimwhitespace');
		$smarty->assign('getLangs', getLangs());
		$smarty->display('header.tpl');

		// Show menue
		include('menue.php');
	}

	// Sanitize/filter all values we can get
	$lang_id = (string)filter_input(INPUT_GET, 'lang_id');
	$p_letter = (string)filter_input(INPUT_GET, 'pletter');

	$content_page = (string)filter_input(INPUT_GET, 'content');
	$id = (string)filter_input(INPUT_GET, 'id');
	$page = (int)filter_input(INPUT_GET, 'page');

	// Get and save needed variables for detail-pages
	if (!empty($content_page)) {
		// Validate content page
		if (isset($valildpages[$content_page])) {
			$content_page = $valildpages[$content_page];
		}
		else {
			$content_page = 'start.php';
		}

		// Save all possible vars of content into session
		if (!empty($id)) {
			$_SESSION['id'] = $id;
		}
		else {
			unset($_SESSION['id']);
		}
		if (!empty($page)) {
			$_SESSION['page'] = $page;
		}
		else {
			unset($_SESSION['page']);
		}
		if (isset($_GET['letter'])) {
			$_SESSION['letter'] = $_GET['letter'];
		}
		else {
			unset($_SESSION['letter']);
		}
		if (isset($_GET['name'])) {
			$_SESSION['name'] = $_GET['name'];
		}
		else {
			unset($_SESSION['name']);
		}
	}
	else {
		// We already have content set, e.g. we added filter to dvd-list
		if (!empty($_SESSION['content'])) {
			// Writeback values
			if (isset($_SESSION['id'])) {
				$id = $_SESSION['id'];
			}
			if (isset($_SESSION['page'])) {
				$page = (int)$_SESSION['page'];
			}
			if (isset($_SESSION['letter'])) {
				$_GET['letter'] = $_SESSION['letter'];
			}
			if (isset($_SESSION['name'])) {
				$_GET['name'] = $_SESSION['name'];
			}

			$content_page = $_SESSION['content'];
		}
		// We start from the beginning
		else {
			$content_page = 'start.php';
		}
	}

	// Show selected page
	include($content_page);

	// Save content for next time
	$_SESSION['content'] = $content_page;

	// Uncomment for developing/debugging
	#echo "<b>GET data:</b><br />";
	#echo "<pre>";
	#print_r($_GET);
	#echo "</pre>";
	#echo "<b>POST data:</b><br />";
	#echo "<pre>";
	#print_r($_POST);
	#echo "</pre>";
	#echo "<b>SESSION data:</b><br />";
	#echo "<pre>";
	#print_r($_SESSION);
	#echo "</pre>";
	#echo "<b>SERVER data:</b><br />";
	#echo "<pre>";
	#print_r($_SERVER);
	#echo "</pre>";

	// Show footer
	if (!isset($_GET['ajax_call']))  {
		$smarty = new pmp_Smarty;
		$smarty->loadFilter('output', 'trimwhitespace');
		$smarty->display('footer.tpl');
	}

	dbclose_pdo();

	ob_flush();
}

#$time = .microtime(true)-$start;
#echo 'Generated in '.$time.' seconds.';
?>