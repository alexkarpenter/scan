<?php
header( 'Content-type: text/html; charset=utf-8' );
include_once './protected/config/config.php';
include_once './protected/lib/funcs.php';
/*
preg_match( '`<td class="logo[^"]+">.+?"ak-name" title="([^"]+)">[^<]+</span>([^<]+)</div>\s*<div class="plane">([^<]+)</div></td>\s*<td class="flight_info"><div class="departure">\s*<span class="time">([^<]+)</span>\s*<span class="date">([^<]+)</span>\s*<div class="point">([^<]+)</div>\s*<div class="airport">([^<]+)<span class="code">/\s*([^<]+)</s.+?<div class="arrival">\s*<span class="time">([^<]+)</span>\s*<span class="date">([^<]+)</span>\s*<div class="point">([^<]+)</div>\s*<div class="airport">([^<]+)<span class="code">/\s*([^<]+)</sp`sui', file_get_contents( '232.html' ), $v );
preg_match( '`<span class=".+?date">([^<]+)</sp.+?time">([^<]+)</sp.+?details-to">\s*<b>([^<]+)</b>.+?>([^<]+)</abbr>.+?detail-text">\s*<span>([^<]+)<.+?<span class=".+?date">([^<]+)</sp.+?time">([^<]+)</sp.+?details-to">\s*<b>([^<]+)</b>.+?>([^<]+)</abbr>.+?detail-text">\s*<span>([^<]+)<`si', file_get_contents( '232.html' ), $v );

//preg_match( '`<form id="form[^"]+"[^>]+>(.+?)</form>`si', file_get_contents( '232.html' ), $form );
//preg_match( '`<div class="variants clf.+?<li id="info(.+?)</li>`si', $form[1], $onward_text );
preg_match( '`<div class="offer"(.+?)(<div class="offer"|</div>\s*<script type="text/javascript">)`sui', $modules_data[$mod_name]['content'], $cont )
dump( $v );
die();

preg_match( '`<div class="offer"(.+?)<div class="offer"`sui', file_get_contents( '232.html' ), $v );
*/
//preg_match( '`td-time">([^<]+)</td>\s*<td class="td-r">([^<,]+)(?:,)?\s*<span>(.*?)\(([^\)]+)\)\s*</span.+?rowspan="2">([^<]+)<span>([^<]+)</span></td>.+?rowspan="2">([0-9]+)\s*ч\.\s*([0-9]+)\s*мин\.<br\s*/>\s*на\s*([^<]+)</td>.+?td-time">([^<]+)</td>.+?"td-r">([^<,]+)(?:,)?\s*<span>(.*?)\(([^\)]+)\)\s*</sp`sui', file_get_contents( '232.html' ), $v );
//dump( $v );
//die();
//$xml = simplexml_load_string( file_get_contents( '232.html' ) );
/*
preg_match( '`(<Flights[^>]+>.+</Flights>)`si', file_get_contents( '232.html' ), $cont );
dump( $cont[1] );
die();
&*/


include_once './protected/view/export.php';


?>