<?
//dump($cfg,$_SERVER['DOCUMENT_ROOT']);
$items_count = 0;
$page = isset( $_REQUEST['page'] ) ? (int) $_REQUEST['page'] : 1;
$per_page = 5;

$search_history = GetUserSearchHistoryList( $items_count, $_COOKIE['loged_user_name'], $page, $per_page );
$pager = Pager( $cfg['WEB_PATH'] . 'history.php?', $items_count, $per_page, $page, 5 );

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
                <li ><a href="<?=$cfg['WEB_PATH'];?>">Поиск</a></li>
                <li class="active"><a href="<?=$cfg['WEB_PATH'];?>history.php">История поисков</a></li>
            </ul>
            <p class="navbar-text navbar-right user_loged"></p>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" data-toggle="modal" data-target="#name_dialog">Войти под другим именим</a></li>
            </ul>

        </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
</div>
<div class="clearfix"></div>
<div class="container">
    <h2>История поисков</h2>
    <? if ( !sizeof( $search_history ) ) { ?>
        <div class="jumbotron">
            <h1>Упс</h1>
            <p>Вы не разу не делали поиск, вам следует обязательно попробывать поиск :)</p>
            <p><a class="btn btn-primary btn-lg" role="button" href="<?=$cfg['WEB_PATH'];?>">Перейти на поиск</a></p>
        </div>
    <? } else { ?>
    <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">История поисков</div>
        <div class="panel-body">
            <div class="table-responsive">
                <?=$pager?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Маршурт</th>
                            <th class="text-center">Дата создания</th>
                            <th class="text-center">Операции</th>
                        </tr>
                    </thead>
                    <tbody>
                    <? foreach ( $search_history as $k => $v ) {?>
                    <tr>
                        <td><?=$v['id']?></td>
                        <td><?=ShowRoutes( $v['routes_text'] );?></td>
                        <td class="text-center"><?=date('<b>H:i:s</b> d.m.Y', $v['start_timestamp'] )?></td>
                        <td class="text-center"><a class="btn btn-primary" role="button" href="<?=$cfg['WEB_PATH'];?>export.php?sid=<?=$v['id']?>"><span class="glyphicon glyphicon-export"></span> Просмотр & Экспорт</a></td>
                    </tr>
                    <? } ?>
                    </tbody>
                </table>
                <?=$pager?>
            </div>
        </div>
        </div>

    <? } ?>
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
