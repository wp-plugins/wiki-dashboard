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
		if ($this->installed_version < 1)
		{
			$this->Uninstall();
			// create table wp_wiki_dashboard
			$q = <<<SQL
				CREATE TABLE  `wordpress`.`wp_wiki_dashboard` (
				  `PageID` bigint(20) unsigned NOT NULL default '0',
				  `RevisionID` bigint(20) NOT NULL,
				  PRIMARY KEY  (`PageID`)
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

	// returns the current user, or the id of the shared user if shareduser is true 
	function GetUser($sharedUser)
	{
		if ($sharedUser)
		{
			// if it's a page shared to all blog users, userID = 0
			return 0;
		}
		else
		{
			// else assign the current user id
			global $userdata;
			get_currentuserinfo();
			return $userdata->ID;
		}
	}

	function AddPage($shared_page, $title, $main_page)
	{
		global $wpdb;
		$userID = $this->GetUser($shared_page);
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
		// adding the first (empty) revision
		$this->AddRevision($userID, $pageID, '', $postDate);
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
		$q = "SELECT RevisionID FROM wp_wiki_revisions WHERE PageID = $pageID AND Content = '$safeContent' AND Date = '$postDate'"; 
		$results = $wpdb->get_results($q);
		$revisionID = $results[0]->RevisionID;
		$q = "INSERT INTO wp_wiki_dashboard SET PageID = $pageID, RevisionID = $revisionID";
		$wpdb->query($q);
	}

	// returns all pages visible from the current user
	function GetPages()
	{
		global $wpdb;
		global $userdata;
		get_currentuserinfo();
		$userID = $userdata->ID;
		$q = "SELECT * FROM wp_wiki_pages WHERE UserID = 0 OR UserID = $userID";
		return $wpdb->get_results($q);
	}

	// Returns the text of the last revision or false if no page exists with the specified ID
	function GetLastRevisionText($pageID)
	{
		global $wpdb;
		$q = "SELECT RevisionID FROM wp_wiki_dashboard WHERE PageID = $pageID";
		$results = $wpdb->get_results($q);
		if (!$results)
			return FALSE;
		$revisionID = $results[0]->RevisionID;
		$q = "SELECT Content FROM wp_wiki_revisions where RevisionID = $revisionID";
		$results = $wpdb->get_results($q);
		if (!$results)
			return FALSE;
		return $results[0]->Content;
	}

	// returns true if the current user can access the page
	function CheckAccessToPage($pageID)
	{
		global $wpdb;
		global $userdata;
		get_currentuserinfo();
		$userID = $userdata->ID;
		$q = "SELECT UserID FROM wp_wiki_pages WHERE PageID = $pageID AND (UserID = 0 OR UserID = $userID)";
		if ($wpdb->query($q) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	// returns the pageID that is the mainpage for the user, if the page doesn't exists the function will create it
	function GetMainPage($sharedPage)
	{
		global $wpdb;
		$userID = $this->GetUser($sharedPage);
		$q = "SELECT PageID FROM wp_wiki_users_main_page where UserID = $userID";
		$results = $wpdb->get_results($q);
		if ($results)
		{
			return $results[0]->PageID;
		} else
		{
			// add the main page for the user
			$this->AddPage($shared_page, "HomePage", true);
			// call this function recorsive
			return $this->GetMainPage($sharedPage);
			// TODO: if addpage fails, this function will recurse infinite times
		}
	}

	function PrintPageNotFoundError()
	{
		echo "<h2>";
		_e("You don't have access to this page, or the page doesn't exists!"); 
		echo "</h2>";
	}

	function PrintPage($pageID, $showDiv)
	{
		// check if the current user can access this page
		if (!$this->CheckAccessToPage($pageID))
		{
			$this->PrintPageNotFoundError();
			return;
		}
		$content = $this->GetLastRevisionText($pageID);
		if ($content === false)
		{
			$this->PrintPageNotFoundError();
			return;
		}
		if ($showDiv)
			echo '<div class="wrap">';
		echo "<h2>".$pageID."</h2>";
		echo $content;
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
				echo "<a href='".$this->wiki_base_page."&wikipage=".$page->PageID."'>".$page->Title."</a>";
			}
		}
		// Print the shared wiki
		$this->PrintPage($this->GetMainPage(true), false);	
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
