<?php
/*
 * Plugin Name: Zen Menu Logic
 * Plugin Uri: http://www.zenofwp.com/zen-plugins/zen-menu-logic/
 * Version: v1.1
 * Author: <a href="http://www.zenofwp.com">Greg Turner</a>
 * Description: Allows user to denote on any page which of many custom menus is to be used as the primary menu.
 * License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

global $wp_version;

if (version_compare ($wp_version, "3.3.1", "<")) {
	exit ('Zen Menu Logic Plugin requires WordPress 3.3.1 or newer.');
}

if (!class_exists ('ZenOfWPMenuLogic')) {
	class ZenOfWPMenuLogic {
	
		protected $primaryMenu;
		
		function ZenOfWPMenuLogic () {
			if ($this->is_menulogic_supported())
				if (is_admin ()) {
					add_action ('save_post', array (&$this, 'save_menulogic'));
					add_action ('add_meta_boxes', array (&$this, 'register_menulogic_metabox'));
				}
				else
					add_filter ('wp_nav_menu_args', array (&$this, 'menulogic'));
		}
		
		function is_menulogic_supported () {	
			// do we have a menu with primary location?
			$locations = get_theme_mod('nav_menu_locations');
			if (empty ($locations)) 		
				return false;
			if (!isset ($locations['primary'])) 	
				return false;
			if ($locations['primary'] == '') 			
				return false;
			if ($locations['primary'] == 0) 			
				return false;
			
			// save the menu id that is in the primary location
			$this->primaryMenu = $locations['primary'];	
			
			// now make sure there is at least one menu, that
			// has at least one menu itme, whose id is the
			// same as that of the primary location
			$nav_menus = wp_get_nav_menus( array('orderby' => 'name') );
			if (empty ($nav_menus))
				return false;
			foreach ($nav_menus as $menu) {
				if (isset ($menu->count))
					if (0 < $menu->count)
						if ($menu->term_id == $this->primaryMenu)
							return true;
			}
			return false;			
		}
		
		// return list of menu names and ids that have at least one
		// menu item
		function getMenuList () {
			$i = 0;
			$nav_menus = wp_get_nav_menus( array('orderby' => 'name') );
			foreach ($nav_menus as $menu) 
				if (isset ($menu->count))
					if (0 < $menu->count) {
						$x['id'] = $menu->term_id;
						$x['name'] = $menu->name;
						$retarray[$i] = $x;
						$i++;
					}
			return $retarray;
		}
		
		// this function is called when user clicks update of a page or post
		// this saves the menu choice for this page
		function save_menulogic ($post_id) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return;
			if ( !wp_verify_nonce( $_POST['zenofwp_menulogic_noncename'], plugin_basename( __FILE__ ) ) )
				return;
			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			}
			else {
				if ( !current_user_can( 'edit_post', $post_id ) )
					return;
			}		
			if (!isset ($_POST['zenofwp_menulogic_menuselect'])) 
				return;
			$menuId = intval ($_POST['zenofwp_menulogic_menuselect']);
			if ($menuId <= 0)
				return;	  
			update_post_meta ($post_id,'zenofwp_menulogic_menuselect', $menuId);
		}		
		
		// get the menu choice for this page.  first see if it has been set
		// and if so, does it match one of the menu id.  If so, return that,
		// otherwise return the primary menu.
		function getPageMenu ($id,$menus) {
			$myMenu = get_post_meta ($id, 'zenofwp_menulogic_menuselect', true);
			if (!empty ($myMenu))
				foreach ($menus as $menu) {
					if ($menu['id'] == $myMenu)
						return $myMenu;				
				}
			return $this->primaryMenu;
		}

		// displays the meta box
		function menulogic_meta_box ($post) {
			wp_nonce_field (plugin_basename ( __FILE__ ), 'zenofwp_menulogic_noncename');
		  
			$menus = $this->getMenuList();
			$defaultMenu = $this->getPageMenu ($post->ID,$menus);

			echo '<label for="zenofwp_menulogic_menuselect">';
			_e('Select Custom Menu', 'zenofwp_menulogic_textdomain' );
			echo '</label> ';
		  
			echo '<ul id="zenofwp_menulogic">';

			foreach ($menus as $theMenu) {
				if ($theMenu['id'] == $defaultMenu)
					echo '<li><input type="radio" name="zenofwp_menulogic_menuselect" id="zenofwp_menulogic_menuselect" value="'.$theMenu['id'].'" checked/><label for="">'.$theMenu['name'].'</label></li>';
				else
					echo '<li><input type="radio" name="zenofwp_menulogic_menuselect" id="zenofwp_menulogic_menuselect" value="'.$theMenu['id'].'"/><label for="">'.$theMenu['name'].'</label></li>';
			}		  
			echo '</ul>';
		}
		
		// register meta box for pages, posts, and all custom post types
		function register_menulogic_metabox() {
			add_meta_box( 
				'zenofwp_menulogic_id',
				__( 'Zen Of WP Menu Logic', 'zenofwp_menulogic_textdomain'),
				array (&$this, 'menulogic_meta_box'),
				'post' 
			);
			add_meta_box(
				'zenofwp_menulogic_id',
				__('Zen Of WP Menu Logic', 'zenofwp_menulogic_textdomain'), 
				array (&$this, 'menulogic_meta_box'),
				'page'
			);
	
			$post_types=get_post_types(array ('_builtin' => false));
			foreach ($post_types  as $post_type ) {
				add_meta_box(
					'zenofwp_menulogic_id',
					__('Zen Of WP Menu Logic', 'zenofwp_menulogic_textdomain'), 
					array (&$this, 'menulogic_meta_box'),
					$post_type->name
				);
			}

		}		

		// filter function called when the navigation menu resolves.
		// if the page's menu choice is set, and if we are doing the
		// primary menu, then chnage the args, so as to make the page's
		// menu be the one to display.
		function menulogic ($args) {
			global $post;
			$id = $post->ID;		
			$menuId = get_post_meta ($id, 'zenofwp_menulogic_menuselect', true);
			if (isset ($menuId))
				if (0 < $menuId)
					if (!empty ($args))
						if (isset ($args['theme_location']))
							if ($args['theme_location'] == 'primary') {
								$args['theme_location'] = '';
								$args['menu'] = $menuId;
							}
			return $args;	
		}
	
	}  // end of class ZenOfWPMenuLogic
}

if (class_exists ('ZenOfWPMenuLogic')) 
	$zenOfWPMenuLogic = new ZenOfWPMenuLogic();


?>