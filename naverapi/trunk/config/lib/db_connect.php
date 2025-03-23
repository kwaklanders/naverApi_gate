<?
/******************************************************************
***   MySql 접속
******************************************************************/
//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL | E_STRICT);

Global $connect;

$connect = @mysql_connect($db_host, $db_user, $db_passwd, $db_name, $db_port) or die("서버 연결에 실패 하였습니다. 계정 또는 패스워드를 확인하세요!!");
@mysql_select_db($connect, $db_name) or die('서버 연결에 실패 하였습니다. 계정 또는 패스워드를 확인하세요!!');

?>
