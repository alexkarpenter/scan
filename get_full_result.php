<?php
header( 'Content-type: text/html; charset=utf-8;' );
include_once( './protected/config/config.php' );
include_once( './protected/lib/funcs.php' );
CreateTables();
$cmd = @$_REQUEST['cmd'];

if ( !isset( $_GET['id'] ) )
{
    echo '{}';
    exit();
}
$id = $_GET['id'];
$search_id = explode( '_', $_GET['id'] );
$search_id = $search_id[0];

$search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
$full_info_file = $search_dir . $id . '_full_info.txt';
echo file_get_contents($full_info_file);

?>