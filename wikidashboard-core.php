<?php

class WikiDashboard {

	function CheckForInstall() 
	{
		global $wpdb;
		$installed_version = get_option($OPTION_VERSION);
		if ($installed_version == '') 
			$installed_version = 0;

		if ($installed_version < 1)
		{
				$q = <<<SQL
					CREATE TABLE  `wordpress`.`wp_wiki_dashboard_content` (
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

		if ($installed_version != $current_version) {
			update_option($OPTION_VERSION, $current_version);
		}
	}

	function AddRevision($userID, $postID)
	{
		
	}

}

?>
