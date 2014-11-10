<?php
header( 'Content-type: text/html; charset=utf-8;' );
include_once( './protected/config/config.php' );
include_once( './protected/lib/funcs.php' );
CreateTables();
$cmd = @$_REQUEST['cmd'];


define( 'DEBUG', false );
//dump( $_POST['fields'] );
//dump( $_POST['agencies'] );
//dump( $_POST['routes'] );

if ( !isset( $_POST['fields'] ) || !isset( $_POST['agencies'] ) || !isset( $_POST['routes'] ) )
{
    echo 0;
    exit();
}


$user = explode( ',', $_POST['routes'] );
$user = $user[0];
$user = explode( '-', $user );
$user = $user[10];
$_POST['user'] = $user;

$search_id = AddSearchHistory( $_POST );

if ( DEBUG )
    $search_id = 600;

if ( !$search_id )
    exit();

$search_request = array(
    'user' => $user,
    'routes' => explode(',', $_POST['routes'] ),
    'agencies' => explode(',', $_POST['agencies'] ),
    'fields' => explode(',', $_POST['fields'] )
);

$search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
$cookies_dir = $search_dir . 'cookies/';
//dump( $cfg, $search_dir, $cookies_dir );

@mkdir(  $search_dir, 0777 );

@mkdir(  $cookies_dir, 0777 );

$modules_data = array();
foreach ( $search_request['routes'] as $r_key => $route )
{
    list( $dep_iata, $arr_iata, $dep_date, $arr_date, $adults, $children, $infants, $one_way, $direct, $class, $user, $route_id ) = explode( '-', $route );

    foreach ( $search_request['agencies'] as $a_key => $agency )
    {
        $uniq_search_id = str_replace( '.', '_', $search_id . '_' . $route_id . '_' . $agency );
        $min_info_file = $search_dir . $uniq_search_id  . '_min_info.txt';
        $full_info_file = $search_dir . $uniq_search_id . '_full_info.txt';
        $cookie_file = $cookies_dir . $uniq_search_id . '_cookie.txt';
        $error = 0;
        $current_process = 0;
        $price = 0;
        $url = '';
        $info = '{}';
        $module_file = $cfg['AGENCIES_MODULES_DIR'] . $agency . '.php';
        $module_url = $cfg['MODULES_PATH'] . $agency . '.php';


        if ( DEBUG )
        {
            $price = mt_rand( 10000, 150000 );
            $url = 'http://www.google.ru/';
            $current_process =  in_array( $agency, array( 'anywayanyday.com', 'aviakassa.ru', 'pososhok.ru', 'davs.ru', 'svyaznoy.travel', 'agent.ru', 'onetwotrip.com' ) ) ? 1 : 100;
            $current_process =  in_array( $agency, array( 'davs.ru' ) ) ? 1 : 100;
            $info = "{}";
        }

        if ( !file_exists( $module_file ) || ( DEBUG && !in_array( $agency, array( 'davs.ru' ) ) ) )
        {
            $error = 1;
            $current_process = 100;
        }

        $min_info_file_default_settings  = '{"e":"' . $error . '","cp":' . $current_process . ',"p":"' . $price . '","u":"' . $url . '"}';
        $full_info_file_default_settings = '{"c":"' . $uniq_search_id . '","e":"' . $error . '","cp":' . $current_process . ',"p":"' . $price . '","u":"' . $url . '","info":' . $info . '}';
        file_put_contents( $min_info_file,  $min_info_file_default_settings );
        file_put_contents( $full_info_file, $full_info_file_default_settings );
        file_put_contents( $cookie_file, '' );

        //Запуск обработчика агенства
        if ( !$error )
        {
            /*
            $data['search_id'] = $search_id;
            $data['route_id'] = $route_id;
            $data['route'] = $route;
            $data['agency'] = $agency;
            $data['fields'] = $_POST['fields'];

            if ( $current_process != 100 )
                LoadURL( $module_url, $data );
            */
            $data['search_id'] = $search_id;
            $data['route_id'] = $route_id;
            $data['route'] = $route;
            $data['agency'] = $agency;
            $data['fields'] = $_POST['fields'];
            $data['module_url'] = $module_url;
            $modules_data[] = $data;
        }
    }
}
file_put_contents( $search_dir . 'search_request.txt', serialize( $search_request ) );
echo $search_id;

if ( $_SERVER['HTTP_HOST'] == 'scaner.avia-centr.ru' )
    LoadURL( $cfg['WEB_PATH'] . 'modules_runer.php', array( 'modules_data' => $modules_data ), 1, 'scaner_es:9tcpws3r' );
else
    LoadURL( $cfg['WEB_PATH'] . 'modules_runer.php', array( 'modules_data' => $modules_data ) );
?>