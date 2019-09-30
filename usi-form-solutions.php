<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

/* 
Plugin Name: Form-Solutions
Plugin URI:  https://github.com/jaschwanda/form-solutions
Description: The Form-Solutions plugin extends WordPress enabling the creation and management of forms. The Form-Solutions plugin is developed and maintained by Universal Solutions.
Version:     1.1.1 (2019-09-30)
Author:      Jim Schwanda
Author URI:  https://www.usi2solve.com/leader
Text Domain: usi-form-solutions
*/

/*
The Form-Solutions plugin extends WordPress enabling the creation and management of forms. The Form-Solutions plugin is developed and maintained by Universal Solutions.
Copyright (C) 2018 Jim Schwanda

Form-Solutions is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Form-Solutions is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty 
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Form-Solutions.  If not, see 
<http://www.gnu.org/licenses/>.
*/

require_once('usi-form-solutions-form.php'); 

class USI_Form_Solutions {

   const VERSION = '1.1.1 (2019-09-30)';
   const NAME = 'Form-Solutions';
   const PREFIX = 'usi-form';
   const TEXTDOMAIN = 'usi-form-solutions';

   public static $capabilities = array(
      'View-Settings' => 'View settings',
      'Edit-Preferences' => 'Edit preferences',
   );

   public static $options = array();

   function __construct() {
      if (empty(USI_Form_Solutions::$options)) {
         $defaults['preferences']['file-location'] = 'plugin';
         $defaults['preferences']['shortcode-prefix'] = 'form';
         USI_Form_Solutions::$options = get_option(self::PREFIX . '-options', $defaults);
      }
      $shortcode_prefix   = USI_Form_Solutions::$options['preferences']['shortcode-prefix'];
      add_shortcode($shortcode_prefix, array($this, 'shortcode'));
   } // __construct();

   static function get_forms_folder() {
      if (!empty(USI_Form_Solutions::$options['preferences']['file-location'])) {
         return(USI_Form_Solutions::$options['preferences']['file-location']);
      }
      return('plugin');
   } // get_forms_folder();

   function shortcode($attributes, $content = null) {

     if (empty($attributes['class'])) return('form class is missing');

      $class = $attributes['class'];
      $file  = 'forms/' . $class . '.php';
      $class = str_replace('-', '_', $class);

      switch ($location = USI_Form_Solutions::get_forms_folder()) {
      default: 
      case 'plugin': $path = plugin_dir_path(__FILE__) . $file; break;
      case 'root'  : $path = ABSPATH . $file; break;
      case 'theme' : $path = get_theme_root() . '/' . $file; break;
      }

      if (file_exists($path)) {
         @ include_once($path);
      } else {
         return('Cannot find file <i>"' . $path . '"</i>');
      }

      $form = new $class();

      if (!empty($attributes['dump'])) usi_log(__METHOD__ . ':class=' . print_r($form, true));

      return($form->process());

   } // shortcode();

} // Class USI_Form_Solutions;
   
new USI_Form_Solutions();

if (is_admin() && !defined('WP_UNINSTALL_PLUGIN')) {
   require_once('usi-form-solutions-admin.php');
   require_once('usi-form-solutions-install.php');
   if (is_dir(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions')) {
      require_once('usi-form-solutions-settings.php'); 
   } else {
      add_action('admin_notices', array('USI_Form_Solutions', 'action_admin_notices'));
   }
}
// --------------------------------------------------------------------------------------------------------------------------- // ?>
