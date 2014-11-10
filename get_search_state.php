<?php
header( 'Content-type: text/html; charset=utf-8;' );
include_once( './protected/config/config.php' );
include_once( './protected/lib/funcs.php' );


$cmd = @$_REQUEST['cmd'];

if ( !isset( $_GET['search_id'] ) )
{
    echo '{}';
    exit();
}
$search_id = $_GET['search_id'];

$search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
$cookies_dir = $search_dir . 'cookies/';
$search_request = unserialize( file_get_contents( $search_dir . 'search_request.txt' ) );
$res = '';
foreach ( $search_request['routes'] as $r_key => $route )
{
    list( $dep_iata, $arr_iata, $dep_date, $arr_date, $adults, $children, $infants, $one_way, $direct, $class, $user, $route_id ) = explode( '-', $route );

    foreach ( $search_request['agencies'] as $a_key => $agency )
    {
        $uniq_search_id = str_replace( '.', '_', $search_id . '_' . $route_id . '_' . $agency );
        $min_info_file = $search_dir . $uniq_search_id  . '_min_info.txt';
        $res .= ( !$res ? '' : ',' ) . '{"d":' . file_get_contents( $min_info_file ) . ', "c":"' . $uniq_search_id . '"}';
    }
}
echo '[' .$res . ']';
//SaveDataTobase( $search_id );

?>