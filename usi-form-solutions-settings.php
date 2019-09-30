<?php // ------------------------------------------------------------------------------------------------------------------------ //

defined('ABSPATH') or die('Accesss not allowed.');

require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-capabilities.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-settings.php');
require_once(plugin_dir_path(__DIR__) . 'usi-wordpress-solutions/usi-wordpress-solutions-versions.php');

class USI_Form_Solutions_Settings extends USI_WordPress_Solutions_Settings {

   const VERSION = '1.1.1 (2019-09-30)';

   protected $is_tabbed = true;

   function __construct() {

      parent::__construct(
         USI_Form_Solutions::NAME, 
         USI_Form_Solutions::PREFIX, 
         USI_Form_Solutions::TEXTDOMAIN,
         USI_Form_Solutions::$options
      );

   } // __construct();

   function config_section_header_preferences() {
      echo '<p>' . __('Changing these settings after the system is in use may cause referencing errors. Make sure that you also change the <b>[ID file="your-form.php"]</b> shortcodes in your content to match the settings you enter here.', USI_Form_Solutions::TEXTDOMAIN) . '</p>' . PHP_EOL;
   } // config_section_header_preferences();

   function fields_sanitize($input) {
      if (!empty($input['preferences']['shortcode-prefix'])) {
         $input['preferences']['shortcode-prefix'] = sanitize_title(strtolower($input['preferences']['shortcode-prefix']));
      }
      $input = parent::fields_sanitize($input);
      return($input);
   } // fields_sanitize();

   function filter_plugin_row_meta($links, $file) {
      if (false !== strpos($file, USI_Form_Solutions::TEXTDOMAIN)) {
         $links[0] = USI_WordPress_Solutions_Versions::link(
            $links[0], 
            USI_Form_Solutions::NAME, 
            USI_Form_Solutions::VERSION, 
            USI_Form_Solutions::TEXTDOMAIN, 
            __DIR__ // Folder containing plugin or theme;
         );
         $links[] = '<a href="https://www.usi2solve.com/donate/form-solutions" target="_blank">' . 
            __('Donate', USI_Form_Solutions::TEXTDOMAIN) . '</a>';
      }
      return($links);
   } // filter_plugin_row_meta();

   function sections() {

      $sections = array(

         'preferences' => array(
            'header_callback' => array($this, 'config_section_header_preferences'),
            'label' => 'Preferences',
            'settings' => array(
               'shortcode-prefix' => array(
                  'type' => 'text', 
                  'label' => 'Shortcode identifier',
                  'notes' => 'Enter lower case text, no spaces or punctuation. This is the <b>ID</b> in [<b>ID</b> file="your-form.php"] used to access the form shortcode in you content. Defaults to <b>form</b>.',
               ),

               'file-location' => array(
                  'type' => 'radio', 
                  'label' => 'Location of <i>"forms"</i> folder',
                  'choices' => array(
                     array(
                        'value' => 'plugin', 
                        'label' => true, 
                        'notes' => __('Plugin folder', USI_Form_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'theme', 
                        'label' => true, 
                        'notes' => __('Theme folder', USI_Form_Solutions::TEXTDOMAIN), 
                        'suffix' => '<br/>',
                     ),
                     array(
                        'value' => 'root', 
                        'label' => true, 
                        'notes' => __('WordPress wp-config.php folder', USI_Form_Solutions::TEXTDOMAIN), 
                     ),
                  ),
                  'notes' => 'Defaults to <b>Plugin folder</b>.',
               ), // file-location;

            )

         ), // preferences;

         'capabilities' => USI_WordPress_Solutions_Capabilities::section(
            USI_Form_Solutions::NAME, 
            USI_Form_Solutions::PREFIX, 
            USI_Form_Solutions::TEXTDOMAIN,
            USI_Form_Solutions::$capabilities,
            USI_Form_Solutions::$options
         ), // capablities;

      );

      foreach ($sections as $name => & $section) {
         foreach ($section['settings'] as $name => & $setting) {
            if (!empty($setting['notes']))
               $setting['notes'] = '<p class="description">' . __($setting['notes'], USI_Form_Solutions::TEXTDOMAIN) . '</p>';
         }
      }
      unset($setting);

      return($sections);

   } // sections();

} // Class USI_Form_Solutions_Settings;

new USI_Form_Solutions_Settings();

// --------------------------------------------------------------------------------------------------------------------------- // ?>