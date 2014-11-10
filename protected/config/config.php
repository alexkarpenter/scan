<?
putenv("COMSPEC=localdev");


$cfg = array();

if ( getenv( 'COMSPEC' ) != '' )
    $cfg['CUR_DIR'] = realpath( dirname( __FILE__ ) . '/../../' ) . '/';
else
    $cfg['CUR_DIR'] = realpath( dirname( __FILE__ ) . '/../../' ) . '/';
$cfg['PROTECTED_DIR'] = $cfg['CUR_DIR'] . 'protected/';
$cfg['AGENCIES_MODULES_DIR'] = $cfg['PROTECTED_DIR'] . 'agencies_modules/';
$cfg['SEARCH_HISTORY_DIR'] = $cfg['AGENCIES_MODULES_DIR'] . 'search_history/';
/*
echo $_SERVER['DOCUMENT_ROOT'];
echo '<br>';
echo $cfg['CUR_DIR'];
echo '<br>';
echo realpath( dirname( __FILE__ ) . '../../../' );
echo '<br>';
echo realpath( dirname( __FILE__ ) . '/../../' );
echo '<br>';
echo dirname( __FILE__ );
echo '<br>';
*/
if ( getenv( 'COMSPEC' ) != '' )
{
    $cfg['SUBFOLDER'] = str_replace( $_SERVER['DOCUMENT_ROOT'], '', str_replace( '\\', '/', $cfg['CUR_DIR'] ) );
    if( strpos( $cfg['SUBFOLDER'], '/' ) !==0 )
        $cfg['SUBFOLDER'] = '/' . $cfg['SUBFOLDER'];
}
else
{
    $cfg['SUBFOLDER'] = str_replace( $_SERVER['DOCUMENT_ROOT'], '', str_replace( '\\', '/', $cfg['CUR_DIR'] ) );
    if( strpos( $cfg['SUBFOLDER'], '/' ) !==0 )
        $cfg['SUBFOLDER'] = '/' . $cfg['SUBFOLDER'];
}

$cfg['WEB_PATH'] = 'http://' . $_SERVER['HTTP_HOST'] . $cfg['SUBFOLDER'];


//if ( getenv( 'COMSPEC' ) == '' )
//    $cfg['WEB_PATH'] = 'http://' . $_SERVER['HTTP_HOST'] . $cfg['SUBFOLDER'] . '~ifrie352/';


$cfg['MODULES_PATH'] = $cfg['WEB_PATH'] . 'protected/agencies_modules/';


$cfg['IMGS_PATH'] = $cfg['WEB_PATH'] . 'imgs/';
$cfg['JS_PATH'] = $cfg['WEB_PATH'] . 'js/';
$cfg['CSS_PATH'] = $cfg['WEB_PATH'] . 'css/';

if ( getenv( 'COMSPEC' ) != '' )
{
    //Настройки на локальном компютере
    $cfg['DB_HOST'] = 'localhost';
    $cfg['DB_USER'] = 'root';
    $cfg['DB_PASS'] = 'зфыьныйд';
    $cfg['DB_NAME'] = 'oncosmos_avia1';
} else
{
    //Настройки для` сервака! рс
    $cfg['DB_HOST'] = 'oncosmos.mysql';
    $cfg['DB_USER'] = 'oncosmos_scaner';
    $cfg['DB_PASS'] = 'apy9tqnk';
    $cfg['DB_NAME'] = 'oncosmos_avia';

    //Настройки для` сервака! бл
    $cfg['DB_HOST'] = 'localhost';
    $cfg['DB_USER'] = 'root';
    $cfg['DB_PASS'] = 'aviascanner';
    $cfg['DB_NAME'] = 'aviascanner';

    //Настройки для` сервака! св
    $cfg['DB_HOST'] = 'localhost';
    $cfg['DB_USER'] = 'fortuneman';
    $cfg['DB_PASS'] = 'tabuma137';
    $cfg['DB_NAME'] = 'avia';

    //Настройки для` сервака! св
    $cfg['DB_HOST'] = 'localhost';
    $cfg['DB_USER'] = 'fortu264_avia';
    $cfg['DB_PASS'] = 'avia';
    $cfg['DB_NAME'] = 'fortu264_avia';

    $cfg['DB_HOST'] = 'oncosmos.mysql';
    $cfg['DB_USER'] = 'oncosmos_scaner';
    $cfg['DB_PASS'] = 'apy9tqnk';
    $cfg['DB_NAME'] = 'oncosmos_avia';

    $cfg['DB_HOST'] = 'a75092.mysql.mchost.ru';
    $cfg['DB_USER'] = 'a75092_000x';
    $cfg['DB_PASS'] = 'Jxba25V6j4';
    $cfg['DB_NAME'] = 'a75092_000x';


    $cfg['DB_HOST'] = 'localhost';
    $cfg['DB_USER'] = 'ifrie352_exspert';
    $cfg['DB_PASS'] = 'exspert';
    $cfg['DB_NAME'] = 'ifrie352_exspert';


    $cfg['DB_HOST'] = 'oncosmos.mysql';
    $cfg['DB_USER'] = 'oncosmos_scaner';
    $cfg['DB_PASS'] = 'davij1op';
    $cfg['DB_NAME'] = 'oncosmos_avia1';



}

//Названия таблиц
$cfg['TBL_USER_ROUTES'] = 'user_routes';
$cfg['TBL_AIRCODES'] = 'tbl_aircodes';
$cfg['TBL_SEARCH_HISTORY'] = 'search_history';

$cfg['STOP_FILE'] = 'stop.txt';
$cfg['PROFILLER'] = 0;
$cfg['dbi'] = NULL;
define( 'OUR_SITE', 'aviakassa_ru' );

