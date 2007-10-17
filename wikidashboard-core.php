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

	function Uninstall()
	{
		global $wpdb;
		$q = "DROP TABLE wp_wiki_revisions";
		$wpdb->query($q);
		$q = "DROP TABLE wp_wiki_pages";
		$wpdb->query($q);
		$q = "DROP TABLE wp_wiki_dashboard";
		$wpdb->query($q);	
		$q = "DROP TABLE wp_wiki_users_main_page";
		$wpdb->query($q);
	}

	function CheckForInstall() 
	{
		global $wpdb;
		global $current_version;

		// if no version are found, force the version to 0 (not installed)
		if ($this->installed_version == '') 
			$this->installed_version = 0;

		// if the plugin first installation, create the tables
		if ($this->installed_version < 2)
		{
			$this->Uninstall();
			// create table wp_wiki_dashboard
			$q = <<<SQL
				CREATE TABLE  `wordpress`.`wp_wiki_dashboard` (
				  `PageID` bigint(20) unsigned NOT NULL default '0',
				  `RevisionID` bigint(20) NOT NULL,
				  PRIMARY KEY  (`PageID`,`RevisionID`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1
SQL;
			$wpdb->query($q);

			// create table wp_wiki_pages
			$q = <<<SQL
				CREATE TABLE  `wordpress`.`wp_wiki_pages` (
				  `PageID` bigint(20) NOT NULL auto_increment,
				  `UserID` bigint(20) NOT NULL,
				  `Title` varchar(255) NOT NULL,
				  `Creation_Date` datetime NOT NULL,
				  PRIMARY KEY  (`PageID`)
				) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1
SQL;
			$wpdb->query($q);

			// create table wp_wiki_revisions
			$q = <<<SQL
				CREATE TABLE  `wordpress`.`wp_wiki_revisions` (
				  `RevisionID` bigint(20) NOT NULL auto_increment,
				  `PageID` bigint(20) NOT NULL,
				  `UserID` bigint(20) NOT NULL,
				  `Content` text,
				  `Date` datetime NOT NULL,
				  PRIMARY KEY  (`RevisionID`)
				) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1
SQL;
			$wpdb->query($q);

			// create table wp_wiki_users_main_page
			$q = <<<SQL
				CREATE TABLE  `wordpress`.`wp_wiki_users_main_page` (
				  `UserID` bigint(20) NOT NULL,
				  `PageID` bigint(20) NOT NULL,
				  PRIMARY KEY  (`UserID`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1
SQL;

			$wpdb->query($q);

//			add_option($OPTION_VERSION, $version, 'Program version', 'yes');
			// add the main page
			$this->AddPage(true, "HomePage", true);
			// update the version option
			if ($this->installed_version != $current_version) {
				update_option($OPTION_VERSION, $current_version);
			}
		}

	}

	function AddPage($shared_page, $title, $main_page)
	{
		global $wpdb;
		$userID;
		// if it's a page shared to all blog users, userID = 0
		if ($shared_page)
		{
			$userID = 0;
		}
		else
		{
			// else assign the current user id
			global $userdata;
			get_currentuserinfo();
			$userID = $userdata->ID;
		}
		$postDate = current_time('mysql');
		$safe_title = $wpdb->escape($title); 
		$q = "INSERT INTO wp_wiki_pages SET UserID = $userID, Title= '$safe_title', Creation_Date = '$postDate'";
		$wpdb->query($q);		
		$q = "SELECT PageID FROM wp_wiki_pages WHERE UserID = $userID AND Title = '$safe_title' AND Creation_Date = '$postDate'";
		$results = $wpdb->get_results($q);
		foreach ($results as $result)
		{
			$pageID = $result->PageID;
		}
	//	$q = "INSERT INTO wp_wiki_revisions VALUES(0, $pageID, '', '$postDate')";
		//$wpdb->query($q);
		// adding the first (empty) revision
		$this->AddRevision($userID, $pageID, '', $postDate);
		$q = "INSERT INTO wp_wiki_dashboard SET PageID = $pageID, RevisionID = 0";
		$wpdb->query($q);
		if ($main_page)
		{
			// check if a main page for this user already exists
			$q = "SELECT * FROM wp_wiki_users_main_page WHERE UserID = $userID";
			if ($wpdb->query($q) > 0)
			{
				// ERROR!! A main page already exists
				echo "A main page already exists for this user";
			} else
			{
				$q = "INSERT INTO wp_wiki_users_main_page VALUES($userID, $pageID)";
				$wpdb->query($q);
			}
		}
	}


	function AddRevision($userID, $pageID, $content, $postDate)
	{
		global $wpdb;
		$safeContent = $wpdb->escape($content); 
		$q = "INSERT INTO wp_wiki_revisions SET PageID = $pageID, Content = '$safeContent', Date = '$postDate'"; 
		$wpdb->query($q);
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

	function PrintPage($pageID, $showDiv)
	{
		if ($showDiv)
			echo '<div class="wrap">';
		echo "<h2>".$pageID."</h2>";
		if ($showDiv)
			echo '</div>';
	}

	function PrintAllPages()
	{
		echo'<div class="wrap">';
		echo'<h2>';
		_e("Wiki");
		echo'</h2>';
		$pages = $this->GetPages();
		if ($pages)
		{
			foreach ($pages as $page) 
			{
				echo "<a href='".$this->wiki_base_page."&wikipage=".$page->ID."'>".$page->Title."</a>";
			}
		}
		// Print the shared wiki
		$this->PrintPage(1, false);	
		echo '</div>';
	}

	function PrintMainPage()
	{
		require("parsing.inc.php");
		global $wiki_dashboard;
		// show the single page
		if(isset($_GET["wikipage"]))
		{
			$wikipage = $_GET["wikipage"];
			$this->PrintPage($wikipage, true);
		}
		// show all pages
		else
		{
			$this->PrintAllPages();
		}
	}

}

?>
