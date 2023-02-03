<?php
// error_reporting(1);
// ini_set('display_errors', true);
// error_reporting();

/**
 * Simpler Zeichenzähler für Beiträge
 *  https://github.com/katjalennartz
 */


// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


function count_characters_info()
{
  global $lang;
  $lang->load('count_characters');
  return array(
    "name" => "{$lang->count_characters_name}",
    "description" => "{$lang->count_characters_descr}",
    "website" => "https://github.com/katjalennartz",
    "author" => "risuena",
    "authorsite" => "https://github.com/katjalennartz",
    "version" => "1.0",
    "compatibility" => "18*"
  );
}

function count_characters_is_installed()
{
  global $db;
  $query = $db->simple_select("settinggroups", "gid", "name='count_characters'");
  $gid = $db->fetch_field($query, "gid");
  if ($gid) {
    return true;
  }
  return false;
}

function count_characters_install()
{
  global $db, $cache, $lang;
  $lang->load('count_characters');
  //reste löschen wenn was schiefgegangen ist
  count_characters_uninstall();

  $settings_group = array(
    "name" => "count_characters",
    "title" => "{$lang->count_characters_setting_title}",
    "description" => "{$lang->count_characters_setting_descr}",
    "disporder" => "0",
    "isdefault" => "0",
  );
  $gid = $db->insert_query("settinggroups", $settings_group);

  $setting_array = array(
    'count_characters_fids' => array(
      'title' => 'Foren',
      'description' => 'In welchen Foren soll der Zeichenzähler angezeigt werden - Elternforen reichen.',
      'optionscode' => 'forumselect',
      'value' => '', // Default
      'disporder' => 1
    ),
    'count_characters_lengthoption' => array(
      'title' => 'Soll eine Mindestpostinglänge angezeigt werden',
      'description' => 'Wie groß ist die Mindestzeichenanzahl, die ihr im Forum habt? ',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 2
    ),
    'count_characters_length' => array(
      'title' => 'Mindestzeichenanzahl',
      'description' => 'Wie groß ist die Mindestzeichenanzahl, die ihr im Forum habt? ',
      'optionscode' => 'numeric',
      'value' => '1500', // Default
      'disporder' => 3
    ),
    'count_characters_typ' => array(
      'title' => 'Zählart',
      'description' => 'Was und wie soll gewählt werden? Mehrfachauswahl möglich.',
      'optionscode' => "checkbox\nspace=Mit Leerzeichen\nwords=Wörter\nno=Ohne Leerzeichen",
      'value' => 'space,words',
      'disporder' => 4
    ),
    'count_characters_htmlsetting' => array(
      'title' => 'Html',
      'description' => 'Soll HTML-Code mitgezählt werden?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 5
    ),
  );

  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid']  = $gid;
    $db->insert_query('settings', $setting);
  }

  // TEMPLATES ERSTELLEN
  // Templates nur global

  // HAUPTSEITE
  $insert_array = array(
    'title'        => 'count_characters_counter',
    'template'    => $db->escape_string('<div class="cc_container" id="cc_container">{$cc_chars}{$cc_words}{$cc_nospace}</div>'),
    'sid'        => '-1',
    'dateline'    => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);

  rebuild_settings();
  require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
  // CSS	
  $css = array(
    'name' => 'count_characters.css',
    'tid' => 1,
    'attachedto' => '',
    "stylesheet" =>    '/* CSS Charcounter*/
    .cc_toless {
      color:  red;
      font-weight: 700;
  }
  ',
    'cachefile' => $db->escape_string(str_replace('/', '', 'count_characters.css')),
    'lastmodified' => time()
  );
  $sid = $db->insert_query("themestylesheets", $css);
  $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

  $tids = $db->simple_select("themes", "tid");
  while ($theme = $db->fetch_array($tids)) {
    update_theme_stylesheet_list($theme['tid']);
  }
}

function count_characters_uninstall()
{
  global $db;

  // EINSTELLUNGEN LÖSCHEN
  $db->delete_query('settings', "name LIKE 'count_characters%'");
  $db->delete_query('settinggroups', "name = 'count_characters'");
  rebuild_settings();

  // TEMPLATES LÖSCHEN
  $db->delete_query("templates", "title LIKE '%count_characters%'");

  // CSS LÖSCHEN
  require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
  $db->delete_query("themestylesheets", "name = 'count_characters.css'");
  $query = $db->simple_select("themes", "tid");
  while ($theme = $db->fetch_array($query)) {
    update_theme_stylesheet_list($theme['tid']);
  }
}

function count_characters_activate()
{
  global $db;


  require MYBB_ROOT . "/inc/adminfunctions_templates.php";

  find_replace_templatesets('newreply', '#' . preg_quote('{$codebuttons}') . '#', '{$codebuttons}{$charactercounter}');
  find_replace_templatesets('editpost', '#' . preg_quote('{$codebuttons}') . '#', '{$codebuttons}{$charactercounter}');
  find_replace_templatesets('newthread', '#' . preg_quote('{$codebuttons}') . '#', '{$codebuttons}{$charactercounter}');
  find_replace_templatesets('newreply', '#' . preg_quote('{$footer}') . '#', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>{$footer}');
  find_replace_templatesets('editpost', '#' . preg_quote('{$footer}') . '#', '<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>{$footer}');
  find_replace_templatesets('showthread_quickreply', '#' . preg_quote('<div class="editor_control_bar"') . '#', '{$charactercounter}<div class="editor_control_bar"');
  find_replace_templatesets('showthread_quickreply', '#' . preg_quote('</form>') . '#', '</form><script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>');
}

function count_characters_deactivate()
{
  global $db;
  require MYBB_ROOT . "/inc/adminfunctions_templates.php";

  find_replace_templatesets('newreply', '#' . preg_quote('{$charactercounter}') . '#', '');
  find_replace_templatesets('editpost', '#' . preg_quote('{$charactercounter}') . '#', '');
  find_replace_templatesets('newthread', '#' . preg_quote('{$charactercounter}') . '#', '');
  find_replace_templatesets('newreply', '#' . preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>') . '#', '');
  find_replace_templatesets('editpost', '#' . preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>') . '#', '');
  find_replace_templatesets('newthread', '#' . preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>') . '#', '');
  find_replace_templatesets('showthread_quickreply', '#' . preg_quote('{$charactercounter}') . '#', '');
  find_replace_templatesets('showthread_quickreply', '#' . preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>') . '#', '');
}

//ADMIN CP STUFF
$plugins->add_hook("admin_config_settings_change", "count_characters_settings_change");
// Set peeker in ACP
function count_characters_settings_change()
{
  global $db, $mybb, $count_characters_settings_peeker;

  $result = $db->simple_select("settinggroups", "gid", "name='count_characters'", array("limit" => 1));
  $group = $db->fetch_array($result);
  $count_characters_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}

$plugins->add_hook("admin_settings_print_peekers", "count_characters_settings_peek");
// Add peeker in ACP
function count_characters_settings_peek(&$peekers)
{
  global $count_characters_settings_peeker;

  if ($count_characters_settings_peeker) {
    // Peeker for legth  settings
    $peekers[] = 'new Peeker($(".setting_count_characters_lengthoption"), $("#row_setting_count_characters_length"),/1/,true)';
  }
}

//MAIN FUNCTION
$plugins->add_hook('newthread_start', 'count_characters_main');
$plugins->add_hook('newreply_end', 'count_characters_main');
$plugins->add_hook("showthread_start", "count_characters_main");
$plugins->add_hook('editpost_end', 'count_characters_main');

function count_characters_main()
{
  global $db, $mybb, $templates, $fid, $charactercounter;

  //Einstellungen bekommen
  //in welchem forum befinden wir uns?
  $thisfid = $fid;
  //foren aus einstellungen
  $fids = $mybb->settings['count_characters_fids'];
  //was soll wie gezählt werden?
  $mybb->settings['count_characters_typ'];
  if (strpos("," . $mybb->settings['count_characters_typ'] . ",", "space") !== false) {
    $cc_chars = "<span id=\"cc_chars\"></span>";
  }
  if (strpos("," . $mybb->settings['count_characters_typ'] . ",", "no") !== false) {
    $cc_nospace = "<span id=\"cc_nospace\"></span>";
  }
  if (strpos("," . $mybb->settings['count_characters_typ'] . ",", "words") !== false) {
    $cc_words = "<span id=\"cc_words\"></span>";
  }

  if ($fids == -1) {
    //zeige counter immer
    eval("\$charactercounter = \"" . $templates->get("count_characters_counter") . "\";");
  } elseif ($fids == "") {
    //zeige counter gar nicht
    $charactercounter = "";
  } else {
    //zeige wenn im richtigen forum
    $parentlist = get_parent_list($thisfid);
    if (strpos($fids, ",") === false) {

      if (strpos("," . $parentlist . ",", "," . $fid . ",") !== false) {
        eval("\$charactercounter = \"" . $templates->get("count_characters_counter") . "\";");
      }
    } else {
      $fidarray = explode(",", $fids);

      foreach ($fidarray as $id) {
        if (strpos("," . $parentlist . ",", "," . trim($id) . ",") !== false) {
          $fidflag = true;
        }
      }
      if ($fidflag) {
        eval("\$charactercounter = \"" . $templates->get("count_characters_counter") . "\";");
      }
    }
  }
}

$plugins->add_hook('xmlhttp', 'character_count_getdata');
function character_count_getdata()
{
  global $db, $mybb;
  $htmlsetting = $mybb->settings['count_characters_htmlsetting'];
  if ($mybb->get_input('action') == 'get_cc_settings') {
    $mybb->settings['count_characters_lengthoption'];

    if ($mybb->settings['count_characters_lengthoption']) {
      $mindest = $mybb->settings['count_characters_length'];

      $data[] = array('length' => $mindest, 'htmlsetting' => $htmlsetting);
    } else {
      $data[] = array('length' => 0, 'htmlsetting' => $htmlsetting);
    }

    echo json_encode($data);
    exit;
  }
}
