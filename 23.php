<?
# headers

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: X-Requested-With');


# include

include '../include/init.php';
include '../include/config.php';
include '../include/common.php';
include '../include/db.php';


# execute

$result=array();


// login

$_USER=login();

if(!$_USER){
    exit;
}

// get game data

$data=json_decode($_USER['data'],true);


// get input data

$method=$_POST['method'];
$_DATA=$_POST;


// method

if($method=='begin'){
    if($data['is_begin'] || $data['is_complete']){
        exit;
    }


    // set data

    $data['is_begin']=true;
    $data['is_complete']=false;
    $data['has_fifty']=true;
    $data['time']=time();
    $data['points']=0;


    // get stage id

    $query=mysql_query("SELECT `id` FROM `stages` ORDER BY `id` ASC LIMIT 1");
    $object=mysql_fetch_assoc($query);

    $data['stage_id']=$object['id'];


    // get question

    $data['question']=get_new_question($data['stage_id']);


    // update data

    update_data($_USER['id'],$data);


    // result

    $result=array(
        'is_begin'=>$data['is_begin'],
        'is_complete'=>$data['is_complete'],
        'has_fifty'=>$data['has_fifty'],
        'time'=>max(0,($data['time']+GAME_MILLIONAIRE_TIME)-time()),
        'stages_points'=>get_stages_points($data['stage_id']),

        'question'=>array(
            'text'=>$data['question']['text'],
            'answers'=>$data['question']['answers']
        )
    );
} else if($method=='next'){
    if(!$data['is_begin'] || $data['is_complete']){
        exit;
    }

    if($data['time']+GAME_MILLIONAIRE_TIME<=time()){
        exit;
    }


    $answer=(int)$_DATA['answer'];


    // get success

    $previous_answer=$data['question']['answer'];
    $success=($answer==$previous_answer);

    $next_stage=false;


    // set points

    $data['points']+=$success ? $data['question']['points'] : 0;


    // get question

    $question=get_new_question($data['stage_id'],$data['question']['points']);

    if($question){
        $data['question']=$question;
    } else {
        $next_stage=true;
    }


    // result

    $result=array(
        'success'=>$success,
        'answer'=>$previous_answer
    );


    // check next stage

    if($next_stage){
        // get stage id

        $query=mysql_query("SELECT `id` FROM `stages` WHERE `id`>'".$data['stage_id']."' ORDER BY `id` ASC LIMIT 1");
        $object=mysql_fetch_assoc($query);

        if($object){
            $data['stage_id']=$object['id'];


            // get question

            $data['question']=get_new_question($data['stage_id']);


            // result

            $result['question']=array(
                'text'=>$data['question']['text'],
                'answers'=>$data['question']['answers']
            );
        } else {
            $data['is_complete']=true;


            // update score

            $result['points']=$data['points'];

            mysql_query("UPDATE `users` SET
			`score`='".$data['points']."'
			WHERE `id`='".$_USER['id']."' LIMIT 1");
        }


        // result

        $result=array_merge($result,array(
            'is_begin'=>$data['is_begin'],
            'is_complete'=>$data['is_complete'],
            'is_next_stage'=>true,
            'has_fifty'=>$data['has_fifty'],
            'time'=>max(0,($data['time']+GAME_MILLIONAIRE_TIME)-time()),
            'stages_points'=>get_stages_points($data['stage_id'])
        ));
    } else if($question){
        // result

        $result['question']=array(
            'text'=>$data['question']['text'],
            'answers'=>$data['question']['answers']
        );
    }


    // update data

    update_data($_USER['id'],$data);
} else if($method=='fifty'){
    if(!$data['is_begin'] || $data['is_complete']){
        exit;
    }

    if($data['time']+GAME_MILLIONAIRE_TIME<=time()){
        exit;
    }

    if(!$data['has_fifty']){
        exit;
    }


    // get answers

    $list=range(0,3);
    $list=array_keys($list);

    unset($list[$data['question']['answer']]);
    unset($list[array_rand($list)]);


    $list=array_values($list);


    // set data

    $data['has_fifty']=false;


    // result

    $result=array(
        'list'=>$list
    );


    // update data

    update_data($_USER['id'],$data);
} else if($method=='end'){
    if(!$data['is_begin'] || $data['is_complete']){
        exit;
    }


    $data['is_complete']=true;


    // update score

    mysql_query("UPDATE `users` SET
	`score`='".$data['points']."'
	WHERE `id`='".$_USER['id']."' LIMIT 1");


    // result

    $result=array(
        'is_complete'=>true,
        'points'=>$data['points']
    );


    // update data

    update_data($_USER['id'],$data);
} else {
    exit;
}


// result

echo json_encode($result);
exit;
?>