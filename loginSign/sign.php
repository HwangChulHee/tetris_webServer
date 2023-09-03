<?php
require_once("/var/www/html/tetris/header/myHeader.php");
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG("sign", $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$email = $_POST['email'];
$id = $_POST['id'];
$password = $_POST['password'];

$hLog->info("받아온 값", compact("email", "id", "password"));

// 1. 받아온 email, password, id 값을 insert
try {
    $sql = "INSERT INTO user (user_id, user_email, user_password) VALUES (:id, :email, :password)"; // 파라미터는 ":key" 형식으로 적어준다.
    $statement = $pdo->prepare($sql);
    $statement->bindValue(":id", $id); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->bindValue(":email", $email); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->bindValue(":password", $password); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.

    $result = $statement->execute();

    //insert 성공 시
    if($result) {
        $hLog->info("데이터 삽입 쿼리 성공. 인증번호가 입력되었습니다.", compact("sql", "id","email", "password"));
        $isSign = 1;
        $msg = "회원가입 되었습니다.";
    } else {
        $error_info = $statement->errorInfo();
        $hLog->error("데이터 삽입 쿼리 실패", "DB 오류 보고 해결", compact("error_info"));
        $isSign = 0;
        $msg = "회원가입에 실패했습니다.";
    }
} catch(PDOException $e) {
    $output = 'DB Error' . $e;
    $hLog->error("데이터 삽입 쿼리 실패", "DB 오류 보고 해결", compact("output"));
    $isSign = 0;
    $msg = "회원가입에 실패했습니다.";
}

//json 시킬 데이터 생성
$response_data = array(
    'isSign' => $isSign,
    'msg' => $msg
);

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 json 데이터", compact("response_json"));
echo $response_json;

?>