<?php // ------------------------------------------------------------------------------------------------------------------------ //

// Error message if cannot find record;
// REsolve if we can use passed in sql, must resolve the primary key build;
// Add database insert;

// Add default value for new form;
// Add page readonly/edit switch for form;

// Add default validation function;
// add e-mail validation and domain name check;
// add help message, regexp validation;
// add password field;
// add file upload field;
// add phone, zipcode, date validation

// add multple submit protection;
// add leave hiden field blank field otherwise robot submit;

/*

Notes:

- You can remove a field from a form but still keep it in your field list by setting 'type'=>'skip';
- The name="field-name" value defaults to the field's array name if 'html_name'=>'field-name' isn't given;

*/

class USI_Form_Solutions_Form {

   const VERSION = '1.1.2 (2019-10-01)';

   protected $action       =  null;
   protected $connection   =  null;
   protected $current_page =  null;
   protected $debug        =  null;
   protected $dbs          =  null;
   protected $error_text   =  null;
   protected $fatal_error  =  null;
   protected $first_page   = 'page 1';
   protected $indent       =  0;
   protected $html_after   = '{i}<!-- Form ------------------------------------------------------------->{n}';
   protected $html_before  = '{n}{i}<!-- Form ------------------------------------------------------------->{n}';
   protected $html_class   =  null;
   protected $html_format  =  true;
   protected $html_id      =  null;
   protected $lock_fail    =  null;  // Submission failed [IP|DATE|HASH];
   protected $lock_info    =  null;  // IP:Date:UniqueID of last submitted form;
   protected $lock_name    =  null;  // $_REQUEST name of lock_text value;
   protected $lock_salt    =  null;  // Hashing salt, should be unique for each form;
   protected $lock_text    =  null;  // Lock_info hashed with lock_salt;
   protected $lock_time    = 'PT1H'; // Time lock valid in DateInterval format, default 1 hour;
   protected $method       = 'POST';
   protected $name         = 'form';
   protected $page_edit    =  false;
   protected $page_save    =  false;
   protected $pages        =  array();
   protected $prefix_class =  null;
   protected $prefix_id    =  null;
   protected $prefix_name  =  null;
   protected $queries      =  null;
   protected $target       =  null;

   function __construct($connection) {
      $this->connection = $connection;
      $this->debug .= print_r($_REQUEST, true) . PHP_EOL;
      $this->lock_name = $this->prefix_name . 'lock';
      $current_time    = new DateTime();
      $remote_addr     = bin2hex(inet_pton($_SERVER['REMOTE_ADDR']));
      if (!empty($_REQUEST[$this->lock_name])) {
         $lock_ip = strtok($_REQUEST[$this->lock_name], ':');
         if ($lock_ip != $remote_addr) {
            $this->lock_fail = 'IP';
         } else {
            $lock_date = strtok(':');
            if ($lock_date < $current_time->format('YmdHis')) {
               $this->lock_fail = 'DATE';
            } else {
               $lock_unique = strtok(':');
               $lock_hash   = strtok(':');
               $this->lock_info = $lock_ip . ':' . $lock_date . ':' . $lock_unique;
               if (sha1($this->lock_info . $this->lock_salt) !== $lock_hash) $this->fail = 'BOGUS';
            }
         }
         if ($this->page_edit) {
            $this->dbs_values_get($queries);
         }
      } else if ($this->queries) {
         $this->dbs_values_get($queries);
      }
      $current_time->add(new DateInterval($this->lock_time));
      $info = $remote_addr . $current_time->format(':YmdHis:') . strtoupper(uniqid());
      $this->lock_text = $info . ':' . sha1($info . $this->lock_salt);
   } // __construct();

   function __destruct() {
      if ($this->debug) usi_log($this->debug);
      $this->dbs_close();
   } // __destruct();

   function dbs_close() {
      if ($this->dbs) {
         @ $this->dbs->close();
         $this->dbs = null;
      }
   } // dbs_close();

   function dbs_save() {
      $fields =& $this->pages['test']['fields'];
      $fields['test']['html'] = 'There was an error';
      $status = $this->dbs_values_update();
/* update status based on dbs status; */
      return(false);
   } // dbs_save();

   function dbs_table_update($page, $queries) {
      foreach ($page['fields'] as $field_name => & $field) {
         if ('skip' == ($type = $field['type'])) continue;
// no old value if form is new input;
         if (!empty($field['old_value']) && ($field['value'] != $field['old_value'])) {
            $table = $field['dbs_table'];
            $queries[$table] .= (!empty($queries[$table]) ? ', ' : '') . '`' . $field['dbs_field'] . '` = "' . $this->dbs->escape_string($field['value']) . '"';
         }
      }
      return($queries);
   } // dbs_table_update();

   function dbs_values_get() {
      if (!$this->dbs) $this->dbs = new mysqli($this->connection['host'], $this->connection['user'], $this->connection['hash'], $this->connection['name']);
      if ($this->dbs->connect_errno) $this->debug .= 'dbs_values_get:error=' . $this->dbs->connect_error;
      foreach ($this->queries as $table => $key) {
         if ('sql' == $table) {
            $sql = $table;
         } else {
            $operator = ' WHERE ';
            $where    = null;
            foreach ($key as $field => $value) {
               $where   .= $operator . '(`' . $field . '` = "' . $this->dbs->escape_string($value) . '")';
               $operator = ' AND ';
            }
            $sql = 'SELECT * FROM `' . $table . '`' . $where . ' LIMIT 1';
            $this->debug .= 'dbs_values_get:where=' . $where . PHP_EOL;
         }
         $this->where[$table] = $where;
         $this->debug .= 'dbs_values_get:sql=' . $sql . PHP_EOL;
         $results = $this->dbs->query($sql, MYSQLI_USE_RESULT);
         if ($this->dbs->errno) $this->debug .= 'dbs_values_get:error=' . $this->dbs->error . PHP_EOL;
         if ($this->dbs->field_count) {
            $row = $results->fetch_assoc();
            $this->debug .= 'dbs_values_get:row=' . print_r($row, true) . PHP_EOL;
            foreach ($this->pages as $page_name => & $page) {
               foreach ($page['fields'] as $field_name => & $field) {
                  if (!empty($field['dbs_field'])) {
                     $dbs_field = $field['dbs_field'];
                     if (!empty($row[$dbs_field])) {
                        switch ($field['sub_type']) {
                        case 'checkbox':
                           break;
                        case 'radio':
                           break;
                        case 'hidden':
                        case 'password':
                        case 'select':
                        case 'submit':
                        case 'text':
                        case 'textarea':
                           $field['value'] = $field['old_value'] = $row[$field['dbs_field']];
                           break;
                        }
                     }
                  }
               }
            }
         }
         @ $results->close();
      }
   } // dbs_values_get();

   function dbs_values_update() {
      $queries = array();
      if ($this->page_save) {
         $queries = $this->dbs_table_update($this->pages[$this->current_page], $queries);
      } else {
         foreach ($this->pages as $page_name => $page) {
            $queries = $this->dbs_table_update($page, $queries);
         }
      }
      foreach ($this->where as $table => $where) {
         if (!empty($queries[$table])) {
            $sql = 'UPDATE `' . $table . '` SET ' . $sql . $queries[$table] . $where;
            $results = $this->dbs->query($sql);
            $this->debug .= 'dbs_table_update:sql=' . $sql . ' results=' . $results . PHP_EOL;
         }
      }
   } // dbs_values_update();

   function process() {

      $page_after = $page_before = '';
      $html = $this->fatal_error;
      if ($this->html_format) {
         $i  = str_repeat(' ', $this->indent);
         $i1 = '{i}';
         $i2 = '{i}  ';
         $n  = PHP_EOL;
      } else {
         $i  = $i1 = $i2 = $n = '';
      }

      try {

         $current_name = $this->prefix_name . 'page';
         $this->current_page = !empty($_REQUEST[$current_name]) ? strip_tags($_REQUEST[$current_name]) : null;
   
         if (!$this->current_page) {
            $this->current_page = $this->first_page;
         } else {
            // Use & to create references so that the field['value'] can be set;
            foreach ($this->pages as $page_name => & $page) {
               foreach ($page['fields'] as $field_name => & $field) {
                  if ('skip' == ($type = $field['type'])) continue;
                  switch ($type) {
                  case 'button':
                  case 'input':
                  case 'select':
                  case 'textarea':
                     $html_name = $this->prefix_name . (empty($field['html_name']) ? $field_name : $field['html_name']);
                     $value = !empty($_REQUEST[$html_name]) ? $_REQUEST[$html_name] : null;
                     if (empty($field['notrim'])) $value = trim($value);
                     $field['value'] = empty($field['nostriptags']) ? strip_tags($value) : $value;
                     break;
                  }
               }
            }
            // Unset the reference forms to prevent array corruption in the following by value usage;
            unset($field);
            unset($page);
            $next_page = null;
            foreach ($this->pages as $page_name => $page) {
               if ($page_name == $this->current_page) {
                  foreach ($page['fields'] as $field_name => $field) {
                     if ('skip' == ($type = $field['type'])) continue;
                     if ((('input' == $type) && ('submit' == $field['sub_type'])) || ('button' == $type)) {
                        if (!empty($field['value']) && ($field['value'] == $field['label'])) {
                           $function = $field['function'];
                           if (method_exists($this, $function)) {
                              if (call_user_func(array($this, $function))) {
                                 $next_page = $field['next_page'];
                                 break 2;
                              } 
                           } else if (!empty($field['next_page'])) {
                              $next_page = $field['next_page'];
                              break 2;
                           }
                        }
                     }
                  }
                  break;
               }
            }
            if ($next_page) $this->current_page = $next_page;
         }
   
         $html = 
            $i1 . '<form action="' . $this->action . '"' . 
            ($this->html_id ? ' id="' . $this->prefix_id . ('"' == $this->html_id ? $this->name : $this->html_id) . '"': '') . 
            ($this->html_class ? ' class="' . $this->prefix_class . ('"' == $this->html_class ? $this->name : $this->html_class) . '"': '') . 
            ' method="' . $this->method . '" name="' . $this->prefix_name . $this->name . '"' .
            ($this->target ? ' target="' . $this->target . '"': '') . '>' . $n .
            $i2 . '<input name="' . $this->lock_name . '" type="hidden" value="' . $this->lock_text . '" />' . $n .
            $i2 . '<input name="' . $current_name . '" type="hidden" value="' . $this->current_page . '" />' . $n;
   
         foreach ($this->pages as $page_name => $page) {
            if ($page_name != $this->current_page) {
               foreach ($page['fields'] as $field_name => $field) {
                  if ('skip' == ($type = $field['type'])) continue;
                  switch ($type) {
                  case 'input':
                  case 'select':
                  case 'textarea':
                     if ('submit' != $field['sub_type']) {
                        $html_name = $this->prefix_name . (empty($field['html_name']) ? $field_name : $field['html_name']);
                        $value = (!empty($field['value']) ? $field['value'] : null);
                        $html .= $i2 . '<input name="' . $html_name . '" type="hidden" value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '" />' . $n;
                     }
                  }
               }
            } 
         }
   
         foreach ($this->pages as $page_name => $page) {
            if ($page_name == $this->current_page) {
               $page_before = (!empty($page['html_before']) ? $page['html_before'] : '');
               $page_after  = (!empty($page['html_after'])  ? $page['html_after']  : '');
               foreach ($page['fields'] as $field_name => $field) {
                  if ('skip' == ($type = $field['type'])) continue;

                  $html5       = !empty($field['html5']);

                  $html_class  = (empty($field['html_class']) ? '' : $field['html_class']);
                  if ('"' == $html_class) $html_class = $field_name;

                  $html_id = (empty($field['html_id']) ? '' : $field['html_id']);
                  if ('"' == $html_id) $html_id = $field_name;

                  $html_error  = (empty($field['error']) ? '' : (empty($field['html_error']) ? '' : $field['html_error']));

                  $html_name   = (empty($field['html_name']) ? $field_name : $field['html_name']);

                  $html_style  = (empty($field['html_style']) ? '' : $field['html_style']);

                  $value       = (!empty($field['value']))       ? $field['value']       : null;
                  $html_label  = (!empty($field['html_label']))  ? $field['html_label']  : null;
                  $html_after  = (!empty($field['html_after']))  ? $field['html_after']  : '';
                  $html_before = (!empty($field['html_before'])) ? str_replace(array('{e}', '{l}'), array($html_error, $html_label), $field['html_before']) : '';

                  $html_common = '';
                  if ($html_id) $html_common .= ' id="' . $this->prefix_id . $html_id . '"';
                  if ($html_class || $html_error) {
                     $html_common .= ' class="';
                     if ($html_class) $html_common .= $this->prefix_class . $html_class . ($html_error ? ' ' : '');
                     if ($html_error) $html_common .= $this->prefix_class . $html_error;
                     $html_common .= '"';
                  }
                  if ($html_name)  $html_common .= ' name="'  . $this->prefix_name . $html_name . '"';
                  if ($html_style) $html_common .= ' style="' . $html_style . '"';

                  if (!empty($field['html_extra'])) $html_common .= ' ' . $field['html_extra'];
                  if (!empty($field['html_js'])) $html_common .= ' ' . $field['html_js'];

                  if ('note' == $type) {
                     if (empty($field['html_inner'])) $html_before = $html_after = '';
                  }
                  $html .= $html_before;
                  switch ($type) {
                  case 'html':
                     $html .= $field['html'];
                     break;
                  case 'button':
                  case 'input':
                     $checked = null;
                     $sub_type = $field['sub_type'];
                     switch ($sub_type) {
                     case 'button':
                     case 'reset':
                     case 'submit':
                        $value = $field['label'];
                        break;
                     case 'checkbox':
                     case 'radio':
                        if ($value == $field_name) {
                           $checked = ' checked="checked"';
                        } else {
                           $value = $field_name;
                        }
                        break;
                     case 'email':
                     case 'number':
                        if ($html5) $sub_type = 'text';
                        break;
                     }
                     $html .= '<' . $type . $html_common . $checked .
                        (isset($field['max']) ? ' max="' . $field['max'] . '"': '') . 
                        (isset($field['min']) ? ' min="' . $field['min'] . '"': '') . 
                        (!empty($field['novalidate']) ? ' formnovalidate="formnovalidate"': '') . 
                        (!empty($field['readonly'])   ? ' readonly': '') . 
                        (!empty($field['required'])   ? ' required': '') . 
                        ( isset($field['step'])       ? ' step="' . $field['step'] . '"': '') .
                        ' type="' . $sub_type . '"' .
                        ($value ? ' value="' . htmlentities($value, ENT_QUOTES, 'UTF-8') . '"': '') .
                        ('button' == $type ? '>' . (!empty($field['html_inner']) ? $field['html_inner'] : '') . '</button>' : ' />');
                     break;
                  case 'note':
                     if (!empty($field['html_inner'])) $html .= $field['html_inner'];
                     break;
                  case 'select':
                     if (!empty($field['indent'])) {
                        $si = str_repeat(' ', $field['indent']);
                        $si2 = $si . '  ';
                        $sn = PHP_EOL;
                     } else {
                        $si = $si2 = $sn = '';
                     }
                     $html .= $si . '<select' . $html_common . '>' . $sn;
                     switch ($field['sub_type']) {
                     default: $list = $field['sub_type']; break;
                     case 'integer': $list = $this->select_integer($field); break;
                     case 'month': $list = $this->select_month(); break;
                     case 'state': $list = $this->select_state(); break;
                     }
                     foreach ($list as $index => $item) {
                        $html .= $si2 . '<option ' . ($index == $value ? 'selected ' : '') . 'value="' . $index . '">' . $item . '</option>' . $sn;
                     }
                     $html .= $si . '</select>';
                     break;
                  case 'textarea':
                     $html .= '<textarea' . $html_common .
                        (isset($field['cols']) ? ' cols="' . $field['cols'] . '"': '') . 
                        (isset($field['rows']) ? ' rows="' . $field['rows'] . '"': '') . 
                        (!empty($field['readonly'])   ? ' readonly': '') . 
                        (!empty($field['required'])   ? ' required': '') . $html_js . '>' .
                        htmlentities($value, ENT_QUOTES, 'UTF-8') . '</textarea>';
                     break;
                  }
                  $html .= $html_after;
               }
               break;
            }
         }
   
         $html .= $i1 . '</form>' . $n;

      } catch(exception $e) {

      }

      return(str_replace(array('{i}', '{n}'), array($i, $n), $this->html_before . $page_before . $html . $page_after . $this->html_after));

   } // process();

   function select_integer($field) {
      $list = array();
      if (!empty($field['missing_index']) && !empty($field['missing_text'])) $list[$field['missing_index']] = $field['missing_text'];
      for ($ith = $field['min']; $ith <= $field['max']; $ith += $field['step']) {
         $list[$ith] = $ith;
      }
      return($list);
   } // select_integer();

   public function select_month($missing_index = '00', $missing_text = '-- Select Month --') {
      return(array(
         $missing_index => $missing_text,
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
   } // select_month();
   
   public function select_state($missing_index = '--', $missing_text = '-- Select State --') {
      return(array(
         $missing_index => $missing_text,
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
   } // select_state();

   function validate() {

   } // validate();

} // Class USI_Form_Solutions_Form;

// --------------------------------------------------------------------------------------------------------------------------- // ?>