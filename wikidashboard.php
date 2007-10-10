<?php
/*
Plugin Name: Wiki Dashboard
Plugin URI: http://www.coders4fun.com/wiki-dashboard
Description: Mini-Wiki on the wordpress dashboard, for multiple autors collaboration.
Version: 0.1
Author: Dzamir
Author URI: http://www.coders4fun.com/dzamir

    Copyright 2007  Dzamir  (email : dzamir@coders4fun.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


load_plugin_textdomain("wiki-dashboard", "wp-content/plugins/wiki-dashboard");
// Hook for adding admin menus
add_action('admin_menu', 'mt_add_pages');

// action function for above hook
function mt_add_pages() {
    // Add a submenu to the custom top-level menu:
    add_submenu_page('index.php', 'Wiki', 'Wiki', 1, 'wiki', 'mt_wiki');
}

// wiki function
function mt_wiki() {
	// include the file with the wiki source code	
	include("mini-wiki.php");
}


?>
