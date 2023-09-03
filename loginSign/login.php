<?php
require_once("/var/www/html/tetris/header/myHeader.php");
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG(pathinfo($_SERVER['PHP_SELF'])['filename'], $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$id = $_POST['id'];
$password = $_POST['password'];

$hLog->info("받아온 값", compact( "id", "password"));

// 1. 받아온 password, id 값을 select

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
}

if($result_count != 0){
    $result_count = 0;
    try {
        $sql = "select * from user A  where A.user_id = :id AND A.user_password = :password";
        $statement = $pdo->prepare($sql); // 파라미터는 ":key" 형식으로 적어준다
        $statement->bindValue(":id", $id); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
        $statement->bindValue(":password", $password); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
        $statement->execute();

        $result = $statement->setFetchMode(PDO::FETCH_ASSOC); // 컬럼명을 키로 사용하는 연관 배열을 반환한다. 가져온 데이터는 $row['id']와 같은 식으로 사용한다.
        $result = $statement->fetchAll(); // 모든 값을 가져온다.

        $result_count =  count($result); // 가져온 값의 개수를 구해준다.
        $hLog->info("select문을 통해 가져온 값", compact("sql", "result", "result_count"));

    } catch (PDOException $e){
        $output = 'DB Error<br>' . $e . '<br>';
        // echo $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
        $hLog->error("DB 오류", "DB 상태 메시지 보고 해결", compact("$output"));
    }

    if ($result_count == 1){
        $isLogin = 1;
        $msg = "로그인에 성공하였습니다.";
    } else {
        $isLogin = 0;
        $msg = "비밀번호를 확인해주세요.";
    }

} else {
    $isLogin = 0;
    $msg = "가입하신 아이디가 없습니다.";
}



//json 시킬 데이터 생성
$response_data = array(
    'isLogin' => $isLogin,
    'msg' => $msg
);

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 데이터", compact("response_data"));
echo $response_json;

?>