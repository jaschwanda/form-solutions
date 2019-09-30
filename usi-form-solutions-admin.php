<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_Form_Solutions_Admin {

   const VERSION = '1.0.0 (2018-03-25)';

   public static $edit_preferences     = false;
   public static $view_settings  = false;

   function __construct() {
      add_action('admin_menu', array($this, 'action_admin_menu'));
   } // __construct();

   function action_admin_menu() {
      self::$edit_preferences = current_user_can(USI_Form_Solutions::NAME . '-Edit-Preferences');
      self::$view_settings    = current_user_can(USI_Form_Solutions::NAME . '-View-Settings');
   } // action_admin_menu();

} // Class USI_Form_Solutions_Admin;

new USI_Form_Solutions_Admin;

// --------------------------------------------------------------------------------------------------------------------------- // ?>
