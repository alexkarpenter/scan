<?
//SaveDataTobase( $_REQUEST['sid'] );
$search_data = GetUserSearchHistory( $_REQUEST['sid'] );


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
        var result_data = <?=$search_data['results']?>;
        var result_routes = '<?=$search_data['routes_text']?>'.split(',');
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
        <div class="col-sm-2">
            <div class="panel panel-primary">
                <div class="panel-heading text-center">
                    <h3 class="panel-title">Опции</h3>
                </div>
                <div class="panel-body ">
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
                            <label><input type="checkbox" name="changing" class="f_check" value="1" />&nbsp;Пересадки</label><br />
                            <!-- <label><input type="checkbox" name="request_time" class="f_check" value="1" />&nbsp;Врм.&nbsp;отрб.&nbsp;запр.</label><br /> -->
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



</body>
</html>
