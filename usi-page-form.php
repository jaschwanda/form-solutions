<?php // ------------------------------------------------------------------------------------------------------------------------ //

/* Copyright (C) 2015 Universal Solutions, Inc. - All Rights Reserved
 * You may use and modify this code only on the machine on which it is installed,
 * you may not redistribute, publish or sell without written permission from
 * Universal Solutions, Inc., for more information contact webmaster@usi2solve.com.
 */

if (1 == count(get_included_files())) { header('location: http://' . $_SERVER['SERVER_NAME'] . '/404.php'); exit; }

define('USI_PAGE_FORM', '2.0.4');

// Disable submit on click;
// Add javascript form validation and validate as you go;
// Add tab index automatically

class USI_Form_checkbox extends USI_Form_Field_Input {

   var $default_checked = false;
   var $value_checked = '1';
   var $value_unchecked = '0';

   function __construct($field_id, $attributes = '', $value = '', $checked = '1', $unchecked = '0') {
      parent::__construct('checkbox', $field_id, $attributes, $value);
      $this->value_checked = $checked;
      $this->value_unchecked = $unchecked;
   } // __construct();

   function render() {
      if ($this->sub) return($this->get_prefix() . $this->sub . $this->get_suffix());
      return($this->get_prefix() . '<input' . (($this->value_checked == $this->post_value) ?  ' checked' : '') . parent::render() . ' type="checkbox" ' . 'value="' . $this->value_checked . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_checkbox;

class USI_Form_error extends USI_Form_Field {

   function __construct($field_id) {
      parent::__construct($field_id, 'error');
   } // __construct();

   function render() {
      $form = $this->page_object->form_object;
      return($form->bad_message ? str_repeat(' ', $form->indent) . '  ' . $form->bad_message . PHP_EOL : '');
   } // render();

} // Class USI_Form_error;

class USI_Form_Field {

   var $field_id = '';
   var $field_type = ''; // error|function|html|input
   var $indent = 0;
   var $page_object = null;

   function __construct(&$field_id, $field_type) {
      if ('+' == $field_id) {
         global $usi_form_object;
         $field_id = $usi_form_object->form_id . '-' . ++$usi_form_object->auto_increment;
      }
      $this->field_id = str_replace(array(' ', '|'), array('_', '_'), $field_id);
      $this->field_type = $field_type;
   } // __construct();

   function get_post() { 
      return(false);
   } // get_post();

   function render() {
      return('');
   } // render();

} // Class USI_Form_Field;

class USI_Form_Field_Input extends USI_Form_Field {

   var $attributes = '';
   var $case = null;
   var $dbs_field = null;
   var $dbs_skip = false;
   var $dbs_table = null;
   var $edit_mode = null;
   var $get_post = true;
   var $function = '';
   var $help = false;
   var $html_class = null;
   var $html_class_bad = null;
   var $html_class_readonly = null;
   var $html_event = '';
   var $html_id = '';
   var $html_name = '';
   var $html_style = null;
   var $html_style_bad = null;
   var $html_style_readonly = null;
   var $html_type = ''; // checkbox|hidden|password|radio|select|submit|text|textarea
   var $is_bad = false;
   var $is_editable = false;
   var $is_leave_blank_field = false;
   var $is_readonly = false;
   var $is_required = false;
   var $label = '';
   var $match_field_id = null;
   var $maxlength = 0;
   var $post_value = '';
   var $prefix = '';
   var $regex = null;
   var $sub = null;
   var $suffix = '';
   var $value = '';
   var $verify = null;

   function __construct($type, $field_id, $attributes, $value) {
      global $usi_form_object;
      parent::__construct($field_id, 'input');
      $this->attributes = $attributes;
      $this->html_name = $this->field_id;
      $this->html_type = $type;
      $this->label = $field_id;
      $this->value = $value;

      $key = strtolower(strtok($this->attributes, '=,'));
      while ('' != $key) {
         $value = strtok('=,');
         $lvalue = strtolower($value);
         switch ($key) {
         case 'back': $this->back_page_id = $value; break;
         case 'case': switch ($lvalue) { case 'caps': case 'lower': case 'upper': $this->case = $lvalue; } break;
         case 'class': $this->html_class = $value; break;
         case 'class_bad': $this->html_class_bad = $value; break;
         case 'class_readonly': $this->html_class_readonly = $value; break;
         case 'dbs': $this->dbs_field = $value; $this->dbs_table = $usi_form_object->dbs_base_table; break;
         case 'dbs_skip': $this->dbs_skip = ('yes' == $lvalue); break;
         case 'dbs_table': $this->dbs_table = $value; break;
         case 'default_checked': $this->default_checked = ('yes' == $lvalue); break;
         case 'edit_mode': $this->edit_mode = $value; $usi_form_object->edit_mode[] = $this; break;
         case 'editable': $this->is_editable = ('yes' == $lvalue); break;
         case 'event': $this->html_event = $value; break;
         case 'first': $this->first = ('yes' == $lvalue); break;
         case 'get_post': $this->get_post = ('yes' == $lvalue); break;
         case 'help': $this->help = $value; break;
         case 'id': $this->html_id = ($value == '"') ? $this->html_name : $value; break;
         case 'indent': (int)$this->indent = (int)$value; break;
         case 'function': $this->function = $value; break;
         case 'match': $this->match_field_id = $value; break;
         case 'label': $this->label = $value; break;
         case 'leave_blank': $this->is_leave_blank_field = ('yes' == $lvalue); break;
         case 'maxlength': $this->maxlength = (int)$value; break;
         case 'method': $this->method = $value; break;
         case 'missing_value': $this->missing_value = $value; break;
         case 'next': $this->next_page_id = $value; break;
         case 'options': if (function_exists($value)) $this->options = $value(); break;
         case 'prefix': $this->prefix = $value; break;
         case 'readonly': $this->is_readonly = ('yes' == $lvalue); break;
         case 'regex': $this->verify = 'regex'; $this->regex = substr($this->attributes, strpos($this->attributes, 'regex=') + 6); 
            break 2; // Exits while();
         case 'required': $this->is_required = ('yes' == $lvalue); break;
         case 'sub': $this->sub = $value; break;
         case 'style': $this->html_style = $value; break;
         case 'style_bad': $this->html_style_bad = $value; break;
         case 'style_readonly': $this->html_style_readonly = $value; break;
         case 'suffix': $this->suffix = $value; break;
         case 'verify':
            switch ($lvalue) {
            case 'captcha':
            case 'date_human':
            case 'date_day':
            case 'date_machine':
            case 'date_month':
            case 'date_year':
            case 'email':
            case 'match':
            case 'phone_us': 
            case 'zip_us': $this->verify = $lvalue; break;
            }
            break;
         }
         $key = strtolower(strtok('=,'));
      }
   } // __construct();

   function get_post() {
      if (!$this->get_post) {
      } else if (!isset($_POST[$this->html_name])) {
         if ('checkbox' == $this->html_type) {
            $this->post_value = $this->value_unchecked;
         } else if ('radio' == $this->html_type) {
         } else {
            $this->post_value = '';
         }
      } else {
         $this->post_value = ($this->maxlength ? substr($_POST[$this->html_name], 0, $this->maxlength) : $_POST[$this->html_name]);
         if ($this->page_object->form_object->debug) usi_debug_message(__METHOD__, "field_id={$this->field_id} post_value={$this->post_value}");
         switch ($this->case) {
         case 'caps' : $this->post_value = ucwords(strtolower($this->post_value)); break;
         case 'lower': $this->post_value = strtolower($this->post_value); break;
         case 'upper': $this->post_value = strtoupper($this->post_value); break;
         }
         if ('radio' == $this->html_type) {
         } else if ('submit' != $this->html_type) {
            $this->value = $this->post_value;
         } else {
            if ($this->page_object->page_id == $this->page_object->form_object->page_id) return(true);
         }
      }
      return(false);
   } // get_post();

   function get_prefix() {
      $this->page_object->form_object->input_replace[4] = $this->field_id;
      $this->page_object->form_object->input_replace[5] = $this->label;
      return($this->prefix ? str_replace($this->page_object->form_object->input_search, 
         $this->page_object->form_object->input_replace, $this->prefix) : '');
   } // get_prefix();

   function get_suffix() {
      return($this->suffix ? str_replace($this->page_object->form_object->input_search, 
         $this->page_object->form_object->input_replace, $this->suffix) : '');
   } // get_suffix();

   function render() {
      $html = '';
      if ('' != $this->html_id) $html .= ' id="' . $this->html_id . '"';
      $class = $this->html_class;
      if ($this->is_bad && $this->html_class_bad) $class .= ($class ? ' ' : '') . $this->html_class_bad;
      if ($this->is_readonly && $this->html_class_readonly) $class .= ($class ? ' ' : '') . $this->html_class_readonly;
      if ($class) $html .= ' class="' . $class . '"';
      if ($this->maxlength) $html .= ' maxlength="' . $this->maxlength . '"';
      if ('' != $this->html_name) $html .= ' name="' . $this->html_name . '"';
      if ('' != $this->html_event) $html .= ' ' . str_replace(array('{~}'), array('='), $this->html_event);
      if ($this->is_readonly) $html .= ' readonly';
      $style = $this->html_style;
      if ($this->is_bad && $this->html_style_bad) $style .= ($style ? ' ' : '') . $this->html_style_bad;
      if ($this->is_readonly && $this->html_style_readonly) $style .= ($style ? ' ' : '') . $this->html_style_readonly;
      if ($style) $html .= ' style="' . $style . '"';
      return($html);
   } // render();

} // Class USI_Form_Field_Input;

class USI_Form_function extends USI_Form_Field {

   var $function = '';

   function __construct($field_id, $function) {
      parent::__construct($field_id, 'function');
      $this->function = $function;
   } // __construct();

   function render() {
      $function = $this->function; 
      return(function_exists($function) ? str_replace($this->page_object->form_object->input_search, 
         $this->page_object->form_object->input_replace, $function($this->page_object->form_object)) : '');
   } // render();

} // Class USI_Form_function;

class USI_Form_hidden extends USI_Form_Field_Input {

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('hidden', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      // It appears that this is never called;
      return($this->get_prefix() . '<input' . parent::render() . ' type="hidden" value="' . $this->value . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_hidden;

class USI_Form_html extends USI_Form_Field {

   var $html = '';

   function __construct($field_id, $html = '') {
      parent::__construct($field_id, 'html');
      $this->html = $html;
   } // __construct();

   function render() {
      return(str_replace($this->page_object->form_object->input_search, 
         $this->page_object->form_object->input_replace, $this->html));
   } // render();

} // Class USI_Form_html;

class USI_Form_Lock {

   var $captch = null;
   var $lock = null;
   var $ip = '';
   var $is_bad = false;
   var $key = '';
   var $padding = '';
   var $uniq = null;

   function __construct($form_id) {
      $this->salt = $form_id;
      $oct = explode('.', $_SERVER['REMOTE_ADDR']);
      $this->ip = sprintf('%02X%02X%02X%02X', (int)$oct[0], (int)$oct[1], (int)$oct[2], (int)$oct[3]);
      $this->padding = strtoupper(substr(hash_hmac('md5', $_SERVER['REMOTE_ADDR'], $this->salt), 0, 11));
      $lock_id = $form_id . '_lock';
      $this->lock = (isset($_POST[$lock_id]) ? $this->decode($_POST[$lock_id]) : $this->encode());
   } // __construct();

   function encode() {
      $this->captcha = usi_captcha_encode(8);
      if (!$this->uniq) $this->uniq = strtoupper(uniqid()) . $this->ip . $this->padding;
      $hash = strtoupper(hash_hmac('md5', $this->uniq, $this->salt));
      $uniq = $this->uniq;
      $off = rand(0, 15);
      $hash = substr($hash, $off) . substr($hash, 0, $off);
      $uniq = substr($uniq, $off) . substr($uniq, 0, $off);
      $lock = sprintf('%1X', $off);
      for ($ith = 0; $ith < 32; $ith++) {
         $lock .= $hash[$ith] . $uniq[$ith];
      }
      return($this->lock = $lock . $this->captcha);
   } // encode();

   function decode($lock) {
      $off = (int)hexdec($lock[0]);
      $hash = $uniq = '';
      for ($ith = 0; $ith < 32; $ith++) {
         $hash .= $lock[2*$ith+1];         
         $uniq .= $lock[2*$ith+2];         
      }
      $hash = substr($hash, -$off) . substr($hash, 0, 32 - $off);
      $this->uniq = substr($uniq, -$off) . substr($uniq, 0, 32 - $off);
      $this->captcha = substr($lock, 65);
      $this->is_bad = ($hash != strtoupper(hash_hmac('md5', $this->uniq, $this->salt)));
      return($lock);
   } // decode();

   function get_mysql_date() {
      return(date('Y-m-d H:i:s', hexdec(substr($this->uniq, 0, 8))));
   } // get_mysql_date();

   function get_unique() {
      return(substr($this->uniq, 0, 13) . '-' . substr($this->uniq, 13, 8));
   } // get_unique();

   function get_unique_english() {
      $ip = hexdec(substr($this->uniq, 13, 2)) . '.' . hexdec(substr($this->uniq, 15, 2)) . '.' .
         hexdec(substr($this->uniq, 17, 2)) . '.' . hexdec(substr($this->uniq, 19, 2));
      $start = hexdec(substr($this->uniq, 0, 8));
      $date = date('F j, Y', $start);
      $time = date('g:i a', $start);
      return("from IP address $ip starting on $date at $time");
   } // get_unique_english();

} // class USI_Form_Lock;

class USI_Form_method extends USI_Form_Field {

   var $method = null;
   var $object = null;

   function __construct($field_id, $object, $method) {
      parent::__construct($field_id, 'method');
      $this->object = $object;
      $this->method = $method;
   } // __construct();

   function render() {
      $method = $this->method; 
      $object = $this->object; 
      return(method_exists($object, $method) ? str_replace($this->page_object->form_object->input_search, 
         $this->page_object->form_object->input_replace, $object->{$method}($this->page_object->form_object)) : '');
   } // render();

} // Class USI_Form_method;

class USI_Form_Object {

   var $attributes = '';
   var $auto_increment = 0;
   var $bad_count = 0;
   var $bad_field_prefix = '<b>';
   var $bad_field_suffix = '</b>';
   var $bad_page_prefix = '<p>';
   var $bad_page_suffix = '</p>';
   var $bad_message = null;
   var $bad_text = 'Please correct the following issue{plural}: ';
   var $dbs = null;
   var $dbs_base_table = null;
   var $debug = null;
   var $duplicate_page_id = '';
   var $edit_mode = array();
   var $first_page_id = '';
   var $form_id = '';
   var $form_padding = '';
   var $input_replace = null;
   var $input_search = null;
   var $lock = null;
   var $method = 'post';
   var $minimum_time = 0;
   var $MYSQL_date = '';
   var $page_id = '';
   var $pages = array();
   var $row = null;
   var $success_page_id = '';
   var $submit_field = null;

   function __construct($form_id, $attributes) {
      global $usi_form_object;
      global $usi_page_cache;
      $usi_form_object = $this;
      $this->dbs = $usi_page_cache->dbs;
      $this->form_id = $form_id;
      $this->attributes = $attributes;
      $this->lock = new USI_Form_Lock($form_id);
      $this->MYSQL_date = $this->lock->get_mysql_date();
      $key = strtolower(strtok($this->attributes, '=,'));
      while ($key != '') {
         $value = strtok('=,');
         $lvalue = strtolower($value);
         switch ($key) {
         case 'dbs_base_table': $this->dbs_base_table = $value; break;
         case 'duplicate_page_id': $this->duplicate_page_id = $value; break;
         case 'first_page_id': $this->first_page_id = $value; break;
         case 'indent': $this->indent = (int)$value; break;
         case 'method': $this->method = $value; break;
         case 'minimum_time': $this->minimum_time = $value; break;
         case 'success_page_id': $this->success_page_id = $value; break;
         }
         $key = strtolower(strtok('=,'));
      }
      $this->input_search = array(
         '{captcha}',
         '{i}',
         '{n}',
         '{~}',
         '{field_id}', // must be 4 position;
         '{label}',    // must be 5 position;
         '{;}',
      );
      $this->input_replace = array(
         $this->lock->captcha,
         str_repeat(' ', $this->indent),
         PHP_EOL,
         '=',
         '',
         '',
         ',',
      );
      usi_version_load('usi-page-form', USI_PAGE_FORM);
   } // __construct();

   function add_pages() {
      $num_args = func_num_args();
      for ($ith = 0; $ith < $num_args; $ith++) {
         $page = func_get_arg($ith);
         $page->form_object = $this;
         $this->pages[$page->page_id] = $page;
      }
   } // add_pages();

   function dump($id) {
      foreach ($this->pages as $page_id => $page) {
         echo "<!-- $id page_id=$page_id -->\n";
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if ('input' != $field->field_type) continue;
            echo "<!-- $id html_name={$field->html_name} post_value={$field->post_value} -->\n";
         }
      }
   } // dump();

   function get_dbs_insert_sql($table, $extra_fields = null, $extra_values = null) {
      $field_names = $field_values = null;
      foreach ($this->pages as $page_id => $page) {
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if (isset($field->dbs_field) && ($table == $field->dbs_table) && !$field->dbs_skip) {
               switch ($field->html_type) {
               case 'checkbox':
                  $field_names .= ($field_names ? ', ' : '') . '`' . $field->dbs_field . '`';
                  $field_values .= ($field_values ? ', ': '') . (($field->value == $field->value_checked) ? "'{$field->value_checked}'" : "'{$field->value_unchecked}'");
                  break;
               case 'hidden':
               case 'password':
               case 'select':
               case 'submit':
               case 'text':
               case 'textarea':
                  $field_names .= ($field_names ? ', ' : '') . '`' . $field->dbs_field . '`';
                  $field_values .= ($field_values ? ', ': '') . '\'';
                  if ('date_human' == $field->verify) {
                     $machine_format = $field->value;
                     usi_is_date_valid($machine_format, false);
                     $field_values .= $machine_format  . '\'';
                  } else {
                     $field_values .= $this->dbs->real_escape_string($field->value) . '\'';
                  }
                  break;
               case 'radio':
                  if ($field->first) {
                     $field_names .= ($field_names ? ', ' : '') . '`' . $field->dbs_field . '`';
                     $field_values .= ($field_values ? ', ': '') . '\'' . $this->dbs->real_escape_string($field->post_value) . '\'';
                  }
                  break;
               }
            }
         }
      }
      if ($extra_fields && $extra_values) {
         $field_names .= ($field_names ? ', ': '') . $extra_fields;
         $field_values .= ($field_values ? ', ': '') . $extra_values;
      }
      return('(' . $field_names . ') VALUES (' . $field_values . ')');
   } // get_dbs_insert_sql();

   function get_dbs_update_sql() {
      $sql = null;
      foreach ($this->pages as $page_id => $page) {
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if (isset($field->dbs_field) && !$field->dbs_skip) {
               switch ($field->html_type) {
               case 'checkbox':
                  $sql .= ($sql ? ', `': '`') . $field->dbs_field . '` = ' . (($field->value == $field->value_checked) ? "'{$field->value_checked}'" : "'{$field->value_unchecked}'");
                  break;
               case 'radio':
                  if ($field->first) $sql .= ($sql ? ', `': '`') . $field->dbs_field . "` = '" . 
                     $this->dbs->real_escape_string($field->post_value) . "'";
                  break;
               case 'hidden':
               case 'password':
               case 'select':
               case 'submit':
               case 'text':
               case 'textarea':
                  $sql .= ($sql ? ', `': '`') . $field->dbs_field . '` = \'' . $this->dbs->real_escape_string($field->value) . '\'';
                  break;
               }
            }
         }
      }
      return($sql);
   } // get_dbs_update_sql();

   function get_dbs_values($sql = null) {
      if ($sql) {
         $results = $this->dbs->query($sql); 
         $count_of_records = $results->num_rows;
         if ($count_of_records == 1) {
            $this->row = $results->fetch_assoc();
            $error = '';
         } else {
            $this->row = null;
            $error = ' error=' . $this->dbs->error;
         }
         if ($this->debug) usi_debug_message(__METHOD__, "sql=$sql count_of_records=$count_of_records$error"); 
      }
      if ($this->row) {
         foreach ($this->pages as $page_id => $page) {
            if (isset($page->fields)) foreach ($page->fields as $field) {
               if (isset($field->dbs_field) && !$field->dbs_skip) {
                  switch ($field->html_type) {
                  case 'checkbox':
                     $field->value = $this->row[$field->dbs_field];
                     break;
                  case 'radio':
                     $field->post_value = $this->row[$field->dbs_field];
                     break;
                  case 'hidden':
                  case 'password':
                  case 'select':
                  case 'submit':
                  case 'text':
                  case 'textarea':
                     $field->value = $this->row[$field->dbs_field];
                     if ($this->debug) usi_debug_message(__METHOD__, "$field->dbs_field={$field->value}"); 
                     break;
                  }
               }
            }
         }
      }
   } // get_dbs_values();

   function get_email_message($label_width = 20, $dbs_fields_only = true) {
      $text = '';
      foreach ($this->pages as $page_id => $page) {
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if ('input' == $field->field_type) {
               if ($dbs_fields_only && !isset($field->dbs_field) && !$field->dbs_skip) continue;
               $label = sprintf('%-' . $label_width . 's = ', $field->label);
               switch ($field->html_type) {
               case 'checkbox':
                  $text .= $label . (($field->value == $field->value_checked) ? 'Yes' : 'no') . PHP_EOL;
                  break;
               case 'radio':
                  if ($field->first) $text .= $label . $field->post_value . PHP_EOL;
                  break;
               case 'hidden':
               case 'password':
               case 'select':
               case 'text':
               case 'textarea':
                  $text .= $label . $field->value . PHP_EOL;
                  break;
               }
            }
         }
      }
      return($text);
   } // get_email_message();

   function is_input_valid() {
      $page = $this->pages[$this->page_id];      
      $is_good = true;
      $day = $message = $month = $year = null;
      if (isset($page->fields)) foreach ($page->fields as $field) {
         if ('input' != $field->field_type) continue;
         if ($field->is_required) {
            if ('checkbox' == $field->html_type) {
               if ($field->value_checked != $field->post_value) {
                  $is_good = $this->set_field_bad($field, $message, ' (box not checked)');
                  continue;
               }
            } else if ('radio' == $field->html_type) {
               if ('' == $field->post_value) {
                  $is_good = $this->set_field_bad($field, $message, ' (selection not made)');
                  continue;
               }
            } else if ('select' == $field->html_type) {
               if ($field->missing_value == $field->post_value) {
                  $is_good = $this->set_field_bad($field, $message, ' is missing');
                  continue;
               }
               if ($field->options) {
                  if (!isset($field->options[$field->post_value])) {
                     $is_good = $this->set_field_bad($field, $message, ' (invalid option selected)');
                     continue;
                  }
               }
            } else {
               if ('' == $field->post_value) {
                  $is_good = $this->set_field_bad($field, $message, ' is missing');
                  continue;
               }
            }
         } else if ('select' == $field->html_type) {
            if ($field->options) {
               if (!isset($field->options[$field->post_value])) {
                  $is_good = $this->set_field_bad($field, $message, ' (invalid option selected)');
                  continue;
               }
            }
         }
         if ($field->verify) {
            switch ($field->verify) {
            case 'captcha':
               if (strtolower(usi_simple_decode($this->lock->captcha)) != strtolower($field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (wrong verification code given)');
                  continue;
               }
               break;
            case 'date_human':
               if (!usi_is_date_valid($field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (valid format is Month day, year)');
                  continue;
               }
               $field->value = $field->post_value;
               break;
            case 'date_day':
               $match_field = $page->fields[$field->match_field_id];
               if ('' == $match_field->value) $match_field->value = '0000-00-00';
               $match_field->value = substr_replace($match_field->value, $field->value, 8, 2);
               $day = $field;
               break;
            case 'date_month':
               $match_field = $page->fields[$field->match_field_id];
               if ('' == $match_field->value) $match_field->value = '0000-00-00';
               $match_field->value = substr_replace($match_field->value, $field->value, 5, 2);
               $month = $field;
               break;
            case 'date_machine':
               if (!$field->is_required && ('0000-00-00' == $field->value)) break;
               if ($day && $month && $year) {
                  switch ($month->value) {
                  case '01': case '03': case '05': case '07': case '08': case '10': case '12': default: $days = 31; break;
                  case '04': case '06': case '09': case '11': $days = 30; break;
                  case '02': $days = ((($year->value%4)==0)&&((($year->value%100)!=0)||(($year->value%400)==0))) ? 29 : 28; break;
                  }
                  if ($days < $day->value) {
                     $is_good = $this->set_field_bad($day, $message, ' is invalid');
                  } else if (0 == $day->value) {
                     $is_good = $this->set_field_bad($day, $message, ' is missing');
                  }
                  if (0 == $month->value) $is_good = $this->set_field_bad($month, $message, ' is missing');
                  if (0 == $year->value) $is_good = $this->set_field_bad($year, $message, ' is missing');
                  $day = $month = $year = null;
               }
               break;
            case 'date_year':
               $match_field = $page->fields[$field->match_field_id];
               if ('' == $match_field->value) $match_field->value = '0000-00-00';
               $match_field->value = substr_replace($match_field->value, $field->value, 0, 4);
               $year = $field;
               break;
            case 'email':
               if (!usi_is_email_valid($field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (address is not valid)');
                  continue;
               }
               break;
            case 'match':
               $match_field = $page->fields[$field->match_field_id];
               if ($field->post_value != $match_field->post_value) {
                  $is_good = $this->set_field_bad($field, $message, 
                     ' (does not match the ' . $this->bad_field_prefix . $match_field->label . $this->bad_field_suffix . ' field)');
                  continue;
               }
               break;
            case 'phone_us':
               if (!usi_is_phone_us_valid($field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (valid format is (nnn)nnn-nnnn or (nnn)nnn-nnnn xnnnn)');
                  continue;
               }
               $field->value = $field->post_value;
               break;
            case 'regex':
               if (!preg_match('/^' . str_replace('\\\\', '\\', $field->regex) . '$/', $field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (' . $field->help . ')');
                  continue;
               }
               $field->value = $field->post_value;
               break;
            case 'zip_us':
               if (!usi_is_zip_us_valid($field->post_value)) {
                  $is_good = $this->set_field_bad($field, $message, ' (valid format is nnnnn or nnnnn-nnnn)');
                  continue;
               }
               $field->value = $field->post_value;
               break;
            }
         }
      }
      $this->make_bad_message($message);
      return($is_good);
   } // is_input_valid();

   function is_submitted() {
      $sql = "SELECT `lock` FROM `{$this->dbs_base_table}` WHERE (`lock` = '" . $this->lock->get_unique() . "')";
      $results = $this->dbs->query($sql); 
      if ($results) if (0 < $results->num_rows) {
         $error = 'This operation has already been completed ' . $this->lock->get_unique_english() . '.';
         $this->bad_message = $this->bad_page_prefix . $error . $this->bad_page_suffix;
         return(true);
      }
      return(false);
   } // is_submitted();

   function make_bad_message($message) {
      $plural = (($this->bad_count > 1) ? 's' : '');
      if ($message) $this->bad_message = $this->bad_page_prefix . str_replace('{plural}', $plural, $this->bad_text) . 
         $message . '.' . $this->bad_page_suffix;
   } // make_bad_message();

   function make_message($message) {
      $plural = (($this->bad_count > 1) ? 's' : '');
      $this->bad_message = $this->bad_page_prefix . str_replace('{plural}', $plural, $message) . $this->bad_page_suffix;
   } // make_message();

   function padding($extra = 0) {
      return(str_repeat(' ', $this->indent + $extra));
   } // padding();

   function process() {
      if ($this->debug) usi_debug_message(__METHOD__, 'begin');
      $p = str_repeat(' ', $this->indent);

      $this->page_id = (isset($_POST[$this->form_id . '_page']) ? $_POST[$this->form_id . '_page'] : $this->first_page_id);

      if (isset($this->edit_mode)) {
         $mode = $cancel = $edit = $update = null;
         foreach ($this->edit_mode as $field) {
            if (isset($_POST[$field->html_name])) $field->post_value = $_POST[$field->html_name]; 
            switch ($field->edit_mode) {
            case 'mode': $mode = $field; break;
            case 'cancel': $cancel = $field; break;
            case 'edit': $edit = $field; break;
            case 'update': $update = $field; break;
            }
         }
         if ($edit && ($edit->value == $edit->post_value)) {
            $readonly = false;
         } else {
            if ($cancel && ($cancel->value == $cancel->post_value)) unset($_POST); 
            $readonly = $status = true;
         }
      }

      foreach ($this->pages as $page_id => $page) {
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if ($field->get_post()) {
               $this->submit_field = $field;
            }
         }
      }

      if ($this->submit_field) {
         if ($this->is_submitted()) {
            $this->page_id = $this->duplicate_page_id;
         } else if ($this->submit_field->back_page_id) {
            $this->page_id = $this->submit_field->back_page_id;
         } else if (!($status = $this->is_input_valid())) {
            // Error message
         } else {
            $function = $this->submit_field->function;
            if (function_exists($function)) $status = $function($this);
            if ($status) $this->page_id = $this->submit_field->next_page_id;
         }
      }

      $page = $this->pages[$this->page_id];

      if (isset($this->edit_mode)) {
         if (function_exists('usi_form_edit_mode')) usi_form_edit_mode($readonly && $status ? 'view' : 'edit', $cancel, $edit, $update);
         $this->readonly($readonly && $status); 
      }

      $html = $this->form_padding . 
              '<form action="" method="' . $this->method . '" name="' . $this->form_id . '">' . PHP_EOL .
         $p . '  <input name="' . $this->form_id . '_lock" type="hidden" value="' . $this->lock->lock . '" />' . PHP_EOL .
         $p . '  <input name="' . $this->form_id . '_page" type="hidden" value="' . $this->page_id . '" />' . PHP_EOL;
      foreach ($this->pages as $page_id => $page_hidden) {
         if ($page_hidden->page_id != $page->page_id) {
            foreach ($page_hidden->fields as $field) {
               // if (is_subclass_of($field, 'USI_Form_Field')) {
               if ('input' == $field->field_type) {
                  if ('submit' != $field->html_type) {
                     if ('radio' == $field->html_type) {
                        if ($field->first) $html .= $p . '  <input name="' . $field->html_name . '" type="hidden" value="' . $field->post_value . '" />' . PHP_EOL;
                     } else {
                        $html .= $p . '  <input' . (('' != $field->html_id) ? ' id="' . $field->html_id . '"' : '') . ' name="' . $field->html_name . '" type="hidden" value="' . $field->value . '" />' . PHP_EOL;
                     }
                  }
               }
               // }
            }
         }
      }

      if ($this->debug) usi_debug_message(__METHOD__, 'render fields:begin');
      if (isset($page->fields)) foreach ($page->fields as $field) {
         if (method_exists($field, 'render')) $html .= $field->render();
      }
      if ($this->debug) usi_debug_message(__METHOD__, 'render fields:end');
      $html .= $p . '</form>' . PHP_EOL;

      if ($this->debug) usi_debug_message(__METHOD__, 'end');
      return($html);
   } // process();

   function readonly($mode) {
      foreach ($this->pages as $page_id => $page) {
         if (isset($page->fields)) foreach ($page->fields as $field) {
            if ('input' != $field->field_type) continue;
            if ($field->is_editable) $field->is_readonly = $mode;
         }
      }
   } // readonly();

   function set_field_bad($field, &$error, $message) {
      if (!$field->is_bad) {
         $field->is_bad = true;
         $error .= ($error ? ', ' : '') . $this->bad_field_prefix . $field->label . $this->bad_field_suffix . $message;
         $this->bad_count++;
      }
      return(false);
   } // set_field_bad();

   function set_page_bad($message) {
      $this->bad_count++;
      $this->bad_message = $this->bad_page_prefix . $message . $this->bad_page_suffix;
      return(false);
   } // set_page_bad();

} // Class USI_Form_Object;

class USI_Form_Page {

   var $page_id = '';
   var $fields = array();
   var $form_object = null;
   var $is_required = false;

   function __construct() {
      $num_args = func_num_args();
      $this->page_id = func_get_arg(0);
      for ($ith = 1; $ith < $num_args; $ith++) {
         $field = func_get_arg($ith);
         if ($field && is_a($field, 'USI_Form_Field')) {
            $field->page_object = $this;
            $this->fields[$field->field_id] = $field;         
         }
      }
   } // __construct();

   function add_field($field) {
      if ($field && is_a($field, 'USI_Form_Field')) {
         $field->page_object = $this;
         $this->fields[$field->field_id] = $field;         
      }
   } // add_field();

} // Class USI_Form_Page;

class USI_Form_password extends USI_Form_Field_Input {

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('text', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      return($this->get_prefix() . '<input' . parent::render() . ' type="password" value="' . $this->value . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_passwrod;

class USI_Form_radio extends USI_Form_Field_Input {

   var $default_checked = false;
   var $first = false;

   function __construct($field_id, $attributes = '', $value = '') {
      $id = str_replace(' ', '_', $label = strtok($field_id, '|'));
      parent::__construct('radio', $field_id, $attributes, strtok(''));
      $this->html_name = $id;
      $this->label = $label;
   } // __construct();

   function render() {
      $checked = ((!isset($POST[$this->html_name]) && $this->default_checked) || ($this->value == $this->post_value) ?  ' checked' : '');
      return($this->get_prefix() . '<input' . $checked . parent::render() . ' type="radio" value="' . $this->value . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_radio;

class USI_Form_select extends USI_Form_Field_Input {

   var $indent = 0;
   var $missing_value = null;
   var $options = null;

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('select', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      if (0 < $this->indent) {
         $ps = str_repeat(' ', $this->indent);
         $po = $ps . '  ';
         $n = PHP_EOL;
      } else {
         $n = $po = $ps = '';
      }
      $html = $this->get_prefix() . '<select' . parent::render() . '>' . $n;
      if ($this->options) {
         if ($this->is_readonly) {
            foreach ($this->options as $key => $value) {
               if ($this->value == (string)$key) {
                  $html .= $po . '<option selected value="' . $key . '">' . $value . '</option>' . $n;
                  break;
               }
            }
         } else {
            foreach ($this->options as $key => $value) {
               $html .= $po . '<option' . (($this->value == (string)$key) ? ' selected' : '') . ' value="' . $key . '">' . $value . '</option>' . $n;
            }
         }
      } else {
         $function = $this->function;
         if (function_exists($function)) $html .= $function($this->page_object->form_object);
      }
      $html .= $ps . '</select>' . $this->get_suffix();
      return($html);
   } // render();

} // Class USI_Form_select;

class USI_Form_submit extends USI_Form_Field_Input {

   var $back_page_id = null;
   var $next_page_id = null;

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('submit', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      if ($this->sub) return($this->get_prefix() . $this->sub . $this->get_suffix());
      return($this->get_prefix() . '<input' . parent::render() . ' type="submit" value="' . $this->value . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_submit;

class USI_Form_text extends USI_Form_Field_Input {

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('text', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      return($this->get_prefix() . '<input' . parent::render() . ' type="text" value="' . $this->value . '" />' . $this->get_suffix());
   } // render();

} // Class USI_Form_text;

class USI_Form_textarea extends USI_Form_Field_Input {

   function __construct($field_id, $attributes = '', $value = '') {
      parent::__construct('textarea', $field_id, $attributes, $value);
   } // __construct();

   function render() {
      return($this->get_prefix() . '<textarea' . parent::render() . '>' . $this->value . '</textarea>' . $this->get_suffix());
   } // render();

} // Class USI_Form_textarea;

function usi_build_id_name($name) {
   $search  = array('&',     ' ', '.', '\'', '#', '!', ',', '(', ')', '"', '_', '--', '--', '--');
   $replace = array('-and-', '-', '-', '-',  '-', '-', '-', '-', '-', '-', '-', '-',  '-',  '-');
   return(trim(str_replace($search, $replace, $name), '- '));
} // usi_build_id_name();
 
function usi_captcha_encode($length) {
   $possible = '23456789AbCdeFGhJkMNpqRsTvWxyZ';
   $possible_length = strlen($possible) - 1;
   $code = '';
   while ($length-- > 0) {
      $char = substr($possible, mt_rand(0, $possible_length), 1);
      $pass = 3;
      while (($pass-- > 0) && (strpos($code, $char) !== false)) {
         $char = substr($possible, mt_rand(0, $possible_length), 1);
      }
      $code .= $char;
   }
   return(usi_simple_encode($code)); 
} // usi_captcha_encode();

if (!function_exists('usi_shortcode_email')) {
   function usi_shortcode_email($attributes, $content = null) {
      $size = (isset($attributes['size']) ? $attributes['size'] : 'p21');
      $subject = (isset($attributes['subject']) ? '&s=' . $attributes['subject'] : '');
      $to = (isset($attributes['to']) ? $attributes['to'] : 'webmaster@sonj.org');
      $local = strtok($to, '@');
      $domain = strtok('.');
      $tld = strtok('');
      $e_mail = "$local@$domain.$tld";
      $e_view = (isset($attributes['view']) ? str_replace('_', ' ', $attributes['view']) : $e_mail);
   
      switch($size) {
      case 'p21': $font='ubuntu-l-webfont.ttf'; $height=21; $points=15 * 0.75; $base=17; $padding=1; 
         $nR=236; $nG= 31; $nB= 39; $hR= 66; $hG= 66; $hB= 66; $nr=$hr=255; $ng=$hg=255; $nb=$hb=255; 
      }
   
      if (isset($_SERVER['HTTP_USER_AGENT'])) if (preg_match('|MSIE ([0-9]{1,2}.[0-9]{1,2})|', $_SERVER['HTTP_USER_AGENT'], $matched)) $base++;
      $font_name = "{$_SERVER['DOCUMENT_ROOT']}/layout/fonts/$font";
      $bound_box = ImageTTFBbox($points, 0, $font_name, $e_view);
      $width = $bound_box[4] - $bound_box[0] + 2 * $padding;
   
      $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . '/layout/images/stuff/cloak.php';
      $address = '?d=' . $domain . '&l=' . $local . '&t=' . $tld . (isset($attributes['view'])?'&v='.$attributes['view']:'');
   
      return('<a href="' . $url . $address . $subject . '" onmouseout="this.style.backgroundPosition=\'0 0\';' .
         '" onmouseover="this.style.backgroundPosition=\'-100% 0\';" style="background:url(' . $url . $address . '&o=' . $size . ');' .
         ' display:inline-block; height:' . $height . 'px; vertical-align:top; width:' . $width . 'px;" target="null"></a>');
   } // usi_shortcode_email();
}

function usi_form_select_day($missing = '-') {
   $day = array($missing => '-- Select Day --');
   for ($ith = 1; $ith <= 31; $ith++) {
      $text = sprintf('%02d', $ith); 
      $day[$text] = $text; 
   }
   return($day);
} // usi_form_select_day();

function usi_form_select_gender() {
   return(array(
      '-' => '-- Select Gender --',
      'F' => 'Female',
      'M' => 'Male',
   ));
} // usi_form_select_gender();

function usi_form_select_month($missing = '-') {
   return(array(
      $missing => '-- Select Month --',
      '01' => 'January',
      '02' => 'February',
      '03' => 'March',
      '04' => 'April',
      '05' => 'May',
      '06' => 'June',
      '07' => 'July',
      '08' => 'August',
      '09' => 'September',
      '10' => 'October',
      '11' => 'November',
      '12' => 'December',
   ));
} // usi_form_select_month();

function usi_form_select_state() {
   return(array(
      '--' => '-- Select State --', 
      'AL' => 'Alabama', 
      'AK' => 'Alaska', 
      'AZ' => 'Arizona', 
      'AR' => 'Arkansas', 
      'CA' => 'California', 
      'CO' => 'Colorado', 
      'CT' => 'Connecticut', 
      'DE' => 'Delaware', 
      'DC' => 'District of Columbia', 
      'FL' => 'Florida', 
      'GA' => 'Georgia', 
      'HI' => 'Hawaii', 
      'ID' => 'Idaho', 
      'IL' => 'Illinois', 
      'IN' => 'Indiana', 
      'IA' => 'Iowa', 
      'KS' => 'Kansas', 
      'KY' => 'Kentucky', 
      'LA' => 'Louisiana', 
      'ME' => 'Maine', 
      'MD' => 'Maryland', 
      'MA' => 'Massachusetts', 
      'MI' => 'Michigan', 
      'MN' => 'Minnesota', 
      'MS' => 'Mississippi', 
      'MO' => 'Missouri', 
      'MT' => 'Montana', 
      'NE' => 'Nebraska', 
      'NV' => 'Nevada', 
      'NJ' => 'New Jersey', 
      'NH' => 'New Hampshire', 
      'NM' => 'New Mexico', 
      'NY' => 'New York', 
      'NC' => 'North Carolina', 
      'ND' => 'North Dakota', 
      'OH' => 'Ohio', 
      'OK' => 'Oklahoma', 
      'OR' => 'Oregon', 
      'PA' => 'Pennsylvania', 
      'RI' => 'Rhode Island', 
      'SC' => 'South Carolina', 
      'SD' => 'South Dakota', 
      'TN' => 'Tennessee', 
      'TX' => 'Texas', 
      'UT' => 'Utah', 
      'VT' => 'Vermont', 
      'VA' => 'Virginia', 
      'WA' => 'Washington', 
      'WV' => 'West Virginia', 
      'WI' => 'Wisconsin', 
      'WY' => 'Wyoming', 
      'AA' => 'Armed Forces-Americas', 
      'AE' => 'Armed Forces-Europe', 
      'AP' => 'Armed Forces-Pacific',
   )); 
} // usi_form_select_state();

function usi_form_skip_captcha($form) {   
   $time = (int)hexdec(substr(uniqid(), 0, 8)) - (int)hexdec(substr($form->lock->uniq, 0, 8));
   $reason = (($form->minimum_time < $time) ? null : 'time ' . $time . '/' . $form->minimum_time);
   if (!$reason) foreach ($form->pages as $page_id => $page) {
      if (isset($page->fields)) foreach ($page->fields as $field) {
         if ('input' == $field->field_type) {
            if ($field->is_leave_blank_field) {
               if ('' != $field->post_value) $reason = 'blank ' . $field->field_id;
            }
         }
      }
   }
   if (!$reason) {
      $sql = 'SELECT * FROM ' . USI_DBS_PRFX . '_USI_bad_ip_addresses' .
         " WHERE (`lower_ip` <= '{$form->lock->ip}') AND (`upper_ip` >= '{$form->lock->ip}') AND (`is_active` = 1)";
      $results = $form->dbs->query($sql); 
      if ($results) if (0 < $form->dbs->num_rows) $reason = 'bad_ip ' . $form->lock->ip;
   }
   if ($reason) {
      $MYSQL_lock = $form->dbs->real_escape_string($form->lock->get_unique());
      $MYSQL_form_id = $form->dbs->real_escape_string($form->form_id);
      $MYSQL_reason = $form->dbs->real_escape_string($reason);
      $sql = 'INSERT INTO ' . USI_DBS_PRFX . '_USI_form_captchas (`lock`, `form_id`, `reason`)' .
         " VALUES ('$MYSQL_lock', '$MYSQL_form_id', '$MYSQL_reason')";
      $form->dbs->query($sql); 
   } else {
      $form->submit_field->next_page_id = $form->success_page_id;
   }
   return(true);
} // usi_form_skip_captcha();

function usi_is_date_valid(&$date, $month_day_year_format = true) {
   if ('' == $date) return(true);
   $value = trim($date);
   if ((USI_DATE_ALPHA == $value) || (USI_DATE_OMEGA == $value)) return(true);
   // If first part 12 or less, assume month ordinal given;
   $months = usi_form_select_month();
   $offset = 0;
   while (ctype_digit($value[$offset])) $offset++;
   if ($offset) {
      $month = (int)substr($value, 0, $offset);
      if ((1 <= $month) && (12 >= $month)) $value = $months[sprintf('%02d', $month)] . ' ' . substr($value, $offset);
   }
   if (0 == ($temp = strtotime($value))) return(false);
   $second = date('s', $temp);
   $minute = date('i', $temp);
   $hour = date('H', $temp);
   $day = date('d', $temp);
   $month = date('m', $temp);
   $year = date('Y', $temp);
   if ($month_day_year_format) {
      $date = $months[sprintf('%02d', $month)] . sprintf(' %d, %d', $day, $year);
   } else {
      $date = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
   }
   return(true);
} // usi_is_date_valid();

function usi_is_email_valid($value) {
   if ('' == $value) return(true);
   if (preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $value)) {
      return(true);
   }
   return(false);
} // usi_is_email_valid();

function usi_is_phone_us_valid(&$value) {
   if ('' == $value) return(true);
   $extension = '';
   $extension_ok = true;
   $phone = str_replace(' ', '', strtolower($value));
   $offset = strpos($phone, 'x');
   if ($offset > 0) {
      $extension = ' ' . substr($phone, $offset);
      if (!preg_match('/^\sx\d+$/', $extension)) $extension_ok = false;
      $phone = substr($phone, 0, $offset);
   }
   $valid = '/^(\()?(\d{3})(\)|\.|\-)?(\d{3})(\.|\-)?(\d{4})$/';
   if ($extension_ok && preg_match($valid, $phone, $matches)) {
      $value = '(' . $matches[2] . ')' . $matches[4] . '-' . $matches[6] . $extension;
      return(true);
   } else {
      return(false);
   }
} // usi_is_phone_us_valid();

function usi_is_zip_us_valid($value) {
   if ('' == $value) return(true);
   return(preg_match('/^([0-9]{5})(\-[0-9]{4})?$/', $value, $matches));
} // usi_is_zip_us_valid();

function usi_this_url() { 
   if (isset($_SERVER['HTTPS'])) {
      $port = 443;
      $url = 'https://';
   } else {
      $port = 80;
      $url = 'http://';
   }
   $server_port = $_SERVER['SERVER_PORT'];
   return($url . $_SERVER['SERVER_NAME'] . (($port == $server_port) ? '' : ':' . $server_port) . $_SERVER['REQUEST_URI']);
} // usi_this_url();

// --------------------------------------------------------------------------------------------------------------------------- // ?>
