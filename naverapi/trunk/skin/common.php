<?
	/******************************************************************
	***   header
	******************************************************************/
	header("Pragma: no-cache");
	header("Content-Type: text/html; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate, max_age=0");
	header("Expires: 0");
	date_default_timezone_set('Asia/Seoul');

	$sessdir = "/home/php/session";
	ini_set("session.save_path", $sessdir);
	session_save_path($sessdir);
	ini_set("session.cache_expire", 480); // 세션 유효시간 : 분 <- 3시간
	ini_set("session.gc_maxlifetime", 28800); // 세션 가비지 컬렉션(로그인시 세션지속 시간) : 초 <- 1일

	session_start();
	//session_cache_expire();   // <<-- 여 앞에 echo 를 때려서 남은 시간을 확인할 수 있다.

	/******************************************************************
	***   MySql 접속 파일
	******************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/db_conv.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/db_set.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/db_connect.php");

	/******************************************************************
	***   라이브러리 함수 파일
	******************************************************************/
	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/common_func.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/const.php");
?>