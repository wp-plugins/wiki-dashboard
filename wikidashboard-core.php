<?php

$OPTION_VERSION = 'wiki_dashboard_version';


class WikiDashboard 
{
	var $installed_version;

	function WikiDashboard()
	{
		$this->installed_version = get_option($OPTION_VERSION);
		echo $this->installed_version;
	}

	function CheckForInstall() 
	{
		global $wpdb;
		global $current_version;

		// if no version are found, force the version to 0 (not installed)
		if ($installed_version == '') 
			$installed_version = 0;

		// if the plugin first installation, create the tables
		if ($installed_version < 1)
		{
				$q = <<<SQL
					CREATE TABLE  wp_wiki_dashboard_content (
					  `UserID` bigint(20) NOT NULL COMMENT '0 for public wiki',
					  `PostID` int(10) unsigned NOT NULL default '0',
					  `RevisionID` int(11) NOT NULL,
					  `Title` text NOT NULL,
					  `Content` longtext,
					  `ModifiedUserID` bigint(20) NOT NULL COMMENT 'ID Of the user that modified this revision',
					  `Date` datetime NOT NULL,
					  PRIMARY KEY  (`UserID`,`PostID`,`RevisionID`)
					) TYPE=MyISAM;
SQL;
				$wpdb->query($q);

	//			add_option($OPTION_VERSION, $version, 'Program version', 'yes');
		}

		// update the version option
		if ($installed_version != $current_version) {
			update_option($OPTION_VERSION, $current_version);
		}
	}

	function AddRevision($userID, $postID)
	{
		
	}

}

?>
