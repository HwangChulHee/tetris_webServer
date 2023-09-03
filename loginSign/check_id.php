<?php
require_once("/var/www/html/tetris/header/myHeader.php");
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG("check_id", $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$id = $_POST['id'];
$hLog->info("받아온 값", compact("id"));

// 1. user 테이블에서 중복되는 아이디가 있는지 조회
$result_count = 0;
try {
    $sql = "select * from user A  where A.user_id = :id";
    $statement = $pdo->prepare($sql); // 파라미터는 ":key" 형식으로 적어준다
    $statement->bindValue(":id", $id); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->execute();

    $result = $statement->setFetchMode(PDO::FETCH_ASSOC); // 컬럼명을 키로 사용하는 연관 배열을 반환한다. 가져온 데이터는 $row['id']와 같은 식으로 사용한다.
    $result = $statement->fetchAll(); // 모든 값을 가져온다.

    $result_count =  count($result); // 가져온 값의 개수를 구해준다.
    $hLog->info("select문을 통해 가져온 값", compact("sql", "result", "result_count"));


} catch (PDOException $e){
    $output = 'DB Error<br>' . $e . '<br>';
    // echo $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
    $hLog->error("DB 오류", "DB 상태 메시지 보고 해결", compact("$output"));

    $isIdDuplicate = 0;
    $msg = "DB 오류.";
}


// 2. 조회값이 있으면 중복된 거.

// 가져온 값이 없으면
if($result_count == 0){
    $isIdDuplicate = 1;
    $msg = "사용하실 수 있는 아이디입니다.";
} else {
    $isIdDuplicate = 0;
    $msg = "이미 해당 아이디가 존재합니다.";
}


//json 시킬 데이터 생성
$response_data = array(
'isIdDuplicate' => $isIdDuplicate,
'msg' => $msg
);

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 json 데이터", compact("response_json"));
echo $response_json;

?>