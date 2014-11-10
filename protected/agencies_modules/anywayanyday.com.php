<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 30.03.14
 * Time: 14:02
 */
//******************************************************************************************
//Стандартная инициализация для всех модулей
file_put_contents('step00.txt','fack');
ignore_user_abort(true);
ini_set('dislpay_errors', 1);
error_reporting( E_ALL );
ini_set("soap.wsdl_cache_enabled", "1");
set_time_limit( 0 );
header( 'Content-type: text/html; charset=utf-8;' );


define( 'DEMO_MODE' , 0 );

include_once( '../../protected/config/config.php' );
include_once( '../../protected/lib/funcs.php' );
include_once( '../../protected/lib/avia_modules.php' );
file_put_contents('step0.txt','fack');
if ( !isset( $_REQUEST['search_id'] ) )
    exit();

$search_id = $_REQUEST['search_id'];
$route_id = $_REQUEST['route_id'];
$agency = $_REQUEST['agency'];
$fields = $_REQUEST['fields'];
// route=TAS-MOW-15.05.2014-20.05.2014-1-1-1-0-1-1-Саша-1
/* Пояснение к параметру route после разрыва его на отрезки с помощю -
 * Порядковый номер    значение      описание
 * 0                   TAS           аэропорт вылета
 * 1                   MOW           аэропорт прибытия
 * 2                   15.05.2014    дата вылета
 * 3                   20.05.2014    дата прибытия
 * 4                   1             количество взрослых
 * 5                   1             количество детей
 * 6                   1             количество младенцев
 * 7                   0             в одну сторону
 * 8                   1             прямые рейсы
 * 9                   1             клсса полёта: 0 - эконом; 1 - бизнес; 2 - первый;
 * 10                  Саша          имя пользователя который создал данный маршрут
 * 11                  1             ID маршрута в таблице  user_routes 
 * *
 */


$route = explode( '-', $_REQUEST['route'] );



$search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
$cookies_dir = $search_dir . 'cookies/';
$uniq_search_id = str_replace( '.', '_', $search_id . '_' . $route_id . '_' . $agency );
$cookie_file = $cookies_dir . $uniq_search_id . '_cookie.txt';
file_put_contents( $cookie_file, '' );
$mod_name = 'mod_' . preg_replace( '`[-\.]`si', '_', $agency );

$min_info_file = $search_dir . $uniq_search_id . '_min_info.txt';
$full_info_file = $search_dir . $uniq_search_id . '_full_info.txt';

$route = array(
    'departure_city' => $route[0], // MOW
    'arrival_city' => $route[1],   // TAS
    'departure_date' => $route[2], // 15.05.2014
    'arrival_date' => $route[3],   // 20.05.2014
    'adults' => $route[4],         // 1
    'children' => $route[5],       // 0
    'infants' => $route[6],        // 0
    'one_way' => $route[7],        // 0
    'direct' => $route[8],         // 0
    'class' => $route[9],          // 0 - эконом; 1 - бизнес; 2 - первый;
    'user' => $route[10],          // Саша
    'mod_name' => $mod_name        // mod_aviakassa_ru
);


//file_put_contents( 'test.txt', $_REQUEST['route'] );

//******************************************************************************************
//Логика работы скрипта

//1. Мулти курл
$mch = curl_multi_init();

//Запускаем обработчик авиа модуля если он существует
$mod_data = $route;
$mod_data['fly_from_iata'] = $route['departure_city'];
$mod_data['fly_to_iata'] = $route['arrival_city'];
$mod_data['date1'] = $route['departure_date'];
$mod_data['date2'] = $route['arrival_date'];
$mod_data['only_direct'] = $route['direct'];
$mod_data['fly_from_city'] = ''; //$fly_from_data['city_rus'],
$mod_data['fly_from_airport'] = ''; //$fly_from_data['name_rus'],
$mod_data['fly_to_city'] = '';//$fly_to_data['city_rus'],
$mod_data['fly_to_airport'] = '';//$fly_to_data['name_rus']


$ch = $mod_name( 'setup', $mod_data, $cookie_file );
file_put_contents('step1.txt','fack');
curl_multi_add_handle( $mch, $ch );
file_put_contents('step2.txt','fack');
$cx = 0;
do
{
    file_put_contents('step3.txt','fack');
    curl_multi_select( $mch );
    file_put_contents('step4.txt','fack');
    while( ( ( $mrc = curl_multi_exec( $mch, $running ) ) == CURLM_CALL_MULTI_PERFORM  ) );
    while ( ( $info = curl_multi_info_read( $mch ) ) && ( $info['msg'] == CURLMSG_DONE ) )
    {
        file_put_contents('step5.txt','fack');
        $cx++;

        //Curl
        $ch = $info['handle'];

        //Содержимое модуля
        $cont = curl_multi_getcontent( $ch );

        //Полные данные модуля
        $modules_data[$mod_name] = array( 'error' => curl_error( $ch ), 'name' => $mod_name, 'mch' => $mch, 'info' => curl_getinfo( $ch ), 'content' => $cont, 'fields' => $fields );

        //Remove module handle from multi curl
        curl_multi_remove_handle( $mch, $ch );

        //Close curl handler
        curl_close( $ch );

        //Запускаем обработчик авиа модуля для парсинга
        $run_new_process = $mod_name( 'parse', $mod_data, $cookie_file );

        //Удаляем контент
        if ( isset( $modules_data[$mod_name]['content'] ) )
        {
            unset( $modules_data[$mod_name]['content'] );
            unset( $modules_data[$mod_name]['info'] );
            unset( $modules_data[$mod_name]['mch'] );
            unset( $modules_data[$mod_name]['error'] );
        }

        //Add new proccess url if exists
        if ( is_resource( $run_new_process ) )
        {
            curl_multi_add_handle( $mch, $run_new_process );
            do
            {
                file_put_contents('step6.txt','fack');
                $mrc = curl_multi_exec( $mch, $running );
            } while ( $mrc == CURLM_CALL_MULTI_PERFORM );
        }
    }

} while ( ( $running > 0 )  );
file_put_contents('step7.txt','fack');

$min_info = json_decode( file_get_contents( $min_info_file ), 1 );
$full_info = json_decode( file_get_contents( $full_info_file ) , 1 );
if ( $min_info['cp'] != 100 )
{
    SaveMinInfo( 3, 100, 0, '' );
    SaveFullInfo( 3, 100, 0, '', '{}' );

}

?>