<?php

// db접속 및 로그 관련 코드
require_once("/var/www/html/tetris/header/myHeader.php");
require_once("/var/www/html/tetris/log/LOG_HCH.php");
$hLog = new HCH_LOG("emailAuth", $_SERVER['PHP_SELF'], "로그 파일 설명", $_SERVER['REMOTE_ADDR']);

$email = $_POST['email'];
//$email = "kum1579@naver.com";
$hLog->info("받아온 값", compact("email"));

$select_count = 0; // 중복검사를 위한 변수


// 우선 이메일 중복 검사를 시행한다.
try {
    $statement = $pdo->prepare("select * from user A  where A.user_email = :email"); // 파라미터는 ":key" 형식으로 적어준다.
    $statement->bindValue(":email", $email); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
    $statement->execute();

    $result = $statement->setFetchMode(PDO::FETCH_ASSOC); // 컬럼명을 키로 사용하는 연관 배열을 반환한다. 가져온 데이터는 $row['id']와 같은 식으로 사용한다.
    $result = $statement->fetchAll(); // 모든 값을 가져온다.

    $select_count = count($result); // 가져온 값의 개수를 구해준다.

    $hLog->info("select문을 통해 가져온 값", compact("result", "select_count"));

} catch (PDOException $e){
    echo $output = 'DB Error<br>' . $e . '<br>';
    echo $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
    $hLog->error("DB 오류", "DB 상태 메시지 보고 해결", compact("$output"));
}


// 중복되지 않았다면 1. 인증번호 생성 후, 2. 메일을 발송해준다.
if($select_count == 0){

    // 1. 인증 번호 생성
    $randomNum = rand(1111,9999);

    // 1-1. 기존에 있던 인증번호 삭제
    delete_auth_num($pdo, $email, $hLog);

    // 1-2. 인증번호 insert
    insert_auth_num($pdo, $email, $randomNum, $hLog);

    // 2. 이메일 발송
    if(send_mail($email, $randomNum) == 1){
        $phrases = "입력하신 이메일로 인증번호가 전송되었습니다.";
        $isSend = 1;
    } else {
        $phrases = "이메일 전송 오류";
        $isSend = 0;
    };


} else {
    $phrases = "이미 사용하고 있는 이메일이 있습니다.";
    $isSend = 0;
}




//json 시킬 데이터 생성
$response_data = array(
'isSend' => $isSend,
'phrases' => $phrases
);

$hLog->info("응답할 데이터", compact("response_data"));

// JSON 데이터 생성
$response_json = json_encode($response_data);

// JSON 데이터 출력
header('Content-Type: application/json');

$hLog->info("응답할 json 데이터", compact("response_json"));
echo $response_json;
?>




<?php

// 이메일 인증 관련 코드
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_mail($email, $auth_num) {

    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.naver.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'kum15479@naver.com';                     //SMTP username
        $mail->Password   = 'webRTC4597@n';                               //SMTP password
        $mail->CharSet = 'UTF-8';
        $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('kum15479@naver.com', '테트리스');
        $mail->addAddress($email);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = '테트리스 : 회원가입 인증번호 입니다.';
        $mail->Body    = '안녕하세요. 테트리스 게임입니다. <br>
                      아래의 인증번호로 이메일 인증을 해주세요. <br><br>
                      인증번호 : '.$auth_num;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();

        return 1;

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return 0;
    }
}

// 겹치는 이메일의 인증번호가 있으면 해당 인증번호를 삭제해주는 쿼리.
function delete_auth_num($pdo, $email, $hLog) {
    try {
        $sql = "DELETE FROM email_auth A WHERE A.email_auth_email = :email"; // 파라미터는 ":key" 형식으로 적어준다.
        $statement = $pdo->prepare($sql);
        $statement->bindValue(":email", $email); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
        $statement->execute();

        $hLog->info("데이터 삭제 쿼리 성공. 인증번호가 삭제되었습니다.", compact("sql", "email"));
    } catch(PDOException $e) {
        $output = 'DB Error' . $e;
        $hLog->error("데이터 삭제 쿼리 실패", "DB 오류 보고 해결", compact("output"));
    }
}

function insert_auth_num($pdo, $email, $auth_num, $hLog) {
    try {
        $sql = "INSERT INTO email_auth (email_auth_email, email_auth_num) VALUES (:email, :auth_num)"; // 파라미터는 ":key" 형식으로 적어준다.
        $statement = $pdo->prepare($sql);
        $statement->bindValue(":email", $email); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
        $statement->bindValue(":auth_num", $auth_num); // 파라미터에 변수를 대입해준다. key는 ":key" 의 형식이고 , value에는 적용하고자 하는 변수값을 넣는다.
        $statement->execute();

        $hLog->info("데이터 삽입 쿼리 성공. 인증번호가 입력되었습니다.", compact("sql", "email", "auth_num"));
    } catch(PDOException $e) {
        $output = 'DB Error' . $e;
        $hLog->error("데이터 삽입 쿼리 실패", "DB 오류 보고 해결", compact("output"));
    }

}

?>

