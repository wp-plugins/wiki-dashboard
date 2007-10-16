<?php

class WikiDashboard 
{
	var $OPTION_VERSION = 'wiki_dashboard_version';
	var $wiki_base_page;
	var $installed_version;

	function WikiDashboard()
	{
		$this->installed_version = get_option($OPTION_VERSION);
		$this->wiki_base_page = $_SERVER['PHP_SELF']."?page=wiki";
	}

	function CheckForInstall() 
	{
		global $wpdb;
		global $current_version;

		// if no version are found, force the version to 0 (not installed)
		if ($this->installed_version == '') 
			$this->installed_version = 0;

		// if the plugin first installation, create the tables
		if ($this->installed_version < 1)
		{
				// create table wp_wiki_dashboard
				$q = <<<SQL
					CREATE TABLE  `wordpress`.`wp_wiki_dashboard` (
					  `PageID` bigint(20) unsigned NOT NULL default '0',
					  `RevisionID` bigint(20) NOT NULL,
					  PRIMARY KEY  (`PageID`,`RevisionID`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1
SQL;
				$wpdb->query($q)
;
				// create table wp_wiki_pages
				$q = <<<SQL
					CREATE TABLE  `wordpress`.`wp_wiki_pages` (
					  `ID` bigint(20) NOT NULL auto_increment,
					  `UserID` bigint(20) NOT NULL,
					  `Title` varchar(255) NOT NULL,
					  `Creation_Date` datetime NOT NULL,
					  PRIMARY KEY  (`ID`,`UserID`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1
SQL;
				$wpdb->query($q);

				// create table wp_wiki_revisions
				$q = <<<SQL
					CREATE TABLE  `wordpress`.`wp_wiki_revisions` (
					  `IDRevision` bigint(20) NOT NULL,
					  `IDPage` bigint(20) NOT NULL,
					  `Content` text,
					  `Date` datetime NOT NULL,
					  PRIMARY KEY  (`IDRevision`,`IDPage`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1
SQL;
				$wpdb->query($q);

	//			add_option($OPTION_VERSION, $version, 'Program version', 'yes');
				// add the main page
				$this->AddPage(TRUE, "HomePage");
		}

		// update the version option
		if ($this->installed_version != $current_version) {
			update_option($OPTION_VERSION, $current_version);
		}
	}

	function AddPage($shared_page, $title)
	{
		global $wpdb;
		$user_id;
		// if it's a page shared to all blog users, user_id = 0
		if ($shared_page)
			$user_id = 0;
		else
		{
			// else assign the current user id
			global $userdata;
			get_currentuserinfo();
			$user_id = $userdata->ID;
		}
		$post_date = current_time('mysql');
		$safe_title = $wpdb->escape($title); 
		$q = "INSERT INTO wp_wiki_pages SET UserID = $user_id, Title= '$safe_title', Creation_Date = '$post_date'";
		$wpdb->query($q);		
//INSERT INTO wp_wiki_pages SET UserID = 0, Title='prova2', Creation_Date= '2007-10-10 10:10:10'
	}

	// returns all pages visible from the current user
	function GetPages()
	{
		global $wpdb;
		global $userdata;
		$results;
		get_currentuserinfo();
		$userid = $userdata->ID;
		$q = "SELECT * FROM wp_wiki_pages WHERE UserID = 0 OR UserID = $userid";
		return $wpdb->get_results($q);
	}

	function GetPage($pageID)
	{
		echo "asd";
	}


	function AddRevision($userID, $pageId, $content)
	{
		$user_id = 0;
		$post_date = current_time('mysql');
	}

	function PrintPage($pageID)
	{
		echo "page ".$pageID;
	}

	function PrintMainPage()
	{
		require("parsing.inc.php");?>
		<div class="wrap">
		<h2><?php _e("Wiki"); ?></h2>		<?php
		global $wiki_dashboard;
		// show the single page
		if(isset($_GET["wikipage"]))
		{
			$wikipage = $_GET["wikipage"];
			$this->PrintPage($wikipage);
		}
		// show all pages
		else
		{
			$pages = $wiki_dashboard->GetPages();
			if ($pages)
			{
				foreach ($pages as $page) 
				{
					echo "<a href='".$this->wiki_base_page."&wikipage=".$page->ID."'>".$page->Title."</a>";
				}
			}
			// Print the shared wiki
			// PrintPage(0);
		}
		?>
		</div><?
	}

}

?>
