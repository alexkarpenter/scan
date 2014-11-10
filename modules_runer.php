<?php
header( 'Content-type: text/html; charset=utf-8;' );
include_once( './protected/config/config.php' );
include_once( './protected/lib/funcs.php' );

if ( !isset( $_POST['modules_data'] ) )
{
    exit();
}

foreach ( $_POST['modules_data'] as $mod_data )
    if ( $_SERVER['HTTP_HOST'] == 'scaner.avia-centr.ru' )
    {
        file_put_contents('step000.txt','fack');
        LoadURL( $mod_data['module_url'], $mod_data, 1, 'scaner_es:9tcpws3r' );
    }
    else
        LoadURL( $mod_data['module_url'], $mod_data );

?>