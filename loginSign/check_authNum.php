<?php
require_once("/var/www/html/tetris/header/myHeader.php");
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG("check_auth_num", $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$email = $_POST['email'];
$auth_num = $_POST['auth_num'];
$hLog->info("받아온 값", compact("email", "auth_num"));

// 1. 받아온 이메일과 인증번호를 통해 값 조회
$result_count = 0;
try {
    $sql = "select * from email_auth A  where A.email_auth_email = :email AND A.email_auth_num = :auth_num";
    $statement = $pdo->prepare($sql); // 파라미터는 ":key" 형식으로 적어준다.
    $statement->bindValue(":email", $email); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->bindValue(":auth_num", $auth_num); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->execute();

    $result = $statement->setFetchMode(PDO::FETCH_ASSOC); // 컬럼명을 키로 사용하는 연관 배열을 반환한다. 가져온 데이터는 $row['id']와 같은 식으로 사용한다.
    $result = $statement->fetchAll(); // 모든 값을 가져온다.

    $result_count =  count($result); // 가져온 값의 개수를 구해준다.
    $hLog->info("select문을 통해 가져온 값", compact("sql", "result", "result_count"));
//    print_r($result);

//    $json_result =  json_encode(array($result), JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT); // 가져온 값을 json화 시켜준다.
//    $hLog->info("json화 시킨 결과값", compact("$json_result"));

//    // 가져온 값을 파싱해주는 로직.
//    // 내부 foreach 문을 통해 값을 inner_arr에 저장할때 조건물을 통해 원하지 않는 값을 필터링 할 수 있다.
//    $outer_arr = array();
//    foreach ($result as $inner_result) {
//        $inner_arr = array();
//        foreach ($inner_result as $key=>$value) {
//            $inner_arr[$key] = $value; // 가져오고 싶지 않은 값이 있다면 여기서 조건문을 걸어 필터링 해준다.
//        }
//        $outer_arr[] = $inner_arr;
//    }
//    print_r($outer_arr);
//    $hLog->info("필터링한 select으로 가져온 값 ", compact("outer_arr"));
//    $taskIdx = $pdo->lastInsertId();


} catch (PDOException $e){
    $output = 'DB Error<br>' . $e . '<br>';
//    echo $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
    $hLog->error("DB 오류", "DB 상태 메시지 보고 해결", compact("$output"));

    $isAgree = 0;
    $msg = "DB 오류.";
}


// 2. 값이 있으면 인증 성공, 없으면 인증 실패. 쿼리 시.. 실패하면 그에 해당하는 msg 입력.

// 가져온 값이 있으면
if($result_count == 1){
    $isAgree = 1;
    $msg = "인증이 완료되었습니다.";
} else {
    $isAgree = 0;
    $msg = "인증 번호가 다릅니다.";
}



//json 시킬 데이터 생성
$response_data = array(
    'isAgree' => $isAgree,
    'msg' => $msg
);

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 json 데이터", compact("response_json"));
echo $response_json;

?>