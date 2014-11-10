<?
//dump($cfg,$_SERVER['DOCUMENT_ROOT']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?=$cfg['WEB_PATH'];?>">
    <title>APS Air Price Scanner</title>
    <script>
        var COMPARISION_AGENCY = '<?=OUR_SITE;?>';
    </script>


    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Bootstrap+chosen -->
    <!--<link href="css/bootstrap_and_chosen.min.css" rel="stylesheet">-->

    <!-- icheck-->
    <link href="css/icheck/flat/_all.css" rel="stylesheet">

    <!-- chosen-->
    <link href="css/chosen/chosen.min.css" rel="stylesheet">
    <link href="css/chosen/chosen-bootstrap.css" rel="stylesheet">

    <!-- typeeahead-->
    <link href="css/typeahead/typeahead.css" rel="stylesheet">

    <!-- Datepicker-->
    <!-- <link href="css/datepicker/datepicker.css" rel="stylesheet">-->
    <!-- Datepicker-->
    <link href="css/eternicode-bootstrap-datepicker/datepicker3.css" rel="stylesheet">

    <!-- Новый вид тултипов-->
    <link href="css/new_tooltip.css" rel="stylesheet">

    <!-- aps-->
    <link href="css/aps.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery/jquery-1.11.0.min.js"></script>
    <script src="js/jquery/jquery-migrate-1.2.1.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Bootstrap -->
    <script src="js/bootstrap.min.js"></script>
    <!-- icheck-->
    <script src="js/icheck/icheck.js"></script>
    <!-- chosen-->
    <script src="js/chosen/chosen.jquery.min.js"></script>
    <!-- typeahead-->
    <script src="js/typeahead/typeahead.bundle.min.js"></script>
    <!-- Bacon-->
    <script src="js/bacon/Bacon.min.js"></script>
    <!-- Underscore-->
    <script src="js/underscore/underscore-min.js"></script>
    <!-- Datepicker-->
    <!-- <script src="js/datepicker/bootstrap-datepicker.js"></script>-->
    <!-- Datepicker-->
    <script src="js/eternicode-bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="js/eternicode-bootstrap-datepicker/locales/bootstrap-datepicker.ru.js"></script>
    <!-- Cookie-->
    <script src="js/cookie/jquery.cookie.js"></script>
    <!-- Input mask-->
    <script src="js/jquery.maskedinput/jquery.maskedinput-1.3.min.js"></script>
    <!-- iata base-->
    <script src="js/iata_base/base.js"></script>

    <!-- Новый вид тултипов-->
    <script src="js/new_tooltip.js"></script>

    <!-- Инициализация всего скрипта-->
    <script src="js/aps.js"></script>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top " role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">APS</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?=$cfg['WEB_PATH'];?>">Поиск</a></li>
                <li><a href="<?=$cfg['WEB_PATH'];?>history.php">История поисков</a></li>
                <!--
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">Nav header</li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>
                -->
            </ul>
            <p class="navbar-text navbar-right user_loged"></p>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" data-toggle="modal" data-target="#name_dialog">Войти под другим именим</a></li>
            </ul>

        </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
</div>
<div class="clearfix"></div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3">
            <div class="panel panel-primary">
                <div class="panel-heading text-center">
                    <h3 class="panel-title">Опции</h3>
                </div>
                <div class="panel-body hide_show_on_search">
                    <ul class="nav nav-tabs" id="myTab">
                        <li class="active"><a href="#agencies" data-toggle="tab">Агентства</a></li>
                        <li><a href="#fields" data-toggle="tab">Поля</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade in active top_padding_5" id="agencies">
                            <div class="well well-sm">
                                <div class="row ">
                                    <div class="col-xs-6 small">

                                        <label style="white-space: nowrap"><input type="checkbox" name="aviakassa.ru" class="av_check"  value="1" tabindex="1" checked="checked"/>&nbsp;&nbsp;aviakassa.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.aviakassa.ru"></span></label><br />
                                        <label><input type="checkbox" name="anywayanyday.com" class="av_check" value="1" tabindex="2" checked="checked"/>&nbsp;&nbsp;awad.com&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.anywayanyday.com" ></span></label><br />
                                        <label><input type="checkbox" name="pososhok.ru" class="av_check" value="1" tabindex="7" checked="checked"/>&nbsp;&nbsp;pososhok.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.pososhok.ru"></span></label><br />
                                        <label><input type="checkbox" name="davs.ru" class="av_check" value="1" tabindex="8" checked="checked"/>&nbsp;&nbsp;davs.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.davs.ru"></span></label><br />
                                        <label><input type="checkbox" name="sindbad.ru" class="av_check" value="1" tabindex="11" checked="checked"/>&nbsp;&nbsp;sindbad.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.sindbad.ru"></span></label><br />
                                        <!-- <label><input type="checkbox" name="bilet-on-line.ru" class="av_check" value="1" tabindex="11" checked="checked"/>&nbsp;&nbsp;bilet-on-line.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.bilet-on-line.ru"></span></label><br />-->
                                        <label><input type="checkbox" name="svyaznoy.travel" class="av_check" value="1" tabindex="11" checked="checked"/>&nbsp;&nbsp;svyaznoy.travel&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.svyaznoy.travel"></span></label><br />
                                        <label><input type="checkbox" name="ozon.travel" class="av_check" value="1" tabindex="11" checked="checked"/>&nbsp;&nbsp;ozon.travel&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.ozon.travel"></span></label><br />
                                        <label><input type="checkbox" name="avia.euroset.ru" class="av_check" value="1" tabindex="11" checked="checked"/>&nbsp;&nbsp;avia.euroset.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://avia.euroset.ru"></span></label><br />

                                    </div>
                                    <div class="col-xs-6 small">
                                        <label><input type="checkbox" name="biletix.ru" class="av_check" value="1" tabindex="10" checked="checked"/>&nbsp;&nbsp;biletix.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.biletix.ru"></span></label><br />
                                        <label><input type="checkbox" name="trip.ru" class="av_check" value="1" tabindex="12" checked="checked"/>&nbsp;&nbsp;trip.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.trip.ru"></span></label><br />
                                        <label><input type="checkbox" name="amargo.ru" class="av_check" value="1" tabindex="9" checked="checked"/>&nbsp;&nbsp;amargo.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.amargo.ru"></span></label><br />
                                        <label><input type="checkbox" name="agent.ru" class="av_check" value="1" tabindex="3" checked="checked"/>&nbsp;&nbsp;agent.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.agent.ru"></span></label><br />
                                        <label><input type="checkbox" name="nabortu.ru" class="av_check" value="1" tabindex="5" checked="checked"/>&nbsp;&nbsp;nabortu.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.nabortu.ru"></span></label><br />
                                        <label><input type="checkbox" name="onetwotrip.com" class="av_check" value="1" tabindex="5" checked="checked"/>&nbsp;&nbsp;onetwotrip.com&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.onetwotrip.com"></span></label><br />
                                        <label><input type="checkbox" name="tickets.ru" class="av_check" value="1" tabindex="5" checked="checked"/>&nbsp;&nbsp;tickets.ru&nbsp;<span class="glyphicon glyphicon-link small text-primary ext_link" link="http://www.tickets.ru"></span></label>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success center-block " id="check_agencies">
                                <span class="glyphicon glyphicon-ok glyphicon "></span>
                                Отметить агентства
                            </button>
                        </div>
                        <div class="tab-pane fade top_padding_5" id="fields">
                            <div class="well well-sm">
                                <div class="row">
                                    <div class="col-xs-6 small">
                                        <label><input type="checkbox" name="dep_time" class="f_check"  value="1" checked="checked" />&nbsp;Время вылета</label><br />
                                        <label><input type="checkbox" name="fly_time" class="f_check" value="1" checked="checked"/>&nbsp;Время полёта</label><br />
                                        <label><input type="checkbox" name="comp_code" class="f_check" value="1" checked="checked"/>&nbsp;Код&nbsp;компании</label><br />
                                        <label><input type="checkbox" name="comp_name" class="f_check" value="1" />&nbsp;Название&nbsp;компании</label><br />
                                    </div>
                                    <div class="col-xs-6 small">
                                        <label><input type="checkbox" name="flight" class="f_check" value="1" checked="checked"/>&nbsp;Рейс</label><br />
                                        <label><input type="checkbox" name="airplane" class="f_check" value="1"/>&nbsp;Самолёт</label><br />
                                        <label><input type="checkbox" name="changing" class="f_check" value="1" />&nbsp;Пересадки</label><br />
                                        <label><input type="checkbox" name="request_time" class="f_check" value="1" />&nbsp;Врм.&nbsp;отрб.&nbsp;запр.</label><br />
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success center-block " id="check_fields">
                                <span class="glyphicon glyphicon-ok glyphicon"></span>
                                Отметить поля
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="well well-sm">
                <form role="form" id="main_form">
                    <div class="row hide_show_on_search">
                        <div class="col-sm-6">
                            <!-- <div class="input-group ">-->
                                <input type="text" class="form-control" id="ff_departure_city" placeholder="Город вылета" autocomplete="off">
                                <!-- <span class="input-group-addon glyphicon glyphicon-plane"></span> -->
                            <!-- </div>-->
                        </div>
                        <div class="col-sm-6">
                            <!-- <div class="input-group ">-->
                                <input type="text" class="form-control" id="ff_arrival_city" placeholder="Город прибытия" autocomplete="off">
                                <!-- <span class="input-group-addon glyphicon glyphicon-plane"></span> -->
                            <!-- </div>-->
                        </div>
                    </div>
                    <div class="row top_padding_15 hide_show_on_search">
                        <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-xs-4 col-sm-6 ">
                                        <div class="input-group ">
                                            <input type="text" class="form-control text-center" id="ff_departure_date" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                                            <span class="input-group-addon glyphicon glyphicon-calendar text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-4 col-sm-6 ">
                                        <div class="input-group ">
                                            <input type="text" class="form-control text-center" id="ff_arrival_date" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                                            <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                </div>

                        </div>
                        <div class="col-sm-6">
                            <div class="row ">
                                <div class="col-xs-4 ">
                                    <select id="ff_adults" class="form-control chosen-select-default ">
                                        <option value="0">Взрос. 0</option>
                                        <option value="1" selected>Взрос. 1</option>
                                        <option value="2">Взрос. 2</option>
                                        <option value="3">Взрос. 3</option>
                                        <option value="4">Взрос. 4</option>
                                        <option value="5">Взрос. 5</option>
                                        <option value="6">Взрос. 6</option>
                                    </select>
                                </div>
                                <div class="col-xs-4">
                                    <select id="ff_children" class="form-control chosen-select-default ">
                                        <option value="0" selected>Дети 0</option>
                                        <option value="1">Дети 1</option>
                                        <option value="2">Дети 2</option>
                                        <option value="3">Дети 3</option>
                                        <option value="4">Дети 4</option>
                                        <option value="5">Дети 5</option>
                                        <option value="6">Дети 6</option>
                                    </select>
                                </div>
                                <div class="col-xs-4 ">
                                    <select id="ff_infants" class="form-control chosen-select-default ">
                                        <option value="0" selected>Младц. 0</option>
                                        <option value="1">Младц. 1</option>
                                        <option value="2">Младц. 2</option>
                                        <option value="3">Младц. 3</option>
                                        <option value="4">Младц. 4</option>
                                        <option value="5">Младц. 5</option>
                                        <option value="6">Младц. 6</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row top_padding_15 hide_show_on_search">
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label class="cursor_hand">
                                        <input type="checkbox" class="icheckbox" id="ff_one_way"> В один конец
                                    </label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="cursor_hand">
                                        <input type="checkbox" class="icheckbox" id="ff_direct"> Прямые рейсы
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <select id="ff_class" class="form-control chosen-select-default ">
                                        <option value="0" selected>Эконом класс</option>
                                        <option value="1">Бизнес класс</option>
                                        <option value="2">Первый класс</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <!---->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-center">

                                <br>
                                <table align="center" border="0">

                                    <tr>
                                        <td>
                                            <span class="hide_show_on_search"> <button class="btn btn-success" style="width: 100%;" type="button" id="add_to_list" ><span class="glyphicon glyphicon-plus"></span> Добавить в список маршрутов</button> </span>
                                        </td>
                                        <td> &nbsp; &nbsp; </td>
                                        <td>
                                            <span class="hide_show_on_search"> <button class="btn btn btn-primary" style="width: 100%;" type="button" id="start_search"><span class="glyphicon glyphicon-search"></span> Поиск (по введёному маршруту)</button> </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            &nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button class="btn btn-danger" style="width: 100%;" type="button" id="stop_search"><span class="glyphicon glyphicon-search"></span> Остановить поиск</button>
                                        </td>
                                        <td> &nbsp; &nbsp; </td>
                                        <td>
                                            <span class="hide_show_on_search"> <button class="btn btn-primary" style="width: 100%;" type="button" id="search_from_routes_list"><span class="glyphicon glyphicon-search"></span> Поиск (по списку маршрутов)</button> </span>
                                        </td>
                                    </tr>
                                </table>

                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel panel-primary">
                <div class="panel-heading ">
                    <h3 class="panel-title text-center">Список маршрутов</h3>
                </div>
                <div class="panel-body hide_show_on_search" id="routes_list">
                    <div class="row">
                        <div class="col-xs-12 routes_list">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <!-- Single button -->
                                <table style="width: 100%;" >
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-success" style="width: 100%;" id="check_routes">
                                                <span class="glyphicon glyphicon-ok"></span>
                                                Отметить маршруты
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 5px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                            <button type="button" class="btn btn-primary" style="width: 100%;" id="edit_routes">
                                                <span class="glyphicon glyphicon glyphicon-edit"></span>
                                                Редактировать даты
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top: 5px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-danger" style="width: 100%;" id="delete_routes">
                                                <span class="glyphicon glyphicon-ok glyphicon-trash" ></span>
                                                Удалить маршруты
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="container-fluid" id="result_content">

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="name_dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Введите ваше имя</h4>
            </div>
            <div class="modal-body">
                <div class="input-group ">
                    <span class="input-group-addon glyphicon glyphicon-user"></span>
                    <input type="text" class="form-control" id="loged_user_name" placeholder="ваше имя" autocomplete="off">
                </div>
                <br>
                <div class="text-right small">
                    <label class="cursor_hand"><input type="checkbox" id="remember_me" checked> Запомнить меня на этом компютере</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="name_dialog_save">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_one_route" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Редактирование даты у маршрута</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-4 col-sm-6 ">
                        <div class="input-group ">
                            <input type="text" class="form-control text-center" id="one_route_edit_dep_date" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                            <span class="input-group-addon glyphicon glyphicon-calendar text-danger"></span>
                        </div>
                    </div>
                    <div class="col-xs-4 col-sm-6 ">
                        <div class="input-group ">
                            <input type="text" class="form-control text-center" id="one_route_edit_arr_date" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                            <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                        </div>
                    </div>
                </div>
                <br>
                <div id="one_route_edit_text"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="save_edit_one_route">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_mass_route" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Редактирование даты у маршрутов</h4>
            </div>
            <div class="modal-body">

                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#one_way_false" role="tab" data-toggle="tab">ТУДА-ОБРАТНО</a></li>
                    <li><a href="#one_way_true" role="tab" data-toggle="tab">ТУДА</a></li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane active" id="one_way_false">
                        <br>
                        <div class="row">
                            <div class="col-xs-4 col-sm-6 ">
                                <div class="input-group ">
                                    <input type="text" class="form-control text-center" id="mass_route_edit_dep_date_false" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                                    <span class="input-group-addon glyphicon glyphicon-calendar text-danger"></span>
                                </div>
                            </div>
                            <div class="col-xs-4 col-sm-6 ">
                                <div class="input-group ">
                                    <input type="text" class="form-control text-center" id="mass_route_edit_arr_date_false" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                                    <span class="input-group-addon glyphicon glyphicon-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="mass_route_edit_false_text"></div>
                    </div>
                    <div class="tab-pane" id="one_way_true">
                        <br>
                        <div class="row">
                            <div class="col-xs-4 col-sm-6 ">
                                <div class="input-group ">
                                    <input type="text" class="form-control text-center" id="mass_route_edit_dep_date_true" placeholder="ДД.ММ.ГГГГ" autocomplete="off">
                                    <span class="input-group-addon glyphicon glyphicon-calendar text-danger"></span>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div id="mass_route_edit_true_text"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="save_edit_mass_route">Сохранить</button>
            </div>
        </div>
    </div>
</div>


</body>
</html>
