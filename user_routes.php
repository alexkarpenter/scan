<?
include_once( './protected/config/config.php' );
include_once( './protected/lib/funcs.php' );
header( 'Content-type: text/html; charset=utf-8;' );
CreateTables();
$cmd = @$_REQUEST['cmd'];

//Добавление направления
switch ( $cmd )
{
    case 'add' :
    {
        if ( !isset( $_POST['user_route'] ) )
        {
            echo  1; // no data
            exit;
        }

        if ( CheckDirection( $_POST['user_route'] ) )
        {
            echo 2; // already exists
            exit;
        }

        if ( !( $id = AddDirection( $_POST['user_route'] ) ) )
        {
            echo 3; //base error
            exit;
        } else
        {
            echo 0; // all ok
            exit;
        }
    } break;

}

//Удаление направлений
if ( $cmd == 'delete' )
{
    if ( !isset( $_POST['routes'] )|| empty( $_POST['routes'] ) )
    {
        echo 0;
        exit;
    }
    if ( DeleteDirs( $_POST['routes'] ) )
        echo 1;
    else
        echo 0;
    exit;
}

//Обновление направлений
if ( $cmd == 'edit' )
{
    if ( !isset( $_POST['data'] )|| empty( $_POST['data'] ) || !sizeof( $_POST['data'] ) )
    {
        echo 0;
        exit;
    }
    if ( SaveDirs( $_POST['data'] ) )
        echo 1;
    else
        echo 0;
    exit;
}



//Список направлений
if ( $cmd == 'list' )
{
    if ( !isset( $user ) )
        $user = $_POST['user'];


    $routes = GetDirections( $user );
    if ( !sizeof( $routes ) )
        echo '<br><br><br><br><br><br><br><center>пуст</center>';
    else
    {

        //echo '<div class="list-group user-routes">' . "\r\n";
        echo '<table class="table table-hover table-condensed">' . "\r\n";

        foreach ( $routes as $id => $r )
        {
            /*<a href="#" class="list-group-item small one-user-route"><input type="checkbox" class="route_checkbox" value="<?=$r['id']?>"/> <?=$r['dep_city']?> [<b><?=$r['dep_iata']?></b>] (<?=preg_replace( '`(\d+)\.(\d+)\.(\d+)`si', '<b>$1</b>.$2', $r['departure_date'] );?> ) <span class="<?=$r['one_way'] == 'true' ? 'glyphicon glyphicon-arrow-right' : ' glyphicon glyphicon-resize-horizontal';?>"></span> <?=$r['arr_city']?> [<b><?=$r['arr_iata']?></b>] <?=$r['one_way'] == 'false' ? preg_replace( '`(\d+)\.(\d+)\.(\d+)`si', '(<b>$1</b>.$2)', $r['arrival_date'] ) : '';?> <button type="button" class="btn btn-xs right delete_button invisible" roue-id="<?=$r['id']?>"><span class="glyphicon glyphicon-trash"></span></button></a>*/
            $route = $r['dep_iata'] . '-' . $r['arr_iata'] . '-' . $r['departure_date'] . '-' . $r['arrival_date'] . '-' . $r['adults'] . '-' . $r['children'] . '-' . $r['infants'] . '-' . (int)( $r['one_way'] == 'true' ) . '-' . (int)( $r['direct'] == 'true' ). '-' . $r['class'] . '-' . $r['user']. '-' . $r['id'];
            $route2 = $r['dep_city'] . ' [<b>' . $r['dep_iata'] . '</b>]~' . $r['arr_city'] . ' [<b>' . $r['arr_iata'] . '</b>]~' . $r['departure_date'] . '~' . $r['arrival_date'] . '~' . (int)( $r['one_way'] == 'true' ) . '~' . (int)( $r['direct'] == 'true' ) . '~' . $id;

            ?>
            <tr class="small one-user-route cursor_hand">
                <td>
                    <input type="checkbox" class="route_checkbox" value="<?=$r['id']?>" route="<?=$route?>" route2="<?=$route2?>"/>
                </td>
                <td>
                    <?=$r['dep_city']?>[<b><?=$r['dep_iata']?></b>] (<?=preg_replace( '`(\d+)\.(\d+)\.(\d+)`si', '<b>$1</b>.$2', $r['departure_date'] );?> )
                    <span class="<?=$r['one_way'] == 'true' ? 'glyphicon glyphicon-arrow-right' : ' glyphicon glyphicon-resize-horizontal';?>"></span>
                    <?=$r['arr_city']?> [<b><?=$r['arr_iata']?></b>] <?=$r['one_way'] == 'false' ? preg_replace( '`(\d+)\.(\d+)\.(\d+)`si', '(<b>$1</b>.$2)', $r['arrival_date'] ) : '';?>
                    <button type="button" class="btn btn-xs right delete_one_route_button invisible btn-danger" roue-id="<?=$r['id']?>" data-toggle="tooltip" data-placement="top" title="Удалить маршрут"><span class="glyphicon glyphicon-trash"></span></button>
                    <button type="button" class="btn btn-xs right edit_one_route_button invisible btn-primary" roue-id="<?=$r['id']?>" data-toggle="tooltip" data-placement="top" title="Редактировать маршрут"><span class="glyphicon glyphicon-pencil"></span></button>
                </td>
            </tr>
            <?
        }
        echo '</table>'. "\r\n";
        //echo '</div>'. "\r\n";

        echo "
        <!-- icheck-->
        <link href='css/icheck/square/_all.css' rel='stylesheet'>
        <script>
        $('.route_checkbox').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_flat-blue'
        });
        $('.delete_button').tooltip({animation:true});

        </script>

        ";

    }

}

?>
