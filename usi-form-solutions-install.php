<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

final class USI_Form_Solutions_Install {

   const VERSION = '1.1.1 (2019-09-30)';

   private function __construct() {
   } // __construct();

   static function init() {
      $file = str_replace('-install', '', __FILE__);
      register_activation_hook($file, array(__CLASS__, 'hook_activation'));
      register_deactivation_hook($file, array(__CLASS__, 'hook_deactivation'));
   } // init();

   static function hook_activation() {

      if (!current_user_can('activate_plugins')) return;

      check_admin_referer('activate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

      $role = get_role('administrator');
      foreach (USI_Form_Solutions::$capabilities as $capability => $description) {
         $role->add_cap(USI_Form_Solutions::NAME . '-' . $capability);
      }

   } // hook_activation();

   static function hook_deactivation() {

      if (!current_user_can('activate_plugins')) return;

      check_admin_referer('deactivate-plugin_' . (isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : ''));

   } // hook_deactivation();

} // Class USI_Form_Solutions_Install;

USI_Form_Solutions_Install::init();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
