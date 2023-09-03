<?php
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG("test", $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$var = $_POST['key'];
$hLog->info("받아온 값", compact("var"));





// json 에 넣을 배열 생성
$response_arr = array();
array_push($response_arr, "apple", "banana");

//json 시킬 데이터 생성
$response_data = array(
'username' => 'player1',
'items' => $response_arr
);

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 json 데이터", compact("response_json"));
echo $response_json;

?>