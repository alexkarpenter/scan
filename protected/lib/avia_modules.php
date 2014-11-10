<?

define( 'LTIMEOUT', 120 );
define( 'TRY_COUNT', 1 );
include_once realpath( dirname( __FILE__ ) ) . "/aviakassa.ru.php";

/********************************************************/
/* Модуль обработки сайта aviakassa.ru последний старый модуль */
/*                                                      */
/********************************************************/
function mod_aviakassa_ru1( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0;

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.aviakassa.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/aviakassa.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    if ( $mode == 'setup' )
        $step = 0;
    $classes = array( 0 => 'all', 1 => 'business', 2 => 'business' );
    //TAS-MOW-15.05.2014-20.05.2014-1-1-1-0-1-1-Саша-1
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'http://www.aviakassa.ru/index.php?go=search/index' );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
            curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

            $data['adults'] = $mod_data['adults'];
            $data['ajaxSearch'] = 'true';
            $data['back_departure_date'] = $date2;
            $data['children'] = $mod_data['children'];
            $data['class'] = $classes[$mod_data['class']];
            $data['cr_back_iata[0]'] = $fly_to_iata;
            $data['cr_back_iata[1]'] = $fly_from_iata;
            $data['cr_date[0]'] = $date1;
            $data['cr_date[1]'] = $date2;
            $data['cr_iata[0]'] = $fly_from_iata;
            $data['cr_iata[1]'] = $fly_to_iata;
            $data['departure_date'] = $date1;
            $data['gogo'] = 1;
            $data['in_iata'] = $fly_to_iata;
            $data['infants'] = $mod_data['infants'];
            $data['infants_seat'] = 0;
            $data['out_iata'] = $fly_from_iata;
            $data['trip_type'] =  $mod_data['one_way']  ? 'OW' : 'RT';
            $data['direct'] =  $mod_data['direct'] ? 'true' : 'false';

            $step = 1;
            dump( $data, $mod_data );
            //die();

            SetCurlPost( $ch, $data );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/5 ) * 1, 0, '' );
            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );

            if ( ( $step == 1 ) && preg_match( '`\{"uid":"([0-9]+)"\}`si', $modules_data[$mod_name]['content'], $m ) )
            {
                dump( $mod_name, 1 );

                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.aviakassa.ru/flights__search?uid=' . $m[1] . '&rgtype=0' );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
                curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );


                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                $step = 2;
                SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );
                return $ch;

            } else if ( ( $step == 2 ) && preg_match( '`uid=([0-9]+)`si', $modules_data[$mod_name]['info']['url'], $ii ) )
            {
                dump( $mod_name, 2 );

                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.aviakassa.ru/flights__search?rqmix=1&uid='. $ii[1] );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
                curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );


                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();


                $step = 3;
                SaveMinInfo( 0, round( 100/3 ) * 3, 0, '' );
                return $ch;

            }
            else if ( ( $step == 3 ) && preg_match( '`\{"success":([^\}]+)\}`si', $modules_data[$mod_name]['content'], $m ) )
            {
                dump( $mod_name, 3 );

                if ( ( $m[1] == 'true' ) && preg_match( '`uid=([0-9]+)`si', $modules_data[$mod_name]['info']['url'], $ii ) )
                {

                    //Устанавливаем новое соединение курл
                    $ch = curl_init();
                    curl_setopt( $ch,  CURLOPT_URL, 'http://www.aviakassa.ru/aviasearch/'. $ii[1] );
                    curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                    curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt( $ch,  CURLOPT_PROXY, '' );
                    curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
                    curl_setopt(  $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();

                    $step = 4;
                    SaveMinInfo( 0, round( 100/5 ) * 4, 0, '' );

                    return $ch;
                } else
                {
                    $modules_data[$mod_name]['min_price'] = 'nd';
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    return false;
                }

            }
            else
            {

                $step = 5;

                dump( $mod_name, 4 );
                $modules_data[$mod_name]['min_price'] = 0;

                if ( !isset( $loc_cx[$fly_to_iata] ) )
                    $loc_cx[$fly_to_iata] = 0;

                $loc_cx[$fly_to_iata]++;

                if ( $code = trim( GetSegment( $modules_data[$mod_name]['content'], '$.jsonData = ', ";\n}catch(e)" ) ) )
                {
                    $code = json_decode( $code, 1 );

					dump( $code );
//					die();

                    //Берем самую низкую цену
                    $min_price = 1000000;
                    $min = '';
                    if ( sizeof( $code['FT'] ) )
                    {

                        foreach ( $code['FT'] as $k => $v )
                        {
                            $price = preg_replace( '`[^0-9]+`si', '', $code['FT'][$k]['FD']['pr'] );
                            if ( $price < $min_price )
                            {
                                $min_price = $price;
                                $min = $k;
                            }
                        }
/*
                        //Пробегаемся по результатам и создаем массив данными
                        $res_array = array();
                        foreach ( $code['FT'][$min]['ST'] as $k => $v )
                        {
                            $rd = array();
                            $rd['dep'] = $v['ba'];
                            $rd['dept'] = GetAirportName( $mod_name, $v['ba'], $code['AP'] );
                            $rd['arr'] = $v['ea'];
                            $rd['arrt'] = GetAirportName( $mod_name, $v['ea'], $code['AP'] );
                            $rd['dpd'] = $v['bd'];
                            $rd['dpt'] = $v['bt'];
                            $rd['ard'] = $v['ed'];
                            $rd['art'] = $v['et'];
                            $rd['flt'] = $v['ft'];
                            $rd['wtt'] = isset( $v['tt'] ) ? $v['tt'] : 0;
                            $rd['coc'] = $code['CR'][$v['cm']]['NM'];
                            $rd['fli'] = $v['cm'] . '-' . $v['fn'];
                            $rd['pln'] = $code['AC'][$v['ac']]['NM'];

                            $n = !$v['gn'] ? 'onw' : 'bkw';
                            if ( !isset( $res_array[$n] ) )
                                $res_array[$n] = array();

                            $res_array[$n][] = $rd;
                        }
*/
                        $res_array = array();
                        $ways = array(0);
                        if ( !$mod_data['one_way'] )
                            $ways[] = 1;
                        foreach ( $ways as $way )
                        {

                            $n = !$way ? 'onw' : 'bkw';
                            if ( !isset( $res_array[$n] ) )
                                $res_array[$n] = array();

                            $r_data = $code['FT'][$min]['GP'][$way]['GS'];
                            reset($r_data);
                            $r_data = $r_data[key( $r_data )]['ss'];

                            foreach ( $r_data as $k => $v )
                            {
                                $v = $code['FT'][$min]['ST'][$v];
                                $rd = array();
                                $rd['dep'] = $v['ba'];
                                $rd['dept'] = GetAirportName( $mod_name, $v['ba'], $code['AP'] );
                                $rd['arr'] = $v['ea'];
                                $rd['arrt'] = GetAirportName( $mod_name, $v['ea'], $code['AP'] );
                                $rd['dpd'] = $v['bd'];
                                $rd['dpt'] = $v['bt'];
                                $rd['ard'] = $v['ed'];
                                $rd['art'] = $v['et'];
                                $rd['flt'] = $v['ft'];
                                $rd['wtt'] = isset( $v['tt'] ) ? $v['tt'] : '';
                                $rd['coc'] = $code['CR'][$v['cm']]['NM'];
                                $rd['fli'] = $v['cm'] . '-' . $v['fn'];
                                $rd['pln'] = $code['AC'][$v['ac']]['NM'];
                                $res_array[$n][] = $rd;
                            }
                        }


                        //Берем детальную информацию о полете
                        $d['dep_date']  = $date1;
                        $d['arr_date']  = $date2;
                        $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                        $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                        $d['dep_from_city']  = $mod_data['fly_from_city'];
                        $d['arr_to_city']    = $mod_data['fly_to_city'];
                        $d['dep_time']  = $code['FT'][$min]['ST'][0]['bt'];
                        $d['fly_time']  = $code['FT'][$min]['GP'][0]['GS'][$d['dep_time']]['tt'];
                        $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                        $d['comp_code'] = $code['FT'][$min]['ST'][0]['cm'];
                        $d['comp_name'] = utow( $code['CR'][$code['FT'][$min]['ST'][0]['cm']]['NM'] );
                        $d['flight']    = $code['FT'][$min]['ST'][0]['cm'] . '-' . $code['FT'][$min]['ST'][0]['fn'];
                        $d['airplane']  = $code['AC'][$code['FT'][$min]['ST'][0]['ac']]['NM'];
                        $ea = $code['FT'][$min]['GP'][0]['GD']['ea'];
                        /*
                        $cx = - 1;
                        foreach ( $code['FT'][$min]['ST'] as $v )
                        {
                            $cx++;
                            if ( $v['ea'] == $ea )
                                break;
                        }
                        $d['changing']  = $cx;

                        if ( !empty( $date2 ) )
                        {
                            $ba = $code['FT'][$min]['GP'][1]['GD']['ba'];
                            foreach ( $code['FT'][$min]['ST'] as $ii => $v )
                                if ( $v['ba'] == $ba )
                                    break;
                            $d['back_flight'] = $code['FT'][$min]['ST'][$ii]['cm'] . '-' . $code['FT'][$min]['ST'][$ii]['fn'];
                        }
                        */
                        $d['link']  = $modules_data[$mod_name]['info']['url'];

                        $modules_data[$mod_name]['min_price'] = $min_price;
                        $modules_data[$mod_name]['fly_details'] = $d;
                        SaveMinInfo( 0, 100, $min_price, $d['link'] );
                        SaveFullInfo( 0, 100, $min_price, $d['link'], json_encode( $res_array ) );
                        dump( $res_array );
                        dump($d);
                    } else
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }

                }
                else
                    if ( preg_match('`Рейсы не найдены`si', $modules_data[$mod_name]['content'], $p ) )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }


                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_aviakassa_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                return false;
            }

        } break;
    }
}
/******************************************************** Конец модуля aviakassa.ru*/

/********************************************************/
/* Модуль обработки сайта aviakassa.ru через апи */
/*                                                      */
/********************************************************/
function mod_aviakassa_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0;

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.aviakassa.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/aviakassa.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    if ( $mode == 'setup' )
        $step = 0;
    $classes = array( 0 => 'economy', 1 => 'business', 2 => 'first' );
    //TAS-MOW-15.05.2014-20.05.2014-1-1-1-0-1-1-Саша-1
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $url = "http://www.aviakassa.ru/serviceflights/?version=1.0&for=SearchFlights";

            $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ver="http://www.aviakassa.ru/serviceflights/?version%3D1.0%26for%3DSearchFlights">
                               <soapenv:Header/>
                               <soapenv:Body>
                                  <ver:search>
                                     <RequestBin>
                                        <Request>
                                           <SearchFlights LinkOnly="false">
                                                <ODPairs Type="'  . ( $mod_data['one_way'] ? 'OW' : 'RT' ) . '" Direct="' . ( $mod_data['direct'] ? 'true' : 'false' ) . '" AroundDates="0">
                                                          <ODPair>
                                                            <DepDate>' . preg_replace( '`(\d{2})\.(\d{2})\.(\d{4})`si', '$3-$2-$1', $date1 ) . 'T00:00:00</DepDate>
                                                            <DepAirp CodeType="IATA">' . $fly_from_iata . '</DepAirp>
                                                            <ArrAirp CodeType="IATA">' . $fly_to_iata . '</ArrAirp>
                                                          </ODPair>
                                                          ' . ( !$mod_data['one_way'] ? '
                                                          <ODPair>
                                                            <DepDate>' . preg_replace( '`(\d{2})\.(\d{2})\.(\d{4})`si', '$3-$2-$1', $date2 ) . 'T00:00:00</DepDate>
                                                            <DepAirp CodeType="IATA">' . $fly_to_iata . '</DepAirp>
                                                            <ArrAirp CodeType="IATA">' . $fly_from_iata . '</ArrAirp>
                                                          </ODPair>' : '' ) . '
                                                        </ODPairs>
                                                <Travellers>
                                                          <Traveller Type="ADT" Count="' . $mod_data['adults'] . '"/>
                                                          <Traveller Type="CNN" Count="' . $mod_data['children'] . '"/>
                                                          <Traveller Type="INF" Count="' . $mod_data['infants'] . '"/>
                                                </Travellers>
                                                <Restrictions>
                                                      <ClassPref>' . $classes[$mod_data['class']] . '</ClassPref>
                                                      <OnlyAvail>false</OnlyAvail>
                                                      <AirVPrefs/>
                                                      <IncludePrivateFare>false</IncludePrivateFare>
                                                      <CurrencyCode>RUB</CurrencyCode>
                                                </Restrictions>
                                           </SearchFlights>
                                        </Request>
                                        <Source>
                                           <ClientId>3</ClientId>
                                           <APIKey>334992BD098BD7B74268D57DFB4E6383</APIKey>
                                           <Language>RU</Language>
                                           <Currency>RUB</Currency>
                                        </Source>
                                     </RequestBin>
                                  </ver:search>
                               </soapenv:Body>
                            </soapenv:Envelope>
            ';

            $header = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: '".$url."'",
                "Content-length: ". strlen( $soap_request ),
            );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $soap_request );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

            /*
            $xml_result = curl_exec( $ch );
            dump( $xml_result );
            die();
            */


            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/2 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse':
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            $modules_data[$mod_name]['content'] = preg_replace( '`.+?(<Flights[^>]+>.+</Flights>).*`si', '$1', str_replace( 'xsi:', '' , $modules_data[$mod_name]['content'] ) );
            $cont = simplexml_load_string( $modules_data[$mod_name]['content'] );
            //dump($cont);
            if ( !sizeof( $cont->Flight ) )
            {
                SaveMinInfo( 2, 100, 0, '' );
                SaveFullInfo( 2, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
            } else
            {
                $min_price = 1000000;
                $min = '';
                foreach ( $cont->Flight as $k => $v )
                {
                    $price = (int)$v->TotalPrice;
                    if ( $price < $min_price )
                    {
                        $min_price = $price;
                        $min = $v;
                    }
                }
                dump($min);

                $res_array = array();

                foreach ( $min->Segments->Segment as $k => $v )
                {
                    $n = (string)$v->Direction == 'forward' ? 'onw' : 'bkw';


                    $rd = array();
                    $rd['dep'] = (string)$v->DepAirp;
                    $rd['dept'] = (string)$v->DepAirp;
                    $rd['arr'] = (string)$v->ArrAirp;
                    $rd['arrt'] = (string)$v->ArrAirp;
                    $rd['dpd'] = preg_replace( '`(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):00`si', '$3.$2.$1', (string)$v->DepDateTime );
                    $rd['dpt'] = preg_replace( '`(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):00`si', '$4:$5', (string)$v->DepDateTime );
                    $rd['ard'] = preg_replace( '`(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):00`si', '$3.$2.$1', (string)$v->ArrDateTime );
                    $rd['art'] = preg_replace( '`(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):00`si', '$4:$5', (string)$v->ArrDateTime );
                    $rd['flt'] = round( (string)$v->FlightTime / 60 ) . ':' . ( (string)$v->FlightTime % 60 ) ;
                    $rd['wtt'] = '';
                    $rd['coc'] = (string)$v->OpAirline;
                    $rd['fli'] = (string)$v->OpAirline . '-' . (string)$v->FlightNumber;
                    $rd['pln'] = (string)$v->AircraftType;
                    $res_array[$n][] = $rd;
                }

                $d['link']  = urldecode( (string)$min->URL );

                $modules_data[$mod_name]['min_price'] = $min_price;
                $modules_data[$mod_name]['fly_details'] = array();
                SaveMinInfo( 0, 100, $min_price, $d['link'] );
                SaveFullInfo( 0, 100, $min_price, $d['link'], json_encode( $res_array ) );

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                dump( $d['link'],$res_array );

                if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_aviakassa_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            }

            return false;
        } break;
    }
}
/******************************************************** Конец модуля aviakassa.ru*/


/********************************************************/
/* Модуль обработки сайта avia.euroset.ru через апи */
/*                                                      */
/********************************************************/
function mod_avia_euroset_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0;

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.aviakassa.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/aviakassa.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    if ( $mode == 'setup' )
        $step = 0;
    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );
    //TAS-MOW-15.05.2014-20.05.2014-1-1-1-0-1-1-Саша-1
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $json_url = "http://abol.avia-centr.ru/search.php";

            $url = "http://148.251.88.176/avia/search.xml?key=fc726afb-e2f3-4a7d-8ecc-a4069356e6be";
            $url .= '&destinations[0][departure]=' . $mod_data['fly_from_iata'];
            $url .= '&destinations[0][arrival]=' . $mod_data['fly_to_iata'];
            $url .= '&destinations[0][date]=' . str_replace( '.', '-', $mod_data['date1'] );
            if ( !$mod_data['one_way'] )
            {
                $url .= '&destinations[1][departure]=' . $mod_data['fly_to_iata'];
                $url .= '&destinations[1][arrival]=' . $mod_data['fly_from_iata'];
                $url .= '&destinations[1][date]=' . str_replace( '.', '-', $mod_data['date2'] );
            }
            $url .= '&service_class=' . $classes[$mod_data['class']];
            $url .= '&adt=' . $mod_data['adults'];
            $url .= '&chd=' . $mod_data['children'];
            $url .= '&inf=' . $mod_data['infants'];
            $url .= '&count=10';

            $data = json_encode( $url );
            $header = array(
                "Content-type: application/json",
                "Content-length: ". strlen( $data ),
            );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $json_url );
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/2 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse':
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }


            $cont = simplexml_load_string( $modules_data[$mod_name]['content'] );
            //dump($cont);
            if ( !sizeof( $cont->recommendations->recommendation ) )
            {
                SaveMinInfo( 2, 100, 0, '' );
                SaveFullInfo( 2, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
            } else
            {
                $min_price = 1000000;
                $min = '';
                foreach ( $cont->recommendations->recommendation as $k => $v )
                {
                    $price = (int)$v->amount->RUR;
                    if ( $price < $min_price )
                    {
                        $min_price = $price;
                        $min = $v;
                    }
                }

                $res_array = array();
                $ways = array(0);
                if ( !$mod_data['one_way'] )
                    $ways[] = 1;

                foreach ( $ways as $way )
                {
                    $r_data = $min->routes->route[$way]->segments->segment;
                    $n = !$way ? 'onw' : 'bkw';
                    if ( !isset( $res_array[$n] ) )
                        $res_array[$n] = array();

                    foreach ( $r_data as $k => $v )
                    {
                        $rd = array();
                        $p = 'departure-airport';
                        $rd['dep'] = (string)$v->$p;
                        $rd['dept'] = (string)$v->$p;
                        $p = 'arrival-airport';
                        $rd['arr'] = (string)$v->$p;
                        $rd['arrt'] = (string)$v->$p;
                        $p = 'departure-time';
                        $rd['dpd'] = preg_replace( '`([^\s*]+)\s*.+`si', '$1', (string)$v->$p );
                        $rd['dpt'] = preg_replace( '`[^\s*]+\s*(.+)`si', '$1', (string)$v->$p );
                        $p = 'arrival-time';
                        $rd['ard'] = preg_replace( '`([^\s*]+)\s*.+`si', '$1', (string)$v->$p );
                        $rd['art'] = preg_replace( '`[^\s*]+\s*(.+)`si', '$1', (string)$v->$p );
                        $p = 'flight-duration';
                        $rd['flt'] = round( (string)$v->$p / 60 ) . ':' . ( (string)$v->$p % 60 ) ;
                        $rd['wtt'] = '';
                        $rd['coc'] = (string)$v->supplier;
                        $p = 'flight-number';
                        $rd['fli'] = (string)$v->supplier . '-' . (string)$v->$p;
                        $rd['pln'] = (string)$v->aircraft;
                        $res_array[$n][] = $rd;
                    }
                }

                $d['link']  = '';

                $modules_data[$mod_name]['min_price'] = $min_price;
                $modules_data[$mod_name]['fly_details'] = array();
                SaveMinInfo( 0, 100, $min_price, $d['link'] );
                SaveFullInfo( 0, 100, $min_price, $d['link'], json_encode( $res_array ) );

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                dump( $d['link'],$res_array );

                if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_avia_euroset_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            }

            return false;
        } break;
    }
}
/******************************************************** Конец модуля avia.euroset.ru*/


/********************************************************/
/* Модуль обработки сайта anywayanyday.com                  */
/*                                                      */
/********************************************************/
function mod_anywayanyday_com( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $IdSynonym = '', $shurl = '';

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.anywayanyday.com/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/anywayanyday.com.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

	dump( $mod_data );
    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );
    switch ( $mode )
    {
        case 'setup' :
        {

            $date1 = preg_replace( '`^([0-9]+)\.([0-9]+).*`si', '$1$2', $date1 );
            $date2 = preg_replace( '`^([0-9]+)\.([0-9]+).*`si', '$1$2', $date2 );
//			$url = 'http://www.anywayanyday.com/avia/searching/new/?StartAirp1Code=' . $fly_from_iata . '&EndAirp1Code=' . $fly_to_iata . '&Date1=' . $date1 . '&ADTQnt=1&CNNQnt=0&INFQnt=0&Class=E&Number=&CustomerTimeZone=' . ( getenv( 'COMSPEC' ) != '' ? 5 : 4 ) .  ( !empty( $date2 ) ? '&StartAirp2Code=' . $fly_to_iata . '&EndAirp2Code=' . $fly_from_iata . '&Date2=' . $date2 : '' );
            $url = 'https://www.anywayanyday.com/api2/NewRequest2/?Partner=awadweb&_Serialize=JSON&Route=' . ( $shurl = $date1 . $fly_from_iata . $fly_to_iata . $date2 . $fly_to_iata . $fly_from_iata . 'AD' . $mod_data['adults'] . 'CN' . $mod_data['children'] . 'IN' . $mod_data['infants'] . 'SC' . $classes[$mod_data['class']] ) . '&_=' . rand();

            SaveFile( $cookie, '' );
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            $IdSynonym = 0;
            SaveMinInfo( 0, round( 100/4 ) * 1, 0, '' );
            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( !$IdSynonym )
                if ( preg_match( '`api2/NewRequest2/`si', $modules_data[$mod_name]['info']['url'] ) && preg_match( '`"IdSynonym":"([^"]+)"`si', $modules_data[$mod_name]['content'], $m ) )
                    $IdSynonym = $m[1];

            if ( $IdSynonym )
                if ( preg_match( '`api2/NewRequest2/`si', $modules_data[$mod_name]['info']['url'] ) || ( preg_match( '`api2/RequestState/`si', $modules_data[$mod_name]['info']['url'] ) && ( intval( preg_replace( '`.+?"Completed":"([^"]+)".*`si', '$1', $modules_data[$mod_name]['content'] ) ) < 100 ) ) )
                {
                    dump( 'step 1',$mod_name, 1, $modules_data[$mod_name]['content'], intval( preg_replace( '`.+?"Completed":"([^"]+)".*`si', '$1', $modules_data[$mod_name]['content'] ) ) );

                    //Устанавливаем новое соединение курл
                    $ch = curl_init();
                    curl_setopt( $ch,  CURLOPT_URL, 'https://www.anywayanyday.com/api2/RequestState/?_Serialize=JSON&R=' . $IdSynonym . '&_=' . rand() );
                    curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                    curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt( $ch,  CURLOPT_PROXY, '' );
                    curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );

                    return $ch;

                } else if ( preg_match( '`api2/RequestState/`si', $modules_data[$mod_name]['info']['url'] ) && ( intval( preg_replace( '`.+?"Completed":"([^"]+)".*`si', '$1', $modules_data[$mod_name]['content'] ) ) == 100 ) )
                {
                    dump( 'step2' );

                    //Устанавливаем новое соединение курл
                    $ch = curl_init();
                    curl_setopt( $ch,  CURLOPT_URL, 'https://www.anywayanyday.com/api2/Fares2/?L=RU&C=RUB&_Serialize=JSON&R=' . $IdSynonym . '&Limit=200&_=' . rand() );

                    curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                    curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt( $ch,  CURLOPT_PROXY, '' );
                    curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );
                    return $ch;

                } else
                {
                    dump( 'step 3', $mod_name, 4 );

                    $modules_data[$mod_name]['min_price'] = 0;

                    if ( !isset( $loc_cx[$fly_to_iata] ) )
                        $loc_cx[$fly_to_iata] = 0;

                    $loc_cx[$fly_to_iata]++;

                    $dat =  json_decode( removeBOM(  $modules_data[$mod_name]['content'] ),1 );

                    if ( $dat['E'] == 'NoFaresFound' )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }
                    else
                    {

                        //Соритируем данные так чтобы вначале были только предложения с минимальными ценами
                        usort( $dat['A'] , create_function( '$a, $b', 'return  $a["F"][0]["A"] - $b["F"][0]["A"];' ) );
					    dump( $dat );
                        /*
                        $onward = $dat['A'][0]['F'][0]['D'][0]['V'];
                        if ( !empty( $date2 ) )
                            $backward = $dat['A'][0]['F'][0]['D'][1]['V'];

                        //Берем детальную информацию о полете
                        $d['dep_date']  = $date1;
                        $d['arr_date']  = $date2;
                        $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                        $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                        $d['dep_from_city']  = $mod_data['fly_from_city'];
                        $d['arr_to_city']    = $mod_data['fly_to_city'];
                        $d['dep_time']  = preg_replace('`[0-9]{4}([0-9]{2})([0-9]{2})`si', '$1:$2', $onward[0]['L'][0]['DD'] );
                        $d['fly_time']  = preg_replace('`([0-9]{2})([0-9]{2})`si', '$1:$2', $onward[0]['L'][0]['FD'] );
                        $d['arr_time']  = preg_replace('`[0-9]{4}([0-9]{2})([0-9]{2})`si', '$1:$2', $onward[0]['L'][0]['AD'] );
                        $d['comp_code'] = $dat['A'][0]['C'];
                        $d['comp_name'] = Awad_get_company_name( $dat['A'][0]['C'], $dat['RF']['C'] );
                        $d['flight']    = $onward[0]['L'][0]['F'];
                        $d['airplane']  = Awad_get_Airplane( $onward[0]['L'][0]['P'], $dat['RF']['P'] );
                        $d['changing']  = sizeof( $onward ) - 1;
                        */
                        $d['link'] = 'https://www.anywayanyday.com/avia/offers/' . $shurl . '/';
                        //if ( !empty( $date2 ) )
                        //    $d['back_flight'] = $backward[0]['L'][0]['F'];
                        $modules_data[$mod_name]['min_price'] = $dat['A'][0]['F'][0]['A'];
                        $modules_data[$mod_name]['fly_details'] = $d;
//                        SaveMinInfo( 0, 100, $dat['A'][0]['F'][0]['A'], $d['link'] );
//                        SaveFullInfo( 0, 100, $dat['A'][0]['F'][0]['A'], $d['link'], '{}' );


                        $res_array = array();
                        $ways = array(0);
                        if ( !$mod_data['one_way'] )
                            $ways[] = 1;
                        foreach ( $ways as $way )
                        {
                            $r_name = $dat['A'][0]['F'][0]['D'][$way]['P'];
                            $r_data = $dat['A'][0]['F'][0]['D'][$way]['V'][0]['L'];
                            $n = !$way ? 'onw' : 'bkw';
                            if ( !isset( $res_array[$n] ) )
                                $res_array[$n] = array();

                            foreach ( $r_data as $k => $v )
                            {
                                $rd = array();
                                $rd['dep'] = $r_name[$k]['C'];
                                $rd['dept'] = GetAirportName( $mod_name, $r_name[$k]['C'], $dat['RF']['A'] );
                                $rd['arr'] = $r_name[$k+1]['C'];
                                $rd['arrt'] = GetAirportName( $mod_name, $r_name[$k+1]['C'], $dat['RF']['A'] );
                                $rd['dpd'] = preg_replace( '`([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})`si', '$1.$2.' . date( 'Y' ), $v['DD'] );
                                $rd['dpt'] = preg_replace( '`([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})`si', '$3:$4' , $v['DD'] );
                                $rd['ard'] = preg_replace( '`([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})`si', '$1.$2.' . date( 'Y' ), $v['AD'] );
                                $rd['art'] = preg_replace( '`([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})`si', '$3:$4' , $v['AD'] );
                                $rd['flt'] = preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2' , $v['FD'] );
                                $rd['wtt'] = $v['D'] ? preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2' , $v['D'] ) : '';
                                $rd['coc'] = Awad_get_company_name( $dat['A'][0]['C'], $dat['RF']['C'] );
                                $rd['fli'] = $v['F'];
                                $rd['pln'] = Awad_get_Airplane( $v['P'], $dat['RF']['P'] );
                                $res_array[$n][] = $rd;
                            }
                        }
                        SaveMinInfo( 0, 100, $dat['A'][0]['F'][0]['A'], $d['link'] );
                        SaveFullInfo( 0, 100, $dat['A'][0]['F'][0]['A'], $d['link'], json_encode( $res_array ) );
                        dump( $res_array );

                        dump($d);
                    }

                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();

                    if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
                    {
                        dump('sher', $modules_data[$mod_name]['min_price'], (string)$modules_data[$mod_name]['min_price'] == 'nf' );
                        if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                            return mod_anywayanyday_com( 'setup', $mod_data, $cookie );
                        else
                        {
                            $modules_data[$mod_name]['min_price'] = 'nf';
                            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                            $modules_data[$mod_data['mod_name']]['end_time'] = time();
                            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );

                            return false;
                        }
                    }


                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                    return false;
                }

        } break;
    }
}
//******************************************************** Конец модуля anywayanyday.com


/********************************************************/
/* Модуль обработки сайта pososhok.ru                  */
/*                                                      */
/********************************************************/
function mod_pososhok_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $step = 0, $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.pososhok.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/pososhok.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => 'ECONOMY', 1 => 'BUSINESS', 2 => 'FIRST' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );
            dump( $mod_name, 1 );
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'http://www.pososhok.ru/');
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
            curl_setopt(  $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

//			SetCurlPost( $ch, array( 'cmd' => 'validate_aviaform' ) );
            $step = 1;
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            if ( !isset( $info['link'] ) )
                unset( $info['link'] );

            SaveMinInfo( 0, round( 100/5 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse' :
        {

			//dump( $modules_data[$mod_name]['info'], $modules_data[$mod_name]['content'] );
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }



//			dump( $modules_data[$mod_name]['info'] );
            if ( ( $step == 1 ) && preg_match( '`<input type="hidden" name="sss" value="([^"]+)"\s*/>`siu', $modules_data[$mod_name]['content'], $m ) )
            {
                dump( $mod_name, 1 );
//					die();
                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.pososhok.ru/' );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, 'http://www.pososhok.ru/' );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 1 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 1 );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
                curl_setopt( $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );


                $data['FlightSearchForm.routeType'] = !$mod_data['one_way'] ? 'ROUND_TRIP' : 'ONE_WAY';
                $data['FlightSearchForm.departureLocation.0'] = $fly_from_iata;
                $data['FlightSearchForm.departureLocation.0.CODE'] = GetAirCode( $mod_name, array(  'iata_code' => $fly_from_iata, 'city' => $mod_data['fly_from_city'], 'airport' => $mod_data['fly_from_airport'] ) );

                $data['FlightSearchForm.arrivalLocation.0'] = $fly_to_iata;
                $data['FlightSearchForm.arrivalLocation.0.CODE'] = GetAirCode( $mod_name, array(  'iata_code' => $fly_to_iata, 'city' => $mod_data['fly_to_city'], 'airport' => $mod_data['fly_to_airport'] ) );

                $data['FlightSearchForm.date.0'] = $date1;
                if ( !empty( $date2 ) )
                    $data['FlightSearchForm.date.1'] = $date2;

                $data['FlightSearchForm.adultsType'] = 'ADULT';
                $data['FlightSearchForm.adultsCount'] = $mod_data['adults'];
                $data['FlightSearchForm.children'] = $mod_data['children'];
                $data['FlightSearchForm.infants'] = $mod_data['infants'];
                $data['FlightSearchForm.anyAirline'] = 'true';
                $data['FlightSearchForm.serviceClass'] = $classes[$mod_data['class']];
                $data['FlightSearchForm.searchType'] = 'FLIGHTS';

                $data['sss'] = $m[1];
                $data['validateForm'] = 'true';
                $data['FlightSearchForm.extendedForm'] = 'false';

                $step = 2;

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
//					dump( $data );
                SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );

                return $ch;

            } else if ( ( $step == 2 ) && ( preg_match( '`(http://www\.pososhok\.ru/avia/2/.+)`si', $modules_data[$mod_name]['info']['url'], $u ) || preg_match( '`(http://www\.pososhok\.ru/avia/2/[^\s]+)`si', $modules_data[$mod_name]['content'], $u ) ) )
            {

                dump( $mod_name, 2 );
//					dump( $modules_data[$mod_name]['content'] );
                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.pososhok.ru/system/modules/com.gridnine.opencms.modules.pososhok/pages/ajax_provider_avia.jsp' );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );

                $data['cmd'] = 'get_search_results';
                $data['do_search'] = 'true';
                $data['search_type'] = 'FLIGHTS';
                $data['search_with_grades'] = 'false';

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                $info['link'] = trim( $u[1] );
                SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );

                return $ch;

            } else if ( preg_match( '`ajax_provider_avia\.jsp`si', $modules_data[$mod_name]['info']['url'] ) && preg_match( '`"action"\s*:\s*"show_results"`si', $modules_data[$mod_name]['content'], $m ) )
            {
                dump( $mod_name, 3 );

                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.pososhok.ru/system/modules/com.gridnine.opencms.modules.pososhok/pages/ajax_provider_avia.jsp' );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 0 );

                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );

                $data['cmd'] = 'get_ajax_search_results';
                $data['insrease_date'] = 'false';
                $data['loadDeals'] = 'true';

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/5 ) * 4, 0, '' );

                return $ch;
            }
            else
            {
                dump( $mod_name, 4 );

                $modules_data[$mod_name]['min_price'] = 0;

                if ( !isset( $loc_cx[$fly_to_iata] ) )
                    $loc_cx[$fly_to_iata] = 0;

                $loc_cx[$fly_to_iata]++;

                $cont = json_decode( $modules_data[$mod_name]['content'], 1 );
                dump( $cont );

                if ( !sizeof( $cont['flightGroups'] ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                }
                else
                {
                    //Берем детальную информацию о полете
                    /*
                    $legs = $cont['flightGroups'][0]['legs'];
                    $fe1 = $legs[0]['elements'][0]['fe'];
                    $fe2 = $legs[sizeof( $legs )-1]['elements'][0]['fe'];
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']  = $fe1[0]['dep_time'];
                    $d['fly_time']  = $legs[0]['elements'][0]['travelDuration'];
                    $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                    $d['comp_code'] = preg_match( '`alt="([^"]+)"`si', $legs[0]['airlineIcon'], $a) ? utow( $a[1] ) : '';
                    $d['comp_name'] = utow( $legs[0]['airline'] );
                    $d['flight']    = preg_replace( '`(.{2})([0-9]+)`si', '$1-$2' , $fe1[0]['reis'] );
                    $d['airplane']  = utow( $fe1[0]['board'] );
                    $d['changing']  = sizeof( $fe1 ) - 1;
                    */

                    $res_array = array();
                    $ways = array(0);
                    if ( !$mod_data['one_way'] )
                        $ways[] = 1;

                    foreach ( $ways as $way )
                    {
                        $r_data = $cont['flightGroups'][0]['legs'][$way]['elements'][0]['fe'];
                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        foreach ( $r_data as $k => $v )
                        {
                            $rd = array();
                            $rd['dep'] = str_replace( 'а/п', '', $v['dep'] == $v['dep_arpt'] ? $v['dep'] : $v['dep'] . ', ' . $v['dep_arpt'] );
                            $rd['dept'] = str_replace( 'а/п', '', $v['dep'] == $v['dep_arpt'] ? $v['dep'] : $v['dep'] . ', ' . $v['dep_arpt'] );
                            $rd['arr'] = str_replace( 'а/п', '', $v['arr'] == $v['arr_arpt'] ? $v['arr'] : $v['arr'] . ', ' . $v['arr_arpt'] );
                            $rd['arrt'] = str_replace( 'а/п', '', $v['arr'] == $v['arr_arpt'] ? $v['arr'] : $v['arr'] . ', ' . $v['arr_arpt'] );
                            $rd['dpd'] = cdate2( $v['dep_date'] );
                            $rd['dpt'] = $v['dep_time'];
                            $rd['ard'] = cdate2( $v['arr_date'] );
                            $rd['art'] = $v['arr_time'];
                            $rd['flt'] = '';
                            $rd['wtt'] = isset( $v['connTime'] ) ? preg_replace( '`([0-9]{2})ч ([0-9]{2})м`si', '$1:$2' , $v['connTime'] ) : '';
                            $rd['coc'] =$cont['flightGroups'][0]['legs'][$way]['airline'];
                            $rd['fli'] = preg_replace( '`([a-z]{2})(.*)`si', '$1-$2' , $v['reis'] );
                            $rd['pln'] = $v['board'];
                            $res_array[$n][] = $rd;
                        }
                    }


                    //if ( !empty( $date2 ) )
                        //$d['back_flight'] = preg_replace( '`(.{2})([0-9]+)`si', '$1-$2' , $fe2[0]['reis'] );

                    $d['link'] = $info['link'];

                    $modules_data[$mod_name]['min_price'] = preg_replace( '`[^0-9]+`si', '', $cont['flightGroups'][0]['adultsPrice'] );
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );

                    dump( $res_array );
                    dump( $d );

                }

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_pososhok_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        return false;
                    }
                }
                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                return false;
            }


        } break;
    }


}
//******************************************************* Конец модуля pososhok.ru

/********************************************************/
/* Модуль обработки сайта davs.ru  через шлюз           */
/*                                                      */
/********************************************************/
function mod_davs_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array();//, $airlines = array();
//	if ( !sizeof( $airlines ) )
//		include 'onetwotrip.com.base.php';;

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.davs.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/davs.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => 'Y', 1 => 'C', 2 => 'F' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );
            $data = array();
            $req_xml  = "\r\n<request>\r\n";
            $req_xml .= "<action>dep</action>\r\n";
            $req_xml .= "<site>davs.ru</site>\r\n";
//			$req_xml .= "<site>aviabilet.ru</site>\r\n";
            $req_xml .= "<ip>127.0.0.1</ip>\r\n";
            $req_xml .= '<DepartureDate>' . str_replace( '.', '/', $date1 ) . "</DepartureDate>\r\n";
            if ( !empty( $date2 ) )
                $req_xml .= '<ArrivalDate>' . str_replace( '.', '/', $date2 ) . "</ArrivalDate>\r\n";
            $req_xml .= '<From>' . $fly_from_iata . "</From>\r\n";
            $req_xml .= '<To>' . $fly_to_iata . "</To>\r\n";
            $req_xml .= "<date_type>exactly</date_type>\r\n";
            $req_xml .= '<oneway>' .  $mod_data['one_way'] . "</oneway>\r\n";
            $req_xml .= "<ItineraryCabinOption>" . $classes[$mod_data['class']]  . "</ItineraryCabinOption>\r\n";
            $req_xml .= "<only_direct>" . (  $mod_data['only_direct'] ? 'true' : 'false' )  . "</only_direct>\r\n";
            $req_xml .= "<ADT>" . $mod_data['adults'] . "</ADT>\r\n";
            $req_xml .= "<YTH>0</YTH>\r\n";
            $req_xml .= "<CHD>" . $mod_data['children'] . "</CHD>\r\n";
            $req_xml .= "<INF>" . $mod_data['infants'] . "</INF>\r\n";
            $req_xml .= "</request>\r\n";
            $data['req_xml'] = $req_xml;

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'http://xml.davs.ru/gate/index.php');
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );

            SetCurlPost( $ch, $data );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/2 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $mod_name, 1 );

            $modules_data[$mod_name]['min_price'] = 0;

            if ( !isset( $loc_cx[$fly_to_iata] ) )
                $loc_cx[$fly_to_iata] = 0;

            $loc_cx[$fly_to_iata]++;

            $modules_data[$mod_name]['content'] = preg_replace( '`<!\[CDATA\[([^\[]+)\]\]>`si', '$1', $modules_data[$mod_name]['content'] );
            $resp = simplexml_load_string( $modules_data[$mod_name]['content'] );

            if ( !isset( $resp->response->items )  )
            {
                $modules_data[$mod_name]['min_price'] = 'nf';
                SaveMinInfo( 2, 100, 0, '' );
                SaveFullInfo( 2, 100, 0, '', '{}' );
            }
            else
            {
                dump( $resp );
                $price_ = $resp->response->items->item[0]->attributes();
                $odata  = $resp->response->items->item[0]->bound[0]->element[0]->segment[0];
                $ftime  = (string)$resp->response->items->item[0]->bound[0]->element[0]->duration;

/*
                $d['dep_date']  = $date1;
                $d['arr_date']  = $date2;
                $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                $d['dep_from_city']  = $mod_data['fly_from_city'];
                $d['arr_to_city']    = $mod_data['fly_to_city'];
                $d['dep_time']  = (string)$odata->departure->time;
                $d['fly_time']  = round( $ftime / 60 ) . ':' . ( $ftime % 60 );
                $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                $d['comp_code'] = (string) $odata->supplier->attributes()->id;
                $d['comp_name'] = utow( (string) $odata->supplier );
                $d['flight']    = $d['comp_code'] . '-' . (string) $odata->carrier_number;
                $d['airplane']  = utow( (string) $odata->equipment );
                $d['changing']  = sizeof( $resp->response->items->item[0]->bound[0]->element[0]->segment ) - 1;
                $sess = (string)$resp->session;
                $elements = urlencode('element[0]') . '=' . urlencode( (string)$resp->response->items->item[0]->bound[0]->element[0]->attributes()->id );
                if ( !empty( $date2 ) )
                    $elements .= '&' . urlencode('element[1]') . '=' . urlencode( (string)$resp->response->items->item[0]->bound[1]->element[0]->attributes()->id );

// 				$d['link'] = 'http://ticket.davs.ru/avia/book.php?PHPSESSID=' . $sess . '&' . $elements . '&site=' . urlencode( 'aviabilet.ru' ) . '&ref=' . urlencode( 'http://aviabilet.ru/' ) . '&style=default';

                if ( !empty( $date2 ) )
                {
                    $odata2 = $resp->response->items->item[0]->bound[1]->element[0]->segment[0];
                    $d['back_flight'] = (string) $odata2->supplier->attributes()->id . '-' . (string) $odata2->carrier_number;
                }
*/

                $res_array = array();
                $ways = array(0);
                if ( !$mod_data['one_way'] )
                    $ways[] = 1;

                foreach ( $ways as $way )
                {
                    $r_data = $resp->response->items->item[0]->bound[$way]->element[0]->segment;
                    $n = !$way ? 'onw' : 'bkw';
                    if ( !isset( $res_array[$n] ) )
                        $res_array[$n] = array();
                    //dump( $r_data );
                    for( $i=0; $i < sizeof( $r_data ); $i++ )
                    {
                        $v = $r_data[$i];
                        //dump($v);
                        $rd = array();
                        $rd['dep'] = (string)$v->departure->city == (string)$v->departure->airport ? (string)$v->departure->city : (string)$v->departure->city . ', ' .  (string)$v->departure->airport;
                        $rd['dept'] = (string)$v->departure->city == (string)$v->departure->airport ? (string)$v->departure->city : (string)$v->departure->city . ', ' .  (string)$v->departure->airport;
                        $rd['arr'] = (string)$v->arrival->city == (string)$v->arrival->airport ? (string)$v->arrival->city : (string)$v->arrival->city . ', ' .  (string)$v->arrival->airport;
                        $rd['arrt'] = (string)$v->arrival->city == (string)$v->arrival->airport ? (string)$v->arrival->city : (string)$v->arrival->city . ', ' .  (string)$v->arrival->airport;
                        $rd['dpd'] = preg_replace( '`([0-9]+)-([0-9]+)-([0-9]+)`si','$3.$2.$1', (string)$v->departure->date );
                        $rd['dpt'] = (string)$v->departure->time;
                        $rd['ard'] = preg_replace( '`([0-9]+)-([0-9]+)-([0-9]+)`si','$3.$2.$1', (string)$v->arrival->date );
                        $rd['art'] = (string)$v->arrival->time;
                        $rd['flt'] = round( (string)$v->duration/ 60 ) . ':' . (string)$v->duration % 60;
                        $rd['wtt'] = '';
                        $rd['coc'] = (string)$v->supplier;
                        $rd['fli'] = preg_replace( '`.+?([a-z]{2})\.png$`si', '$1' , (string)$v->image ) . '-' . (string)$v->carrier_number;
                        $rd['pln'] = (string)$v->equipment;
                        $res_array[$n][] = $rd;
                    }
                }


                $modules_data[$mod_name]['min_price'] = (string)$price_['price'];
                //$modules_data[$mod_name]['fly_details'] = $d;
                SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], '' );
                SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], '', json_encode( $res_array ) );
                dump( $res_array );


            }

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            if ( $modules_data[$mod_name]['min_price'] == 'nf' )
            {
                if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                    return mod_davs_ru( 'setup', $mod_data, $cookie );
                else
                {
                    $modules_data[$mod_name]['min_price'] = 'nf';
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );

                    return false;
                }
            }

            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            return false;

        } break;
    }


}
//******************************************************* Конец модуля davs.ru


/********************************************************/
/* Модуль обработки сайта sindbad.ru                    */
/*                                                      */
/********************************************************/
function mod_sindbad_ru( $mode, $mod_data, $cookie  )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'https://sindbad.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/sindbad.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            list( $d, $m, $y ) = explode( '.', $date1 );
            $data['type'] = !$mod_data['one_way'] ? 'RT' : 'OW';
            $data['src'] = $fly_from_iata;
            $data['dateOutDay'] = $d;
            $data['dateOut'] = $m . '-' . $y;
            $data['dst'] = $fly_to_iata;
            if ( !$mod_data['one_way'] )
            {
                list( $d, $m, $y ) = explode( '.', $date2 );
                $data['dateInDay'] = $d;
                $data['dateIn'] = $m . '-' . $y;
            }
            $data['adultNum'] = $mod_data['adults'];
            $data['childNum'] = $mod_data['children'];
            $data['infantNum'] = $mod_data['infants'];
            $data['serviceClass'] = $classes[$mod_data['class']];
            $data['direct'] = $mod_data['direct'];


            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'https://sindbad.ru/ru/flight/ajaxSearch?' . PostDataEncode( $data ) );

            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            SaveMinInfo( 0, round( 100/3 ) * 1, 0, '' );
            return $ch;

        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( preg_match( '`flight/ajaxSearch`si', $modules_data[$mod_name]['info']['url'] ) && preg_match( '`^(' . $fly_from_iata . $fly_to_iata . '.+)`si', $modules_data[$mod_name]['content'], $m ) )
            {
                dump( $mod_name , 1 );
                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://www.sindbad.ru/ru/flight/results/' . trim( $m[1] ) );
                curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                curl_setopt( $ch, CURLOPT_REFERER, $ref );
                curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch, CURLOPT_PROXY, '' );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_NOBODY, 0 );
                curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );

                $modules_data[$mod_name]['min_price'] = 0;

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/3 ) * 2, 0, '' );
                return $ch;

            } else
            {
                dump( $mod_name , 2 );

                $modules_data[$mod_name]['min_price'] = 0;
                $cont = ( $modules_data[$mod_name]['content'] );


				//dump( $cont );
				//die();


                if ( !isset( $loc_cx[$fly_to_iata] ) )
                    $loc_cx[$fly_to_iata] = 0;

                $loc_cx[$fly_to_iata]++;


//                if ( preg_match('`<div class="clf">\s*<div class="cost">\s*<span class="age-icon age-adult">\s*</span>\s*&nbsp;\s*<span class="lPrice" id="[^"]+">([^<]+)</span>`si', $cont, $p ) )
                if ( preg_match( '`<form.+?id="form[^"]+"[^>]+>(.+?)</form>`si', $cont, $form ) )
                {
                    /*
                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']       = preg_match('`</div>\s*<div class="col"><span class="abbr">[^<]+</span>\s*([^<]+)<br>`si', $cont, $a ) ? $a[1] : '00:00';
                    $d['fly_time']       = preg_match('`</div><div class="col">([0-9]+)\s*ч\.\s*([0-9]+)\s*мин\.</div>`si', $cont, $a ) ? ( $a[1] . ':' . $a[2] ) : '00:00';
                    $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                    $d['comp_code'] = trim( preg_match('`<p>Рейс\s*([^0-9]+)([0-9]+)`si', $cont, $a ) ? $a[1] : '' );
                    $d['comp_name'] = trim( preg_match('`<p>Рейс\s*[^0-9]+[0-9]+\s*авиакомпании\s*«([^»]+)»`si', $cont, $a ) ? $a[1] : '' );
                    $d['flight']    = trim( preg_match('`<p>Рейс\s*([a-z0-9]+)([0-9]+)`si', $cont, $a ) ? $a[1] . '-' . $a[2] : '' );
                    $d['airplane']  = preg_match('`Выполняется самолетом ([^\.]+).<`si', $cont, $a ) ? $a[1] : '';
                    $d['changing']  = preg_match( '`<div class="col transfer"><ul class="clf">(.+?)</ul>`si', $cont, $a ) && preg_match_all( '`(<li>)`si', $a[1], $a ) ? sizeof( $a[1] ) : 0;
                    $d['link'] = $modules_data[$mod_name]['info']['url'];

                    if ( !empty( $date2 ) )
                    {
                        preg_match( '`<div class="variants back clf pie fBack">.+?<p>Рейс\s*([a-z]+)([0-9]+) `si', $cont, $n );
                        $d['back_flight'] = $n[1] . '-' . $n[2];
                    }

                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $p[1] ) ) );
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает!' );

                    dump($d);
                    */



                    $res_array = array();
                    $ways = array( '`<div class="variants clf.+?<li id="info(.+?)</li>`si' => 0 );
                    if ( !$mod_data['one_way'] )
                        $ways['`<div class="variants back.+?<li id="info(.+?)</li>`si'] = 1;

                    foreach ( $ways as $reg => $way )
                    {
                        preg_match( $reg, $form[1], $text );
                        preg_match_all( '`<div class="fly">(.+?)</div>`si', $text[1], $flys );
                        preg_match_all( '`<div class="trans">(.+?)</div>`si', $text[1], $trans );
                        $flys = $flys[1];
                        $wait_times = $trans[1];
                        //dump( $flys, $wait_times );
                        //continue;

                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        for( $i=0; $i < sizeof( $flys ); $i++ )
                        {
                            preg_match( '`<p class="clf"><span class="time">(.+?)&nbsp;<span class="descr">(.+?)</span></span><span class="info">\s*вылет:\s*([^<]+)<br><span class="descr">\s*аэропорт\s*«([^»]+)».+?</span></span></p><p class="clf"><span class="time">(.+?)&nbsp;<span class="descr">(.+?)</span></span><span class="info">\s*прибытие:\s*([^<]+)<br><span class="descr">\s*аэропорт\s*«([^»]+)».+?</span></span></p><p>Рейс\s*(.{2})([0-9]+)\s*авиакомпании\s*«([^»]+)».+?</p><p class="fr">\s*В\s*пути([^<]+)</p><p>\s*Выполняется\s*самолетом\s*([^<]+)</p>`sui', $flys[$i], $v );
                            //dump( $flys[$i], $v );
                            //continue;

                            $rd = array();
                            $rd['dep'] = $v[3] == $v[4] ? $v[3] : $v[3] . ', ' .  $v[4];
                            $rd['dept'] = $v[3] == $v[4] ? $v[3] : $v[3] . ', ' .  $v[4];
                            $rd['arr'] = $v[7] == $v[8] ? $v[7] : $v[7] . ', ' .  $v[8];
                            $rd['arrt'] = $v[7] == $v[8] ? $v[7] : $v[7] . ', ' .  $v[8];
                            $rd['dpd'] = $v[1];
                            $rd['dpt'] = $v[2];
                            $rd['ard'] = $v[5];
                            $rd['art'] = $v[6];
                            $rd['flt'] = trim( preg_replace('`([0-9]+)\s*ч\.\s([0-9]+)\s*мин\.`si', '$1:$2', $v[12] ) );
                            $rd['wtt'] = isset( $wait_times[$i] ) ? trim( preg_replace('`.*?<p class="fr">Время пересадки\s*([0-9]+)\s*ч\.\s([0-9]+)\s*мин\.</p>.*`sui', '$1:$2', $wait_times[$i] ) ) : '';
                            $rd['coc'] = $v[11];
                            $rd['fli'] = $v[9] . '-' . $v[10];
                            $rd['pln'] = $v[13];
                            $res_array[$n][] = $rd;
                        }

                    }

                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( preg_replace('`.+?<span class="lPrice" id="[^"]+">([^<]+)</span>.*`si', '$1', $form[1] ) ) ) );
                    $d['link'] = $modules_data[$mod_name]['info']['url'];
                    $modules_data[$mod_name]['fly_details'] = $d;

                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                    dump( $res_array );


                }
                else
                    if ( preg_match('`Не\s*найдены\s*рейсы\s*либо`si', $cont, $p ) )
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                    }
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                    }


                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_sindbad_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';

                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                return false;
            }
        } break;
    }


}
//******************************************************* Конец модуля sindbad.ru


/********************************************************/
/* Модуль обработки сайта bilet-on-line.ru              */
/*                                                      */
/********************************************************/
function mod_bilet_on_line_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $info = array(), $info2 = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://bilet-on-line.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/bilet-on-line.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );
            list( $d, $m, $y ) = explode( '.', $date1 );
            $y = date( 'y' );

            $data['algorithm'] = 'AIR.SEARCH';
            $data['operation'] = 'SEARCH';
            $data['dateFrom'] = '';
            $data['dateTo'] = '';
            $data['departure_0'] = $fly_from_iata;
            $data['arrival_0'] = $fly_to_iata;
            $data['departure_1'] = '';
            $data['arrival_1'] = '';
            $data['departure_2'] = '';
            $data['arrival_2'] = '';
            $data['departure_3'] = '';
            $data['arrival_3'] = '';

            $data['airline1'] = 'NONE';
            $data['airline2'] = 'NONE';
            $data['airline3'] = 'NONE';

            $data['departureTime_0'] = '00:00:00';
            $data['departureTime_1'] = '00:00:00';
            $data['departureTime_2'] = '00:00:00';
            $data['departureTime_3'] = '00:00:00';

            if ( !empty( $date2 ) )
                $data['flightType'] = 'TWO.WAYS';
            else
                $data['flightType'] = 'ONE.WAY';


            $data['dateStart0'] = $date1;
            $data['dateEnd'] = $date2;
            $data['dateStart1'] = '19.06.2012';
            $data['dateStart2'] = '19.06.2012';
            $data['dateStart3'] = '19.06.2012';
            $data['lowFareSearch'] = 'false';
            $data['passType_15419056'] = 1;
            $data['passType_15419057'] = 0;
            $data['passType_15419058'] = 0;

            $data['bookingClass'] = 'Y';
            $info = $data;
//			dump($data);

            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, 'https://www.bilet-on-line.ru/xworkview.htm?sid='  );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

            SetCurlPost( $ch, $data );

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/5 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );
            if ( preg_match( '`xworkview\.htm\?sid=[0-9]*$`si', $modules_data[$mod_name]['info']['url'] ) && preg_match( '`"longTaskId":"([^"]*)",\s*"status":"",\s*"sid":"([^"]+)"\s*}`si', $modules_data[$mod_name]['content'], $m ) && !$m[1] )
            {
                dump( $mod_name, 1 );
                //Устанавливаем новое соединение курл
                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'https://www.bilet-on-line.ru/xworkview.htm?sid=' . $m[2] );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                SetCurlPost( $ch, $info );

                $modules_data[$mod_name]['min_price'] = 0;

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );

                return $ch;

            } else
                if ( preg_match( '`xworkview\.htm\?sid=([0-9]+)$`si', $modules_data[$mod_name]['info']['url'], $m ) )
                {
                    dump( $mod_name, 2 );
                    //Устанавливаем новое соединение курл
                    $ch = curl_init();
                    curl_setopt( $ch,  CURLOPT_URL, 'https://www.bilet-on-line.ru/xworkview.htm?algorithm=AIR.SEARCH&stage=2&sid=' . $m[1] );
                    curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                    curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt( $ch,  CURLOPT_PROXY, '' );
                    curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch,  CURLOPT_NOBODY, 0 );

                    $modules_data[$mod_name]['min_price'] = 0;

                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );

                    return $ch;

                } else
                    if ( preg_match( '`xworkview\.htm\?algorithm=AIR\.SEARCH&stage=2&sid=([0-9]+)$`si', $modules_data[$mod_name]['info']['url'], $m ) )
                    {
                        dump( $mod_name, 3 );

                        $modules_data[$mod_name]['content'] = utow( $modules_data[$mod_name]['content'] );

                        if ( !preg_match_all('`<nobr>\s*<a href="javascript:showSequences\(\'([^\']+)\'\);">\s*([0-9]+)[^<]+</a>\s*</nobr>`si', $modules_data[$mod_name]['content'], $p ) )
                        {
                            $modules_data[$mod_name]['min_price'] = 'nf';
                            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                            $modules_data[$mod_data['mod_name']]['end_time'] = time();
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );
                            return false;
                        } else
                        {
                            $seqs = $p[1];
                            $prices = $p[2];
                            asort( $prices );
                            reset( $prices );
                            list( $k, $min_price ) = each( $prices );
                            $seq = $seqs[$k];

                            //Устанавливаем новое соединение курл
                            $ch = curl_init();
                            curl_setopt( $ch,  CURLOPT_URL, 'https://www.bilet-on-line.ru/xworkview.htm?algorithm=AIR.SEARCH&stage=2&sid=' . $m[1] . '&myvar=1' );
                            curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                            curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                            curl_setopt( $ch,  CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                            curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                            curl_setopt( $ch,  CURLOPT_HEADER, 0 );
                            curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                            curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                            curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                            curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );
                            curl_setopt( $ch,  CURLOPT_PROXY, '' );
                            curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                            curl_setopt( $ch,  CURLOPT_NOBODY, 0 );

                            $data = array();
                            $data['aaxmlrequest'] = 'true';
                            $data['dateIn'] = '';
                            $data['dateOut'] = '';
                            $data['operation'] = 'SHOW_SEQUENCES';
                            $data['operationCode'] = '';
                            $data['sequenceId'] = '';
                            $data['sequences'] = $seq;

                            SetCurlPost( $ch, $data );

                            $modules_data[$mod_name]['min_price'] = 0;
                            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                            $modules_data[$mod_data['mod_name']]['end_time'] = time();
                            SaveMinInfo( 0, round( 100/5 ) * 4, 0, '' );

                            return $ch;

                        }

                    } else
                    {
                        dump( $mod_name, 4 );

                        $modules_data[$mod_name]['min_price'] = 0;

                        if ( !isset( $loc_cx[$fly_to_iata] ) )
                            $loc_cx[$fly_to_iata] = 0;

                        $loc_cx[$fly_to_iata]++;
                        $modules_data[$mod_name]['content'] = utow( $modules_data[$mod_name]['content'] );
                        $cont = GetSegment( $modules_data[$mod_name]['content'], '<table width="100%" class="table_vilet blue_border">', '</table>' );

                        if ( preg_match('`"font-size:22px">\s*([0-9]+)`si', $cont, $p ) )
                        {

                            //Берем детальную информацию о полете
                            $d['dep_date']  = $date1;
                            $d['arr_date']  = $date2;
                            $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                            $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                            $d['dep_from_city']  = $mod_data['fly_from_city'];
                            $d['arr_to_city']    = $mod_data['fly_to_city'];
                            $d['dep_time']       = preg_match('`<font class="colorm">Вылет:\s*</font>\s*<br>\s*([^<]+)<br/>`si', $cont, $a ) ? $a[1] : '00:00';

                            $d['fly_time']       = preg_match('`Общее\s*время\s*в\s*пути:\s*([0-9]+)\s*час\s*([0-9]+)\s*мин`si', $cont, $a ) ? ( $a[1] . ':' . $a[2] ) : '00:00';
                            $d['arr_time']       = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );

                            $d['comp_code'] = trim( preg_match('`<td class="textright">(?:\s*<img[^>]+>&nbsp;&nbsp;&nbsp;)?\s*<br>.+?([a-z0-9]{2})\s+[0-9]+<br/>`si', $cont, $a ) ? $a[1] : '' );
                            $d['comp_name'] = trim( preg_match('`<td class="textright">(?:\s*<img[^>]+>&nbsp;&nbsp;&nbsp;)?\s*<br>(.+?)[a-z0-9]{2}\s+[0-9]+<br/>`si', $cont, $a ) ? $a[1] : '' );
                            $d['flight']    = trim( preg_match('`<td class="textright">(?:\s*<img[^>]+>&nbsp;&nbsp;&nbsp;)?\s*<br>.+?([a-z0-9]{2})\s+([0-9]+)<br/>`si', $cont, $a ) ? $a[1] . '-' . $a[2] : '' );
                            $d['airplane']  = trim( preg_match('`Самолет:([^<]+)<br/>`si', $cont, $a ) ? $a[1] : '' );
                            $d['changing']  = preg_match( '`Туда:(.+?)Обратно:`si', $cont = preg_replace( '`<!--.+?-->`si', '', $cont ), $a ) && preg_match_all( '`(Пересадка\.)`si', $a[1], $a ) ? sizeof( $a[1] ) : 0;
                            $d['link'] = $modules_data[$mod_name]['info']['url'];
                            if ( !empty( $date2 ) )
                            {
                                preg_match( '`>Обратно:.+?<br>\s*.+?\s*([a-z]{2})\s*([0-9]+)<br/>\s*Самолет:`si', $cont, $n );
                                $d['back_flight'] = $n[1] . '-' . $n[2];
                            }


                            $modules_data[$mod_name]['min_price'] = trim( $p[1] );
                            $modules_data[$mod_name]['fly_details'] = $d;
                            SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                            SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает!' );

                            dump($d);
                        }
                        else
                        {
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );
                            $modules_data[$mod_name]['min_price'] = 'nf';
                        }


                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();

                        if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                        {
                            if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                                return mod_bilet_on_line_ru( 'setup', $mod_data, $cookie );
                            else
                            {
                                $modules_data[$mod_name]['min_price'] = 'nf';
                                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                                SaveMinInfo( 2, 100, 0, '' );
                                SaveFullInfo( 2, 100, 0, '', '{}' );

                                return false;
                            }
                        }

                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                        return false;
                    }


        } break;
    }
}
/******************************************************** Конец модуля bilet-on-line.ru*/


/********************************************************/
/* Модуль обработки сайта svyaznoy.travel               */
/*                                                      */
/********************************************************/
function mod_svyaznoy_travel( $mode, $mod_data, $cookie )
{


    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0, $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'https://www.svyaznoy.travel/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/svyaznoy.travel.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    if ( $mode == 'setup' )
        $step = 0;

    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $url = 'https://www.svyaznoy.travel/';
            $ch = curl_init();
            curl_setopt(  $ch, CURLOPT_URL, $url );
            curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt(  $ch, CURLOPT_REFERER, $ref );
            curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt(  $ch, CURLOPT_HEADER, 0 );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt(  $ch, CURLOPT_PROXY, '' );
            curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
            curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/4 ) * 1, 0, '' );

            $step = 1;

            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( $step == 1 )
            {
                dump( $step );
                $data['action'] = 'start';
                $data['ad'] = $mod_data['adults'];
                $data['cn'] = $mod_data['children'];
                $data['ic'] = $mod_data['infants'];
                $data['cs'] = $classes[$mod_data['class']];
                $data['from_code'] = $fly_from_iata;
                $data['from_date'] = $date1;
                $data['to_code'] = $fly_to_iata;
                $data['to_date'] = $date2;
                $data['source'] = 'svyaznoy_online_test';

                $url = 'https://www.svyaznoy.travel/avia/inc/addsearchinfo.php';
                $ch = curl_init();
                SetCurlPost( $ch, $data );
                curl_setopt(  $ch, CURLOPT_URL, $url );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3', 'X-Requested-With: XMLHttpRequest' ) );
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/4 ) * 2, 0, '' );

                $step = 2;
                return $ch;
            } else if ( $step == 2 )
            {
                if ( intval( $modules_data[$mod_name]['content'] ) > 0  )
                {
                    dump( $step );
                    $data['ad'] = $mod_data['adults'];
                    $data['cn'] = $mod_data['children'];
                    $data['in'] = $mod_data['infants'];
                    $data['cs'] = $classes[$mod_data['class']];
                    $data['route'] = preg_replace( '`([0-9]{2})\.([0-9]{2})\.[0-9]{4}`si', '$1$2', $date1 ) . $fly_from_iata . $fly_to_iata . preg_replace( '`([0-9]{2})\.([0-9]{2})\.[0-9]{4}`si', '$1$2', $date2 );
                    $data['search_by_airport_from'] = 0;
                    $data['search_by_airport_to'] = 0;
                    $data['source'] = 'svyaznoy_online_test';
                    $data['srcmarker'] = 'svyaznoy_online_test';


                    $url = 'https://www.svyaznoy.travel/o_api/searching/startSync/';
                    $ch = curl_init();
                    SetCurlPost( $ch, $data );
                    curl_setopt(  $ch, CURLOPT_URL, $url );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3', 'X-Requested-With: XMLHttpRequest' ) );
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/4 ) * 3, 0, '' );
                    $step = 3;
                    return $ch;
                }
            }


            $modules_data[$mod_name]['min_price'] = 0;

            if ( !isset( $loc_cx[$fly_to_iata] ) )
                $loc_cx[$fly_to_iata] = 0;

            $loc_cx[$fly_to_iata]++;


            if ( $step == 3 )
            {

                $cont = json_decode( $modules_data[$mod_name]['content'], 1 );
                dump( $cont );

                if ( sizeof( $cont['frs'] ) )
                {

                    $price = $cont['frs'][0]['prcInf']['amt'];
                    /*
                    $o_id = $cont['frs'][0]['dirs'][0]['trps'][0]['id'];
                    $b_id =  isset( $cont['frs'][0]['dirs'][1] ) ? $cont['frs'][0]['dirs'][1]['trps'][0]['id'] : 0;
                    $on_trps = $cont['frs'][0]['dirs'][0]['trps'];
                    $onward = $cont['trps'][$o_id];
                    $backward = $cont['trps'][$b_id];
                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']  = preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2', $onward['stTm'] );
                    list( $h1, $m1 ) = explode( ':', preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2', $onward['fltTm'] ) );
                    list( $h2, $m2 ) = explode( ':', preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2', $onward['stpTm'] ) );
                    $d['fly_time']  = ( $h1 + $h2 + round( ( $m1 + $m2 ) / 60 ) ) . ':' . ( ( $m1 + $m2 ) % 60 );
                    $d['arr_time']  = preg_replace( '`([0-9]{2})([0-9]{2})`si', '$1:$2', $cont['trps'][$on_trps[sizeof($on_trps)-1]['id']]['endTm'] );
                    $d['comp_code'] = $onward['airCmp'];
                    $d['comp_name'] = $onward['airCmp'];
                    $d['flight']    = $onward['airCmp'] . '-' . $onward['fltNm'];
                    $d['airplane']  = $cont['planes'][$onward['plane']];
                    $d['changing']  = sizeof( $cont['frs'][0]['dirs'][0]['trps'] ) - 1;
                    $d['link'] = '';


                    if ( !empty( $date2 ) )
                        $d['back_flight'] = $backward['airCmp'] . '-' . $backward['fltNm'];

*/

                    $res_array = array();
                    $ways = array(0);
                    if ( !$mod_data['one_way'] )
                        $ways[] = 1;

                    foreach ( $ways as $way )
                    {
                        $r_data = $cont['frs'][0]['dirs'][$way]['trps'];
                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();
                        //dump( $r_data );
                        for( $i=0; $i < sizeof( $r_data ); $i++ )
                        {
                            $v = $cont['trps'][$r_data[$i]['id']];;
                            //dump($v);
                            $rd = array();
                            $rd['dep'] = $v['from'];
                            $rd['dept'] = $v['from'];
                            $rd['arr'] = $v['to'];
                            $rd['arrt'] = $v['to'];
                            $rd['dpd'] = preg_replace( '`([0-9]{4})([0-9]{2})([0-9]{2})`si','$3.$2.$1', $v['stDt'] );
                            $rd['dpt'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['stTm'] );
                            $rd['ard'] = !isset( $v['dayChg'] ) ? $rd['dpd'] : date( 'd.m.Y', strtotime( $rd['dpd'] ) + 86400 );
                            $rd['art'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['endTm'] );
                            $rd['flt'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['fltTm'] );
                            $rd['wtt'] = isset( $v['stpTm'] ) ? preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['stpTm'] ) : '';
                            $rd['coc'] = $v['airCmp'];
                            $rd['fli'] = $v['airCmp'] . '-' . $v['fltNm'];
                            $rd['pln'] = $cont['planes'][$v['plane']];
                            $res_array[$n][] = $rd;
                        }
                    }

                    $modules_data[$mod_name]['min_price'] = $price;
                    $modules_data[$mod_name]['fly_details'] = array();
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], '' );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'],'', json_encode( $res_array ) );

                    dump( $res_array );

                } else
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                }

            }

            /*
				if ( preg_match('`<span>([^<]+)<font class="verySmallText">`si', $modules_data[$mod_name]['content'], $p ) )
					$modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $p[1] ) ) );
				else if ( preg_match('`К сожалению, мы не смогли найти`si', utow( $modules_data[$mod_name]['content'] ), $p ) )
					$modules_data[$mod_name]['min_price'] = 'nd';
				else
					$modules_data[$mod_name]['min_price'] = 'nf';
			*/

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
            {
                if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                    return mod_svyaznoy_travel( 'setup', $mod_data, $cookie );
                else
                {
                    $modules_data[$mod_name]['min_price'] = 'nf';
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );

                    return false;
                }
            }

            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            return false;

        } break;
    }
}
//******************************************************** Конец модуля svyaznoy.travel*/



/********************************************************/
/* Модуль обработки сайта biletix.ru                  */
/*                                                      */
/********************************************************/
function mod_biletix_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'https://biletix.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/biletix.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => ' ', 1 => 'Б', 2 => 'Б' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'https://biletix.ru/' );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/3 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( preg_match( '`biletix\.ru/$`si', $modules_data[$mod_name]['info']['url'] ) )
            {
                dump($mod_name , 1)	;
                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://biletix.ru/index.php?tsi_frontoffice_cmd=order_switch' );
                curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                curl_setopt( $ch, CURLOPT_REFERER, $ref );
                curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch, CURLOPT_PROXY, '' );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_NOBODY, 0 );
//					curl_setopt( $ch, CURLOPT_COOKIE, 'CLTRACK=i41s1t27vruu88r9jdlr59qhi0; CLSESSID=i41s1t27vruu88r9jdlr59qhi0;' );
                curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );

                //$data['DirectOnly'] = $mod_data['direct'];
                $data['RT_OW'] = $mod_data['one_way'] ? 'OW' : 'RT';
                $data['adult'] = $mod_data['adults'];
                $data['arrival'] = ' (' .$fly_to_iata . ')';
                $data['child'] = $mod_data['children'];
                $data['class'] = $classes[$mod_data['class']];
                $data['date_format'] = 'site';
                $data['dateback'] = $date2;
                $data['dateback_view'] = $date2;
                $data['dateto'] = $date1;
                $data['dateto_view'] = $date1;
                $data['depart'] = ' (' . $fly_from_iata . ')';
                $data['ibe_ajax_mode'] = 1;
                $data['ibe_ajax_update_areas'] = '#ts_ag_reservation_container,#ts_ag_reservation_stages_container,#ts_basket_container,#ts_ag_personal_menu_container,#ts_ag_ga_container,#ts_ag_reservation_container__form_top,#ts_ag_reservation_container__offer,#ts_ag_reservation_container__offer_lowcost,#ts_ag_reservation_container__split_fares,#ts_ag_offer_filter_container,#ts_ag_all_in_one_offer_filter_horizontal_container,#ts_ag_carrier_matrix_container,#ts_ag_currency,#ts_ag_auth_form,#ts_ag_auth_line_main';
                $data['infant'] = $mod_data['infants'];
                $data['next_page'] = 'choose_trip';

                dump($data);

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/3 ) * 2, 0, '' );

                return $ch;

            }  else
            {
                dump( $mod_name , 3, $modules_data[$mod_name]['info']['url'] );
			    //dump( $modules_data[$mod_name]['content'] );

                $modules_data[$mod_name]['min_price'] = 0;


                if ( !isset( $loc_cx[$fly_to_iata] ) )
                    $loc_cx[$fly_to_iata] = 0;

                $loc_cx[$fly_to_iata]++;
                if ( preg_match('`Варианты перевозки не найдены`si', $modules_data[$mod_name]['content'], $p ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                } else
                if ( !preg_match( '`<div class="offer"(.+?)</form>`sui', $modules_data[$mod_name]['content'], $cont ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';

                } else
                {
                    $cont = $cont[1];
//                    $cont = GetSegment(  $modules_data[$mod_name]['content'], '<div class="offer"', '<div class="offer"' );
                    //dump( $cont );
                    //die();


                    $res_array = array();
                    $ways = array( '`<div class="direction outbound">.+?<tbody class="variant dir_to selected"(.+?)</tbody>`si' => 0 );
                    if ( !$mod_data['one_way'] )
                        $ways['`<div class="direction inbound">.+?<tbody class="variant dir_back selected"(.+?)</tbody>`si'] = 1;

                    foreach ( $ways as $reg => $way )
                    {
                        preg_match( $reg, $cont, $text );
                        preg_match_all( '`<tr class="(.+?)</tr>`si', $text[1], $flys );
                        preg_match_all( '`<div class="trans">(.+?)</div>`si', $text[1], $trans );
                        $flys = $flys[1];
                        $wait_times = $trans[1];
                        //dump( $flys, $wait_times );
                        //continue;

                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        for( $i=0; $i < sizeof( $flys ); $i++ )
                        {
                            preg_match( '`<td class="logo[^"]+">.+?"ak-name" title="([^"]+)">[^<]+</span>([^<]+)</div>\s*<div class="plane">([^<]+)</div>\s*</td>\s*<td class="flight_info">\s*<div class="departure">\s*<span class="time">([^<]+)</span>\s*<span class="date">([^<]+)</span>\s*<div class="point">([^<]+)</div>\s*<div class="airport">([^<]+)<span class="code">/\s*([^<]+)</s.+?<div class="arrival">\s*<span class="time">([^<]+)</span>\s*<span class="date">([^<]+)</span>\s*<div class="point">([^<]+)</div>\s*<div class="airport">([^<]+)<span class="code">/\s*([^<]+)</sp`si', $flys[$i], $v );
                            //dump( $flys[$i], $v );
                            //continue;
                            $v[6] = trim( $v[6] );
                            $v[7] = trim( $v[7] );
                            $v[11] = trim( $v[11] );
                            $v[12] = trim( $v[12] );

                            $rd = array();
                            $rd['dep'] = $v[8];
                            $rd['dept'] = $v[6] == $v[7] ? $v[6] : $v[6] . ', ' .  $v[7];
                            $rd['arr'] = $v[13];
                            $rd['arrt'] = $v[11] == $v[12] ? $v[11] : $v[11] . ', ' .  $v[12];
                            $rd['dpd'] = $v[5];
                            $rd['dpt'] = $v[4];
                            $rd['ard'] = $v[10];
                            $rd['art'] = $v[9];
                            $rd['flt'] = '';
                            $rd['wtt'] = '';
                            $rd['coc'] = $v[1];
                            $rd['fli'] = trim( str_replace( '&nbsp;', '-', $v[2] ) );
                            $rd['pln'] = $v[3];
                            $res_array[$n][] = $rd;
                        }

                    }


                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( preg_replace('`.+?<td class="price">\s*<div class="caption" title="[^"]+">([^<]+)</div>.*`si', '$1', $cont ) ) ) );
                    $d['link'] = '';
                    $modules_data[$mod_name]['fly_details'] = $d;

                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                    dump( $res_array );
                }
/*
                if ( preg_match('`<td class="price">\s*<div class="caption" title="[^"]+">([^<]+)</div>`si', $cont, $p ) )
                {
                    $onward = GetSegment( $cont, '<div class="direction outbound">', '<div class="direction inbound">' );
                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']       = preg_match('`<div class="departure">\s*<span class="time">([^<]+)</span>`si', $onward, $a ) ? $a[1] : '00:00';
                    $d['fly_time']       = preg_match('`В пути:\s*([0-9]+)&nbsp;.&nbsp;([0-9]+)\s*`si', $onward, $a ) ? $a[1] . ':' . $a[2] : '00:00';
                    $d['arr_time']  = preg_match('`<div class="arrival">\s*<span class="time">([^<]+)</span>`si', $onward, $a ) ? $a[1] : '00:00';
                    $d['comp_code'] = trim( preg_match('`<td class="logo logo-small-([^"]+)">`si', $onward, $a ) ? $a[1] : '' );
                    $d['comp_name'] = trim( preg_match('`<span class="ak-name" [^>]+>([^<]+)</span>`si', $onward, $a ) ? $a[1] : '' );
                    $d['flight']    = trim( preg_match('`<span class="ak-name" [^>]+>[^<]+</span>\s*(.{2})&nbsp;([^<\s]+)\s*<`si', $onward, $a ) ? $a[1] . '-' . $a[2] : '' );
                    $d['airplane']  = trim( preg_match('`<div class="plane">([^<]+)</div>`si', $onward, $a ) ? $a[1] : '' );
                    $d['changing']  = trim( preg_match( '`<div class="departure">\s*<span class="time">([^<]+)</span>`si', $onward, $a ) ? sizeof( $a[1] ) - 1 : 0 );
                    $d['link']  = '';

                    if ( !empty( $date2 ) )
                    {
                        $backward = GetSegment( $cont, '<div class="direction inbound">', '<td class="rules_and_coditions">' );
                        $d['back_flight'] = trim( preg_match('`<span class="ak-name" [^>]+>[^<]+</span>\s*(.{2})&nbsp;([^<\s]+)\s*<`si', $backward, $a ) ? $a[1] . '-' . $a[2] : '' );
                    }


                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $p[1] ) ) );
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает!' );

                    dump($d);
                }

                else
                    if ( preg_match('`Варианты перевозки не найдены`si', $modules_data[$mod_name]['content'], $p ) )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }
                    else
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }
*/

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_biletix_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                return false;
            }
        } break;
    }


}
//******************************************************** Конец модуля biletix.ru


/********************************************************/
/* Модуль обработки сайта trip.ru                  */
/*                                                      */
/********************************************************/
function mod_trip_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $info = array(), $step = 0;

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.trip.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/trip.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => 'Y', 1 => 'C', 2 => 'F' );
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $ch = curl_init();
            curl_setopt( $ch,  CURLOPT_URL, 'http://www.trip.ru/' );
            curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
            curl_setopt( $ch,  CURLOPT_REFERER, $ref );
            curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch,  CURLOPT_HEADER, 1 );
            curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch,  CURLOPT_PROXY, '' );
            curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
            curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            $step = 1;
            SaveMinInfo( 0, round( 100/4 ) * 1, 0, '' );
            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info'] );


            if ( ( $step == 1 ) && preg_match( '`<input name="authenticity_token" type="hidden" value="([^"]+)"\s*/>`si', $modules_data[$mod_name]['content'], $m ) )
            {

                dump($step );

                $data['utf8'] = '%E2%9C%93';
                $data['authenticity_token'] = $m[1];
                $data['e_travel_flights_search']['landing_page'] = '';
                $data['e_travel_flights_search']['affiliate_id'] = '';
                $data['e_travel_flights_search']['affiliate_marker'] = '';
                $data['e_travel_flights_search']['one_way'] = $mod_data['one_way'] ? 'true' : 'false';
                $d = explode( '.', $date1 );
                $data['e_travel_flights_search']['departure'] = $d[2] . '-' . $d[1] . '-' . $d[0];
                $d = explode( '.', $date2 );
                $data['e_travel_flights_search']['return'] = $d[2] . '-' . $d[1] . '-' . $d[0];
                $data['e_travel_flights_search']['three_days'] = 0;
                $data['e_travel_flights_search']['from'] = iconv( 'windows-1251', 'utf-8', '(' . $fly_from_iata . ')' );
                $data['e_travel_flights_search']['to'] = iconv( 'windows-1251', 'utf-8', '(' .  $fly_to_iata . ')' );
                $data['departure_return'] = iconv( 'windows-1251', 'utf-8', '(' .  $fly_to_iata . ')' );
                $data['passengers'] = $mod_data['adults'] + $mod_data['children'] + $mod_data['infants'];
                $data['commit'] = "Поиск";
                $data['e_travel_flights_search']['airline'] = '';
                $data['e_travel_flights_search']['seat_class'] = $classes[$mod_data['class']];
                $data['e_travel_flights_search']['direct'] = $mod_data['direct'];

                //$data['e_travel_flights_search_departure_datepicker']=str_replace( '.', '/', $date1 );
                //$data['e_travel_flights_search']['return_departure_diff'] = '432000';
                //$data['e_travel_flights_search_return_datepicker']=str_replace( '.', '/', $date2 );
                $data['e_travel_flights_search']['adults'] = $mod_data['adults'];
                $data['e_travel_flights_search']['children'] = $mod_data['children'];
                $data['e_travel_flights_search']['infants'] = $mod_data['infants'];

                $ch = curl_init();
                curl_setopt( $ch,  CURLOPT_URL, 'http://www.trip.ru/flights/searches' );
                curl_setopt( $ch,  CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch,  CURLOPT_TIMEOUT, 60 );
                curl_setopt( $ch,  CURLOPT_REFERER, $ref );
                curl_setopt( $ch,  CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch,  CURLOPT_HEADER, 1 );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,  CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch,  CURLOPT_PROXY, '' );
                curl_setopt( $ch,  CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch,  CURLOPT_NOBODY, 0 );
                curl_setopt( $ch,  CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch,  CURLOPT_COOKIEFILE, $cookie );

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                $step = 2;
                SetCurlPost( $ch, $data );
                SaveMinInfo( 0, round( 100/4 ) * 2, 0, '' );
                //$info['link'] = $modules_data[$mod_name]['info']['url'];

                return $ch;

            } else
                if ( ( $step == 2 ) && preg_match( '`http://www\.trip\.ru/flights/searches/(.{32})$`si', $modules_data[$mod_name]['info']['url'], $m ) )
                {
                    dump( $step );

                    $ch = curl_init();
                    curl_setopt(  $ch, CURLOPT_URL, 'http://www.trip.ru/flights/searches/' . trim( $m[1] ) . '/journey_groups' );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt(  $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );

                    $modules_data[$mod_name]['min_price'] = 0;

                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    $info['link'] = $modules_data[$mod_name]['info']['url'];

                    usleep( 1000 );
                    $step = 3;
                    SaveMinInfo( 0, round( 100/4 ) * 3, 0, '' );

                    return $ch;

                } else
                {
                    dump( $step );
//				dump( $modules_data[$mod_name]['info'] );
//				die();

                    $modules_data[$mod_name]['min_price'] = 0;

                    if ( !isset( $loc_cx[$fly_to_iata] ) )
                        $loc_cx[$fly_to_iata] = 0;

                    $loc_cx[$fly_to_iata]++;
                    /*
                    $cont = GetSegment( utow( $modules_data[$mod_name]['content'] ), '<div class="resultGroup" id="', '<div class="resultGroup" id="' );


                    if ( preg_match( '`<span class="groupPrice">([^<]+)</span>`si', $cont, $p ) )
                    {
                        //Берем детальную информацию о полете
                        $d['dep_date']  = $date1;
                        $d['arr_date']  = $date2;
                        $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                        $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                        $d['dep_from_city']  = $mod_data['fly_from_city'];
                        $d['arr_to_city']    = $mod_data['fly_to_city'];
                        $d['dep_time']       = preg_match('`<span class="departureTime">([^<]+)</span>`si', $cont, $a ) ? $a[1] : '00:00';
                        $d['fly_time']       = preg_match('`<span class="totalJourneyTime">\s*Длительность\s*:\s*([0-9]+)h\s*(?:([0-9]+)m\s*)?<`si', $cont, $a ) ? $a[1] . ':' . intval( $a[2] ) : '00:00';
                        $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                        $d['comp_code'] = trim( preg_match('`<td class="flight">\s*([^\-]+)-[0-9]+\s*`si', $cont, $a ) ? $a[1] : '' );
                        $d['comp_name'] = trim( preg_match('`<span class="airline">([^<]+)</span>`si', $cont, $a ) ? $a[1] : '' );
                        $d['flight']    = trim( preg_match('`<td class="flight">\s*([^\-]+-[0-9]+)`si', $cont, $a ) ? $a[1] : '' );
                        $d['airplane']  = trim( preg_match('`<span class="aircraftDescription">([^<]+)</span>`si', $cont, $a ) ? $a[1] : '' );
                        preg_match( '`<td class="legStopsColumn">(.+?)</td>`si', $cont, $a );
                        $a = trim( $a[1] );
                        if ( preg_match( '`\-`si', $a ) )
                            $d['changing'] = 0;
                        else if ( preg_match( '`<abbr`si', $a ) )
                            $d['changing']  = 1;
                        else if ( preg_match( '`([0-9]+)\s*перес`si', $a, $a ) )
                            $d['changing']  = $a[1];
                        $d['link'] =  $info['link'];

                        if ( !empty( $date2 ) )
                            $d['back_flight'] = trim( preg_match('`Обратно.+?<td class="flight">\s*([^\-]+-[0-9]+)`si', $cont, $a ) ? $a[1] : '' );

                        $modules_data[$mod_name]['min_price'] = str_replace( ',', '.', trim( preg_replace( '`[^0-9,]+`si', '', strip_tags( $p[1] ) ) ) );
                        $modules_data[$mod_name]['fly_details'] = $d;
                        SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                        SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает!' );


                        dump( $d );

                    }
                    else if ( preg_match( '`<div id="notificationsContainer" class="warningmsg">`si', $modules_data[$mod_name]['content'] ) )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }

                    else
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }
*/

                    if ( !preg_match( '`<div class="row resultGroup product-details flights-product-details"(.+?)<div class="price-details">`si', $modules_data[$mod_name]['content'], $cont ) )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';

                    } else
                    {
                        $cont = $cont[1];
                        //dump( $cont );
                        //die();
//                    $cont = GetSegment(  $modules_data[$mod_name]['content'], '<div class="offer"', '<div class="offer"' );
                        //dump( $cont );
                        //die();


                        $res_array = array();
                        $ways = array( '`class="tripDetails departure"(.+?<div class="product-details-footer">)`si' => 0 );
                        if ( !$mod_data['one_way'] )
                            $ways['`class="tripDetails return"(.+?<div class="product-details-footer">)`si'] = 1;

                        foreach ( $ways as $reg => $way )
                        {
                            preg_match( $reg, $cont, $text );
                            preg_match_all( '`<div class="flights-product-details-leg">(.+?)<div class="product-details-content-column product-details-icons-column right show-for-medium-only">`si', $text[1], $flys );
                            preg_match_all( '`<div class="product-details-separator">\s*<span>(.+?)</span>\s*</div>`si', $text[1], $trans );
                            $flys = $flys[1];
                            $wait_times = $trans[1];
                            //if ( $way )
                                //dump( $text[1] );
                            //dump( $wait_times );
//                            dump( $flys );
//                            die();continue;

                            $n = !$way ? 'onw' : 'bkw';
                            if ( !isset( $res_array[$n] ) )
                                $res_array[$n] = array();

                            for( $i=0; $i < sizeof( $flys ); $i++ )
                            {
                                preg_match( '`<span class=".+?date">([^<]+)</sp.+?time">([^<]+)</sp.+?details-to">\s*<b>([^<]+)</b>.+?>([^<]+)</abbr>.+?detail-text">\s*<span>([^<]+)<.+?<span class=".+?date">([^<]+)</sp.+?time">([^<]+)</sp.+?details-to">\s*<b>([^<]+)</b>.+?>([^<]+)</abbr>.+?detail-text">\s*<span>([^<]+)<.+?clock_gray"></i>\s*<span>([^<]+)</span>.+?<li>Рейс ([^<]+)</li>.+?aircraftDescription">([^<]+)</s`si', $flys[$i], $v );
                                //dump( $v, $flys[$i] );
                                //die();continue;

                                $rd = array();
                                $rd['dep'] = $v[4];
                                $rd['dept'] = $v[5] == $v[3] ? $v[5] : $v[5] . ', ' .  $v[3];
                                $rd['arr'] = $v[9];
                                $rd['arrt'] = $v[10] == $v[8] ? $v[10] : $v[10] . ', ' . $v[8];
                                $rd['dpd'] = preg_replace( '`.+?([0-9]{2})/([0-9]{2})`si', '$1.$2' . date('.Y'), $v[1] );
                                $rd['dpt'] = $v[2];
                                $rd['ard'] = preg_replace( '`.+?([0-9]{2})/([0-9]{2})`si', '$1.$2' . date('.Y'), $v[6] );
                                $rd['art'] = $v[7];
                                $rd['flt'] = preg_replace( '`([0-9]+)h\s*([0-9]+)m`si', '$1.$2' , $v[11] );
                                $rd['wtt'] = isset( $wait_times[$i] ) ? preg_replace( '`.+?([0-9]+)h\s*([0-9]+)m.*`si', '$1:$2', $wait_times[$i] ) : '';
                                $rd['coc'] = preg_replace( '`.+?class="airlineLogo" src="[^"]+" title="([^"]+)".*`si', '$1', $flys[$i] );
                                $rd['fli'] = $v[12];
                                $rd['pln'] = $v[13];
                                $res_array[$n][] = $rd;
                            }

                        }


                        preg_match('`<div class="price">(.+?)</div>`si', $cont, $price );
                        $price = $price[1];
                        $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $price ) ) );
                        $d['link'] =  $info['link'];
                        $modules_data[$mod_name]['fly_details'] = $d;

                        SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                        SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                        dump( $res_array );
                    }


                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();

                    if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
                    {
                        if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                            return mod_trip_ru( 'setup', $mod_data, $cookie );
                        else
                        {
                            $modules_data[$mod_name]['min_price'] = 'nf';

                            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                            $modules_data[$mod_data['mod_name']]['end_time'] = time();
                            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );

                            return false;
                        }
                    }

                    dump( $mod_name, $d['link'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                    return false;


                }
        } break;
    }


}
//******************************************************** Конец модуля trip.ru


/********************************************************/
/* Модуль обработки сайта amargo.ru                  */
/*                                                      */
/********************************************************/
function mod_amargo_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.amargo.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
//    $cookie = getcwd() . '/cookies/amargo.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];
    $classes = array( 0 => 'Y', 1 => 'C', 2 => 'F' );
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $ch = curl_init();
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.amargo.ru/' );
            curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt(  $ch, CURLOPT_REFERER, $ref );
            curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt(  $ch, CURLOPT_HEADER, 0 );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt(  $ch, CURLOPT_PROXY, '' );
            curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
            curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );
            curl_setopt(  $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/4 ) * 1, 0, '' );
            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );
            if ( preg_match( '`http://www\.amargo\.ru/?$`si', $modules_data[$mod_name]['info']['url'] ) )
            {
                dump($mod_name, 1 );
                $days = empty( $date2 )  ? '' : round( ( strtotime( $date2 ) - strtotime( $date1 ) ) / 86400 );

                $date1 = str_replace( '.', '/', $date1 );
                $date2 = @str_replace( '.', '/', $date2 );
                list( $d1, $m1, $y1 ) = explode( '/', $date1 );
                @list( $d2, $m2, $y2 ) = explode( '/', $date2 );
                $loc1 = GetAirCode( $mod_name, array(  'iata_code' => $fly_from_iata, 'city' => $mod_data['fly_from_city'], 'airport' => $mod_data['fly_from_airport'] ) );

                $loc2 = GetAirCode( $mod_name, array(  'iata_code' => $fly_to_iata, 'city' => $mod_data['fly_to_city'], 'airport' => $mod_data['fly_to_airport'] ) );


                if ( $mod_data['direct'] )
                    $data['Search/AirDirectOnly'] = 1;
                $data['xSellMode'] = 'false';
                $data['dropOffLocationRequired'] = 'false';
                $data['searchTypeValidator'] = 'F';
                $data['Search/searchType'] = 'F';
                $data['Search/flightType'] = $mod_data['one_way'] ? 'oneway' : 'return';

                $data['Search/DateInformation/numNights'] = $days;
                $data['Search/OriginDestinationInformation/Origin/location_input'] = $loc1;
                $data['Search/OriginDestinationInformation/Origin/location'] = $loc1;
                $data['Search/OriginDestinationInformation/Origin/location_leg'] = '';

                $data['Search/DateInformation/depart'] = $date1;
                $data['Search/DateInformation/departDay'] = $d1;
                $data['Search/DateInformation/departMonth'] = $m1;
                $data['Search/DateInformation/departYear'] = $y1;
                $data['Search/DateInformation/departDate'] = $date1;

                $data['Search/OriginDestinationInformation/Destination/location_input'] = $loc2;
                $data['Search/OriginDestinationInformation/Destination/location'] = $loc2;
                $data['Search/OriginDestinationInformation/Destination/location_leg'] = '';

                $data['Search/DateInformation/return'] = $date2;
                $data['Search/DateInformation/returnDay'] = $d2;
                $data['Search/DateInformation/returnMonth'] = $m2;
                $data['Search/DateInformation/returnYear'] = $y2;
                $data['Search/DateInformation/returnDate'] = $date2;
                $data['Search/DateInformation/language'] = 'ru';
                $data['Search/calendarSearched'] = 'false';

                $data['Search/HotelInformation/RoomInformation$1$/adults'] = $mod_data['adults'];
                $data['Search/HotelInformation/RoomInformation$1$/children'] = $mod_data['children'];
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$1$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$2$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$3$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$4$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$5$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$6$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$7$'] = '0';
                $data['Search/HotelInformation/RoomInformation$1$/ChildAges/childAge$8$'] = '0';

                $data['Search/HotelInformation/RoomInformation$2$/adults'] = $mod_data['adults'];
                $data['Search/HotelInformation/RoomInformation$2$/children'] = $mod_data['children'];
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$1$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$2$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$3$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$4$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$5$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$6$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$7$'] = '0';
                $data['Search/HotelInformation/RoomInformation$2$/ChildAges/childAge$8$'] = '0';

                $data['Search/HotelInformation/RoomInformation$3$/adults'] = $mod_data['adults'];
                $data['Search/HotelInformation/RoomInformation$3$/children'] = $mod_data['children'];
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$1$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$2$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$3$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$4$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$5$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$6$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$7$'] = '0';
                $data['Search/HotelInformation/RoomInformation$3$/ChildAges/childAge$8$'] = '0';

                $data['Search/EventInformation/adults'] = $mod_data['adults'];
                $data['Search/EventInformation/children'] = $mod_data['children'];

                $data['Search/EventInformation/ChildAges/childAge$1$'] = '0';
                $data['Search/EventInformation/ChildAges/childAge$2$'] = '0';
                $data['Search/EventInformation/ChildAges/childAge$3$'] = '0';
                $data['Search/EventInformation/ChildAges/childAge$4$'] = '0';
                $data['Search/EventInformation/ChildAges/childAge$5$'] = '0';

                $data['Search/Passengers/adults'] = $mod_data['adults'];
                $data['Search/Passengers/children'] = $mod_data['children'];
                $data['Search/Passengers/infants'] = $mod_data['infants'];

                $data['infantsOnLap'] = 'false';
                $data['Search/moreOptions'] = 'false';

                $data['Search/seatClass'] = $classes[$mod_data['class']];
                $data['Search/airlinePrefs/airlinePref'] = '';
                $data['Search/alliance'] = '';
                dump($data);


                $ch = curl_init();
                curl_setopt(  $ch, CURLOPT_URL, 'http://www.amargo.ru/common/processSearchForm.do' );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60);
                curl_setopt(  $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );
                curl_setopt(  $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/4 ) * 2, 0, '' );
                return $ch;

            }
            else if ( preg_match( '`common/spinner\.do$`si', $modules_data[$mod_name]['info']['url'] ) )
            {
                dump($mod_name, 2 );
                $ch = curl_init();
                curl_setopt(  $ch, CURLOPT_URL, 'http://www.amargo.ru/common/processSearch.do' );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                curl_setopt(  $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );
                curl_setopt(  $ch, CURLOPT_ENCODING, 'gzip, deflate, identity' );

                SetCurlPost( $ch, array( 'd' => 1 ) );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/4 ) * 3, 0, '' );

                return $ch;
            }
            else
            {

                dump($mod_name, 3 );

                $modules_data[$mod_name]['min_price'] = 0;

                if ( !isset( $loc_cx[$fly_to_iata] ) )
                    $loc_cx[$fly_to_iata] = 0;

                $loc_cx[$fly_to_iata]++;

/*
                if ( preg_match('`<div class="displayInline priceNumber currencyFont">([^,+]+),[0-9]*</div>\s*<div class="displayInline currencySymbol currencySymbolFont" lang="ru"> RUB</div>\s*</span></span>\s*<div class="hlimit">`si', $modules_data[$mod_name]['content'], $p ) )
                {
                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']       = trim( preg_match('`Вылет&nbsp;<span class="ftime" style="[^"]+">([^<]+)</span>`si', $cont, $a ) ? $a[1] : '00:00' );
                    $d['fly_time']       = trim( preg_match('`-10px;">(?:&nbsp;)?([0-9]+)ч\.&nbsp;([0-9]+)мин\.<`si', $cont, $a ) ? $a[1] . ':' . $a[2] : '00:00' );
                    $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                    $d['comp_code'] = trim( preg_match('`air/logos/([^\.]+)\.gif"`si', $cont, $a ) ? $a[1] : '' );
                    $d['comp_name'] = trim( preg_match('`air/logos/[^\.]+\.gif" title="([^"]+)"`si', $cont, $a ) ? $a[1] : '' );
                    $d['flight']    = trim( preg_match('`<span class="show-equip-type"><b>([0-9a-z]+)([0-9]+)</b>`si', $cont, $a ) ? $a[1] . '-' . $a[2] : '' );
                    $d['airplane']  = trim( preg_match('`<div class="infcont">([^<]+)</div>`si', $cont, $a ) ? $a[1] : '' );
                    $d['changing']  = preg_match( '`<div class="gray currencySymbolFont">([^<]*)</div>`si', $cont, $a ) ? intval( preg_replace( '`[^0-9]+`si', '', $a[1] ) ) : 0;
                    $d['link'] = '';

                    if ( !empty( $date2 ) )
                        $d['back_flight'] = trim( preg_match('`"dir">Обратно</div>.+?"show-equip-type"><b>([a-z]{2})([0-9]+)`si', $cont, $a ) ? $a[1] . '-' . $a[2] : '' );

                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $p[1] ) ) );
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], '{}' );

                    dump( $d );
                }
                else if ( preg_match('`На Ваш запрос варианты рейсов`si', utow( $modules_data[$mod_name]['content'] ), $p ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                }
                else
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                }

*/


                if ( !preg_match( '`(<div class="dialogpere".+?)<table width="100%">\s*<tr>\s*<td width="80%">`si', $modules_data[$mod_name]['content'], $cont ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';

                } else
                {
                    $cont = $cont[1];
                    //dump( $cont );
                    //die();
//                    $cont = GetSegment(  $modules_data[$mod_name]['content'], '<div class="offer"', '<div class="offer"' );
                    //dump( $cont );
                    //die();


                    $res_array = array();
                    $ways = array( '`(<table cellspacing="0" class="perectab">.+?</table>)`si' => 0 );
                    if ( !$mod_data['one_way'] )
                        $ways['`</table>.+?(<table cellspacing="0" class="perectab">.+?</table>)`si'] = 1;

                    foreach ( $ways as $reg => $way )
                    {
                        preg_match( $reg, $cont, $text );


                        preg_match_all( '`(<tr>\s*<td class="datetime">.+?</tr>\s*<tr>.+?</tr>\s*<tr>.+?</tr>)`si', $text[1], $flys );
                        preg_match_all( '`пересадка&nbsp;<span class="(?:red )?bold">([^<]+)</span>`si', $text[1], $trans );
                        $flys = $flys[1];
                        $wait_times = $trans[1];
                        //if ( $way )
                        //dump( $text[1] );
                        //dump( $wait_times );
                            //dump( $flys, $wait_times );
                            //die();continue;

                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        for( $i=0; $i < sizeof( $flys ); $i++ )
                        {
                            preg_match( '`<td class="datetime">(.+?)</td>.+?<td class="cities"><b>([^<]+)</b>,\s*(.+?)<span class="gray">&nbsp;\((.+?)\)</span>.+?<td class="perelogo" rowspan="3">\s*<img alt="([^"]+)".+?<span class="show-equip-type"><b>([^<]+)</b>.+?class="infcont">([^<]+)</d.+?продлится&nbsp;<b>([^<]+)<.+?<td class="datetime">(.+?)</td>.+?<td class="cities"><b>([^<]+)</b>,\s*(.+?)<span class="gray">&nbsp;\((.+?)\)</span>`si', $flys[$i], $v );
                            unset($v[0]);
                            //dump( $v  );
                            //;continue;

                            $rd = array();
                            $rd['dep'] = $v[4];
                            $rd['dept'] = $v[2] == $v[3] ? $v[2] : $v[2] . ', ' .  $v[3];
                            $rd['arr'] = $v[12];
                            $rd['arrt'] = $v[10] == $v[11] ? $v[10] : $v[10] . ', ' . $v[11];
                            $rd['dpd'] = !$way ? $date1 : $date2;
                            $rd['dpt'] = preg_replace( '`.*?([0-9]{2}:[0-9]{2}).*`si', '$1', strip_tags( $v[1] ) );
                            $rd['ard'] = !$way ? $date1 : $date2;
                            $rd['art'] = preg_replace( '`.*?([0-9]{2}:[0-9]{2}).*`si', '$1', strip_tags( $v[9] ) );
                            $rd['flt'] = $v[8];
                            $rd['wtt'] = isset( $wait_times[$i] ) ? $wait_times[$i]  : '';
                            $rd['coc'] = $v[5];
                            $rd['fli'] = preg_replace( '`([a-z]{2})([0-9]+)`si', '$1-$2',$v[6] );
                            $rd['pln'] = $v[7];
                            $res_array[$n][] = $rd;
                        }

                    }


                    preg_match('`<div class="displayInline priceNumber\s*(?:currencyFont)?">([^,+]+),[0-9]*</div>`sui', $cont, $price );
                    $price = $price[1];
                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $price ) ) );
                    $d['link'] =  '';
                    $modules_data[$mod_name]['fly_details'] = $d;

                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                    dump( $res_array );
                }

                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                {
                    if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                        return mod_amargo_ru( 'setup', $mod_data, $cookie );
                    else
                    {
                        $modules_data[$mod_name]['min_price'] = 'nf';
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();
                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );

                        return false;
                    }
                }

                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                return false;
            }


        } break;
    }


}
//******************************************************** Конец модуля amargo.ru


/********************************************************/
/* Модуль обработки сайта agent.ru                      */
/*                                                      */
/********************************************************/
function mod_agent_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://agent.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/agent.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    $classes = array( 0 => 'ECONOMIC', 1 => 'BUSINESS', 2 => 'FIRST' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, 'https://www.agent.ru/ru/' );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );


            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            SaveMinInfo( 0, round( 100/6 ) * 1, 0, '' );

            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );
//			dump( $modules_data[$mod_name]['content'] );


            if ( preg_match( '`www\.agent\.ru/$`si', $modules_data[$mod_name]['info']['url'] ) )
            {
                $from = GetAirCode( $mod_name, array(  'iata_code' => $fly_from_iata, 'city' => $mod_data['fly_from_city'], 'airport' => $mod_data['fly_from_airport'] ) );
                $to   = GetAirCode( $mod_name, array(  'iata_code' => $fly_to_iata, 'city' => $mod_data['fly_to_city'], 'airport' => $mod_data['fly_to_airport'] ) );

                $data['segments[0].departurePointId'] = $from['id'];
                $data['segments[0].departurePointType'] = $from['type'];

                $data['segments[0].arrivalPointId'] = $to['id'];
                $data['segments[0].arrivalPointType'] = $to['type'];

                $data['segments[0].departureDate'] = $date1;

                if ( !empty( $date2 ) )
                {
                    $data['segments[1].departurePointId'] = $to['id'];
                    $data['segments[1].departurePointType'] = $to['type'];

                    $data['segments[1].arrivalPointId'] = $from['id'];
                    $data['segments[1].arrivalPointType'] = $from['type'];

                    $data['segments[1].departureDate'] = $date2;
                }

                $data['adultsCount'] = $mod_data['adults'];
                $data['childrenCount'] = $mod_data['children'];
                $data['infantsWithoutSeatCount'] = '0';
                $data['infantsWithSeatCount'] = $mod_data['children'];
                $data['bookingClass'] = $classes[$mod_data['class']];
                if ( $mod_data['direct'] )
                    $data['directFlightsOnly'] = 'on';

                dump( $data );

                $ch = curl_init();
                curl_setopt( $ch, CURLOPT_URL, 'https://www.agent.ru/ru/booking/' );
                curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                curl_setopt( $ch, CURLOPT_REFERER, $ref );
                curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt( $ch, CURLOPT_HEADER, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch, CURLOPT_PROXY, '' );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_NOBODY, 0 );
                curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );

                SetCurlPost( $ch, $data );
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                $info['link'] = PostDataEncode( $data );
                SaveMinInfo( 0, round( 100/6 ) * 2, 0, '' );

                return $ch;
            } else
                if ( preg_match( '`booking/searching$`si', $modules_data[$mod_name]['info']['url'] ) || ( preg_match( '`\{"active":([^,]+),`si', $modules_data[$mod_name]['content'], $m ) && ( $m[1] == 'true') ) )
                {
                    dump( $mod_name , 1 );
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, 'https://www.agent.ru/ru/services/booking/searchStatus' );
                    curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                    curl_setopt( $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch, CURLOPT_HEADER, 0 );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch, CURLOPT_PROXY, '' );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                    SetCurlPost( $ch, '' );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/6 ) * 3, 0, '' );

                    return $ch;

                } else if ( preg_match( '`services/booking/searchStatus$`si', $modules_data[$mod_name]['info']['url'] ) )
                {
                    dump( $mod_name , 2 );
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, 'https://www.agent.ru/ru/booking/searching' );
                    curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                    curl_setopt( $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch, CURLOPT_HEADER, 0 );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch, CURLOPT_PROXY, '' );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                    SetCurlPost( $ch, '' );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/6 ) * 4, 0, '' );

                    return $ch;

                } else if ( preg_match( '`booking/choice$`si', $modules_data[$mod_name]['info']['url'] ) )
                {
                    dump( $mod_name , 3 );
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, 'https://www.agent.ru/ru/services/booking/variants?page=0&_=1311268158920' );
                    curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
                    curl_setopt( $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt( $ch, CURLOPT_HEADER, 0 );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt( $ch, CURLOPT_PROXY, '' );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/6 ) * 5, 0, '' );

                    return $ch;

                } else
                {

                    dump( $mod_name , 4 );

                    $modules_data[$mod_name]['min_price'] = 0;

                    if ( !isset( $loc_cx[$fly_to_iata] ) )
                        $loc_cx[$fly_to_iata] = 0;

                    $loc_cx[$fly_to_iata]++;

                    if ( preg_match( '`FLIGHTS_NOT_FOUND`si', $modules_data[$mod_name]['info']['url'] ) )
                    {
                        SaveMinInfo( 2, 100, 0, '' );
                        SaveFullInfo( 2, 100, 0, '', '{}' );
                        $modules_data[$mod_name]['min_price'] = 'nf';
                    }
                    else
                    {
                        $cont = json_decode( $modules_data[$mod_name]['content'], 1 );
                        dump( $cont );

                        if ( !$cont )
                        {
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );
                            $modules_data[$mod_name]['min_price'] = 'nf';
                        }
                        else
                        {
                            /*
                            $flight = $cont['list'][0]['bookingFlightSegments'][0]['flights'][0]['bookingFlightInfo'];
                            $flight2 = $cont['list'][0]['bookingFlightSegments'][1]['flights'][0]['bookingFlightInfo'];

                            //Берем детальную информацию о полете
                            $d['dep_date']  = $date1;
                            $d['arr_date']  = $date2;
                            $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                            $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                            $d['dep_from_city']  = $mod_data['fly_from_city'];
                            $d['arr_to_city']    = $mod_data['fly_to_city'];
                            $d['dep_time']  = date( 'H:i', round( $flight['departureDateTime'] / 1000 ) + $flight['departureDateServerTimezoneOffset'] * 60 );
                            $d['fly_time']  = $cont['list'][0]['bookingFlightSegments'][0]['totalSegmentTime']['hours'] . ':' . $cont['list'][0]['bookingFlightSegments'][0]['totalSegmentTime']['minutes'];
                            $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                            $d['comp_code'] = $flight['operatingAircompany']['codeIATA'];
                            $d['comp_name'] = utow( $flight['operatingAircompany']['name'] );
                            $d['flight']    = $flight['operatingAircompany']['codeIATA'] . '-' . $flight['flightNumber'];
                            $d['airplane']  = utow( $flight['planeType']['name'] );
                            $d['changing']  = sizeof( $cont['list'][0]['bookingFlightSegments'][0]['flights'] ) - 1;
                            $d['link']      = 'http://www.agent.ru/ru/booking/directSearch?' . $info['link'] . 'showLowcost=true&directFlightsOnly=false&bookingClass=ECONOMIC&aircompanyId=&allianceId=';

                            if ( !empty( $date2 ) )
                                $d['back_flight'] = $flight2['operatingAircompany']['codeIATA'] . '-' . $flight2['flightNumber'];
                            */
                            //$d['link']      = 'http://www.agent.ru/ru/booking/directSearch?' . $info['link'] . 'showLowcost=true&directFlightsOnly=' . ( $mod_data['direct'] ? 'true' : 'false' ) . '&bookingClass=' . $classes[$mod_data['class']] . '&aircompanyId=&allianceId=';
                            $d['link'] = 'http://www.agent.ru' . $cont['list'][0]['url'];

                            $res_array = array();
                            $ways = array(0);
                            if ( !$mod_data['one_way'] )
                                $ways[] = 1;

                            foreach ( $ways as $way )
                            {
                                $r_data = $cont['list'][0]['bookingFlightSegments'][$way]['flights'];
                                $r_data2 = $cont['list'][0]['bookingFlightSegments'][$way]['timeBetweenFlights'];
                                $n = !$way ? 'onw' : 'bkw';
                                if ( !isset( $res_array[$n] ) )
                                    $res_array[$n] = array();
                                //dump( $r_data );
                                for( $i=0; $i < sizeof( $r_data ); $i++ )
                                {
                                    $v = $r_data[$i]['bookingFlightInfo'];
                                    //dump($v);
                                    $rd = array();
                                    $rd['dep'] = $v['departureAirport']['codeIATA'];
                                    $rd['dept'] = $v['departureCity']['name'] == $v['departureAirport']['name'] ? $v['departureCity']['name'] : $v['departureCity']['name'] . ', ' .  $v['departureAirport']['name'];
                                    $rd['arr'] = $v['arrivalAirport']['codeIATA'];
                                    $rd['arrt'] = $v['arrivalCity']['name'] == $v['arrivalAirport']['name'] ? $v['arrivalCity']['name'] : $v['arrivalCity']['name'] . ', ' .  $v['arrivalAirport']['name'];
                                    $rd['dpd'] = date( 'd.m.Y', round( $v['departureDateTime'] / 1000 ) + $v['departureDateServerTimezoneOffset'] * 60 );
                                    $rd['dpt'] = date( 'H:i', round( $v['departureDateTime'] / 1000 ) + $v['departureDateServerTimezoneOffset'] * 60 );
                                    $rd['ard'] = date( 'd.m.Y', round( $v['arrivalDateTime'] / 1000 ) + $v['arrivalDateServerTimezoneOffset'] * 60 );
                                    $rd['art'] = date( 'H:i', round( $v['arrivalDateTime'] / 1000 ) + $v['arrivalDateServerTimezoneOffset'] * 60 );
                                    $rd['flt'] = $v['totalFlightTime']['hours'] . ':' . $v['totalFlightTime']['minutes'];
                                    $rd['wtt'] = sizeof( $r_data2 ) && isset( $r_data2[$i] ) ? $r_data2[$i]['hours'] . ':' . $r_data2[$i]['minutes'] : '';
                                    $rd['coc'] = $v['marketingAircompany']['name'];
                                    $rd['fli'] = $v['marketingAircompany']['codeIATA'] . '-' . $v['flightNumber'];
                                    $rd['pln'] = $v['planeType']['name'];

                                    $res_array[$n][] = $rd;
                                }
                            }
                            dump( $res_array );


                            $modules_data[$mod_name]['min_price'] = $cont['list'][0]['bookingPrice']['totalCost'];
                            $modules_data[$mod_name]['fly_details'] = $d;
                            SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                            SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                            dump($d);
                        }
                    }

                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();

                    if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
                    {
                        if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                            return mod_agent_ru( 'setup', $mod_data, $cookie );
                        else
                        {
                            $modules_data[$mod_name]['min_price'] = 'nf';
                            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                            $modules_data[$mod_data['mod_name']]['end_time'] = time();
                            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );

                            return false;
                        }
                    }

                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                    return false;

                }
        } break;
    }


}
/******************************************************** Конец модуля agent.ru */


/********************************************************/
/* Модуль обработки сайта nabortu.ru                  */
/*                                                      */
/********************************************************/
function mod_nabortu_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0, $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.nabortu.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/nabortu.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    if ( $mode == 'setup' )
        $step = 0;
    $classes = array( 0 => 'ekonom', 1 => 'business', 2 => 'business' );
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $url = 'http://www.nabortu.ru/';
            $ch = curl_init();
            curl_setopt(  $ch, CURLOPT_URL, $url );
            curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt(  $ch, CURLOPT_REFERER, $ref );
            curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt(  $ch, CURLOPT_HEADER, 0 );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt(  $ch, CURLOPT_PROXY, '' );
            curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
            curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            $step = 1;
            SaveMinInfo( 0, round( 100/4 ) * 1, 0, '' );

            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( $step == 1 )
            {
                dump( $step );
                if ( $mod_data['direct'] )
                    $data['direct'] = 'on';
                $data['PartnerId'] = 'NABORTU';
                $data['adults_num'] = $mod_data['adults'];
                $data['aviapost'] = 'aviapost';
                $data['children_num'] = $mod_data['children'];
                $data['codeFrom'] = $fly_from_iata;
                $data['codeWhere'] = $fly_to_iata;
                $data['datein'] = $date1;
                $data['dateinfull'] = $date1;
                $data['dateout'] = $date2;
                $data['dateoutfull'] = $date2;
                $data['fb-class'] = $classes[$mod_data['class']];
                $data['from'] = $mod_data['fly_from_city'];
                $data['infants_num'] = $mod_data['infants'];
                $data['init'] = 'true';
                $data['nameFrom'] = $mod_data['fly_from_city'];
                $data['nameWhere'] = $mod_data['fly_to_city'];
                $data['need_return'] = !$mod_data['one_way'] ? 1 : 0;
                $data['posted'] = 'yes';
                $data['where'] = $mod_data['fly_to_city'];

                $url = 'http://www.nabortu.ru/wp-content/themes/nabortu/ajax/callGetVariants.php';
                $ch = curl_init();
                SetCurlPost( $ch, $data );
                curl_setopt(  $ch, CURLOPT_URL, $url );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3', 'X-Requested-With: XMLHttpRequest' ) );
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/4 ) * 2, 0, '' );

                $step = 2;
                return $ch;
            } else if ( $step == 2 )
            {
                dump( $modules_data[$mod_name]['content'] );
                if ( intval( $modules_data[$mod_name]['content'] ) > 0  )
                {
                    dump( $step );
                    if ( $mod_data['direct'] )
                        $data['direct'] = 'on';
                    $data['PartnerId'] = 'NABORTU';
                    $data['adults_num'] = $mod_data['adults'];
                    $data['aviapost'] = 'aviapost';
                    $data['children_num'] = $mod_data['children'];
                    $data['codeFrom'] = $fly_from_iata;
                    $data['codeWhere'] = $fly_to_iata;
                    $data['datein'] = $date1;
                    $data['dateinfull'] = $date1;
                    $data['dateout'] = $date2;
                    $data['dateoutfull'] = $date2;
                    $data['fb-class'] = $classes[$mod_data['class']];
                    $data['from'] = $mod_data['fly_from_city'];
                    $data['infants_num'] = $mod_data['infants'];
                    $data['init'] = 'true';
                    $data['nameFrom'] = $mod_data['fly_from_city'];
                    $data['nameWhere'] = $mod_data['fly_to_city'];
                    $data['need_return'] = !$mod_data['one_way'] ? 1 : 0;
                    $data['posted'] = 'yes';
                    $data['where'] = $mod_data['fly_to_city'];


                    $url = 'http://www.nabortu.ru/aviabilety/?' . PostDataEncode( $data );
                    $ch = curl_init();
                    curl_setopt(  $ch, CURLOPT_URL, $url );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3', 'X-Requested-With: XMLHttpRequest' ) );
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/4 ) * 3, 0, '' );

                    $step = 3;
                    return $ch;
                } else
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                }
            }


            $modules_data[$mod_name]['min_price'] = 0;

            if ( !isset( $loc_cx[$fly_to_iata] ) )
                $loc_cx[$fly_to_iata] = 0;

            $loc_cx[$fly_to_iata]++;


            if ( $step == 3 )
            {

/*
                $cont = GetSegment( $duf = utow( $modules_data[$mod_name]['content'] ), '<td class="avia-buy"', '<td class="avia-buy"' );
                if ( preg_match('`Купить\s*за\s*<span>([^<]+)<`si', $cont, $p ) )
                {

                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']  = preg_match('`<td class="td-time">([^<]+)</td>`si', $cont, $a ) ? $a[1] : '00:00';
                    $d['fly_time']  = preg_match('`<div class="reis-time">В\s*пути\s*([0-9]+)\s*ч\.\s*([0-9]+)\s*мин\.</div>`si', $cont, $a ) ? ( $a[1] . ':' . $a[2] ) : '00:00';
                    $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                    $d['comp_code'] = trim( preg_match('`<td class="td-company" rowspan="2">(.{2})-[^<]+<span>([^<]+)</span>`si', $cont, $a ) ? trim( $a[1] ) : '' );
                    $d['comp_name'] = trim( preg_match('`<td class="td-company" rowspan="2">.{2}-[^<]+<span>([^<]+)</span>`si', $cont, $a ) ? trim( $a[1] ) : '' );
                    $d['flight']    = trim( preg_match('`<td class="td-company" rowspan="2">(.{2}-[^<]+)<span>[^<]+</span>`si', $cont, $a ) ? trim( $a[1] ) : '' );
                    $d['airplane']  = preg_match('`<td class="td-time2" rowspan="2">[^<]+<br />на\s*([^<]+)</td>`si', $cont, $a ) ? trim( $a[1] ) : '';
                    $d['changing']  = preg_match( '`<td class="avia-peresadka">(.+?)</td>`si', $cont, $m ) && preg_match_all( '`<span\s*class="f"\s*>`si', $m[1], $a ) ? sizeof( $a[0] ) - 1 : 0;
                    $d['link'] = $modules_data[$mod_name]['info']['url'];

                    if ( !empty( $date2 ) )
                        $d['back_flight'] = trim( preg_match( '`<tr\s*transfercount="[^<"]*">\s*<td class="avia-company">\s*<div class="air">.+?</div>\s*<div class="air-label">([^<]+)<`si', $cont, $a ) ? $a[1]  : '' );

                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9,]+`si', '', strip_tags( $p[1] ) ) );
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает' );

                    dump( $d );
                }
                else
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                }

*/
                if ( !preg_match( '`(<table class="tab-reis tab-reis2">.+?<div class="pr-label">[^<]+</div>)`si', $modules_data[$mod_name]['content'], $cont ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';

                } else
                {
                    $cont = $cont[1];
                    //dump( $cont );
                    //die();
//                    $cont = GetSegment(  $modules_data[$mod_name]['content'], '<div class="offer"', '<div class="offer"' );
                    //dump( $cont );
                    //die();


                    $res_array = array();
                    $ways = array( '`(<table class="tab-reis tab-reis2">.+?</table>)`si' => 0 );
                    if ( !$mod_data['one_way'] )
                        $ways['`(<table class="tab-reis tab-reis-back tab-reis2">.+?</table>)`si'] = 1;

                    foreach ( $ways as $reg => $way )
                    {
                        preg_match( $reg, $cont, $text );
                        preg_match_all( '`(<tr class="fst">.+?</tr>\s*<tr class="lst">.+?</tr>)`si', $text[1], $flys );
                        preg_match_all( '`Пересадка\s*([0-9]+)\s*ч\.\s*([0-9]+)\s*мин\.`sui', $text[1], $trans );
                        $flys = $flys[1];
                        $wait_times = $trans[1];
                        //if ( $way )
                        //dump( $text[1] );
                        //dump( $wait_times );
//                        dump($text[1], $flys, $wait_times );
//                        die();
                        //dump($text[1], $flys, $wait_times );

                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        for( $i=0; $i < sizeof( $flys ); $i++ )
                        {
                            preg_match( '`td-time">([^<]+)</td>\s*<td class="td-r">([^<,]+)(?:,)?\s*<span>(.*?)\(([^\)]+)\)\s*</span.+?rowspan="2">([^<]+)<span>([^<]+)</span></td>.+?rowspan="2">([0-9]+)\s*ч\.\s*([0-9]+)\s*мин\.<br\s*/>\s*на\s*([^<]+)</td>.+?td-time">([^<]+)</td>.+?"td-r">([^<,]+)(?:,)?\s*<span>(.*?)\(([^\)]+)\)\s*</sp`sui', $flys[$i], $v );
                            unset($v[0]);
//                            dump( $flys[$i], $v  );
//                            die();continue;
                            $v[2] = trim( str_replace(',', '', $v[2] ) );
                            $v[3] = trim( str_replace(',', '', $v[3] ) );
                            $v[11] = trim( str_replace(',', '', $v[11] ) );
                            $v[12] = trim( str_replace(',', '', $v[12] ) );
                            dump($v[2], $v[3], $v[11], $v[12]);

                            $rd = array();
                            $rd['dep'] = $v[4];
                            $rd['dept'] =  !$v[3] || ( $v[2] == $v[3] )  ? $v[2] : $v[2] . ', ' .  $v[3];
                            $rd['arr'] = $v[13];
                            $rd['arrt'] = !$v[12] || ( $v[11] == $v[12] )  ? $v[11] : $v[11] . ', ' . $v[12];
                            $rd['dpd'] = !$way ? $date1 : $date2;
                            $rd['dpt'] = $v[1];
                            $rd['ard'] = !$way ? $date1 : $date2;
                            $rd['art'] = $v[10];
                            $rd['flt'] = $v[7] . ':' . $v[8];
                            $rd['wtt'] = isset( $wait_times[$i] ) ? $wait_times[$i]  : '';
                            $rd['coc'] = $v[6];
                            $rd['fli'] = $v[5];
                            $rd['pln'] = $v[9];
                            $res_array[$n][] = $rd;
                        }

                    }


                    preg_match('`<div class="pr-label">(.+?)</div>`sui', $cont, $price );
                    $price = $price[1];
                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $price ) ) );
                    $d['link'] = $modules_data[$mod_name]['info']['url'];
                    $modules_data[$mod_name]['fly_details'] = $d;

                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                    dump( $res_array );
                }


            /*
				if ( preg_match('`<span>([^<]+)<font class="verySmallText">`si', $modules_data[$mod_name]['content'], $p ) )
					$modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $p[1] ) ) );
				else if ( preg_match('`К сожалению, мы не смогли найти`si', utow( $modules_data[$mod_name]['content'] ), $p ) )
					$modules_data[$mod_name]['min_price'] = 'nd';
				else
					$modules_data[$mod_name]['min_price'] = 'nf';
			*/
            }

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
            {
                if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                    return mod_nabortu_ru( 'setup', $mod_data, $cookie );
                else
                {
                    $modules_data[$mod_name]['min_price'] = 'nf';
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );

                    return false;
                }
            }

            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            return false;

        } break;
    }


}
/******************************************************** Конец модуля nabortu.ru */


/********************************************************/
/* Модуль обработки сайта onetwotrip.com                */
/*                                                      */
/********************************************************/
function mod_onetwotrip_com( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch;
    static $loc_cx = array(), $timeout = array(), $info = array(), $airlines = array();
    if ( !sizeof( $airlines ) )
        include 'onetwotrip.com.base.php';
    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.onetwotrip.com/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/onetwotrip.com.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];


    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );
            list( $d, $m, $y ) = explode( '.', $date1 );
            $y = date( 'y' );
            $s = '';
            if ( !$mod_data['one_way'] )
            {
                list( $d2, $m2, $y2 ) = explode( '.', $date2 );
                $s = $d2 . $m2;
            }
            //referrer=' . urlencode( $ref )
            $url = 'http://www.onetwotrip.com/_api/searching/startSync/?' . 'ad=' . $mod_data['adults'] . ( $mod_data['children'] ?  '&cn=' . $mod_data['children'] : '' ) . ( $mod_data['infants'] ? '&in=' . $mod_data['infants'] : '' ) . '&cs=' . $classes[$mod_data['class']] . '&route=' . ( $info['link'] = $d . $m . $fly_from_iata . $fly_to_iata . $s );

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url  );
            curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 180 );
            curl_setopt( $ch, CURLOPT_REFERER, $ref );
            curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $ch, CURLOPT_PROXY, '' );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_NOBODY, 0 );
            curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();
            SaveMinInfo( 0, round( 100/2 ) * 1, 0, '' );

            return $ch;
        } break;
        case 'parse' :
        {
            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );
            dump( $mod_name, 1 );
//				die( $modules_data[$mod_name]['content'] );


            $modules_data[$mod_name]['min_price'] = 0;

            if ( !isset( $loc_cx[$fly_to_iata] ) )
                $loc_cx[$fly_to_iata] = 0;

            $loc_cx[$fly_to_iata]++;
            $cont = json_decode( $modules_data[$mod_name]['content'], 1 );
            //file_put_contents($fly_to_iata.$fly_from_iata.'.txt', $modules_data[$mod_name]['content'] );
            dump( $modules_data[$mod_name], $modules_data[$mod_name]['content'] );
            GetWorkTime( true );
            usort( $cont['frs'] , create_function( '$a, $b', 'return  TotalPrice( $a["pmtVrnts"]["transactions"]) - TotalPrice( $b["pmtVrnts"]["transactions"] );' ) );
            dump( GetWorkTime( ) );
            //dump( $cont );

            if ( !sizeof( $cont['frs'] ) )
            {
                SaveMinInfo( 2, 100, 0, '' );
                SaveFullInfo( 2, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
            }
            else
            {
                $amount = $cont['frs'][0]['prcInf']['amt'];
                $s = 1;
                if ( $cont['frs'][0]['prcInf']['cur'] != 'RUB' )
                {
                    $cur = $cont['frs'][0]['prcInf']['cur'] . 'RUB';
                    $s = $cont['rates'][$cur];
                }
                $price = ceil( $s * $amount );
                $id1 = $cont['frs'][0]['dirs'][0]['trps'][0]['id'];


                //Берем детальную информацию о полете
                /*
                $d['dep_date']  = $date1;
                $d['arr_date']  = $date2;
                $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                $d['dep_from_city']  = $mod_data['fly_from_city'];
                $d['arr_to_city']    = $mod_data['fly_to_city'];
                $d['dep_time']  = preg_replace('`([0-9]{2})([0-9]{2})`si', '$1:$2', $cont['trps'][$id1]['stTm'] );
                $d['fly_time']  = preg_replace('`([0-9]{2})([0-9]{2})`si', '$1:$2', $cont['trps'][$id1]['fltTm'] );
                $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                $d['comp_code'] = $cont['trps'][$id1]['airCmp'];
                $d['comp_name'] = utow( isset( $cont['trps'][$id1]['oprdBy'] ) ? $airlines[$cont['trps'][$id1]['oprdBy']] : $airlines[$cont['trps'][$id1]['airCmp']] );
                $d['flight']    = $cont['trps'][$id1]['airCmp'] . '-' . $cont['trps'][$id1]['fltNm'];
                $d['airplane']  = utow( $cont['planes'][$cont['trps'][0]['plane']] );
                $d['changing']  = sizeof( $cont['frs'][0]['dirs'][0]['trps'] ) - 1;
                */
                $d['link'] = 'http://www.onetwotrip.com/#' . $info['link'];

                if ( !empty( $date2 ) )
                {
                    $id2 = $cont['frs'][0]['dirs'][1]['trps'][0]['id'];
                    $d['back_flight'] = $cont['trps'][$id2]['airCmp'] . '-' . $cont['trps'][$id2]['fltNm'];
                }

                $res_array = array();
                $ways = array(0);
                if ( !$mod_data['one_way'] )
                    $ways[] = 1;

                foreach ( $ways as $way )
                {
                    $r_data = $cont['frs'][0]['dirs'][$way]['trps'];
                    $n = !$way ? 'onw' : 'bkw';
                    if ( !isset( $res_array[$n] ) )
                        $res_array[$n] = array();
                    //dump( $r_data );
                    for( $i=0; $i < sizeof( $r_data ); $i++ )
                    {
                        $v = $cont['trps'][$r_data[$i]['id']];;
                        $v2 = $r_data[$i];
                        //dump($v);
                        $rd = array();
                        $rd['dep'] = $v['from'];
                        $rd['dept'] = $v['from'];
                        $rd['arr'] = $v['to'];
                        $rd['arrt'] = $v['to'];
                        $rd['dpd'] = preg_replace( '`([0-9]{4})([0-9]{2})([0-9]{2})`si','$3.$2.$1', $v['stDt'] );
                        $rd['dpt'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['stTm'] );
                        $rd['ard'] = !isset( $v['dayChg'] ) ? $rd['dpd'] : date( 'd.m.Y', strtotime( $rd['dpd'] ) + 86400 );
                        $rd['art'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['endTm'] );
                        $rd['flt'] = preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v['fltTm'] );
                        $rd['wtt'] = isset( $v2['stpTm'] ) ? preg_replace( '`([0-9]{2})([0-9]{2})`si','$1:$2', $v2['stpTm'] ) : '';
                        $rd['coc'] = $v['airCmp'];
                        $rd['fli'] = $v['airCmp'] . '-' . $v['fltNm'];
                        $rd['pln'] = $cont['planes'][$v['plane']];
                        $res_array[$n][] = $rd;
                    }
                }

                $modules_data[$mod_name]['min_price'] = $price;
                $modules_data[$mod_name]['fly_details'] = $d;

                SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode($res_array) );

                //dump($d);
                dump(  $res_array);

            }

            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            if ( $modules_data[$mod_name]['min_price'] == 'nf' )
            {
                if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                    return mod_onetwotrip_com( 'setup', $mod_data, $cookie );
                else
                {
                    $modules_data[$mod_name]['min_price'] = 'nf';

                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );

                    return false;
                }
            }

            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            return false;

        } break;
    }


}
/******************************************************** Конец модуля onetwotrip.com */


/********************************************************/
/* Модуль обработки сайта tickets.ru                    */
/*                                                      */
/********************************************************/
function mod_tickets_ru( $mode, $mod_data, $cookie )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0, $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {
        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.tickets.ru/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/tickets.ru.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    if ( $mode == 'setup' )
        $step = 0;
    $classes = array( 0 => 'E', 1 => 'B', 2 => 'B' );

    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $url = 'http://www.tickets.ru/';
            $ch = curl_init();
            curl_setopt(  $ch, CURLOPT_URL, $url );
            curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt(  $ch, CURLOPT_REFERER, $ref );
            curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt(  $ch, CURLOPT_HEADER, 0 );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt(  $ch, CURLOPT_PROXY, '' );
            curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
            curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            $step = 1;
            SaveMinInfo( 0, round( 100/5 ) * 1, 0, '' );
            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            if ( $step == 1 )
            {
                dump( $step );

                $data['ad'] = $mod_data['adults'];
                $data['chd'] = $mod_data['children'];
                $data['inf'] = $mod_data['infants'];
                $data['class'] = $classes[$mod_data['class']];

                $data['directions']['plus_minus'][0] = 0;
                $data['directions']['departure_date'][0] = $date1;
                $data['directions']['from_name'][0] = $mod_data['fly_from_city'];
                $data['directions']['from_code'][0] = $fly_from_iata;
                $data['directions']['to_name'][0] = $mod_data['fly_to_city'];
                $data['directions']['to_code'][0] = $fly_to_iata;
                if ( !$mod_data['one_way'] )
                {
                    $data['directions']['plus_minus'][1] = 0;
                    $data['directions']['departure_date'][1] = $date2;
                    $data['directions']['from_name'][1] = $mod_data['fly_to_city'];
                    $data['directions']['from_code'][1] = $fly_to_iata;
                    $data['directions']['to_name'][1] = $mod_data['fly_from_city'];
                    $data['directions']['to_code'][1] = $fly_from_iata;
                }

                $url = 'http://avia.tickets.ru/m/search';
                $ch = curl_init();
                SetCurlPost(  $ch, $data );
                curl_setopt(  $ch, CURLOPT_URL, $url );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3', 'X-Requested-With: XMLHttpRequest' ) );
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );

                $step = 2;
                return $ch;
            } else if ( $step == 2 )
            {
//					dump( $modules_data[$mod_name]['content'] );
                preg_match('`{"params":{"session_id":"([^"]+)"},"code":false,"count":([0-9]+)`si', $modules_data[$mod_name]['content'], $p1 );

                if ( intval( $p1[2] ) > 0  )
                {
                    dump( $step );

                    $url = 'http://avia.tickets.ru/m/search/results?session_id=' . $p1[1];
                    $ch = curl_init();
                    curl_setopt(  $ch, CURLOPT_URL, $url );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );
                    $step = 3;
                    return $ch;
                }
            } else if ( $step== 3 )
            {
                dump( $step );
                if ( !preg_match('`<div class="offer_tools">\s*<a href="([^"]+)"`si', $modules_data[$mod_name]['content'], $link ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';
                } else
                {
                    $link = str_replace( 'fare_conditions', 'booking', $link[1] ) ;
                    $url = $link;
                    $ch = curl_init();
                    curl_setopt(  $ch, CURLOPT_URL, $url );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );
                    $step = 4;
                    return $ch;
                }
            }



            $modules_data[$mod_name]['min_price'] = 0;

            if ( !isset( $loc_cx[$fly_to_iata] ) )
                $loc_cx[$fly_to_iata] = 0;

            $loc_cx[$fly_to_iata]++;


            if ( $step == 4 )
            {
                dump( $step );


/*
                dump( $modules_data[$mod_name]['content'] );
                $cont = GetSegment( utow( $modules_data[$mod_name]['content'] ), '<div class="one_offer">', '<div class="one_offer">' );


                if ( preg_match( '`<div class="your_price">[^<]+<strong>.+?<span class="RUR ">([^<]+)</span>`si', $cont, $p ) )
                {
                    $price = trim( preg_replace( '`[^0-9,]+`si', '', strip_tags( $p[1] ) ) );
                    //Берем детальную информацию о полете
                    $d['dep_date']  = $date1;
                    $d['arr_date']  = $date2;
                    $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                    $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                    $d['dep_from_city']  = $mod_data['fly_from_city'];
                    $d['arr_to_city']    = $mod_data['fly_to_city'];
                    $d['dep_time']  = preg_replace( '`.+?<li>вылет\s*<strong>([^<]+)<.*`si', '$1', $cont );
                    $d['fly_time']  = preg_replace( '`.+?в\s*пути\s*<strong>\s*([0-9]+)\s*ч\s*([0-9]+)\s*мин.*`si', '$1:$2', $cont );
                    $d['arr_time']  = preg_replace( '`.+?<li>прилет\s*<strong>([^<]+)</strong>.*`si', '$1', $cont );
                    $d['comp_code'] = preg_replace( '`.+?<li>Рейс\s*<strong>(.{2})-[0-9]+</strong></li>.*`si', '$1', $cont );
                    $d['comp_name'] = preg_replace( '`.+?<li>Рейс\s*<strong>(.{2})-[0-9]+</strong></li>.*`si', '$1', $cont );
                    $d['flight']    = preg_replace( '`.+?<li>Рейс\s*<strong>(.+?)</strong></li>.*`si', '$1', $cont );
                    $d['airplane']  = preg_replace( '`.+?<li>Рейс\s*<strong>[^<]+</strong></li>\s*<li>([^<]+)</li>.*`si', '$1', $cont );
                    preg_match( '`<div class="flight_time">(.+?)</div>`si', $cont, $mm );
                    $d['changing'] = preg_match_all( '`<li>`si', $mm[1], $mm2 ) ? sizeof( $mm2[0] ) - 1 : 0;
                    $d['link'] = $modules_data[$mod_name]['info']['url'];

                    if ( !empty( $date2 ) )
                    {
                        preg_match_all( '`<li>Рейс\s*<strong>(.+?)</strong></li>`si', $cont, $m1 );
                        $d['back_flight'] = $m1[1][1];
                    }

                    $modules_data[$mod_name]['min_price'] = $price;
                    $modules_data[$mod_name]['fly_details'] = $d;
                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], 'ура работает' );

                    //dump( $d );
                }
*/
                if ( !preg_match( '`(<!-- one offer -->(.+?)<!-- end one offer -->)`si', $modules_data[$mod_name]['content'], $cont ) )
                {
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    $modules_data[$mod_name]['min_price'] = 'nf';

                } else
                {
                    $cont = $cont[1];
                    //dump( $cont );
                    //die();
//                    $cont = GetSegment(  $modules_data[$mod_name]['content'], '<div class="offer"', '<div class="offer"' );
                    //dump( $cont );
                    //die();


                    $res_array = array();
                    $ways = array( '`(<div class="segment_heading">.+?)<div class="visa_infoblock"`si' => 0 );
                    if ( !$mod_data['one_way'] )
                        $ways['`<div class="visa_infoblock.+?(<div class="segment_heading">.+?)<div class="visa_infoblock"`si'] = 1;

                    foreach ( $ways as $reg => $way )
                    {
                        preg_match( $reg, $cont, $text );
                        preg_match_all( '`(<div class="flight_attributes (?:noborder)?">.+?</div>\s*<div class="clear"></div>\s*</div>)`si', $text[1], $flys );
                        preg_match_all( '`пересадка<br/>\s*<strong>([0-9]+\s*ч\s*[0-9]+)\s*мин</strong>`sui', $text[1], $trans );

                        $flys = $flys[1];
                        $wait_times = $trans[1];
                        //if ( $way )
                        //dump( $text[1] );
                            //dump( $wait_times );
//                        dump($text[1], $flys, $wait_times );
                        //dump( $flys );
                        //die();
                        //dump($text[1], $flys, $wait_times );

                        $n = !$way ? 'onw' : 'bkw';
                        if ( !isset( $res_array[$n] ) )
                            $res_array[$n] = array();

                        for( $i=0; $i < sizeof( $flys ); $i++ )
                        {
                            preg_match( '`<li>Рейс\s*<strong>([^<]+)</strong></li>\s*<li>([^<]+)</li>.+?вылет\s*<strong>([^<]+)</strong>,</li>\s*<li><strong>([^<]+)</strong></li>\s*<li><strong>([^<]+)</strong></li>.+?в\s*пути\s*<strong>([0-9]+)\s*ч\s*([0-9]+)\s*мин.+?прилет\s*<strong>([^<]+)</strong>,</li>\s*<li><strong>([^<]+)</strong></li>\s*<li><strong>([^<]+)</strong></li>`sui', $flys[$i], $v );
                            unset($v[0]);
                            //dump( $flys[$i], $v  );
                            //die();continue;


                            $rd = array();
                            $rd['dep'] = $v[4] == $v[5] ? $v[4] : $v[4] . ', ' .  $v[5];
                            $rd['dept'] = $v[4] == $v[5] ? $v[4] : $v[4] . ', ' .  $v[5];
                            $rd['arr'] = $v[9] == $v[10]  ? $v[9] : $v[9] . ', ' . $v[10];
                            $rd['arrt'] = $v[9] == $v[10]  ? $v[9] : $v[9] . ', ' . $v[10];
                            $rd['dpd'] = !$way ? $date1 : $date2;
                            $rd['dpt'] = $v[3];
                            $rd['ard'] = !$way ? $date1 : $date2;
                            $rd['art'] = $v[8];
                            $rd['flt'] = $v[6] . ':' . $v[7];
                            $rd['wtt'] = isset( $wait_times[$i] ) ? preg_replace( '`([0-9]+)\s*ч\s*([0-9]+)`sui', '$1:$2', $wait_times[$i] )  : '';
                            $rd['coc'] = '';
                            $rd['fli'] = $v[1];
                            $rd['pln'] = $v[2];
                            $res_array[$n][] = $rd;
                        }

                    }


                    preg_match('`<div class="way_topay">\s*<strong>(.+?)</strong>\s*</div>`sui', $modules_data[$mod_name]['content'], $price );
                    $price = $price[1];
                    $modules_data[$mod_name]['min_price'] = trim( preg_replace( '`[^0-9]+`si', '', strip_tags( $price ) ) );
                    $d['link'] = $modules_data[$mod_name]['info']['url'];
                    $modules_data[$mod_name]['fly_details'] = $d;

                    SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                    SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );
                    dump( $res_array );
            }
            }
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            if ( (string)$modules_data[$mod_name]['min_price'] == 'nf' )
            {
                if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                    return mod_tickets_ru( 'setup', $mod_data, $cookie );
                else
                {
                    $modules_data[$mod_name]['min_price'] = 'nf';
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();
                    dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                    SaveMinInfo( 2, 100, 0, '' );
                    SaveFullInfo( 2, 100, 0, '', '{}' );
                    return false;
                }
            }

            dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

            return false;

    } break;
    }

}
/******************************************************** Конец модуля tickets.ru */



/********************************************************/
/* Модуль обработки сайта ozon.travel                  */
/*                                                      */
/********************************************************/
function mod_ozon_travel( $mode, $mod_data, $cookie  )
{
    global $modules_data, $modules_handlers, $mch ;
    static $loc_cx = array(), $timeout = array(), $step = 0, $info = array();

    if ( !isset( $timeout[$mod_data['fly_to_iata']] ) || ( $mode == 'setup' ) )
        $timeout[$mod_data['fly_to_iata']] = time();

    $t = time() - $timeout[$mod_data['fly_to_iata']];

    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];

    if ( $t >= LTIMEOUT  )
    {

        $modules_data[$mod_data['mod_name']]['end_time'] = time();
        $modules_data[$mod_data['mod_name']]['min_price'] = 'to';
        return false;
    }

    $mod_name = $mod_data['mod_name'];

    $ref = 'http://www.ozon.travel/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    //$cookie = getcwd() . '/cookies/ozon.travel.txt';

    $fly_from_iata = $mod_data['fly_from_iata'];
    $fly_to_iata = $mod_data['fly_to_iata'];
    $date1 = $mod_data['date1'];
    $date2 = $mod_data['date2'];
    $only_direct = $mod_data['only_direct'];

    if ( $mode == 'setup' )
        $step = 0;
//	echo urldecode('Commander.CommandExecutionMode=ASYNCHRONOUS&Commander.Command=SearchAirTariffsCalendar&From1=41c43e59-1673-486b-bc1b-c352fd83388a&To1=607b4aa2-5281-406c-a2ce-4bd52c29ef9a&From2=607b4aa2-5281-406c-a2ce-4bd52c29ef9a&To2=41c43e59-1673-486b-bc1b-c352fd83388a&Date1=2012-06-20&Date2=2012-06-25&ServiceClass=ECONOMY&Dlts=1&Children=0&Infants=0')	;
//	die();
    $classes = array( 0 => 'ECONOMY', 1 => 'BUSINESS', 2 => 'FIRST' );
    switch ( $mode )
    {
        case 'setup' :
        {
            SaveFile( $cookie, '' );

            $date1 = str_replace( '.', '/', $date1 );
            $date2 = @str_replace( '.', '/', $date2 );
            list( $d1, $m1, $y1 ) = explode( '/', $date1 );
            @list( $d2, $m2, $y2 ) = explode( '/', $date2 );
            $loc1 = GetAirCode( $mod_name, array(  'iata_code' => $fly_from_iata, 'city' => $mod_data['fly_from_city'], 'airport' => $mod_data['fly_from_airport'] ) );
            $loc2 = GetAirCode( $mod_name, array(  'iata_code' => $fly_to_iata, 'city' => $mod_data['fly_to_city'], 'airport' => $mod_data['fly_to_airport'] ) );
            dump($loc1, $loc2);

            $ch = curl_init();
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.ozon.travel/ajax/ajax-search-commander.html' );
            curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt(  $ch, CURLOPT_REFERER, $ref );
            curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
            curl_setopt(  $ch, CURLOPT_HEADER, 0 );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt(  $ch, CURLOPT_PROXY, '' );
            curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
            curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
            curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3' ) );

            $data['Commander.CommandExecutionMode'] = 'ASYNCHRONOUS';
            $data['Commander.Command'] = 'SearchAirTariffsCalendar';

            $data['From1'] = $loc1;
            $data['To1'] = $loc2;
            $data['Date1'] = $y1 . '-' . $m1 . '-' . $d1;
            if ( !$mod_data['one_way'] )
            {
                $data['From2'] = $loc2;
                $data['To2'] = $loc1;
                $data['Date2'] = $y2 . '-' . $m2 . '-' . $d2;
            }
            $data['ServiceClass'] = $classes[$mod_data['class']];
            $data['Dlts'] = $mod_data['adults'];
            $data['Children'] = $mod_data['children'];
            $data['Infants'] = $mod_data['infants'];

            SetCurlPost( $ch, $data );
            $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
            $modules_data[$mod_data['mod_name']]['end_time'] = time();

            $step = 1;
            SaveMinInfo( 0, round( 100/5 ) * 1, 0, '' );

            return $ch;

        } break;
        case 'parse' :
        {

            //В случай ошибки в сети
            if ( $modules_data[$mod_name]['error'] || !$modules_data[$mod_name]['content'] || empty( $modules_data[$mod_name]['content'] ) )
            {
                SaveMinInfo( 3, 100, 0, '' );
                SaveFullInfo( 3, 100, 0, '', '{}' );
                $modules_data[$mod_name]['min_price'] = 'nf';
                return false;
            }

            dump( $modules_data[$mod_name]['info']['url'] );
            if (  preg_match( '`/ajax/ajax-search-commander\.html$`si', $modules_data[$mod_name]['info']['url'] ) && ( $step == 1 ) && preg_match( '`"CommandId":\s*"([^"]+)",\s*"ContextSessionId":\s*"([^"]+)",`si', $modules_data[$mod_name]['content'], $m ) )
            {

                $data['ContextSessionId'] = $m[2];
                $data['Quantity'] = '2';
                $data['SearchId'] = $info['Commander.CommandId'] = $m[1];

                dump($mod_name, 1 );
                $ch = curl_init();
                curl_setopt(  $ch, CURLOPT_URL, 'http://www.ozon.travel/ajax/check-storage-global.html' );
                curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                curl_setopt(  $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt(  $ch, CURLOPT_PROXY, '' );
                curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );

                SetCurlPost( $ch, $data );

                $modules_data[$mod_name]['min_price'] = 0;
                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                $modules_data[$mod_data['mod_name']]['end_time'] = time();

                $step = 2;
                SaveMinInfo( 0, round( 100/5 ) * 2, 0, '' );

                return $ch;

            }  else
                if ( ( preg_match( '`ajax/check-storage-global\.html$`si', $modules_data[$mod_name]['info']['url'] ) && ( $step == 2 ) && preg_match( '`"Activity":\s*"DONE"`si', $modules_data[$mod_name]['content'], $m ) ) || ( preg_match( '`ajax/ajax-request-commander-y2\.html$`si', $modules_data[$mod_name]['info']['url'] ) && ( $step == 3 ) && preg_match( '`"Activity":\s*"PROCESSING"`si', $modules_data[$mod_name]['content'], $m ) ) )
                {

                    dump($mod_name, 2 );

                    $data['Commander.Command'] = 'SearchAirTariffsCalendar';
                    $data['Commander.CommandExecutionMode'] = 'SHOW_STATE';
                    $data['Commander.CommandId'] = $info['Commander.CommandId'];

                    $ch = curl_init();
                    curl_setopt(  $ch, CURLOPT_URL, 'http://www.ozon.travel/ajax/ajax-request-commander-y2.html' );
                    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                    @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                    curl_setopt(  $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                    curl_setopt(  $ch, CURLOPT_PROXY, '' );
                    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                    curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );

                    SetCurlPost( $ch, $data );

                    $modules_data[$mod_name]['min_price'] = 0;
                    $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                    $modules_data[$mod_data['mod_name']]['end_time'] = time();

                    $step = 3;
                    SaveMinInfo( 0, round( 100/5 ) * 3, 0, '' );

                    return $ch;
                } else
                    if ( preg_match( '`ajax/ajax-request-commander-y2\.html$`si', $modules_data[$mod_name]['info']['url'] ) && ( $step == 3 ) )
                    {
                        dump($mod_name, 3 );

                        $data['Tariffs.Command'] = 'SearchAirTariffsCalendar';
                        $data['Tariffs.CommandExecutionMode'] = 'SHOW_STATE';
                        $data['Tariffs.CommandId'] = $info['Commander.CommandId'];

                        $ch = curl_init();
                        curl_setopt(  $ch, CURLOPT_URL, 'http://www.ozon.travel/ajax/avia_result_data.html' );
                        curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
                        @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                        curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
                        curl_setopt(  $ch, CURLOPT_REFERER, $modules_data[$mod_name]['info']['url'] );
                        curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
                        curl_setopt(  $ch, CURLOPT_HEADER, 0 );
                        curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
                        curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
                        curl_setopt(  $ch, CURLOPT_PROXY, '' );
                        curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
                        curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
                        curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
                        curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );
                        curl_setopt(  $ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3') );

                        SetCurlPost( $ch, $data );

                        $modules_data[$mod_name]['min_price'] = 0;
                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();

                        $step = 4;
                        SaveMinInfo( 0, round( 100/5 ) * 4, 0, '' );

                        return $ch;
                    } else
                    {

                        dump( $mod_name, 4 );

                        $modules_data[$mod_name]['min_price'] = 0;

                        if ( !isset( $loc_cx[$fly_to_iata] ) )
                            $loc_cx[$fly_to_iata] = 0;

                        $loc_cx[$fly_to_iata]++;
                        $cont = preg_replace( '`new\s*Date\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)`sui', '"$3.$2.$1"', trim( substr( $modules_data[$mod_name]['content'], 3 ) ) );
                        $cont = json_decode( $cont, 1 );


                        if ( !isset( $cont['data'] ) || !sizeof( $cont['data'] ) )
                        {
                            SaveMinInfo( 2, 100, 0, '' );
                            SaveFullInfo( 2, 100, 0, '', '{}' );
                            $modules_data[$mod_name]['min_price'] = 'nf';
                        } else
                        {

/*
                            $flight = $cont['data'][0]['segments'][0]['flights'][0]['flightLegs'][0];
                            $flight2 = $cont['data'][0]['segments'][1]['flights'][0]['flightLegs'][0];

                            //Берем детальную информацию о полете
                            $d['dep_date']  = $date1;
                            $d['arr_date']  = $date2;
                            $d['dep_from_iata']  = $mod_data['fly_from_iata'];
                            $d['arr_to_iata']    = $mod_data['fly_to_iata'];
                            $d['dep_from_city']  = $mod_data['fly_from_city'];
                            $d['arr_to_city']    = $mod_data['fly_to_city'];
                            $d['dep_time']  = preg_replace( '`([0-9]+:[0-9]+):[0-9]+`si', '$1', $flight['fromTime'] );
                            $d['fly_time']  = preg_replace( '`.+?([0-9]+)H([0-9]+)M$`si', '$1:$2', $cont['data'][0]['segments'][0]['flights'][0]['time'] );
                            $d['arr_time']  = GetArrTime( $d['dep_date'], $d['dep_time'], $d['fly_time'] );
                            $d['comp_code'] = $flight['airlineCode'];
                            $d['comp_name'] = utow( $flight['airline'] );
                            $d['flight']    = $flight['airlineCode'] . '-' . $flight['flightNo'];
                            $d['airplane']  = utow( $flight['airplane'] );
                            $d['changing']  = $cont['data'][0]['segments'][0]['stopsCount'];

                            if ( !empty( $date2 ) )
                                $d['back_flight'] = $flight2['airlineCode'] . '-' . $flight2['flightNo'];

                            $modules_data[$mod_name]['min_price'] = $cont['data'][0]['prices'][0];
                            $modules_data[$mod_name]['fly_details'] = $d;
                            dump($d);
*/
                            $res_array = array();
                            $ways = array(0);
                            if ( !$mod_data['one_way'] )
                                $ways[] = 1;

                            foreach ( $ways as $way )
                            {
                                $r_data = $cont['data'][0]['segments'][$way]['flights'][0]['flightLegs'];
                                $wait_times = $cont['data'][0]['segments'][$way]['flights'][0]['stops'];
                                $n = !$way ? 'onw' : 'bkw';
                                if ( !isset( $res_array[$n] ) )
                                    $res_array[$n] = array();

                                foreach ( $r_data as $k => $v )
                                {
                                    $rd = array();
                                    $rd['dep'] = $v['from']['code'];
                                    $rd['dept'] = $v['from']['city'] == $v['from']['airport'] ? $v['from']['city'] : $v['from']['city'] . ', ' . $v['from']['airport'];
                                    $rd['arr'] = $v['to']['code'];
                                    $rd['arrt'] = $v['to']['city'] == $v['to']['airport'] ? $v['to']['city'] : $v['to']['city'] . ', ' . $v['to']['airport'];
                                    $rd['dpd'] = preg_replace('`([0-9]{4})-([0-9]{2})-([0-9]{2})`si', '$3.$2.$1', $v['fromDate'] );
                                    $rd['dpt'] = preg_replace('`([0-9]{2}):([0-9]{2})`si', '$1.$2', $v['fromTime'] );
                                    $rd['ard'] = preg_replace('`([0-9]{4})-([0-9]{2})-([0-9]{2})`si', '$3.$2.$1', $v['toDate'] );
                                    $rd['art'] = preg_replace('`([0-9]{2}):([0-9]{2})`si', '$1.$2', $v['toTime'] );
                                    $rd['flt'] = '';
                                    $rd['wtt'] = isset( $wait_times[$k] ) ? preg_replace( '`PT([0-9]+)H([0-9]+)M`si', '$1:$2' , $wait_times[$k]['time'] ) : '';
                                    $rd['coc'] =$v['airline'];
                                    $rd['fli'] = $v['airlineCode'] . '-' . $v['flightNo'];
                                    $rd['pln'] = $v['airplane'];
                                    $res_array[$n][] = $rd;
                                }
                            }


                            //if ( !empty( $date2 ) )
                            //$d['back_flight'] = preg_replace( '`(.{2})([0-9]+)`si', '$1-$2' , $fe2[0]['reis'] );

                            $d['link'] = '';

                            $modules_data[$mod_name]['min_price'] = $cont['data'][0]['prices'][0];
                            $modules_data[$mod_name]['fly_details'] = $d;
                            SaveMinInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'] );
                            SaveFullInfo( 0, 100, $modules_data[$mod_name]['min_price'], $d['link'], json_encode( $res_array ) );

                            dump( $res_array );
                            dump( $d );
                        }

                        $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                        $modules_data[$mod_data['mod_name']]['end_time'] = time();

                        if ( $modules_data[$mod_name]['min_price'] == 'nf' )
                        {
                            if ( $loc_cx[$fly_to_iata] < TRY_COUNT )
                                return mod_ozon_travel( 'setup', $mod_data, $cookie );
                            else
                            {
                                $modules_data[$mod_name]['min_price'] = 'nf';
                                $modules_data[$mod_data['mod_name']]['start_time'] = $timeout[$mod_data['fly_to_iata']];
                                $modules_data[$mod_data['mod_name']]['end_time'] = time();
                                dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );
                                SaveMinInfo( 2, 100, 0, '' );
                                SaveFullInfo( 2, 100, 0, '', '{}' );
                                return false;
                            }
                        }

                        dump( $mod_name, $modules_data[$mod_name]['info']['url'], $fly_to_iata, $modules_data[$mod_name]['min_price'] );

                        return false;
                    }


        } break;
    }


}
/******************************************************** Конец модуля ozon.travel*/


/**
 * function SetCurlPost - Функция устанавливает опции поста
 *
 * @param in resource $ch - параметры curl соеденения
 * @param in mixed $data - пост параметры
 * @return;
 */
function SetCurlPost( &$ch, $data )
{
    curl_setopt( $ch, CURLOPT_POST, 1 );

    if ( is_string( $data ) )
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
    else
    {
        $is_multipart = false;
        foreach ( $data as $k => $v )
            if ( !is_array( $v ) && preg_match( '`^@`si', $v ) )
            {
                $is_multipart = true;
                break;
            }
        if ( !$is_multipart )
        {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, substr( PostDataEncode( $data ), 0, -1 ) );
        }
        else
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
    }
}



/**
 * function curl_redir_exec_ - Редирект в случай если с CURLOPT_FOLLOWLOCATION проблемы
 *
 * @param in resource $ch - соединение с курл
 * @return string - Возвразщяет url кодированную строку параметра $data
 */

function curl_redir_exec_( &$ch )
{
    static $curl_loops = array( );
    static $curl_max_loops = 3;
    if ( !isset( $curl_loops[$ch] ) )
        $curl_loops[$ch] = 0;

    if ( $curl_loops[$ch] >= $curl_max_loops )
    {
        $curl_loops[$ch] = 0;
        return FALSE;
    }

    curl_setopt( $ch, CURLOPT_HEADER, 1 );
    $data = curl_exec( $ch );
    if ( empty( $data ) )
    {
        $curl_loops[$ch] = 0;
        return '';
    }

    $header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
    $header = substr( $data, 0, $header_size - 4 );

    $data = substr( $data, $header_size );

    curl_setopt( $ch, CURLOPT_HEADER, 0 );

    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    if ( $http_code == 301 || $http_code == 302 )
    {
        $matches = array();

        preg_match( "`Location:(.*?)(\r?\n)?(?(2)|$)`si", $header, $matches );

        $url = @parse_url( trim( $matches[1] ) );

        if ( !$url )
        {
            $curl_loops[$ch] = 0;
            return $data;
        }

        $last_url = parse_url( curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL ) );

        if ( !$url['scheme'] )
            $url['scheme'] = $last_url['scheme'];
        if ( !$url['host'] )
            $url['host'] = $last_url['host'];
        if ( !$url['path'] )
            $url['path'] = $last_url['path'];

        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ( @$url['query'] ? '?' . $url['query'] : '' );

        curl_setopt( $ch, CURLOPT_URL, $new_url);

        $curl_loops[$ch]++;

        return curl_redir_exec_( $ch );
    } else
    {
        $curl_loops[$ch] = 0;
        return $data;
    }
}



/**
 * function GetAirCode - Возвращяет код обозначения аэропорта на сайте агенства по IATA коду.
 *
 * @param in string $mod_name - Название агенства для которго надо взять код
 * @param in array $fly_data - данные об полете
 * @return string - Возвразщяет код
 */

function GetAirCode( $mod_name, $fly_data )
{

    $ref = 'http://www.' . $mod_name . '/';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0';
    $cookie = getcwd() . '/cookies/for_get_iata_codes.txt';

    $ch = curl_init();
    curl_setopt(  $ch, CURLOPT_FAILONERROR, 0 );
    @curl_setopt(  $ch, CURLOPT_FOLLOWLOCATION, 1 );
    curl_setopt(  $ch, CURLOPT_TIMEOUT, 60 );
    curl_setopt(  $ch, CURLOPT_REFERER, $ref );
    curl_setopt(  $ch, CURLOPT_USERAGENT, $user_agent );
    curl_setopt(  $ch, CURLOPT_HEADER, 0 );
    curl_setopt(  $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt(  $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt(  $ch, CURLOPT_PROXY, '' );
    curl_setopt(  $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt(  $ch, CURLOPT_NOBODY, 0 );
    curl_setopt(  $ch, CURLOPT_COOKIEJAR, $cookie );
    curl_setopt(  $ch, CURLOPT_COOKIEFILE, $cookie );

    switch ( $mod_name)
    {
        case 'mod_pososhok_ru':
        {
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.pososhok.ru/system/modules/com.gridnine.opencms.modules.pososhok/pages/ajax_provider_locations.jsp' );
            SetCurlPost( $ch, array( 'locale=' => 'ru', 'showItems' => 15, 'term' => trim( $fly_data['iata_code'] ) ) );
            $res = curl_exec( $ch );
            curl_close( $ch );

            preg_match_all( '`"value": "([^"]+)"`si', $res, $res );
            $res = $res[1];
            $iata = $fly_data['iata_code'];
            foreach ( $res as $iata  )
                if ( $iata == $fly_data['iata_code'] )
                    break;

            return trim( $iata );
        } break;
        case 'davs.ru':
        {
//			curl_setopt(  $ch, CURLOPT_URL, 'http://www.davs.ru/test/sities_suggestion.php');
//			SetCurlPost( $ch, array( 'search' => ( $fly_data['city'] ) ) );
            curl_setopt(  $ch, CURLOPT_URL, 'http://ticket.davs.ru/avia/search.php?term=' . urlencode( $fly_data['city'] ) );
            $res = curl_exec( $ch );
            curl_close( $ch );
            $res = json_decode( $res, 1 );
            return $res[0]['id'];

//			dump( $res );
//			die();
//			$res = preg_replace( '`(^\[)|(\]$)`si', '', $res );
//			if ( preg_match( '`\["[^"]+",\s*"[^"]+",\s*"([^"]+)",\s*"' . $fly_data['city'] . '"\]`si', $res, $r ) )
//				return trim( @$r[1] );
//			else
//				return '';

        } break;
        case 'mod_amargo_ru':
        {

            curl_setopt(  $ch, CURLOPT_URL, 'http://www.amargo.ru/hierarchylocationsearch.do');
            SetCurlPost( $ch, 'ajaxSearch=' . iconv( 'windows-1251', 'utf-8', $fly_data['iata_code'] ) . '&searchableOnly=false&locationType=airport&language=ru' );
//			SetCurlPost( $ch, 'ajaxSearch=' .  wtou( $fly_data['city'] ) . '&searchableOnly=false&locationType=airport&language=ru' );
            $res = curl_exec( $ch );;
            curl_close( $ch );
//			dump($fly_data['city'],mb_detect_encoding($fly_data['city'], array('UTF-8', 'Windows-1251')),$res);
            if ( preg_match( '`<li id="([^"]+)"`si', $res, $r ) )
                return trim( @$r[1] );
            else
                return '';

        } break;
        case 'mod_agent_ru':
        {

            curl_setopt(  $ch, CURLOPT_HTTPHEADER, array( 'X-Requested-With: XMLHttpRequest' ) );
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.agent.ru/ru/services/reference/flightPoints?name=' . $fly_data['iata_code'] . '&_=' . time()  );
            $res = curl_exec( $ch );
            curl_close( $ch );
//			dump( iconv( 'utf-8', 'windows-1251', $res ) );

            $js = json_decode( $res ) ;

            $d = array( 'id' => 0, 'type' => '', 'name' => '' );
//			dump( $js, $fly_data['iata_code'] );
//			die();

            if ( sizeof ( $js->flightPoints ) )
                foreach ( $js->flightPoints as $v )
                    if ( $fly_data['iata_code'] == $v->codeIata )
                    {
                        $d['id'] = $v->id;
                        $d['type'] = $v->flightPointType;
                        $d['name'] = iconv( 'utf-8', 'windows-1251', $v->name );
                        break;
                    }
//			dump( $d );
            return $d;

        } break;
        case 'mod_ozon_travel':
        {
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.ozon.travel/ajax/cities.html' );
            $data['Commander.Command'] = 'SearchAirLocation';
            $data['Top'] = 7;
            $data['Word'] = $fly_data['iata_code'];
            SetCurlPost( $ch, $data );
            $res = curl_exec( $ch );
            curl_close( $ch );
            $id = '';
            if ( preg_match( '`"Code": "' . $fly_data['iata_code'] . '","Country": "[^"]+","Id": "([^"]+)"`si', $res, $m ) )
                $id = $m[1];
            return $id;

        } break;
        case 'nabortu.ru':
        {
            curl_setopt(  $ch, CURLOPT_URL, 'http://www.nabortu.ru/modules/mod_aform/from_where.php?city=' . iconv( 'windows-1251', 'utf-8', $fly_data['city'] ) );
            $res = curl_exec( $ch );
            curl_close( $ch );
            return $res;

        } break;


    }

    @curl_close( $ch );
    return '';
}

function GetPrice( $p, $short = false )
{

    if ( !$short )
    {
        if ( $p == 'nf' )
            return '<font style="font-size:10px;">не найд.</font>';
        else
            if ( $p == 'nd' )
                return '<font style="font-size:10px;">ошибка направления</font>';
            else
                if ( $p == 'to' )
                    return '<font style="font-size:10px;">таймаут</font>';
                else
                    return $p;

    } else
    {
        if ( $p == 'nf' )
            return 'не найд.';
        else
            if ( $p == 'nd' )
                return 'ошб. напр.';
            else
                if ( $p == 'to' )
                    return 'таймаут';
                else
                    return $p;
    }

}

function ShutdownParser( $stoped = 0 )
{
    global $cfg, $modules_handlers, $mch;
    static $is_called = false;
    if ( $is_called )
        return ;

    $is_called = true;

    $gbf = realpath( dirname( __FILE__) ) . '/global_stat.txt';

    if ( file_get_contents( $gbf ) == 100 )
        die();

    SaveFile( $gbf, 100 );

    if ( !$stoped )
        SaveFile( realpath( dirname( __FILE__) ) . '/current_stat.txt', '<script> alert( "ВНИМАНИЕ! Работа парсера была остановлена по причине срабатывания ограничения на время выполенения скрипта." ); </script>' );
    else
        SaveFile( realpath( dirname( __FILE__) ) . '/current_stat.txt', '<script> alert( "Работа парсера успешно остановлена!" ); </script>' );

    //Уничтожаем соеденения курла
    if ( is_array( $modules_handlers ) && sizeof ( $modules_handlers ) )
        foreach ( $modules_handlers as $mn => $c_hand )
        {
            curl_multi_remove_handle( $mch, $c_hand );
            curl_close( $c_hand );
            unset( $modules_handlers[$mn] );
        }

    if ( is_resource( $mch ) )
        curl_multi_close( $mch );

    SaveFile( realpath( dirname( __FILE__) ) . '/stop.txt', 0 );

    die( );

}

function ModOtherInfo( $mod_data, $for_excel = false )
{
    if ( !isset( $mod_data['fly_details'] ) )
        return '';
    $fields = $mod_data['fields'];
    if ( empty( $mod_data['fields'] ) )
        $fields = array();
    else
        $fields = explode(',', $mod_data['fields'] );
    $fields_titles = array(
        'dep_time' => 'Вылет.: ',
        'fly_time' => 'Полет: ',
        'comp_code' => 'Код: ',
        'comp_name' => 'Комп: ',
        'flight' => 'Рейс: ',
        'airplane' => 'Борт: ',
        'changing' => 'Прсд.: ',
        'request_time' => 'с',
        'back_flight' => 'Обр.&nbsp;рейс: ',
    );

    $request_time = round( ( $kk = ( $mod_data['end_time'] - $mod_data['start_time'] ) ) / 60 ) . ':' . ( $kk % 60 );
    $s = '<span class="otherinfo">' . "\n";
    foreach ( $fields_titles as $name => $title )
        if ( in_array( $name, $fields ) )
            if ( $name != 'request_time' )
                $s .= '<span class="' . $name . '">' . $title . $mod_data['fly_details'][$name] . '</span>' . "\n";
            else
                $s .= '<span class="' . $name . '">Зап.: ' . $request_time . $title . '</span>' . "\n";
    $s .= '</span>';
    if ( $for_excel )
        $s = "\n" . trim( strip_tags( $s ) );
    return  $s;
}
function GetSegment( &$text, $from, $to )
{
    if ( ( $start = strpos( $text, $from ) ) === false )
        return '';

    $start += strlen( $from );

    if ( ( $end = strpos( $text, $to, $start ) ) === false )
        return '';

    return substr( $text, $start, $end - $start );
}

function dec( $str )
{
    $str = preg_replace( "/\\\\u([0-9a-f]{3,4})/i", "&#x\\1;", $str );
    $str = html_entity_decode( $str, null, 'UTF-8' );
    return $str;
}
function GetArrTime( $dep_date, $dep_time, $fly_time )
{
    list( $h, $m ) = explode( ':', $fly_time );
    return date( 'H:i',  strtotime( $dep_date . ' '. $dep_time ) + ( $h - 1 ) * 3600 + $m * 60 );
}
function Awad_get_company_name($code, $companies )
{
    foreach ( $companies as $k => $v )
    {
        if ( $v['C'] == $code )
            return $v['N'];
    }
    return '';
}
function Awad_get_Airplane( $code, $airplanes )
{
    foreach ( $airplanes as $k => $v )
    {
        if ( $v['C'] == $code )
            return $v['N'];
    }
    return '';
}

function GetAirportName( $agency, $code, $reference )
{
    switch ( $agency )
    {
        case 'mod_anywayanyday_com' :
        {
            foreach ( $reference as  $v )
            {
                if ( $v['C'] == $code )
                    return ( $v['N'] == $v['CT'] ) ? $v['CT'] : $v['CT'] . ', ' . $v['N'];
            }

        } break;
        case 'mod_aviakassa_ru' :
        {

            return $reference[$code]['NM'];

        } break;

    }
}

function TotalPrice( $ar )
{
    $s = 0;
    foreach ( $ar as $v )
        $s += $v['total'];
    return round( $s );
}

?>