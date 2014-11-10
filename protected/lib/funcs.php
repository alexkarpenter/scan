<?


function dump() {
    //return 'Отключен вывод';
    echo "<pre>";
    print_r( func_get_args() );
    echo "</pre>";
}

function is_work() {
    global $cfg;
    if ( !file_exists( $cfg['STOP_FILE'] ) )
        return false;
    return trim( implode( '', file( $cfg['STOP_FILE'] ) ) );
}

function start_work() {
    global $cfg;
    $fid = fopen( $cfg['STOP_FILE'], 'w' );
    fwrite( $fid , '1' );
    fclose( $fid );
}


function stop_work() {
    global $cfg;
    $fid = fopen( $cfg['STOP_FILE'], 'w' );
    fwrite( $fid , '0' );
    fclose( $fid );
}

function getvar( $v ) {
    if ( ini_get( 'magic_quotes_gpc' ) )
        return stripslashes( $v ) ;
    return  $v;
}

function GetPhones()
{
    global $db_array;
    $f = 'auto.ru_phones.txt';
    if ( !file_exists( $f ) || !filesize( $f ) )
        return array();

    $db_array = array_map( create_function( '$v', 'return trim($v);' ), explode( "\r\n", trim( file_get_contents( $f ) ) ) );
}

function AddPhone( $phone )
{
    global $db_array;
    $f = 'auto.ru_phones.txt';
    AppendFile( $f, $phone . "\r\n" );
    $db_array[] = trim( $phone );
}

function PhoneExists( $phone )
{
    global $db_array;
    return ( array_search( $phone, $db_array ) !== false );

}

function AppendFile( $file_name, $content ) {
    $fid = fopen( $file_name , "ab+" );
    fwrite( $fid, $content );
    fclose( $fid );
}

function SaveFile( $file_name, $content ) {

    $fid = fopen( $file_name , 'w' );

    fwrite( $fid, $content );

    fclose( $fid );

}

function date_() {
    return '<b>' . date( 'Y.m.d H:i:s' ) . '</b>';
}

function message( $str ) {
    echo $str;
    flush();
}

function finish()
{
    die();
}
function InitCurl( &$curl, $ibma = 1, $lac = 2, $ct = 30 ) {
    $curl->INTERVAL_BEETWEN_ATTEMPTS = $ibma;
    $curl->LOAD_ATTEMPTS_COUNT = $lac;
    $curl->CURLOPT_TIMEOUT = $ct;
    $curl->show_error = true;
}

/**
 * function DBConnect - соеденяется с базой данных
 *
 * @return;
 */
function DBConnect()
{
    global $cfg;

    if ( !is_resource( $cfg['dbi'] = mysql_connect( $cfg['DB_HOST'], $cfg['DB_USER'], $cfg['DB_PASS'] ) ) )
        die( 'Ошибка подключения к базе данных' );

    if ( !mysql_select_db( $cfg['DB_NAME'], $cfg['dbi'] ) )
        die( 'Ошибка подлкючения к базе. Невозможно выбрать базу!' );

    /*
        DBQuery('SET NAMES "utf8"' );
        DBQuery("set character_set_client='utf8'" );
        DBQuery("set character_set_results='utf8'" );
        DBQuery("set collation_connection='cp1251_general_ci'" );
        */
        DBQuery('SET NAMES "utf8"' );
        DBQuery("set character_set_client='utf8'" );
        DBQuery("set character_set_results='utf8'" );
        DBQuery("set collation_connection='utf8_general_ci'" );


}

/**
 * function DBQuery - делает запрос к базе данных
 *
 * @param in string $sql - запрос которого надо выполнить
 * @return resource or null - возвращяет резултат выполнения запроса в виде ресурса илиже null в случай ошибки
 */
function DBQuery( $sql )
{
    global $cfg;

    if ( !is_resource( $cfg['dbi'] ) )
        DBConnect();

    if ( $cfg['PROFILLER'] )
        GetWorkTime( 1 );
    $r = @mysql_query( $sql, $cfg['dbi'] );

    if ( $cfg['PROFILLER'] )
    {
        if ( !isset( $cfg['PROFILLER_INFO'] ) )
            $cfg['PROFILLER_INFO'] = array();
        $cfg['PROFILLER_INFO'][] = array( 'work_time'=> GetWorkTime(), 'sql' => $sql );
    }

    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    return $r;
}
function CheckDirection( $data, $id = 0 )
{
    global $cfg;
    $data['user'] = strtoupper( $data['user'] );
    $hash = md5( serialize($data) );
    $sql = 'SELECT id FROM `' . $cfg['TBL_USER_ROUTES'] . '` WHERE hash = \'' . mysql_escape_string( $hash ) . '\' ' . ( $id ? ' AND id <> \'' . $id . '\'' : '');
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    if ( !mysql_num_rows( $res ) )
        return 0;

    $id = mysql_fetch_assoc( $res );

    return $id['id'];

}

function AddDirection( $data )
{
    global $cfg;
    $data1 = $data;
    $data1['user'] = strtoupper( $data1['user'] );
    $hash = md5( serialize($data1) );

    $sql = 'INSERT INTO  `' . $cfg['TBL_USER_ROUTES'] . '`  SET';
    $sql .=' departure_city = \'' . mysql_escape_string( $data['departure_city'] ) . '\',';
    $sql .=' arrival_city = \'' . mysql_escape_string( $data['arrival_city'] ) . '\',';
    $sql .=' departure_date = \'' . mysql_escape_string( $data['departure_date'] ) . '\',';
    $sql .=' arrival_date = \'' . mysql_escape_string( $data['arrival_date'] ) . '\',';
    $sql .=' adults = \'' . mysql_escape_string( $data['adults'] ) . '\',';
    $sql .=' children = \'' . mysql_escape_string( $data['children'] ) . '\',';
    $sql .=' infants = \'' . mysql_escape_string( $data['infants'] ) . '\',';
    $sql .=' one_way = \'' . mysql_escape_string( $data['one_way'] ) . '\',';
    $sql .=' direct = \'' . mysql_escape_string( $data['direct'] ) . '\',';
    $sql .=' class = \'' . mysql_escape_string( $data['class'] ) . '\',';
    $sql .=' user = \'' . mysql_escape_string( $data['user'] ) . '\',';
    $sql .=' create_date = \'' . mysql_escape_string( time() ) . '\',';
    $sql .=' hash = \'' . mysql_escape_string( $hash ) . '\'';

    DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return 0;
    }

    return mysql_insert_id( $cfg['dbi'] );
}

function EditDirection( $data )
{
    global $cfg;
    $sql = 'UPDATE `' . $cfg['TBL_DIRECTIONS'] . '`  SET';
    $sql .='  aircode_id = \'' . mysql_escape_string( $data['new_aircode_id'] ) . '\'';
    $sql .=' WHERE aircode_id = ' . $data['old_aircode_id'];


    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return 0;
    }

    return 1;
}


function GetDirections( $user )
{
    global $cfg;
    $sql = 'SELECT * FROM `' . $cfg['TBL_USER_ROUTES'] . '` WHERE user = \'' . mysql_escape_string( $user ) . '\' ORDER BY create_date';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    if ( !mysql_num_rows( $res ) )
        return array();

    $ar = array();
    while ( $r = mysql_fetch_assoc( $res ) )
        $ar[$r['id']] = $r;

    mysql_free_result( $res );

    $ar2 = array();
    foreach ( $ar as $k => $v )
    {
        $ar2[$k] = $v;
        $sql = 'SELECT id, city_rus, name_rus, iata_code, country_rus FROM `' . $cfg['TBL_AIRCODES'] . '` WHERE id IN( ' .  $v['departure_city'] . ',' . $v['arrival_city'] . ' )';

        $res = DBQuery( $sql );
        while ( $r1 = mysql_fetch_assoc( $res ) )
        {
            if ( $v['departure_city'] == $r1['id'] )
            {
                $ar2[$k]['dep_city'] = $r1['city_rus'];
                $ar2[$k]['dep_name'] = $r1['name_rus'];
                $ar2[$k]['dep_iata'] = $r1['iata_code'];
                $ar2[$k]['dep_country'] = $r1['country_rus'];
            }
            if ( $v['arrival_city'] == $r1['id'] )
            {
                $ar2[$k]['arr_city'] = $r1['city_rus'];
                $ar2[$k]['arr_name'] = $r1['name_rus'];
                $ar2[$k]['arr_iata'] = $r1['iata_code'];
                $ar2[$k]['arr_country'] = $r1['country_rus'];
            }
        }
    }

    mysql_free_result( $res );
    return $ar2;

}

function GetDirect( $id )
{
    global $cfg;
    $sql = 'SELECT tb1.* FROM `' . $cfg['TBL_AIRCODES'] . '` tb1  WHERE tb1.id = ' . $id . ' LIMIT 1';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    if ( !mysql_num_rows( $res ) )
        return array();

    return mysql_fetch_assoc( $res );
}

function DeleteDirs( $dirs )
{
    global $cfg;
    $sql = 'DELETE FROM `' . $cfg['TBL_USER_ROUTES'] . '` WHERE id IN (' . $dirs . ')';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    return true;
}
function SaveDirs( $data )
{
    global $cfg;
    $ok = false;
    foreach ( $data as $k => $v )
    {
        list( $one_way, $route_id, $dep_date, $arr_date ) = $v;
        //dump( $one_way, $route_id, $dep_date, $arr_date );
        if ( !$route_id || ( !$one_way && ( !$dep_date || !$arr_date ) ) || ( $one_way && !$dep_date ) )
            continue;

        $sql = 'UPDATE `' . $cfg['TBL_USER_ROUTES'] . '` SET departure_date = \'' . mysql_escape_string( $dep_date ) . '\' ' . ( !$one_way ? ' , arrival_date = \'' . mysql_escape_string( $arr_date ) . '\'' : '' ) . ' WHERE id IN (' . $route_id . ') ';
        $res = DBQuery( $sql );
        //dump( $sql );
        if ( mysql_error( $cfg['dbi'] ) )
        {
            return false;
        }
        $ok = true;
    }
    return $ok;
}


function SaveDirs2( $dirs )
{
    global $cfg;

    $sql = 'UPDATE `' . $cfg['TBL_DIRECTIONS'] . '` SET checked = 0';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }
    if ( !empty( $dirs ) )
    {
        $sql = 'UPDATE `' . $cfg['TBL_DIRECTIONS'] . '` SET checked = 1 WHERE aircode_id IN (' . $dirs . ')';
        $res = DBQuery( $sql );
        if ( mysql_error( $cfg['dbi'] ) )
        {
            die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
            return NULL;
        }

    }
    return true;
}

function SearchDirection( $name, $id = 0 )
{
    global $cfg;
    $sql = 'SELECT * FROM `' . $cfg['TBL_AIRCODES'] . '` WHERE city_rus LIKE \'' . mysql_escape_string( $name ) . '%\' OR iata_code LIKE \'' . mysql_escape_string( $name ) . '%\' OR name_rus LIKE \'' . mysql_escape_string( $name ) . '%\' OR  name_eng LIKE \'' . mysql_escape_string( $name ) . '%\' OR  city_eng LIKE \'' . mysql_escape_string( $name ) . '%\' OR country_rus LIKE \'' . mysql_escape_string( $name ) . '%\' OR country_eng LIKE \'' . mysql_escape_string( $name ) . '%\' ' . ( $id ? ' AND id <> \'' . $id . '\'' : '') . ' LIMIT 25';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    if ( !mysql_num_rows( $res ) )
        return array();

    $ar = array();
    while ( $r = mysql_fetch_assoc( $res ) )
        $ar[$r['id']] = $r;

    return $ar;

}

function GetDirectionsList( )
{
    global $cfg;
    $sql = 'SELECT * FROM `' . $cfg['TBL_AIRCODES'] . '` ';
    $res = DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return NULL;
    }

    if ( !mysql_num_rows( $res ) )
        return array();

    $ar = array();
    while ( $r = mysql_fetch_assoc( $res ) )
        $ar[$r['id']] = $r;

    return $ar;

}


function IsLoadedURL( $url ) {

    $sql = 'SELECT murl FROM tbl_loaded WHERE murl = \'' . md5( mysql_escape_string( $url ) ) . '\' LIMIT 1';

    $res = DBQuery( $sql );

    //Если произошла ошибка базы!
    if ( mysql_error() ) {
        message('<br> Ошибка базы! ' . mysql_error() . ' Линия: ' . __LINE__ . '<br>' );
        return true;
    }

    if ( mysql_num_rows( $res ) )
        return mysql_fetch_assoc( $res );
    else
        return false;
}

function AddLoadedURL( $url, $post_id ) {

    $sql = 'INSERT INTO tbl_loaded SET murl = \'' . md5( mysql_escape_string( $url ) ) . '\', post_id = ' . intval( $post_id );

    $res = DBQuery( $sql );

    //Если произошла ошибка базы!
    if ( mysql_error() ) {
        message('<br> Ошибка базы! ' . mysql_error() . ' Линия: ' . __LINE__ . '<br>' );
        return false;
    }
    return true;
}

function CreateLoadedTable() {

    $sql = 'CREATE TABLE IF NOT EXISTS `tbl_loaded` (
		  `id` int(11) NOT NULL auto_increment,
		  `post_id` int(11) NOT NULL default \'0\',
		  `murl` varchar(32) NOT NULL default \'\',
		  PRIMARY KEY  (`id`),
		  KEY `murl` (`murl`)
		  ) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT=\'Таблица загруженных урл \' AUTO_INCREMENT=1
    ';

    $res = DBQuery( $sql );

}
function AddSearchHistory( $data )
{
    global $cfg;

    $sql = 'INSERT INTO  `' . $cfg['TBL_SEARCH_HISTORY'] . '`  SET';
    $sql .=' `user` = \'' . mysql_escape_string( $data['user'] ) . '\',';
    $sql .=' routes = \'' . mysql_escape_string( $data['routes'] ) . '\',';
    $sql .=' agencies = \'' . mysql_escape_string( $data['agencies'] ) . '\',';
    $sql .=' `fields` = \'' . mysql_escape_string( $data['fields'] ) . '\',';
    $sql .=' start_timestamp = \'' . mysql_escape_string( time() ) . '\'';

    DBQuery( $sql );
    if ( mysql_error( $cfg['dbi'] ) )
    {
        die( 'Ошибка выполнения запроса к базе, в файле: ' . __FILE__ . ' на линии ' . __LINE__ . '<br> в запросе: ' . $sql . '<br> С текстом ошибки:' . mysql_error( $cfg['dbi'] ) );
        return 0;
    }

    return mysql_insert_id( $cfg['dbi'] );
}


function CreateTables()
{
    global $cfg;
    $sql = "CREATE TABLE IF NOT EXISTS `" . $cfg['TBL_USER_ROUTES'] . "` (
    		  `id` INT NOT NULL AUTO_INCREMENT ,
              `departure_city` VARCHAR( 255 ) NOT NULL ,
              `arrival_city` VARCHAR( 255 ) NOT NULL ,
              `departure_date` VARCHAR( 255 ) NOT NULL ,
              `arrival_date` VARCHAR( 255 ) NOT NULL ,
              `adults` VARCHAR( 10 ) NOT NULL ,
              `children` VARCHAR( 10 ) NOT NULL ,
              `infants` VARCHAR( 10 ) NOT NULL ,
              `one_way` VARCHAR( 10 ) NOT NULL ,
              `direct` VARCHAR( 10 ) NOT NULL ,
              `class` varchar(20) NOT NULL default '',
              `user` VARCHAR( 255 ) NOT NULL ,
              `create_date` INT NOT NULL ,
              `hash` varchar(32) NOT NULL default '',
              PRIMARY KEY ( `id` ),
              KEY `hash` (`hash`)
            ) CHARACTER SET = utf8 COMMENT = 'Список маршрутов';
	";

    $res = DBQuery( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS `" . $cfg['TBL_SEARCH_HISTORY'] . "` (
              `id` int(11) NOT NULL auto_increment,
              `user` varchar(255) NOT NULL default '',
              `routes` text NOT NULL,
              `agencies` text NOT NULL,
              `fields` tinytext NOT NULL,
              `results` MEDIUMTEXT NOT NULL,
              `start_timestamp` int(11) NOT NULL default '0',
              `end_timestamp` int(11) NOT NULL default '0',
              PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='История поисков для пользовател?' AUTO_INCREMENT=1
	";

    $res = DBQuery( $sql );

}


/***********************************************************************************
Функция img_resize(): генерация thumbnails
Параметры:
$src             - имя исходного файла
$dest            - имя генерируемого файла
$width, $height  - ширина и высота генерируемого изображения, в пикселях
Необязательные параметры:
$rgb             - цвет фона, по умолчанию - белый
$quality         - качество генерируемого JPEG, по умолчанию - максимальное (100)
 ***********************************************************************************/

function RatioResizeImg( $src, $dest, $width, $height, $rgb=0xFFFFFF, $quality = 100 ) {

    if ( !file_exists( $src ) )
        return false;

    $size = getimagesize($src);

    if ( $size === false )
        return false;

    // Определяем исходный формат по MIME-информации, предоставленной
    // функцией getimagesize, и выбираем соответствующую формату
    // imagecreatefrom-функцию.
    $format = strtolower( substr( $size['mime'], strpos( $size['mime'], '/' ) + 1 ) );
    $icfunc = "imagecreatefrom" . $format;
    if ( !function_exists( $icfunc ) )
        return false;

    $x_ratio = $width / $size[0];
    $y_ratio = $height / $size[1];

    $ratio       = min( $x_ratio, $y_ratio );
    $use_x_ratio = ( $x_ratio == $ratio );

    $new_width   = $use_x_ratio  ? $width  : floor( $size[0] * $ratio );
    $new_height  = !$use_x_ratio ? $height : floor( $size[1] * $ratio );
    $new_left    = $use_x_ratio  ? 0 : floor( ( $width - $new_width ) / 2 );
    $new_top     = !$use_x_ratio ? 0 : floor( ( $height - $new_height ) / 2 );

    $isrc = $icfunc( $src );
    $idest = imagecreatetruecolor( $width, $height );

    imagefill( $idest, 0, 0, $rgb );
    imagecopyresampled( $idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1] );

    imagejpeg( $idest, $dest, 100 );
//  imagejpeg( $idest );

    imagedestroy( $isrc );
    imagedestroy( $idest );
    return true;

}

/**
 * function CreateDirectoriesTree - создает дерево каталогов из второго переданного параметра в каталоге переданного в первом параметре!
 *
 * @param in string $create_where - путь существуешему каталоге где будет построена дерево каталогов
 * @param in string $directories_tree - строка вида /ad/de/fr/ из которого и будет создана дерево каталогов в каталоге $create_where
 * @return boolean - возвращяет true если все каталоги созданы иначе false
 */
function CreateDirectoriesTree( $create_where, $directories_tree )
{
    if ( !is_dir( $create_where ) )
    {
        echo ( 'Директория ' . $create_where . ' не существует!'  );
        return false;
    }
    //Нормализация путей
    $directories_tree = preg_replace( '`^[/\\\](.+?)`si', '$1', $directories_tree );
    $directories_tree = preg_replace( '`(.+?)[/\\\]$`si', '$1', $directories_tree );
    $create_where = preg_replace( '`(.+?)[^/\\\]$`si', '$1\\', $create_where );
    $directories_tree = preg_split('`[/\\\]`si', $directories_tree );

    for ( $path = '',$i = 0; $i < sizeof( $directories_tree ); $i++ )
    {
        $path .= $directories_tree[$i] . '/';
        if ( !is_dir( $c = $create_where . $path ) )
            mkdir( $c, 0777 );

        @chmod( $c, 0777 );

    }

    if ( is_dir( $c ) )
        return true;
    else
        return false;
}
function cdate( $date )
{
    $date = '30 июня 23:41';
    $months = array( 'янв', 'фев', 'март', 'апр', 'май', 'июн', 'июл', 'авгу', 'сент', 'окт', 'ноя', 'дек' );
    $date = preg_replace( '`<[^>]+>`si', ' ', $date );
//	dump( $date );
    $date = trim( preg_replace( '`\s+`si', ' ', $date ) );
    $rr = explode( ' ', $date );
//	dump( $rr  );

    if ( sizeof( $rr ) == 2 )
    {
        if ( $rr[0] == 'Сегодня' )
            $date = date( 'Y-m-d ' ) . $rr[1] . ':00';
        else
            $date = date( 'Y-m-d ', time() - 86400 ) . $rr[1] . ':00';
    } else
    {

        foreach ( $months as $mi => $m )
            if ( preg_match( '`^' . $m . '`si', trim( $rr[1] ) ) )
                break;
        $mi++;
        if ( strlen( $mi ) == 1 )
            $mi = '0' . $mi;

        $date = date('Y-') . $mi . '-' . trim( $rr[0] ) . ' ' . $rr[2] . ':00';
    }

    return  strtotime( $date );
}
function cdate2( $date )
{
    $date = '30 июня 23:41';
    $months = array( 'янв', 'фев', 'март', 'апр', 'май', 'июн', 'июл', 'авгу', 'сент', 'окт', 'ноя', 'дек' );
    $date = preg_replace( '`<[^>]+>`si', ' ', $date );
//	dump( $date );
    $date = trim( preg_replace( '`\s+`si', ' ', $date ) );
    $rr = explode( ' ', $date );


    foreach ( $months as $mi => $m )
        if ( preg_match( '`^' . $m . '`si', trim( $rr[1] ) ) )
            break;
    $mi++;
    if ( strlen( $mi ) == 1 )
        $mi = '0' . $mi;

    $date = trim( $rr[0] ) . '.' . $mi . '.' . date('Y');

    return  $date;
}


function GetStat( &$items_count, $page = 1, $per_page = 10 )
{
    global $cfg;
    $sql = 'SELECT SQL_CALC_FOUND_ROWS tb1.* FROM `' . $cfg['TBL_DB'] . '` tb1, `tbl_loaded` tb2 WHERE tb1.id = tb2.post_id ORDER BY tb1.id DESC LIMIT ' . ( $page - 1 ) * $per_page . ', ' . $per_page;;
    $res = DBQuery( $sql );
    $items = array();

    if ( !mysql_num_rows( $res ) )
        return $items;

    while( $r = mysql_fetch_assoc( $res ) )
        $items[] = $r;

    $sql = 'SELECT FOUND_ROWS() AS cnt';
    $res = DBQuery( $sql );

    $items_count = mysql_fetch_assoc( $res );
    $items_count = $items_count['cnt'];

    return $items;

}


function Pager( $url, $items_count, $items_per_page, $page = 1, $page_refers_per_page = 5 )
{
    $li = $url . '&page=';
    $pages = '<style>.gray{color:#999999;font-size:11px;}a.pgr,a.pgr:visited{color:#000000;font-size:12px;}.darkgray{border:1px solid #000000;background-color:#9CABB7;color:#FFFFFF;padding:0px;font-size:12px;}.prgt{font-size:13px;color:#E46C6D;</style>&nbsp;';
    $pages_count = ( ( $items_count % $items_per_page != 0 ) ) ? floor( $items_count / $items_per_page ) + 1 : floor( $items_count / $items_per_page );
    $start_page = ( $page - $page_refers_per_page <= 0  ) ? 1 : $page - $page_refers_per_page + 1;
    $page_refers_per_page_count = ( ( $page - $page_refers_per_page < 0 ) ? $page : $page_refers_per_page ) + ( ( $page + $page_refers_per_page > $pages_count ) ? ( $pages_count - $page )  :  $page_refers_per_page - 1 );

    $pages = '<ul class="pagination">';

    if ( $start_page > 1 )
    {
        $pages .= '<li><a href="' . $li . '1">1</a></li>';
        $pages .= '<li><a href="' . $li . ( $start_page - 1 ) . '">...</a></li>';
    }

    for ( $index = -1; ++$index <= $page_refers_per_page_count-1; )
    {
        if ( $index + $start_page == $page )
            $pages .= '<li class="active"><a href="#"> ' . ( $start_page + $index ) . '</a></li>';
        else
            $pages .= '<li><a href="' . $li . ( $start_page + $index ) . '">' . ( $start_page + $index ) . '</a></li>';
    }

    if ( $page + $page_refers_per_page <= $pages_count )
    {
        $pages .= '<li><a href="' . $li . ( $start_page + $page_refers_per_page_count ) . '">...</a></li>';
        $pages .= '<li><a href="' . $li . $pages_count . '">' . $pages_count . '</a></li>';
    }

    $pages .= '</ul>';

    if ( $pages_count <= 1 )
        $pages = '';

    return $pages;

}
function utow( $s )
{
    return u2w( $s, $to = "w" );
    //return iconv( 'utf-8', 'windows-1251', $s );
    return iconv( 'utf-8', 'windows-1251//IGNORE', $s );
}
function wtou( $s )
{
    return iconv( 'windows-1251', 'utf-8//IGNORE', $s );
}

/**
 * function u2w - Перекодирует данные поступившые как utf8 в windows-1251
 *
 * @param in string $str - параметр для перекодировки
 * @return string - Возвразщяет перекодированную строку параметра $str в кодировке windows-1251
 */

function u2w( $str, $to = "w" ) {
    $outstr = '';
    $recode = array();
    $recode['k'] = array(
        0x2500,0x2502,0x250c,0x2510,0x2514,0x2518,0x251c,0x2524,
        0x252c,0x2534,0x253c,0x2580,0x2584,0x2588,0x258c,0x2590,
        0x2591,0x2592,0x2593,0x2320,0x25a0,0x2219,0x221a,0x2248,
        0x2264,0x2265,0x00a0,0x2321,0x00b0,0x00b2,0x00b7,0x00f7,
        0x2550,0x2551,0x2552,0x0451,0x2553,0x2554,0x2555,0x2556,
        0x2557,0x2558,0x2559,0x255a,0x255b,0x255c,0x255d,0x255e,
        0x255f,0x2560,0x2561,0x0401,0x2562,0x2563,0x2564,0x2565,
        0x2566,0x2567,0x2568,0x2569,0x256a,0x256b,0x256c,0x00a9,
        0x044e,0x0430,0x0431,0x0446,0x0434,0x0435,0x0444,0x0433,
        0x0445,0x0438,0x0439,0x043a,0x043b,0x043c,0x043d,0x043e,
        0x043f,0x044f,0x0440,0x0441,0x0442,0x0443,0x0436,0x0432,
        0x044c,0x044b,0x0437,0x0448,0x044d,0x0449,0x0447,0x044a,
        0x042e,0x0410,0x0411,0x0426,0x0414,0x0415,0x0424,0x0413,
        0x0425,0x0418,0x0419,0x041a,0x041b,0x041c,0x041d,0x041e,
        0x041f,0x042f,0x0420,0x0421,0x0422,0x0423,0x0416,0x0412,
        0x042c,0x042b,0x0417,0x0428,0x042d,0x0429,0x0427,0x042a
    );
    $recode['w'] = array(
        0x0402,0x0403,0x201A,0x0453,0x201E,0x2026,0x2020,0x2021,
        0x20AC,0x2030,0x0409,0x2039,0x040A,0x040C,0x040B,0x040F,
        0x0452,0x2018,0x2019,0x201C,0x201D,0x2022,0x2013,0x2014,
        0x0000,0x2122,0x0459,0x203A,0x045A,0x045C,0x045B,0x045F,
        0x00A0,0x040E,0x045E,0x0408,0x00A4,0x0490,0x00A6,0x00A7,
        0x0401,0x00A9,0x0404,0x00AB,0x00AC,0x00AD,0x00AE,0x0407,
        0x00B0,0x00B1,0x0406,0x0456,0x0491,0x00B5,0x00B6,0x00B7,
        0x0451,0x2116,0x0454,0x00BB,0x0458,0x0405,0x0455,0x0457,
        0x0410,0x0411,0x0412,0x0413,0x0414,0x0415,0x0416,0x0417,
        0x0418,0x0419,0x041A,0x041B,0x041C,0x041D,0x041E,0x041F,
        0x0420,0x0421,0x0422,0x0423,0x0424,0x0425,0x0426,0x0427,
        0x0428,0x0429,0x042A,0x042B,0x042C,0x042D,0x042E,0x042F,
        0x0430,0x0431,0x0432,0x0433,0x0434,0x0435,0x0436,0x0437,
        0x0438,0x0439,0x043A,0x043B,0x043C,0x043D,0x043E,0x043F,
        0x0440,0x0441,0x0442,0x0443,0x0444,0x0445,0x0446,0x0447,
        0x0448,0x0449,0x044A,0x044B,0x044C,0x044D,0x044E,0x044F
    );
    $recode['i'] = array(
        0x0080,0x0081,0x0082,0x0083,0x0084,0x0085,0x0086,0x0087,
        0x0088,0x0089,0x008A,0x008B,0x008C,0x008D,0x008E,0x008F,
        0x0090,0x0091,0x0092,0x0093,0x0094,0x0095,0x0096,0x0097,
        0x0098,0x0099,0x009A,0x009B,0x009C,0x009D,0x009E,0x009F,
        0x00A0,0x0401,0x0402,0x0403,0x0404,0x0405,0x0406,0x0407,
        0x0408,0x0409,0x040A,0x040B,0x040C,0x00AD,0x040E,0x040F,
        0x0410,0x0411,0x0412,0x0413,0x0414,0x0415,0x0416,0x0417,
        0x0418,0x0419,0x041A,0x041B,0x041C,0x041D,0x041E,0x041F,
        0x0420,0x0421,0x0422,0x0423,0x0424,0x0425,0x0426,0x0427,
        0x0428,0x0429,0x042A,0x042B,0x042C,0x042D,0x042E,0x042F,
        0x0430,0x0431,0x0432,0x0433,0x0434,0x0435,0x0436,0x0437,
        0x0438,0x0439,0x043A,0x043B,0x043C,0x043D,0x043E,0x043F,
        0x0440,0x0441,0x0442,0x0443,0x0444,0x0445,0x0446,0x0447,
        0x0448,0x0449,0x044A,0x044B,0x044C,0x044D,0x044E,0x044F,
        0x2116,0x0451,0x0452,0x0453,0x0454,0x0455,0x0456,0x0457,
        0x0458,0x0459,0x045A,0x045B,0x045C,0x00A7,0x045E,0x045F
    );
    $recode['a'] = array(
        0x0410,0x0411,0x0412,0x0413,0x0414,0x0415,0x0416,0x0417,
        0x0418,0x0419,0x041a,0x041b,0x041c,0x041d,0x041e,0x041f,
        0x0420,0x0421,0x0422,0x0423,0x0424,0x0425,0x0426,0x0427,
        0x0428,0x0429,0x042a,0x042b,0x042c,0x042d,0x042e,0x042f,
        0x0430,0x0431,0x0432,0x0433,0x0434,0x0435,0x0436,0x0437,
        0x0438,0x0439,0x043a,0x043b,0x043c,0x043d,0x043e,0x043f,
        0x2591,0x2592,0x2593,0x2502,0x2524,0x2561,0x2562,0x2556,
        0x2555,0x2563,0x2551,0x2557,0x255d,0x255c,0x255b,0x2510,
        0x2514,0x2534,0x252c,0x251c,0x2500,0x253c,0x255e,0x255f,
        0x255a,0x2554,0x2569,0x2566,0x2560,0x2550,0x256c,0x2567,
        0x2568,0x2564,0x2565,0x2559,0x2558,0x2552,0x2553,0x256b,
        0x256a,0x2518,0x250c,0x2588,0x2584,0x258c,0x2590,0x2580,
        0x0440,0x0441,0x0442,0x0443,0x0444,0x0445,0x0446,0x0447,
        0x0448,0x0449,0x044a,0x044b,0x044c,0x044d,0x044e,0x044f,
        0x0401,0x0451,0x0404,0x0454,0x0407,0x0457,0x040e,0x045e,
        0x00b0,0x2219,0x00b7,0x221a,0x2116,0x00a4,0x25a0,0x00a0
    );
    $recode['d'] = $recode['a'];
    $recode['m'] = array(
        0x0410,0x0411,0x0412,0x0413,0x0414,0x0415,0x0416,0x0417,
        0x0418,0x0419,0x041A,0x041B,0x041C,0x041D,0x041E,0x041F,
        0x0420,0x0421,0x0422,0x0423,0x0424,0x0425,0x0426,0x0427,
        0x0428,0x0429,0x042A,0x042B,0x042C,0x042D,0x042E,0x042F,
        0x2020,0x00B0,0x00A2,0x00A3,0x00A7,0x2022,0x00B6,0x0406,
        0x00AE,0x00A9,0x2122,0x0402,0x0452,0x2260,0x0403,0x0453,
        0x221E,0x00B1,0x2264,0x2265,0x0456,0x00B5,0x2202,0x0408,
        0x0404,0x0454,0x0407,0x0457,0x0409,0x0459,0x040A,0x045A,
        0x0458,0x0405,0x00AC,0x221A,0x0192,0x2248,0x2206,0x00AB,
        0x00BB,0x2026,0x00A0,0x040B,0x045B,0x040C,0x045C,0x0455,
        0x2013,0x2014,0x201C,0x201D,0x2018,0x2019,0x00F7,0x201E,
        0x040E,0x045E,0x040F,0x045F,0x2116,0x0401,0x0451,0x044F,
        0x0430,0x0431,0x0432,0x0433,0x0434,0x0435,0x0436,0x0437,
        0x0438,0x0439,0x043A,0x043B,0x043C,0x043D,0x043E,0x043F,
        0x0440,0x0441,0x0442,0x0443,0x0444,0x0445,0x0446,0x0447,
        0x0448,0x0449,0x044A,0x044B,0x044C,0x044D,0x044E,0x00A4
    );
    $and = 0x3F;
    for ( $i = 0; $i < strlen( $str ); $i++ ) {
        $letter = 0x0;
        $octet = array();
        $octet[0] = ord( $str[$i] );
        $octets = 1;
        $andfirst = 0x7F;
        if ( ( $octet[0] >> 1 ) == 0x7E ) {
            $octets = 6;
            $andfirst = 0x1;
        } elseif ( ( $octet[0] >> 2 ) == 0x3E ) {
            $octets = 5;
            $andfirst = 0x3;
        } elseif ( ( $octet[0] >> 3 ) == 0x1E ) {
            $octets = 4;
            $andfirst = 0x7;
        } elseif ( ( $octet[0] >> 4 ) == 0xE ) {
            $octets = 3;
            $andfirst = 0xF;
        } elseif ( ( $octet[0] >> 5 ) ==0x6 ) {
            $octets = 2;
            $andfirst = 0x1F;
        }
        $octet[0] = $octet[0] & $andfirst;
        $octet[0] = $octet[0] << ( $octets - 1 ) * 6;
        $letter += $octet[0];
        for ( $j = 1; $j < $octets; $j++ ) {
            $i++;
            $octet[$j] = ord( $str[$i] ) & $and;
            $octet[$j] = $octet[$j] << ( $octets - 1 - $j ) * 6;
            $letter += $octet[$j];
        }
        if ( $letter < 0x80 )
            $outstr .= chr( $letter );
        else
            if ( in_array( $letter, $recode[$to] ) )
                $outstr .= chr( array_search( $letter, $recode[$to] ) + 128 );
    }
    return $outstr;
}

function convert_to ( $source, $target_encoding )
{
    // detect the character encoding of the incoming file
    $encoding = mb_detect_encoding( $source, "auto" );

    // escape all of the question marks so we can remove artifacts from
    // the unicode conversion process
    $target = str_replace( "?", "[question_mark]", $source );

    // convert the string to the target encoding
    $target = mb_convert_encoding( $target, $target_encoding, $encoding);

    // remove any question marks that have been introduced because of illegal characters
    $target = str_replace( "?", "", $target );

    // replace the token string "[question_mark]" with the symbol "?"
    $target = str_replace( "[question_mark]", "?", $target );

    return $target;
}

function json_clean_decode($json, $assoc = false, $depth = 512, $options = 0) {
    // search and remove comments like /* */ and //
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);

    if(version_compare(phpversion(), '5.4.0', '>=')) {
        $json = json_decode($json, $assoc, $depth, $options);
    }
    elseif(version_compare(phpversion(), '5.3.0', '>=')) {
        $json = json_decode($json, $assoc, $depth);
    }
    else {
        $json = json_decode($json, $assoc);
    }

    return $json;
}
function json_decode_nice($json, $assoc = FALSE){
    $json = str_replace(array("\n","\r"),"",$json);
    $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json);
    return json_decode($json,$assoc);
}
function removeBOM($str="") {
    if(substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
        $str = substr($str, 3);
    }
    return $str;
}


/**
 * LoadURL загрузка указанного урла
 *
 * @param string $url url которого надо загрузить
 * @param array $data данные передаваемые через пост
 * @param int $timeout время таймаута после которого происходит разрыв соеденения
 * @param string $log_pass логин пароль при необходимости
 * @param int $auth_type тип авторизации
 * @param array $HEADERS http загаловки
 * @param string $cookiejar  файл куки
 * @param string $cookiefile файл куки
 * @return array возвращяет скаченную страницу
 */
function LoadURL( $url, $data, $timeout = 1, $log_pass = '', $auth_type = 0, $HEADERS = array(), $cookiejar = '', $cookiefile = '' )
{
    //$user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.22) Gecko/20110902 Firefox/3.6.22';

    $HEADERS[] = 'Accept: text/html,application/xhtml+xml,application/xml,application/json;q=0.9,*/*;q=0.8';
    $HEADERS[] = 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3';
    $HEADERS[] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7';
    //$HEADERS[] = 'Cookie: BALANCEID=balancer.http2';


    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
    //curl_setopt( $ch, CURLOPT_REFERER, $url );
    //curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_NOBODY, 0 );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $HEADERS );
    curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

    if ( !empty( $log_pass ) )
    {
        if ( $auth_type )
            curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST );

        curl_setopt( $ch, CURLOPT_USERPWD, $log_pass );
    }

    //curl_setopt( $ch, CURLOPT_LOW_SPEED_LIMIT, 5 ); // 5 байт/сек
    //curl_setopt( $ch, CURLOPT_LOW_SPEED_TIME, $timeout ); // таймаут
    if ( sizeof( $data ) )
    {
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, substr( PostDataEncode( $data ), 0, -1 ) );
    }

    if ( $cookiejar )
    {
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookiejar );
        curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookiefile );
    }

    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    $res = curl_exec( $ch );
    $inf = curl_getinfo( $ch );
    $err = curl_error( $ch  );
    curl_close( $ch );

    return array( 'error' => $err, 'info' => $inf, 'content' => $res );
}

/**
 * PostDataEncode  Кодирует данные перед отправкой пост данных.
 *
 * @param array $data  параметры передаваемые через пост
 * @param string $keyprefix  префикс
 * @param string $keypostfix  постфикс
 * @return string  Возвразщяет url кодированную строку параметра $data
 */
function PostDataEncode( $data, $keyprefix = '', $keypostfix = '' )
{

    assert( is_array( $data ) );

    $vars = null;

    foreach ( $data as $key => $value )
    {
        if( is_array( $value ) )
            $vars .= PostDataEncode( $value, $keyprefix . $key . $keypostfix . urlencode( '[' ), urlencode( ']' ) );
        else
            $vars .= $keyprefix . $key . $keypostfix . '=' . (!preg_match( '`^@`si', $value ) ? urlencode( $value ) : $value ) . '&';
    }

    return $vars;
}

function SaveMinInfo( $error, $current_process, $price, $url )
{
    global $cfg;
    static $pp = 0;
    $pp++;
    $search_id = $_REQUEST['search_id'];
    $route_id = $_REQUEST['route_id'];
    $agency = $_REQUEST['agency'];
    $fields = $_REQUEST['fields'];

    $search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
    $uniq_search_id = str_replace( '.', '_', $search_id . '_' . $route_id . '_' . $agency );
    $min_info_file = $search_dir . $uniq_search_id  . '_min_info.txt';

    $s = '{"e":"' . $error . '","cp":' . $current_process . ',"p":"' . $price . '","u":"' . $url . '"}';
    file_put_contents( $min_info_file,  $s );
}

function SaveFullInfo( $error, $current_process, $price, $url, $info )
{
    global $cfg;
    $search_id = $_REQUEST['search_id'];
    $route_id = $_REQUEST['route_id'];
    $agency = $_REQUEST['agency'];
    $fields = $_REQUEST['fields'];

    $search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
    $uniq_search_id = str_replace( '.', '_', $search_id . '_' . $route_id . '_' . $agency );
    $full_info_file = $search_dir . $uniq_search_id . '_full_info.txt';

    $s = '{"c":"' . $uniq_search_id . '","e":"' . $error . '","cp":' . $current_process . ',"p":"' . $price . '","u":"' . $url . '","info":' . $info . '}';
    file_put_contents( $full_info_file,  $s );
    SaveDataTobase( $search_id );

// На выходе мы должны получить данные в формате
/*
    {
        "c"    : "1_1_agent_ru",                                        // $search_id . '_' . $route_id . '_' . $agency
        "e"    : "0",                                                   // 0 - всё ок, 1 - Не работает, 2 - Не найдено
        "cp"   : "100",                                                 // процес хода поиска в процентах
        "p"    : "34232",                                               // цена билета
        "u"    : "http://www.agent.ru/ru/...",                          // URL страницы покупки билета
        "info" :
                {
                    "onw" :[                                            // Данные ТУДА
                                {
                                    "dep"  : "TAS"                      // Аэропорт вылета
                                    "dept" : "Ташкент, Южный"           // Аэропорт вылета в понятном виде
                                    "arr"  : "MOW"                      // Аэропорт прибытия
                                    "arrt" : "Москва, Шереметево"       // Аэропорт прибытия в понятном виде
                                    "dpd"  : "25.07.2014"               // Дата вылета ( Аэропорт вылета )
                                    "dpt"  : "04:45"                    // Время вылета ( Аэропорт вылета)
                                    "ard"  : "28.07.2014"               // Дата прибытия ( Аэропорт прибытия )
                                    "art"  : "14:40"                    // Время прибытия ( Аэропорт прибытия )
                                    "flt"  : "01:35"                    // Время полёта
                                    "wtt"  : "02:30"                    // Время ожидания
                                    "coc"  : "SU"                       // Компания перевозчик
                                    "fli"  : "SU-1423"                  // Номер рейса
                                    "pln"  : "Airbus 312"               // Самолет
                                },
                                ...
                                ...
                            ]
                    ,
                    "bkw" :[                                            // Данные ОБРАТНО
                                {
                                    "dep"  : "TAS"                      // Аэропорт вылета
                                    "dept" : "Ташкент, Южный"           // Аэропорт вылета в понятном виде
                                    "arr"  : "MOW"                      // Аэропорт прибытия
                                    "arrt" : "Москва, Шереметево"       // Аэропорт прибытия в понятном виде
                                    "dpd"  : "25.07.2014"               // Дата вылета ( Аэропорт вылета )
                                    "dpt"  : "04:45"                    // Время вылета ( Аэропорт вылета)
                                    "ard"  : "28.07.2014"               // Дата прибытия ( Аэропорт прибытия )
                                    "art"  : "14:40"                    // Время прибытия ( Аэропорт прибытия )
                                    "flt"  : "01:35"                    // Время полёта
                                    "wtt"  : "02:30"                    // Время ожидания
                                    "coc"  : "SU"                       // Компания перевозчик
                                    "fli"  : "SU-1423"                  // Номер рейса
                                    "pln"  : "Airbus 312"               // Самолет
                                },
                                ...
                                ...
                            ]

                }
    }

*/
}

function GetWorkTime( $set_time = false )
{
    static $works = array();
    list( $usec, $sec ) = explode( " ", microtime() );
    $t = ( (float) $usec + (float) $sec );
    if ( !$set_time )
    {
        if ( !sizeof( $works ) )
            return ( $t );
        else
        {
            $t_ = array_pop( $works );
            return round( $t - $t_, 5 );
        }
    } else
        array_push( $works, $t );
}

function SaveDataTobase( $search_id )
{
    global $cfg;
    $search_dir = $cfg['SEARCH_HISTORY_DIR'] . $search_id . '/';
    $files = glob( $search_dir . $search_id . '_*full_info.txt' );
    $ret = '';
    foreach ( $files as $file )
        $ret .= ( $ret ? ',' : '' ) . file_get_contents( $file );
    $ret = '[' . $ret . ']';

    $sql  ='UPDATE `' . $cfg['TBL_SEARCH_HISTORY'] . '`  SET ';
    $sql .='results = \'' . mysql_escape_string( $ret ) . '\' , end_timestamp = ' . time() . ' ';
    $sql .='WHERE id = ' . $search_id;

    $res = DBQuery( $sql );
}

function GetCityByIATA( $iatas )
{
    global $cfg;
    $sql = 'SELECT city_rus, name_rus, iata_code, country_rus FROM `' . $cfg['TBL_AIRCODES'] . '` WHERE iata_code IN( ' . $iatas . ' )';
    $res = DBQuery( $sql );
    $items = array();

    if ( !mysql_num_rows( $res ) )
        return $items;

    while( $r = mysql_fetch_assoc( $res ) )
        $items[$r['iata_code']] = str_replace('-', '_', $r['city_rus'] ) . ' [<b>' . $r['iata_code'] . '</b>]';


    return $items;
}

function GetUserSearchHistoryList( &$items_count, $user, $page = 1, $per_page = 10 )
{
    global $cfg;
    $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM `' . $cfg['TBL_SEARCH_HISTORY'] . '` WHERE  `user` = \'' . mysql_escape_string( $user ) . '\'  ORDER BY id DESC LIMIT ' . ( $page - 1 ) * $per_page . ', ' . $per_page;
    $res = DBQuery( $sql );
    $items = array();

    if ( !mysql_num_rows( $res ) )
        return $items;

    $iatas = array();
    while( $r = mysql_fetch_assoc( $res ) )
    {
        $items[] = $r;
        $routes = explode( ',', $r['routes'] );
        foreach ( $routes as $rote )
        {
            $d = explode( '-', $rote );
            $iatas[] = $d[0];
            $iatas[] = $d[1];
        }
    }


    $sql = 'SELECT FOUND_ROWS() AS cnt';
    $res = DBQuery( $sql );

    $items_count = mysql_fetch_assoc( $res );
    $items_count = $items_count['cnt'];
    $iatas = array_keys( array_flip( $iatas ) );

    $iatas = GetCityByIATA( '"' . implode( '","', $iatas ) . '"' );

    foreach ( $items as $k => $item )
    {
        $items[$k]['routes_text'] = strtr( $item['routes'], $iatas );
    }


    return $items;

}

function GetUserSearchHistory( $id )
{
    global $cfg;
    $sql = 'SELECT * FROM `' . $cfg['TBL_SEARCH_HISTORY'] . '` WHERE  `id` = \'' . mysql_escape_string( $id ) . '\'  LIMIT 1';
    $res = DBQuery( $sql );
    $items = array();

    if ( !mysql_num_rows( $res ) )
        return $items;

    $iatas = array();
    while( $r = mysql_fetch_assoc( $res ) )
    {
        $items[] = $r;
        $routes = explode( ',', $r['routes'] );
        foreach ( $routes as $rote )
        {
            $d = explode( '-', $rote );
            $iatas[] = $d[0];
            $iatas[] = $d[1];
        }
    }

    $iatas = array_keys( array_flip( $iatas ) );

    $iatas = GetCityByIATA( '"' . implode( '","', $iatas ) . '"' );

    foreach ( $items as $k => $item )
    {
        $items[$k]['routes_text'] = strtr( $item['routes'], $iatas );
    }

    $items = $items[0];

    return $items;

}


function ShowRoutes( $routes )
{
    $routes = explode( ',', $routes );
    $ret = '';

    foreach ( $routes as $ri => $route  )
    {
        list( $dep_iata, $arr_iata, $dep_date, $arr_date, $adults, $children, $infants, $one_way, $direct, $class, $user, $route_id ) = explode( '-', $route );
        $ar = ' <span class="' . ( $one_way ? 'glyphicon glyphicon-arrow-right' : 'glyphicon glyphicon-resize-horizontal' ) . '"></span> ';
        $ret .= '<div class="top_border2">' . $dep_iata . $ar . $arr_iata . '&nbsp;' . $dep_date . ( !$one_way ? ' &#151; ' . $arr_date : '' ) . '</div>';
        if ( $ri >= 2 )
        {
            $ret .= '...<br> и ещё ' . ( sizeof( $routes ) - 3 ) . ' маршрут(ов)';
            return $ret;
        }
    }
    return $ret;
}


function FlushHistoryDirectory( $dir = ''  )
{
    global $cfg;
    if ( !$dir )
        $dir = $cfg['SEARCH_HISTORY_DIR'];
    $dir = preg_replace( '`(.+?)[\\/]$`si', '$1' ,$dir );
    dump( $dir );

    $did = opendir( $dir );
    if ( is_resource( $did ) )
    {
        while ( $r = readdir( $did ) )
        {
            if ( ( $r == '.' ) || ( $r == '..' ) )
                continue;
            if ( is_dir( $dir . '/' . $r  ) )
            {
                dump( $dir . '/' . $r );
                FlushHistoryDirectory( $dir . '/' . $r );
                @rmdir( $dir . '/' . $r  );
                dump( $dir . '/' . $r );

            }
            else
                @unlink( $dir . '/' . $r );
        }
        closedir( $did );
    }

}


?>