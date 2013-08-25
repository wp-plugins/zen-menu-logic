<?php
/*
 * Plugin Name: Zen Menu Logic
 * Plugin Uri: http://www.zml.zenofwp.com/
 * Version: v1.4
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
		
		function ZenOfWPMenuLogic () {
			add_action ('init', array ($this, 'initMe'));
		}
		
		function initMe () {
			if ($this->is_menulogic_supported())
				if (is_admin ()) {
					add_action ('admin_menu', array (&$this, 'register_settings_menu'));
					add_action ('admin_init', array (&$this, 'init_settings'));				
				
					if ($this->is_primary_set ()) {
						add_action ('save_post', array (&$this, 'save_menulogic'));
						add_action ('add_meta_boxes', array (&$this, 'register_menulogic_metabox'));
					}

				}
				else {
					if ($this->is_primary_set ())
						add_filter ('wp_nav_menu_args', array (&$this, 'menulogic'));
				}		
		}
		
		function register_settings_menu () {
			add_options_page ('Zen Menu Logic Settings', 'Zen Menu Logic', 'manage_options', __FILE__, array (&$this, 'draw_options_page'));
		}
		
		function init_settings () {
			register_setting ('menu_logic_options', 'menu_logic_options', array (&$this, 'validate_menu_logic_options'));
			add_settings_section ('zen_menu_logic_settings', 'Zen Menu Logic Settings', array (&$this, 'draw_expl'), __FILE__);
			add_settings_field ('zen_menu_logic_settings_name', 'Select which location you want Zen Menu Logic to work on', array (&$this, 'draw_option'), __FILE__, 'zen_menu_logic_settings');
		}
		
		function draw_options_page () {
		?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Zen Menu Logic</h2>
		<form action="options.php" method="post">
		<?php settings_fields ('menu_logic_options'); ?>
		<?php do_settings_sections (__FILE__); ?>
		<input name="Submit" type="submit" value="Save Changes"/>
		</form></div>
		<?php
		}
		
		function draw_expl () {
			echo 'Your theme may have registered multiple menu locations, under different names.<br/>';
			echo 'There is a radio button for each named location.';
		}
		
		function draw_option () {
			$locations = get_theme_mod('nav_menu_locations');
			$keys = array_keys ($locations);
			$options = get_option ('menu_logic_options');
			if (!empty ($options))
				if (isset ($options ['primary_name'])) {
					$name = $options ['primary_name'];
					echo '<ul>';	
					foreach ($keys as $key) {
						if ($key == $name)
							echo '<li><input type="radio" name="menu_logic_options[primary_name]" id="zenofwp_menulogic_optioinselect" value="'.$key.'" checked/><label for="">'.$key.'</label></li>';
						else
							echo '<li><input type="radio" name="menu_logic_options[primary_name]" id="zenofwp_menulogic_optionselect" value="'.$key.'"/><label for="">'.$key.'</label></li>';
					}	
					echo '</ul>';	
					return;
				}
			echo '<ul>';		
			foreach ($keys as $key) {
				echo '<li><input type="radio" name="menu_logic_options[primary_name]" id="zenofwp_menulogic_optionselect" value="'.$key.'"/><label for="">'.$key.'</label></li>';			
			}
			echo '</ul>';
		}
		
		function validate_menu_logic_options ($input) {
			return $input;
		}
		
		function is_primary_set () {
			$options = get_option ('menu_logic_options');
			if (!empty ($options))
				if (isset ($options ['primary_name']))
					return true;
			return false;
		}
		
		function is_menulogic_supported () {	
			// do we have a least one menu location?
			$locations = get_theme_mod('nav_menu_locations');
			if (empty ($locations)) {	
echo 'no locations';			
				return false;
			}
			
			// now make sure there is at least one menu, that
			// has at least one menu item
			$nav_menus = wp_get_nav_menus( array('orderby' => 'name') );
			if (empty ($nav_menus))
				return false;
			foreach ($nav_menus as $menu) {
				if (isset ($menu->count))
					if (0 < $menu->count)
						return true;
			}
			return false;			
		}
			
		// this function is called when user clicks update of a page or post
		// this saves the menu choice for this page
		function save_menulogic ($post_id) {
			if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return;
				
			if (array_key_exists ('post_type', $_POST) == FALSE)
				return;
				
			if (array_key_exists ('zenofwp_menulogic_noncename', $_POST) == FALSE)
				return;
				
			if ('page' == $_POST['post_type']) {
				if (!current_user_can( 'edit_page', $post_id))
					return;
				}
				else {
					if (!current_user_can( 'edit_post', $post_id))
						return;
				}
				
			if ( !wp_verify_nonce( $_POST['zenofwp_menulogic_noncename'], plugin_basename( __FILE__ ) ) )
				return;	
				
			if (!isset ($_POST['zenofwp_menulogic_menuselect'])) 
				return;
			$menuId = intval ($_POST['zenofwp_menulogic_menuselect']);
			if ($menuId <= 0)
				return;	  
			update_post_meta ($post_id,'zenofwp_menulogic_menuselect', $menuId);
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
		
		// get the menu choice for this page.  first see if it has been set
		// and if so, does it match one of the menu id.  If so, return that
		function getPageMenu ($id,$menus) {
			$myMenu = get_post_meta ($id, 'zenofwp_menulogic_menuselect', true);
			if (isset ($myMenu))
				foreach ($menus as $menu) {
					if ($menu['id'] == $myMenu)
						return $myMenu;				
				}
			update_post_meta ($id,'zenofwp_menulogic_menuselect', 0);				
			return 0;
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
			$options = get_option ('menu_logic_options');
			if (!empty ($options))
				if (isset ($options ['primary_name'])) {
					$name = $options ['primary_name'];		
					$menuId = get_post_meta ($id, 'zenofwp_menulogic_menuselect', true);			
					if (isset ($menuId))
						if (0 < $menuId)
							if (!empty ($args))
								if (isset ($args['theme_location']))
									if ($args['theme_location'] == $name) {
										$args['theme_location'] = '';
										$args['menu'] = $menuId;
									}
					}
			return $args;	
		}
	
	}  // end of class ZenOfWPMenuLogic
}

if (class_exists ('ZenOfWPMenuLogic')) 
	$zenOfWPMenuLogic = new ZenOfWPMenuLogic();


?>