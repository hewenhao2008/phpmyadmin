<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * dumps a database
 *
 * @package PhpMyAdmin
 */

/**
 * Gets some core libraries
 */
require_once 'libraries/common.inc.php';
require_once 'libraries/config/page_settings.class.php';

PMA_PageSettings::showGroup('Export');

$response = PMA_Response::getInstance();
$header   = $response->getHeader();
$scripts  = $header->getScripts();
$scripts->addFile('export.js');

// $sub_part is also used in db_info.inc.php to see if we are coming from
// db_export.php, in which case we don't obey $cfg['MaxTableList']
$sub_part  = '_export';
require_once 'libraries/db_common.inc.php';
$url_query .= '&amp;goto=db_export.php';
require_once 'libraries/db_info.inc.php';

/**
 * Displays the form
 */
$export_page_title = __('View dump (schema) of database');

// exit if no tables in db found
if ($num_tables < 1) {
    PMA_Message::error(__('No tables found in database.'))->display();
    exit;
} // end if

$multi_values  = '<div class="export_table_list_container">';
if (isset($_GET['structure_or_data_forced'])) {
    $force_val = htmlspecialchars($_GET['structure_or_data_forced']);
} else {
    $force_val = 0;
}
$multi_values .= '<input type="hidden" name="structure_or_data_forced" value="' . $force_val . '">';
$multi_values .= '<table class="export_table_select">'
    . '<thead><tr><th></th>'
    . '<th>' . __('Tables') . '</th>'
    . '<th class="export_structure">' . __('Structure') . '</th>'
    . '<th class="export_data">' . __('Data') . '</th>'
    . '</tr><tr>'
    . '<td></td>'
    . '<td class="export_table_name all">' . __('Select all') . '</td>'
    . '<td class="export_structure all"><input type="checkbox" id="table_structure_all" /></td>'
    . '<td class="export_data all"><input type="checkbox" id="table_data_all" /></td>'
    . '</tr></thead>'
    . '<tbody>';
$multi_values .= "\n";

// when called by libraries/mult_submits.inc.php
if (!empty($_POST['selected_tbl']) && empty($table_select)) {
    $table_select = $_POST['selected_tbl'];
}

// Check if the selected tables are defined in $_GET
// (from clicking Back button on export.php)
if (isset($_GET['table_select'])) {
    $_GET['table_select'] = urldecode($_GET['table_select']);
    $_GET['table_select'] = explode(",", $_GET['table_select']);
}
if (isset($_GET['table_structure'])) {
    $_GET['table_structure'] = urldecode($_GET['table_structure']);
    $_GET['table_structure'] = explode(",", $_GET['table_structure']);
}
if (isset($_GET['table_data'])) {
    $_GET['table_data'] = urldecode($_GET['table_data']);
    $_GET['table_data'] = explode(",", $_GET['table_data']);
}

foreach ($tables as $each_table) {
    if (isset($_GET['table_select'])) {
        if (in_array($each_table['Name'], $_GET['table_select'])) {
            $is_checked = ' checked="checked"';
        } else {
            $is_checked = '';
        }
    } elseif (isset($table_select)) {
        if (in_array($each_table['Name'], $table_select)) {
            $is_checked = ' checked="checked"';
        } else {
            $is_checked = '';
        }
    } else {
        $is_checked = ' checked="checked"';
    }
    if (isset($_GET['table_structure'])) {
        if (in_array($each_table['Name'], $_GET['table_structure'])) {
            $str_checked = ' checked="checked"';
        } else {
            $str_checked = '';
        }
    } else {
        $str_checked = $is_checked;
    }
    if (isset($_GET['table_data'])) {
        if (in_array($each_table['Name'], $_GET['table_data'])) {
            $data_checked = ' checked="checked"';
        } else {
            $data_checked = '';
        }
    } else {
        $data_checked = $is_checked;
    }
    $table_html   = htmlspecialchars($each_table['Name']);
    $multi_values .= '<tr>';
    $multi_values .= '<td><input type="checkbox" name="table_select[]"'
        . ' value="' . $table_html . '"' . $is_checked . ' /></td>';
    $multi_values .= '<td class="export_table_name">' . str_replace(' ', '&nbsp;', $table_html) . '</td>';
    $multi_values .= '<td class="export_structure"><input type="checkbox" name="table_structure[]"'
        . ' value="' . $table_html . '"' . $str_checked . ' /></td>';
    $multi_values .= '<td class="export_data"><input type="checkbox" name="table_data[]"'
        . ' value="' . $table_html . '"' . $data_checked . ' /></td>';
    $multi_values .= '</tr>';
} // end for

$multi_values .= "\n";
$multi_values .= '</tbody></table></div>';

$export_type = 'database';
require_once 'libraries/display_export.inc.php';
