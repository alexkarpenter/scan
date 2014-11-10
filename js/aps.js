$(document).ready(function()
{



    //************************************************************************************************************************************************
    //
    //ЧАСТЬ 1. ИНИЦИАЛИЗАЦИЯ БИБЛИОТЕК, ПЛАГИНОВ, ОБЯВЛЕНИЕ ВСПОМОГАТЕЛЬНЫХ ФУНКЦИЙ
    //
    //************************************************************************************************************************************************
    var data_for_direct_search = {};
    var gsearch_type = '';


    auto_complete_elements = '#ff_departure_city, #ff_arrival_city';
    date_picker_elements = '#ff_departure_date, #ff_arrival_date, #one_route_edit_dep_date, #one_route_edit_arr_date,#mass_route_edit_dep_date_false, #mass_route_edit_arr_date_false, #mass_route_edit_dep_date_true'

    //Красивые чекбоксы
    $('.av_check,.f_check, .icheckbox, #remember_me, .route_checkbox').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });

    // Chosen красивый селектбокс
    $('.chosen-select-default').chosen({disable_search_threshold: 10});

    //Typeahead & Bloodhound
    var cities = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: $.map( full_iata, function(state) { return { value: state[0], id:state[1] }; })
    });
    cities.initialize();
    $(auto_complete_elements).typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            autoselect: true
        },
        {
            name: 'states',
            displayKey: 'value',
            source: cities.ttAdapter()
        });

    //Модальное окно ввода имени пользователя
    $('#name_dialog').modal({backdrop:'static', show : false, keyboard: false});

    //Модальное окно редактирования даты маршрута
    $('#edit_one_route').modal({backdrop:'static', show : false, keyboard: false});

    //Модальное окно редактирования даты маршрутов
    $('#edit_mass_route').modal({backdrop:'static', show : false, keyboard: false});

    //Датапикеры
    date1 = $(date_picker_elements).datepicker({
        format: "dd.mm.yyyy",
        language: "ru",
        keyboardNavigation: true,
        autoclose: true,
        forceParse: false
    });
/*
    date2 = $('#ff_arrival_date').datepicker({
        format: "dd.mm.yyyy",
        language: "ru",
        keyboardNavigation: true,
        autoclose: true,
        forceParse: false
    });
*/
    //Инпут маски для дат
    $(date_picker_elements).mask("99.99.9999");



    //************************************************************************************************************************************************
    //
    //ЧАСТЬ 2. ОБЯВЛЕНИЕ ОБЫЧНЫХ ВСПОМОГАТЕЛЬНЫХ ФУНКЦИЙ
    //
    //************************************************************************************************************************************************



    LogConsole = function( v ){console.log(v);};
    OpenAgencyRef = function (url ){window.open( url, '_blank' );};
    getAgencyRef = function(e){ e.stopPropagation(); return $(e.target).attr('link');};
    isEmpty = function(v){return v.length == 0 };
    isNotEmpty = function(v){return v.length > 0 };
    isEqual = function(a,b){return a==b};
    isEqual2 = function(a){return function(b){return a==b}};
    noErrors = function(v){return ( v < 0 );};
    CanBeAdded = function(v){ return v[0] && v[1] };
    BuildAddToListRequest = function(v){ return {url: 'user_routes.php', type: 'post', data: { cmd :'add', user_route:v[2] } };};
    BuildRoutesListRequest = function(usern){ return {url: 'user_routes.php', type: 'post', data: { cmd :'list', user: usern } };};
    BuildDeleteRoutesRequest = function(r){ return {url: 'user_routes.php', type: 'post', data: { cmd :'delete', routes: r } };};
    BuildEditRoutesRequest = function(r){ return {url: 'user_routes.php', type: 'post', data: { cmd :'edit', data: r } };};
    BuildSearchIdRequest = function(search_type){ gsearch_type = search_type;  return {url: 'get_search_id.php', type: 'post', data:{fields : GetCheckedCheckboxAttributes('.f_check:checked', 'name'), agencies: GetCheckedCheckboxAttributes('.av_check:checked', 'name'), routes : search_type != 'direct' ? GetCheckedCheckboxAttributes('.routes_list .route_checkbox:checked', 'route') : DirectSearchData(0)}};};
    BuildSearchStateRequest = function(id){ return {url: 'get_search_state.php', type: 'get', dataType: "json", data: { search_id : id } };};
    BuildSearchFullResultRequest = function(sid){ return {url: 'get_full_result.php', type: 'get', dataType: "json", data: { id : sid } };};
    ReturnInput = function(v){ var ta = arguments[1];if ( typeof( arguments[1] ) == 'undefined' ) return v; else return function (v1){ return v1[ta];}};
    EnableButtons = function (elements){ return function(state){$(elements).attr('disabled',!state)};};
    ShowLoggedUserName = function (v){if (v) $('.user_loged').html('Вы вошли как <b>' + v + '<b>');};
    keyCodeIs = function (keyCode) {return function(event) { return event.keyCode == keyCode }};
    isValidDate = function( e ) { return RegExp( '[0-9]+\.[0-9]+\.[0-9]{4}', 'gi').test( $(e[0].target).val() ) };
    SetUserNameFocus = function(){$('#loged_user_name').focus();};
    SetFocus = function(el){ return function(d){$(el).focus();} };
    ShowHideUserRouteOperationsButtons = function( d ) { var b = d[0].find( '.delete_one_route_button, .edit_one_route_button' ); !d[1] ? b.addClass('invisible') : b.removeClass('invisible'); };
    ChangeRoutesActive = function (){ $('#routes_list .route_checkbox').each( function(ch){ var r = $(this).parent().parent().parent(); if ( $(this).is(':checked') ) r.addClass('success'); else r.removeClass('success'); } ) };
    SetActiveRoute = function (e) { var r = e; var ch = r.find('.route_checkbox'); ch.iCheck('toggle'); ChangeRoutesActive(); };
    ShowConfirm = function(t){return function(e){e.stopPropagation();return confirm(t)}};
    GetCheckedList = function(obj){return $(obj).find(':checked').map(function(){return $(this).val();}).get().join(',');};
    GetAttr = function(n) { return function(obj){ return $(this).attr(n);}};
    GetCheckedCheckboxAttributes = function (obj, attr){var rt =$(obj).map(GetAttr(attr)).get().join(',');  return rt;};
    CellLoadingText = function (c) { return '<div align="center"><div class="cell_loading"></div>Загрузка (' + c+ '%)</div>'};
    ShowInfoAboutSearchWasStoped = function(){$('#result_content .cell_loading').parent().html('<span class="text-danger">Остановлен</span>')};
    CellErrorText = function(e){var es = ['','Не работ.','Не найд.','Ошб. сети']; return '<div align="center"><span class="text-danger">' + es[e] + '</span></div>'; };
    CellsForLoad = function(res) { var loaded = $('.res_loaded').map(function(v){return $(this).attr('id').replace( /rc_/gi, '' );}).get(); var for_load = _.filter( res, function( v, k ){ return !parseInt( v.d.e ) && ( parseInt( v.d.cp ) == 100 ) && !_.contains( loaded, v.c ); } ); return for_load; };
    UpdateDataToDirectSearch = function (data)
    {
//        data_for_direct_search.departure_city = data.departure_city;
//        data_for_direct_search.arrival_city = data.arrival_city;
        data_for_direct_search.departure_date = data.departure_date;
        data_for_direct_search.arrival_date = data.arrival_date;
        data_for_direct_search.adults = data.adults;
        data_for_direct_search.children = data.children;
        data_for_direct_search.infants = data.infants;
        data_for_direct_search.one_way = data.one_way;
        data_for_direct_search.direct = data.direct;
        data_for_direct_search.class = data.class;
        data_for_direct_search.user = data.user;
        data_for_direct_search.route_id = 'ds';
    };
    PrepareSaveDates = function (type)
    {
        return function (e)
        {
            if (type == 'one')
            {
                var o = $(e.currentTarget);
                return [[o.attr('one_way'), o.attr('route_id'), $('#one_route_edit_dep_date').val(), $('#one_route_edit_arr_date').val()]];
            } else if (type == 'mass')
            {
                var o = $(e.currentTarget), r=[], rf = o.attr('route_id_false').split(','), rt = o.attr('route_id_true').split(',');
                var dep_date_false = $('#mass_route_edit_dep_date_false').val();
                var arr_date_false = $('#mass_route_edit_arr_date_false').val();
                var dep_date_true = $('#mass_route_edit_dep_date_true').val();
                r = _.map( rf, function(v){ return [0, v, dep_date_false, arr_date_false]; }).concat( _.map( rt, function(v){ return [1, v, dep_date_true, '']; }) );
                return r;
            }
        }
    }

    DirectSearchData = function(type)
    {
        var d = data_for_direct_search, r;
        if ( !type )
            r = d.departure_city + '-' + d.arrival_city + '-' + d.departure_date + '-' + d.arrival_date + '-' + d.adults + '-' + d.children + '-' + d.infants + '-' + ( d.one_way ? 1 : 0 ) + '-' + ( d.direct ? 1 : 0 )  + '-' + d.class + '-' + d.user + '-' + d.route_id;
        else
            r = [d.departure_city_txt + '~' + d.arrival_city_txt + '~' + d.departure_date + '~' + d.arrival_date + '~' + ( d.one_way ? 1 : 0 ) + '~' + ( d.direct ? 1 : 0 )  + '~' + d.route_id];

        return r;
    }


    //Возвращяет код оишбки в форме
    function FormError( data )
    {
        var errors =
            [
                isEmpty( data.departure_city ),
                isEmpty( data.arrival_city ),
                isEqual( data.departure_city, data.arrival_city ),
                isEmpty( data.departure_date ),
                !data.one_way && isEmpty( data.arrival_date ),
                !parseInt(data.adults) && !parseInt(data.children) && !parseInt(data.infants)
            ];
        //LogConsole(errors);
        return _.indexOf( errors, true );
    }

    //Срабатывает при закрытии модального окна
    function SaveUserName(ev)
    {
        ShowLoggedUserName( $('#loged_user_name').val() );
        var rm = $('#remember_me').is(':checked');
        $.cookie( 'remember_me', rm ? 1 : 0, { expires: 365 } );
        if ( rm )
            $.cookie( 'loged_user_name', $('#loged_user_name').val(), { expires: 365 } );
    }

    //Функция инициализации датыпикеров
    function GetDate( e )
    {
        var obj = $(e[0].target);
        var entered = e[1];
        if ( obj.attr('id') == 'ff_departure_date' )
        {
            if ( !entered )
            {
                var newDate = new Date( obj.datepicker('getDate') );
                newDate.setDate( newDate.getDate() + 1 );
                var d = newDate.getDate() + '.' + newDate.getMonth() + '.' + newDate.getFullYear();
                $('#ff_arrival_date').datepicker( 'setStartDate', d );
            }
            if ( entered )
                if ( !$( '#one_way' ).is(':disabled') )
                    $('#ff_arrival_date').focus();
                else
                if ( !$( '#add_to_list' ).is(':disabled') )
                    $( '#add_to_list').focus();
        } else
        if ( !$( '#add_to_list' ).is(':disabled') )
            $( '#add_to_list').focus();

        if (entered)
            obj.datepicker('hide');
        return obj.val();
    }

    //Функция возвращяет название города
    function GetCity( d )
    {
        var obj = $( d[0].target) ;

        if ( obj.attr( 'id' ) == 'ff_departure_city' )
        {
            data_for_direct_search.departure_city = d[1].value.replace(/([^,]+),[^\[]+\[([^\]]+)\]/gi,"$2" );
            data_for_direct_search.departure_city_txt = d[1].value.replace(/([^,]+),[^\[]+\[([^\]]+)\]/gi,"$1 [<b>$2</b>]" );
            $('#ff_arrival_city').focus();
        }
        else
        {
            data_for_direct_search.arrival_city = d[1].value.replace(/([^,]+),[^\[]+\[([^\]]+)\]/gi,"$2" );
            data_for_direct_search.arrival_city_txt = d[1].value.replace(/([^,]+),[^\[]+\[([^\]]+)\]/gi,"$1 [<b>$2</b>]" );
            $('#ff_departure_date').focus();
        }

        return d[1].id;
    }

    //Ресуем таблицу с будущими результатами
    function RenderNewResultTable(search_id)
    {
        var agencies = Array('№', 'Направление' ).concat( GetCheckedCheckboxAttributes('.av_check:checked', 'name').split(',') );

        var routes1  = GetCheckedCheckboxAttributes('.routes_list .route_checkbox:checked', 'route').split(',');
        var routes2  = gsearch_type != 'direct' ? GetCheckedCheckboxAttributes('.routes_list .route_checkbox:checked', 'route2').split(',') : DirectSearchData(1);

        var cell = function (val, key) { return '<th class="text-center">' + val + '</th>' };
        var loading = CellLoadingText(1);

        var head = '<tr>' + _.map( agencies, cell ).join( '' ) + '</tr>';
        var rows = _.map( routes2, function( val, key )
            {
                var rd = val.split( '~' );
                var r = Array( key + 1, rd[0] + '<br>' + rd[1] ).concat( _.map( _.range( 0, agencies.length - 2 ), function( val2, key2 ){ return loading;} ) );

                var price_row = '<tr class="text-center small" id="rr' + search_id + '_' + rd[6]  + '">' + _.map( r, function (val2, key2) { var id = key2 > 1 ? 'id="rc_' + search_id + '_' + rd[6] + '_' + agencies[key2].replace( /\./gi, '_' ) + '"' : ''; return  '<td ' + id + '>' + val2 + '</td>' } ).join('') + '</tr>';
                var info_row  = '<tr class="text-center" style="display: none;" id="rr' + search_id + '_' + rd[6]  + '_"><td class="selected_bottom_cell" colspan="' + ( agencies.length ) + '">' + _.map( r, function (val2, key2) { var id = key2 > 1 ? 'id="rc_' + search_id + '_' + rd[6] + '_' + agencies[key2].replace( /\./gi, '_' ) + '_"' : ''; return  '<div class="" style="display: none" ' + id + '></div>' } ).join('') + '</td></tr>';
                return price_row + info_row;
            }
        ).join('');
        var table = '<table class="table table-bordered table-hover table-condensed">' + head + rows + '</table>';
        $('#result_content').html( '<h2>Результаты</h2>' + table );
    }

    //Ресуем таблицу с будущими результатами
    function RenderNewResultTable2( result, routes )
    {
        //return false;
        console.log( result );
        var res_rows = _.groupBy( result, function(r){ return r.c.replace(/([0-9]+_[A-Za-z0-9]+).+/gi, '$1' ); } );
        var agencies = _.map( _.first( _.toArray( res_rows ) ), function (v,k){ return v.c.replace(/[0-9]+_[A-Za-z0-9]+_(.+)/gi, '$1' ); } );
        var top_headers = Array('№', 'Направление' ).concat(_.map( agencies, function (v, k ){ return v.replace(/(.+?)_([A-Za-z]+)$/gi, '$1.$2').replace(/_/gi, '-' );  }) );
        var search_id = parseInt( _.first( _.map( _.first( _.toArray( res_rows ) ), function (v,k){ return v.c.replace(/([0-9]+)_[A-Za-z0-9]+_.+/gi, '$1' ); } ) ) );

        var cell = function (val, key) { return '<th class="text-center">' + val + '</th>' };
        var loading = CellLoadingText(1);

        var head = '<tr>' + _.map( top_headers, cell ).join( '' ) + '</tr>';

        var rows = _.map( routes, function( route, r_key )
            {
                var rd, r ,price_row, info_row;

                rd = route.split( '-' );
                r = Array( r_key + 1, rd[0] + '<br>' + rd[1] ).concat( _.map( _.range( 0, top_headers.length - 2 ), function( val2, key2 ){ return loading;} ) );


                price_row  = '<tr class="text-center small " id="rr' + search_id + '_' + rd[11]  + '">';
                price_row += _.map( r, function (val, key)
                {
                    var id = key > 1 ? 'id="rc_' + search_id + '_' + rd[11] + '_' + agencies[key-2] + '"' : '';
                    return '<td ' + id + '>' + val + '</td>'
                } ).join('') ;
                price_row += '</tr>';


                info_row  =  '<tr class="text-center" style="display: none;" id="rr' + search_id + '_' + rd[11]  + '_"><td class="selected_bottom_cell" colspan="' + ( top_headers.length ) + '">';
                info_row  += _.map( r, function (val, key)
                {
                    var id = key > 1 ? 'id="rc_' + search_id + '_' + rd[11] + '_' + agencies[key-2] + '_"' : '';
                    return  '<div class="" style="display: none" ' + id + '></div>' } ).join('') ;
                info_row  += '</td></tr>';

                return price_row + info_row;
            }
        ).join('');
        var table = '<table class="table table-bordered table-hover table-condensed">' + head + rows + '</table>';
        $('#result_content').html( '<h2>Результаты</h2>' + table );

        //Этап UpdateResultTable
        UpdateResultTable2( result );

        //Этап SetBestPrice
        SetBestPrice2( result );

    }


    //Прячим панели атакже некоторые элементы формы поиска
    function HideShowPanelsAndSearchForm(hide)
    {
        if ( hide )
            $('.hide_show_on_search').slideUp(250);
        else
            $('.hide_show_on_search').slideDown(250);
    }

    //Обновляет данные в таблице результатов
    function UpdateResultTable(res)
    {
        var change_loading_text = function ( obj, index ) { $('#rc_'+obj.c).html(CellLoadingText(obj.d.cp))};
        _.each( res,  function ( obj, index )
            {
                var t = $('#rc_'+obj.c);
                //Если не завершен
                if ( parseInt(obj.d.cp) != 100 )
                    change_loading_text( obj, index );
                else
                {
                    //Если есть ошибка
                    if ( obj.d.e > 0 )
                        t.html( CellErrorText( obj.d.e ) );
                    else
                    {
                        var r = t.hasClass('res_loaded');
                        if ( !r )
                            t.html('<div class="price_cont"><a class="cel_price ' + ( obj.d.u ? 'underline' : '' ) + '" ' + (obj.d.u ? ' target="_blank" href="' + obj.d.u + '"' : '' ) + '>' + BeautifullyPrice( obj.d.p ) + '</a><br><span class="compare_price"></span></div>');
                    }
                }
            }
        );

    }
    //Обновляет данные в таблице результатов
    function UpdateResultTable2(res)
    {
        var change_loading_text = function ( obj, index ) { $('#rc_'+obj.c).html(CellLoadingText(obj.cp))};
        _.each( res,  function ( obj, index )
            {
                var t = $('#rc_'+obj.c);

                //Если не завершен
                if ( parseInt(obj.cp) != 100 )
                    change_loading_text( obj, index );
                else
                {

                    //Если есть ошибка
                    if ( obj.e > 0 )
                        t.html( CellErrorText( obj.e ) );
                    else
                    {
                        var r = t.hasClass('res_loaded');
                        console.log( obj.cp, obj.e, r );
                        if ( !r )
                            t.html('<div class="price_cont"><a class="cel_price ' + ( obj.u ? 'underline' : '' ) + '" ' + (obj.u ? ' target="_blank" href="' + obj.u + '"' : '' ) + '>' + BeautifullyPrice( obj.p ) + '</a><br><span class="compare_price"></span></div>');
                    }
                }
            }
        );

    }


    //Внедряет полную информацию об одном маршруте
    function RenderOneRouteFullInfoTable( res )
    {
        var head_texts = Array( '№', 'Вылет', 'Прилет', 'Рейс', 'Самолет', 'Время полёта', 'Ожидание' );
        var cell = function (val, key) { return '<th class="text-left .info ">' + val + '</th>' };
        var head = '<tr>' + _.map( head_texts, cell ).join( '' ) + '</tr>';
        var all = _.map( res.info, function( routes, way ) {
            var tables = '';
            var rows = _.map( routes, function( route, index )
            {
                var td = '';
                td += '<td>' + ( index + 1 ) + '</td>';
                //td += '<td class="text-left txt11px">' + ( route.dep == route.dept ? route.dep : route.dep + '<br>' + route.dept ) + '<br>' + route.dpd + '<b>' + route.dpt + '</b>' + '</td>';
                td += '<td class="text-left txt11px">' + ( route.dep == route.dept ? route.dep : route.dept ) + '<br><b>' + route.dpd + ' ' + route.dpt + '</b>' + '</td>';
                //td += '<td class="text-left txt11px">' + ( route.arr == route.arrt ? route.arr : route.arr + '<br>' + route.arrt ) + '<br>' + route.ard + '<b>' + route.art + '</b>' + '</td>';
                td += '<td class="text-left txt11px">' + ( route.arr == route.arrt ? route.arr : route.arrt ) + '<br><b>' + route.ard + ' ' + route.art + '</b>' + '</td>';
                td += '<td class="text-left txt11px">' + route.fli + '</td>';
                td += '<td class="text-left txt11px">' + route.pln + '</td>';
                td += '<td class="text-left txt11px">' + route.flt + '</td>';
                td += '<td class="text-left txt11px">' + route.wtt + '</td>';
                return '<tr>' + td + '</tr>';

            } ).join('');
            tables += '<table class="table  table-condensed table-bordered" style="width: 800px; float:left; margin-left: 30px;" >' + '<tr><th colspan="7" class="text-center bg-info">' + ( way == 'onw' ? 'ТУДА' : 'ОБРАТНО' ) + '</th></tr>' + head + rows + '</table>';
            return tables;
        }).join( '' );




        return all;
    }

    //Рендрим полную инфорамцию
    function RenderFullResult(res)
    {

        var r_id = res.c.split('_');
        var res_table = RenderOneRouteFullInfoTable( res );
        var res_popup_table = res_table.replace(/style=".+?"/gi, '');
        r_id = r_id[0] + '_' + r_id[1];
        $( '#rc_' + res.c + ' .price_cont').parent().append( '<button type="button" class="btn btn-xs right btn-primary info_button"  row_info_id="' + r_id + '" row_info_cell_id="' + (res.c) + '" data-toggle="tooltip" data-placement="top" title="Развернуть полную информацию"><span class=" glyphicon glyphicon-chevron-down"></span></button>' );

        $( '#rc_' + res.c + '_').html( res_table + '<div class="hidden" id="' + 'rc_' + res.c + '_div">' + res_popup_table + '</div>' );
        $( '#rc_' + res.c ).attr( 'rel', 'tooltip' );
        $( '#rc_' + res.c ).attr( 'tooltip_source', 'rc_' + res.c + '_div' );

        $('.info_button').tooltip( {animation:true} );
        new_tooltip();
    }

    //Показываем прячим полную информацию
    function ShowHideFullInfo( target_button )
    {

        var opened_row = $('.open_info_row');
        var opened_cell = $('.open_info_cell');
        var row_info =  $('#rr' + target_button.attr('row_info_id') + '_');
        var row_info_cell =  $('#rc_' + target_button.attr('row_info_cell_id') + '_');
        var rows_not_equal =  opened_row.attr( 'id' ) != row_info.attr( 'id' );
        var cells_not_equal =  opened_cell.attr( 'id' ) != row_info_cell.attr( 'id' );
        var t = 500;
        var last_button = opened_cell.length ? opened_cell.attr('id').replace(/rc_(.+)_$/gi, '$1' ) : '';

        if ( last_button.length > 0 && ( last_button != target_button.attr('row_info_cell_id') ) )
        {
            $( 'button[row_info_cell_id=' + last_button + ']' ).find( 'span' ).toggleClass( 'glyphicon glyphicon-chevron-down glyphicon glyphicon-chevron-up' );
            $( '#rr' + $( 'button[row_info_cell_id=' + last_button + ']' ).attr( 'row_info_id' ) + '_' );
            $( '#rr' + $( 'button[row_info_cell_id=' + last_button + ']' ).attr( 'row_info_id' ) + ' td' ).toggleClass( 'selected_top_cells' );
            $( '#rc_' + $( 'button[row_info_cell_id=' + last_button + ']' ).attr( 'row_info_cell_id' ) ).toggleClass( 'selected_top_cell' );
        }

        target_button.find( 'span').toggleClass( 'glyphicon glyphicon-chevron-down glyphicon glyphicon-chevron-up' );
        $( '#rc_' + target_button.attr( 'row_info_cell_id' ) ).toggleClass( 'selected_top_cell' );
        $( '#rr' + target_button.attr( 'row_info_id' ) + ' td' ).toggleClass( 'selected_top_cells' );

        if ( !opened_row.length )
        {
            row_info.toggleClass( 'open_info_row' ).show();
            row_info_cell.toggleClass( 'open_info_cell' ).slideDown( t );
        }
        else
            if ( rows_not_equal )
            {
                opened_cell.toggleClass( 'open_info_cell' ).slideUp( t, function(){ opened_row.toggleClass( 'open_info_row').hide(); } );
                row_info.toggleClass( 'open_info_row' ).show();
                row_info_cell.toggleClass( 'open_info_cell').slideDown( t );
            } else
            {
                if ( cells_not_equal )
                {
                    opened_cell.toggleClass( 'open_info_cell' ).hide();
                    row_info_cell.toggleClass( 'open_info_cell' ).show();
                } else
                {
                    row_info_cell.toggleClass( 'open_info_cell' );
                    if ( !row_info_cell.hasClass( 'open_info_cell' ) )
                        row_info_cell.slideUp( t, function(){ row_info.toggleClass( 'open_info_row').hide(); } );
                }
            }
    }

    //Обновление лучщего прайса
    function SetBestPrice( res )
    {
        var res_rows = _.groupBy( res, function(r){ return r.c.replace(/([0-9]+_[A-Za-z0-9]+).+/gi, '$1' ) } );
        _.each( res_rows, function( row, row_id ){

            var min_price_cell = _.min( row, function( cell, cell_id ){ return ( parseInt( cell.d.e ) == 0 ) && ( parseInt( cell.d.cp ) >= 100 ) && ( parseInt( cell.d.p ) > 0 ) ?  parseInt( cell.d.p ) : 9000000; });
            var min_price = parseInt( min_price_cell.d.p );
            var min_price_agency = min_price_cell.c.replace( /[0-9]+_[0-9]+_(.+)/gi, '$1' );
            if ( ( min_price != 9000000 ) && ( parseInt( min_price_cell.d.cp ) >= 100 ) )
            {
                $( '#rr' + row_id +' .best_price' ).removeClass( 'best_price' );
                $( '#rc_' + min_price_cell.c ).addClass( 'best_price' );
            }
            _.each( row, function( cell, cell_id ) {
                if ( ( parseInt( cell.d.cp ) >= 100 ) && ( parseInt( cell.d.e ) == 0 ) && ( parseInt( cell.d.p ) > 0 ) )
                {
                    var curr_price_agency = cell.c.replace( /[0-9]+_[0-9]+_(.+)/gi, '$1' );
                    var curr_price = parseInt( cell.d.p );
                    if ( ( min_price != 9000000 ) && ( curr_price_agency != min_price_agency ) && ( curr_price > 0 ) )
                    {
                         $('#rc_'+cell.c).find( '.compare_price').text(  BeautifullyPrice( min_price - curr_price ) );
                    }
                }
            } );


        } );
        //console.log( res_rows );
        //console.log( res );
    }

    //Обновление лучщего прайса
    function SetBestPrice2( res )
    {
        var res_rows = _.groupBy( res, function(r){ return r.c.replace(/([0-9]+_[A-Za-z0-9]+).+/gi, '$1' ) } );
        _.each( res_rows, function( row, row_id ){

            var min_price_cell = _.min( row, function( cell, cell_id ){ return ( parseInt( cell.e ) == 0 ) && ( parseInt( cell.cp ) >= 100 ) && ( parseInt( cell.p ) > 0 ) ?  parseInt( cell.p ) : 9000000; });
            var min_price = parseInt( min_price_cell.p );
            var min_price_agency = min_price_cell.c.replace( /[0-9]+_[0-9]+_(.+)/gi, '$1' );
            if ( ( min_price != 9000000 ) && ( parseInt( min_price_cell.cp ) >= 100 ) )
            {
                $( '#rr' + row_id +' .best_price' ).removeClass( 'best_price' );
                $( '#rc_' + min_price_cell.c ).addClass( 'best_price' );
            }
            _.each( row, function( cell, cell_id ) {
                if ( ( parseInt( cell.cp ) >= 100 ) && ( parseInt( cell.e ) == 0 ) && ( parseInt( cell.p ) > 0 ) )
                {
                    var curr_price_agency = cell.c.replace( /[0-9]+_[0-9]+_(.+)/gi, '$1' );
                    var curr_price = parseInt( cell.p );
                    if ( ( min_price != 9000000 ) && ( curr_price_agency != min_price_agency ) && ( curr_price > 0 ) )
                    {
                        $('#rc_'+cell.c).find( '.compare_price').text(  BeautifullyPrice( min_price - curr_price ) );
                    }
                }
            } );


        } );
        //console.log( res_rows );
        //console.log( res );
    }

    //Остановить запросы если поиск завершен
    function StopRequestsIfSearchIsComplated( res )
    {
        var is_completed_cell = function(c) { return ( parseInt(c.d.e) > 0 ) || ( parseInt(c.d.cp) >= 100 ) };
        var is_finished = _.every( res, is_completed_cell );
        if ( is_finished )
            $( '#stop_search').trigger('click');
    }

    //Красивая цена
    function BeautifullyPrice( p )
    {
        var m;
        m = p < 0 ? '-' : '';
        p = Math.abs(p) + '';
        if ( p.length <= 3 )
            return m + p;
        return m + BeautifullyPrice( p.substr( 0, p.length - 3 ) ) + ',' + p.substr( p.length - 3, 3 );
    }

    //Показываем окно редактирования даты маршрута(ов)
    function ShowRouteEditWindow( data )
    {

        var routes = data[1], edit_type = data[0], r, ar, one_way_false_txt ='', one_way_true_txt = '', one_way_false_ids = '', one_way_true_ids = '';
        if ( edit_type == 'one' )
        {
            r = routes.split( '~' );
            r[4] = parseInt( r[4] );
            ar = ' <span class="' + ( r[4] ? 'glyphicon glyphicon-arrow-right' : 'glyphicon glyphicon-resize-horizontal' ) + '"></span> ';
            $('#one_route_edit_text').html( r[0] + ar + r[1] + '&nbsp;' + r[2] + ( !r[4] ? ' &#151; ' + r[3] : '' ) );
            $('#one_route_edit_arr_date').attr( 'disabled', Boolean(r[4]) );
            $('#one_route_edit_dep_date, #one_route_edit_arr_date').attr( 'placeholder', 'ДД.ММ.ГГГГ').val( '');
            $('#edit_one_route').modal('show');
            $('#save_edit_one_route').attr( 'one_way', r[4] ).attr( 'route_id', r[6] );

        } else if ( edit_type == 'mass' )
        {
            var i;
            routes = routes.split(',');
            for (i = 0; i < routes.length; i++ )
            {
                r = routes[i].split( '~' );
                r[4] = parseInt( r[4] );
                ar = ' <span class="' + ( r[4] ? 'glyphicon glyphicon-arrow-right' : 'glyphicon glyphicon-resize-horizontal' ) + '"></span> ';
                if ( !r[4] )
                {
                    one_way_false_ids += (!one_way_false_ids ? '' : ',' ) + r[6];
                    one_way_false_txt += '<div class="top_border">' + r[0] + ar + r[1] + '&nbsp;' + r[2] + ( !r[4] ? ' &#151; ' + r[3] : '' ) + '</div>';
                }
                else
                {
                    one_way_true_ids += (!one_way_true_ids ? '' : ',' ) + r[6];
                    one_way_true_txt  += '<div class="top_border">' + r[0] + ar + r[1] + '&nbsp;' + r[2] + ( !r[4] ? ' &#151; ' + r[3] : '' ) + '</div>';
                }
            }

            $('#mass_route_edit_dep_date_false, #mass_route_edit_arr_date_false, #mass_route_edit_dep_date_true').attr( 'placeholder', 'ДД.ММ.ГГГГ').val( '');
            $('#mass_route_edit_false_text').html( one_way_false_txt );
            $('#mass_route_edit_true_text').html( one_way_true_txt );
            $('#edit_mass_route').modal('show');
            $('#save_edit_mass_route').attr( 'route_id_false', one_way_false_ids ).attr( 'route_id_true', one_way_true_ids );
        }

    }

    //Имя пользователя
    var lun =  parseInt( $.cookie( 'remember_me' ) ) ? $.cookie( 'loged_user_name' ) : '';
    ShowLoggedUserName( $('#loged_user_name').val( lun ).val() );



    //************************************************************************************************************************************************
    //
    //ЧАСТЬ 3. ОБЯВЛЕНИЕ ПОТОКОВ СОБЫТИЙ (EventStream), СВОЙСТВ(Property)
    //
    //************************************************************************************************************************************************



    //Возвращяет поток данныхв случай если был нажат клавиша
    function keyDownEvents( el, keyCode )
    {
        return $(el).asEventStream("keydown").filter(keyCodeIs(keyCode)).map(function(e){return e});
    }

    //Возвращяет поток данных в случай если была отжата клавиша
    function keyUpEvents( el, keyCode )
    {
        return $(el).asEventStream("keyup").filter(keyCodeIs(keyCode)).map(function(e){return e});
    }

    //Возвращяет поток данных в случай если была нажата илиже отжата клавиша
    function keyStateProperty(keyCode)
    {
        return keyDownEvents(keyCode).merge(keyUpEvents(keyCode));
    }

    //Возвращяет поток данных для полей городов
    function GetCityVal ( el )
    {
        return $(el).asEventStream( 'typeahead:selected', function( event, selected_item, dataset_name ){return [event, selected_item, dataset_name];}).map(GetCity);
    }

    //Возвращяет поток данных для полей дат
    function GetDateVal(el)
    {
        var date_stream =  $(el).asEventStream('changeDate').map( function(e){return [e,false]} );
        var enter_stream = keyDownEvents( el, 13).map( function(e){return [e,true]} );
        return date_stream.merge( enter_stream ).filter( isValidDate ).map( GetDate );
    }

    //Возвращяет поток данных для селектбоксов
    function GetSelectBoxVal(el)
    {
        return $(el).asEventStream('change').map(function(e,p){return $(e.target).val()});
    }

    //Возвращяет поток данных для чекбоксов
    function GetCheckBoxState(el)
    {
        return $(el).asEventStream('ifChanged change').map(function(e){ e.stopPropagation(); return $(e.target).is(':checked');});
    }

    //Возвращяет поток данных текстового поля
    function GetInputTextVal(el)
    {
        return $(el).asEventStream('change').map(function(e){ e.stopPropagation(); return $(e.target).val();});
    }

    //Возвращяет поток данныъ в случай если у формы был вызван функция reset. при этом возвращяемые данные в потоке могут быть установлены через переменную v
    function ResetFormElement( v )
    {
        if ( typeof( form_reseted ) == 'undefined' )
            form_reseted = $('#main_form').asEventStream('reset');

        return form_reseted.map( v).toProperty( v );
    }

    //Посылает запрос через ajax и возвращяет поток данных
    function RequestViaAjax( request )
    {
        return Bacon.fromPromise( $.ajax( request ) );
    }

    //Посылает запрос через ajax и возвращяет поток данных
    function LoadFullResult(for_load)
    {
        var bus = new Bacon.Bus();
        _.each( for_load, function( v, k )
                {
                    $('#rc_' + v.c ).addClass('res_loaded');
                    bus.plug( Bacon.once(1).map(v.c).map( BuildSearchFullResultRequest ).flatMapLatest( RequestViaAjax ) );
                }
        );
        return bus;
    }

    //Кнопка Отметить агенства
    agency_checkbox_clicked = $('#check_agencies').asEventStream( 'click' ).map( 'toggle' ).toProperty( 'update' );

    //Кнопка Отметить поля
    field_checkbox_clicked = $('#check_fields').asEventStream( 'click' ).map( 'toggle' ).toProperty( 'update' );

    //Кнопка Отметить маршруты в списке маршрутов
    route_checkbox_clicked = $('#check_routes').asEventStream( 'click' );

    //При кликании на иконку ссылки у чекбоксов агенств
    agency_anchor_clicked = $('.ext_link').asEventStream('click').map( getAgencyRef );

    //МОДАЛЬНОЕ ОКНО
    //Имя пользователя
    loged_user_name = GetInputTextVal('#loged_user_name').toProperty( $('#loged_user_name').val() );

    //Модальное окно ввода имени пользователя было открыто
    user_name_modal_dialog_opened = $('#name_dialog').asEventStream('shown.bs.modal').map(false);

    //Модальное окно ввода имени пользователя было закрыто
    user_name_modal_dialog_closed = $('#name_dialog').asEventStream('hidden.bs.modal').map(true);

    //Модальное окно закрыто?
    user_name_modal_dialog_is_hidden = Bacon.mergeAll( user_name_modal_dialog_opened, user_name_modal_dialog_closed ).toProperty( true );

    //Не порали показать модальное окно ввода имении пользователя?
    its_time_to_show_user_name_modal_dialog = Bacon.combineAsArray( loged_user_name.map(isEmpty), user_name_modal_dialog_is_hidden  );

    //Пора показать модальное окно ввода имени пользователя
    show_user_name_modal_dialog = its_time_to_show_user_name_modal_dialog.filter(_.isEqual, [true, true] ).map( true );

    //Имя пользователя веден
    user_name_entered = its_time_to_show_user_name_modal_dialog.filter(_.isEqual, [false, true] ).map( true );


    //ФОРМА
    //Состояние чекбокса при клике на "В один конец"
    one_way_state = GetCheckBoxState('#ff_one_way');

    //Собираем данные формы
    form_data = Bacon.combineTemplate(
        {
            departure_city : GetCityVal ( '#ff_departure_city').merge(ResetFormElement('')), //Город вылета
            arrival_city : GetCityVal ( '#ff_arrival_city' ).merge(ResetFormElement('')), //Город прибытия
            departure_date : GetDateVal( '#ff_departure_date' ).merge(ResetFormElement('')), //Дата ывлета
            arrival_date : GetDateVal( '#ff_arrival_date' ).merge(ResetFormElement('')), //Дата прибытия
            adults : GetSelectBoxVal('#ff_adults').merge(ResetFormElement(1)), //взрослые
            children : GetSelectBoxVal('#ff_children').merge(ResetFormElement(0)), //дети
            infants : GetSelectBoxVal('#ff_infants').merge(ResetFormElement(0)), //младенцы
            one_way : one_way_state.merge(ResetFormElement(false)), // "В один конец"
            direct : GetCheckBoxState('#ff_direct').merge(ResetFormElement(false)), //Только прямые рейсы
            class : GetSelectBoxVal('#ff_class').merge(ResetFormElement(0)), // класс
            user : loged_user_name // имя пользователя,
        }
    );
    form_data.onValue(UpdateDataToDirectSearch);

    //Код ошибки формы
    form_error = form_data.map(FormError);

    //Данные в форме верные?
    is_valid_form = form_error.map(noErrors);

    //Кнопка "добавить в список маршрутов" была кликнута
    add_to_list_button_clicked = $('#add_to_list').asEventStream('click').map(true).merge(ResetFormElement(false));

    //Готовим запрос добавления нового маршрута в случай если форма валидная атакже была кликнута на "добавить в список маршрутов"
    add_user_route_request = Bacon.combineAsArray( is_valid_form, add_to_list_button_clicked, form_data).filter(CanBeAdded).map(BuildAddToListRequest).doAction(function(){ $('#main_form').trigger( 'reset' ); $('#ff_departure_city').focus();  });

    //Получаем ответа сервера после того как отправили запрос на добавления нового маршрута в список маршрутов
    add_user_route_responce = add_user_route_request.flatMapLatest(RequestViaAjax);

    //Добавление нового маршрута произашло успешно
    add_user_route_success = add_user_route_responce.map(function(v){return [v];}).filter(_.isEqual, ["0"] ).map(true);

    //Кликнули на кнопку удалить в списке маршрутов илиже кликнули на кнопку удалить маршруты под списком маршрутов
    route_delete_clicked = $('#routes_list').asEventStream( 'click', '.delete_one_route_button').filter(ShowConfirm('Вы уверены в том что хотите удалить маршрут?')).map(function(e){return $(e.currentTarget).attr('roue-id');}).merge($('#delete_routes').asEventStream( 'click').filter(ShowConfirm('Вы уверены в том что хотите удалить выбранные маршруты?')).map(function(e){return GetCheckedList('#routes_list');})).filter(isNotEmpty);

    //Запрос на удаление маршрутов
    routes_delete_request = route_delete_clicked.map( BuildDeleteRoutesRequest );

    //Ответ от сервера после удаления маршрутов
    routes_delete_responce = routes_delete_request.flatMapLatest(RequestViaAjax);

    //Удаление маршрутов прошло удачно
    routes_delete_success = routes_delete_responce.map(isEqual2(1));

    //Открыт диалоговое окно для изменения даты у одного маршрута
    one_route_edit_window_opened = $('#edit_one_route').asEventStream('shown.bs.modal').map(true).toProperty(false);

    //Открыт диалоговое окно для изменения даты у маршрутов
    mass_route_edit_window_opened = $('#edit_mass_route').asEventStream('shown.bs.modal').map(true).toProperty(false);

    //Кликнули на кнопку редактирование одного маршрута в списке маршрутов
    route_edit_clicked = $('#routes_list').asEventStream( 'click', '.edit_one_route_button' ).map( function(e){ return ['one', $(e.currentTarget).parent().parent().find('.route_checkbox').attr('route2')];} ).merge( $('#edit_routes').asEventStream( 'click').map( function(e){ return ['mass', GetCheckedCheckboxAttributes('.routes_list .route_checkbox:checked', 'route2')];} )).filter(function(d){ return isNotEmpty(d[1])});

    //Была нажата кнопка сохранение изменения даты у одного маршрута
    save_edit_one_route_button_clicked = $('#save_edit_one_route').asEventStream('click').map(PrepareSaveDates('one'));

    //Была нажата кнопка сохранение изменения даты у  маршрутов
    save_edit_mass_route_button_clicked = $('#save_edit_mass_route').asEventStream('click').map(PrepareSaveDates('mass'));

    //Событие сохранения изменений даты у маршрутов
    save_edit_routes_dates = Bacon.mergeAll( save_edit_one_route_button_clicked, save_edit_mass_route_button_clicked );

    //Запрос на обновление даты у маршрута
    routes_edit_request = save_edit_routes_dates.map( BuildEditRoutesRequest );

    //Ответ от сервера после обновления маршрута(ов)
    routes_edit_responce = routes_edit_request.flatMapLatest(RequestViaAjax);

    //Обновление маршрута(ов) прошло удачно
    routes_edit_success = routes_edit_responce.map(isEqual2(1));

    //При наведении мышки над одним маршрутом в списке маршрутов
    show_hide_one_user_route_operations_buttons = $('#routes_list').asEventStream( 'mouseenter mouseleave', '.one-user-route').map(function(e){ return [ $(e.target), e.handleObj.origType == 'mouseenter' ? true : false]; });

    //При кликании на один маршрут в списке маршрутов
    one_route_clicked = $('#routes_list').asEventStream( 'click', '.one-user-route').map(function(e){ return $(e.currentTarget);}).merge($('#routes_list').asEventStream( 'ifClicked', '.route_checkbox'  ).map(function(e){ return $(e.currentTarget).parent().parent().parent();}));

    //Готовим запрос на получение списка маршрутов пользователя в случай если ранье был добавлен новый маршрут илиже был изменен имя пользователя
    user_routes_list_request = Bacon.combineAsArray( Bacon.mergeAll( add_user_route_success, user_name_entered, routes_delete_success, routes_edit_success ), loged_user_name ).filter( ReturnInput( '', 0 ) ).map( ReturnInput( '', 1 ) ).map( BuildRoutesListRequest );

    //Получили от сервера на запрос списка маршрутов
    user_routes_list_responce = user_routes_list_request.flatMapLatest(RequestViaAjax);

    //Активность кнопки удалить маршруты
    multi_route_delete_button_active = Bacon.mergeAll( user_routes_list_responce, one_route_clicked, route_checkbox_clicked).delay(200).map(function(e){return GetCheckedList('#routes_list');}).map(isNotEmpty).toProperty(false);

    //Кнопка начать поиск по списку маршрутов была кликнута
    search_from_routes_list_button_clicked = $( '#search_from_routes_list' ).asEventStream('click').map('from_list');

    //Кнопка начать поиск , прямой поиск без внесения в список маршрутов
    start_search_button_clicked = $( '#start_search' ).asEventStream('click').map('direct');

    //Кнопка остановить поиск была кликнута
    stop_search_button_clicked = $( '#stop_search' ).asEventStream('click').map(true);

    //Запрос на добавление нового поискового запроса
    search_id_request = Bacon.mergeAll( search_from_routes_list_button_clicked, start_search_button_clicked ).map( BuildSearchIdRequest);

    //Ответ от добавления нового поискового запроса
    search_id_responce = search_id_request.flatMapLatest(RequestViaAjax);

    //Если получили id поискового запроса
    search_id_success = search_id_responce.filter(function(d){return d > 0}).doAction(RenderNewResultTable).map(true).toProperty(false);

    //Идет поиск
    search_is_active = Bacon.mergeAll( search_id_success.map(isEqual2(true)), stop_search_button_clicked.not());

    //Запрос проверки состояния поиска
    search_state_request = Bacon.combineAsArray( search_is_active, search_id_responce, Bacon.interval( 1000 ) ).filter( ReturnInput( '', 0 ) ).map( ReturnInput( '', 1 )).map( BuildSearchStateRequest);

    //Ответ от сервера при получении статуса поиска
    search_state_responce = search_state_request.flatMapLatest(RequestViaAjax).doAction(UpdateResultTable).doAction(SetBestPrice).doAction(StopRequestsIfSearchIsComplated);
    search_state_responce.onValue(function(v){});

    //Запрос на получение полной информации об перелете
    search_full_result_responce = search_state_responce.map(CellsForLoad).filter(isNotEmpty).flatMap(LoadFullResult);

    //При кликании на кнопку раскрыть информацию
    full_info_clicked = $('#result_content').asEventStream( 'click', '.info_button').map( function( e ){ return $(e.currentTarget); } );

    //Активность кнопки начать поиск по списку маршрутов
    search_from_routes_list_button_active = Bacon.mergeAll( /* user_routes_list_responce.delay(200).map(function(e){ return $('.route_checkbox').length > 0 }), */ search_is_active.not(), multi_route_delete_button_active ).toProperty(false);

    //Активность кнопки поиск по списку маршрутов
    stop_search_button_active = Bacon.mergeAll(search_is_active).toProperty(false);

    //Готовим запросы на запуск агенств
    //run_agency_request = Bacon.combineAsArray( search_is_active, search_id_responce, loged_user_name, search_is_active.map(function(){return $('#routes_list .route_checkbox').map(GetAttr('route')).get()}),$('.av_check:checked').map(GetAttr('name')).get(), $('.f_check:checked').map(GetAttr('name')).get().join(',') ).filter(function(d){ return d[0] == true}).map(BuildRunAgencyRequest);
    //run_agency_request.log();

    //************************************************************************************************************************************************
    //
    // ЧАСТЬ 4. ЛОГИКА РАБОТЫ, ФУНКЦИОНАЛ  (Side effects)
    //
    //************************************************************************************************************************************************



    //Кнопка Отметить агенства
    agency_checkbox_clicked.assign( $('.av_check'), 'iCheck' );

    //Кнопка Отметить поля
    field_checkbox_clicked.assign( $('.f_check'), 'iCheck' );

    //Кнопка Отметить маршруты
    route_checkbox_clicked.onValue( function(e){  $('.route_checkbox').iCheck('toggle'); ChangeRoutesActive(); } );

    //При кликании на иконку ссылки у чекбоксов агенств
    agency_anchor_clicked.onValue( OpenAgencyRef );

    //МОДАЛЬНОЕ ОКНО
    //При показе модального окна передаем фокус к текстовому полю
    user_name_modal_dialog_opened.onValue( SetUserNameFocus );

    //При закрытии модального окна
    user_name_modal_dialog_closed.onValue( SaveUserName );

    //Показываем диалоговое окно в случай если имя пользователя пустое атакже модальное окно закрыто
    show_user_name_modal_dialog.assign( $('#name_dialog'), 'modal', 'show' );

    //ФОРМА
    //Включение отключение поля даты прибытия при кликании на "В один конец"
    one_way_state.assign($('#ff_arrival_date'), 'attr', 'disabled');

    //Если даннные в форме верные то возвращяем активность кнопкам поиска атакже доавбления в список маршрутов
    Bacon.mergeAll( is_valid_form ).onValue( EnableButtons('#add_to_list, #start_search') );

    //Подгружаем список маршрутов пользователя
    user_routes_list_responce.assign($('#routes_list .routes_list'), 'html');

    //Показываем илиже прячим кнопку удаления одного маршрута
    show_hide_one_user_route_operations_buttons.onValue(ShowHideUserRouteOperationsButtons);

    //Помечаем чекбокс при кликании на один маршрут в списке маршрутов
    one_route_clicked.onValue(SetActiveRoute);

    //активируем кнопку удалить маршруты
    multi_route_delete_button_active.onValue(EnableButtons('#delete_routes, #edit_routes'));
    
    //Активируем кнопку начать поиск по списку маршрутов
    search_from_routes_list_button_active.onValue(EnableButtons('#search_from_routes_list'));

    //Активируем кнопку остановить поиск
    stop_search_button_active.onValue(EnableButtons('#stop_search'));

    //Показываем панели атакже элементы формы поиска
    search_is_active.onValue(HideShowPanelsAndSearchForm);

    //При кликании на остановить поиск
    stop_search_button_clicked.onValue(ShowInfoAboutSearchWasStoped);

    //Если загрузили полную информацию об полете
    search_full_result_responce.onValue(RenderFullResult);

    //При кликании на кнопку раскрыть информацию
    full_info_clicked.onValue(ShowHideFullInfo);

    //При кликании на редактирование маршрута
    route_edit_clicked.onValue(ShowRouteEditWindow);

    //При открыти диалогового окна для изменения даты у одного маршрута
    one_route_edit_window_opened.onValue( SetFocus('#one_route_edit_dep_date') );

    //При открыти диалогового окна для изменения даты у маршрутов
    mass_route_edit_window_opened.onValue( SetFocus('#mass_route_edit_dep_date_false') );

    //save_edit_one_route_button_clicked.onValue(LogConsole);

   if ( typeof ( result_data ) != 'undefined' )
       RenderNewResultTable2( result_data, result_routes );

});