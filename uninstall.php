<?php // ------------------------------------------------------------------------------------------------------------------------ //

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-uninstall.php');

require_once('usi-form-solutions.php');

final class USI_Form_Solutions_Uninstall {

   const VERSION = '1.1.1 (2019-09-30)';

   private function __construct() {
   } // __construct();

   static function uninstall() {

      if (!defined('WP_UNINSTALL_PLUGIN')) exit;

   } // uninstall();

} // Class USI_Form_Solutions_Uninstall;

USI_WordPress_Solutions_Uninstall::uninstall(USI_Form_Solutions::PREFIX);

USI_Form_Solutions_Uninstall::uninstall();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
