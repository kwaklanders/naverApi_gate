<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/api/class_naver_api_cms_booking.php");?>
<?
/*
네이버->대행사

예약생성 POST       /businesses/{agencyBusinessId}/biz-items/{agencyBizItemId}/bookings
예약상태업데이트 PATCH /businesses/{agencyBusinessId}/biz-items/{agencyBizItemId}/bookings/{bookingId}
주문상태업데이트 PATCH /businesses/{agencyBusinessId}/biz-items/{agencyBizItemId}/bookings/{bookingId}/npay-product-orders/{nPayProductOrderNumber}
*/

//uri param
$param =  $_REQUEST["param"];
//list($agencyBusinessId, , $agencyBizItemId, ,$bookingId, $nPay_product_orders, $nPayProductOrderNumber) = explode("/", $param);
list($agencyBusinessId, $biz_items_name, $agencyBizItemId, $bookings_name,$bookingId, $nPay_product_orders, $nPayProductOrderNumber) = explode("/", $param);

//json param
$json = file_get_contents('php://input');
$json_data = json_decode($json, true);

$method = $_SERVER["REQUEST_METHOD"];


//로그...tail -f /var/log/httpd/naverapires.qpass.kr-error_log
error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("[[".$method."]businesses request]");
error_log("[url==>businesses/".$param."]");
error_log("[json==>".$json."]");
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");


//예약생성
if( $method == "POST" )
{
	create_order($agencyBusinessId, $agencyBizItemId, $json);
}
//상태 업데이트
else if( $method == "PATCH" )
{
	//paid는 네이버결제 완료시 호출 되지만 의미없고 타임아웃이 2초라 문제많아 무조건 성공으로 회신
	if( $json_data["bookingDetails"]["status"] == "paid" )
	{
		$http_status_code = 204;
		http_response_code($http_status_code);
	}
	else
	{
		//예약상태업데이트(일괄)
		if( $nPay_product_orders == "" )
		{
			update_order($agencyBusinessId, $agencyBizItemId, $bookingId, $json);
		}

		//네이버페이주문상태업데이트(부분취소,사용)
		else
		{
			$result_order = update_order_npay($agencyBusinessId, $agencyBizItemId, $bookingId, $nPayProductOrderNumber, $json);

			echo_json_encode($result_order);

			
		}
	}
}



//예약생성
function create_order($agencyBusinessId, $agencyBizItemId, $json)
{
	//업체조회
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " cmsId ";
	$strSql .= " from '' ";
	$strSql .= " where 1=1 ";
	$strSql .= " and agencyKey = '".$agencyBusinessId."' ";	//CMS업체ID
	$strSql .= "  ";
	$strSql .= "  ";
//	echo $strSql;
//	exit;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	//성공 201
	if( $rsCount == 1 )
	{
		//대매사 연동
		$objNaverCmsApiBooking = new NaverCmsApiBooking();

		$cmsId = mysql_result($rsList, 0, 0);
	//부분취소 불가

		$rtn_data = $objNaverCmsApiBooking->create_booking($cmsId, $agencyBusinessId, $agencyBizItemId, $json);
		$rtn_json = json_decode($rtn_data, true);

		//성공
		if( $rtn_json["result_code"] == "0000" )
		{
			$return_data = $rtn_json["json"];
			$http_status_code = 201;

			http_response_code($http_status_code);
			echo $return_data;
		}
		//실패
		else
		{
			//대행사 오류 (ERROR)
			//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
			//예약 가능 수량 없음 (EXCEED)
			$errorBody = json_encode($rtn_json, JSON_UNESCAPED_UNICODE);

			$return_data["errorCode"] = "ERROR";
			$return_data["errorBody"] = $errorBody;
			$http_status_code = 409;

			http_response_code($http_status_code);
			echo_json_encode($return_data);
		}
	}
	//실패 409
	else
	{
		//대행사 오류 (ERROR)
		//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
		//예약 가능 수량 없음 (EXCEED)

		$return_data["errorCode"] = "ERROR";
		$return_data["errorBody"] = array();
		$http_status_code = 409;

		http_response_code($http_status_code);
		echo_json_encode($return_data);
	}


	//로그...tail -f /var/log/httpd/naverapires.qpass.kr-error_log
	error_log("    ");
	error_log("    ");
	error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("[business create_order response]");
	error_log("[http_status_code==>".$http_status_code."]");
	error_log("[json==>".json_encode($return_data, JSON_UNESCAPED_UNICODE)."]");
	error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("    ");
	error_log("    ");

}



//예약상태업데이트
function update_order($agencyBusinessId, $agencyBizItemId, $bookingId, $json)
{
	//업체조회
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " cmsId ";
	$strSql .= " from '' ";
	$strSql .= " where 1=1 ";
	$strSql .= " and agencyKey = '".$agencyBusinessId."' ";	//CMS업체ID
	$strSql .= "  ";
	$strSql .= "  ";
	//echo $strSql;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	//성공 201
	if( $rsCount == 1 )
	{
		//대매사 연동
		$objNaverCmsApiBooking = new NaverCmsApiBooking();

		$cmsId = mysql_result($rsList, 0, 0);

		$rtn_data = $objNaverCmsApiBooking->update_booking($cmsId, $agencyBusinessId, $agencyBizItemId, $bookingId, $json);
		$rtn_json = json_decode($rtn_data, true);

		//성공
		if( $rtn_json["result_code"] == "0000" )
		{
			$return_data = $rtn_json["json"];
			$http_status_code = 204;

			http_response_code($http_status_code);
//			echo $return_data;
		}
		//실패
		else
		{
			//대행사 오류 (ERROR)
			//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
			//예약 가능 수량 없음 (EXCEED)
			$errorBody = json_encode($rtn_json, JSON_UNESCAPED_UNICODE);

			$return_data["errorCode"] = "ERROR";
			$return_data["errorBody"] = $errorBody;
			$http_status_code = 409;

			http_response_code($http_status_code);
			echo_json_encode($return_data);
		}
	}
	//실패 409
	else
	{
		//대행사 오류 (ERROR)
		//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
		//예약 가능 수량 없음 (EXCEED)

		$return_data["errorCode"] = "ERROR";
		$return_data["errorBody"] = $errorBody;
		$http_status_code = 409;

		http_response_code($http_status_code);
		echo_json_encode($return_data);
	}

	//로그...tail -f /var/log/httpd/naverapires.qpass.kr-error_log
	error_log("    ");
	error_log("    ");
	error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("[business update_order response]");
	error_log("[bookingId ==> ".$bookingId."]");
	error_log("[http_status_code==>".$http_status_code."]");
	error_log("[json==>".json_encode($return_data, JSON_UNESCAPED_UNICODE)."]");
	error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("    ");
	error_log("    ");

}


//네이버페이 부분취소, 부분사용 주문상태 업데이트
function update_order_npay($agencyBusinessId, $agencyBizItemId, $bookingId, $nPayProductOrderNumber, $json)
{
	//업체조회
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " cmsId ";
	$strSql .= " from '' ";
	$strSql .= " where 1=1 ";
	$strSql .= " and agencyKey = '".$agencyBusinessId."' ";	//CMS업체ID
	$strSql .= "  ";
	$strSql .= "  ";
	//echo $strSql;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	//성공 201
	if( $rsCount == 1 )
	{
		//대매사 연동
		$objNaverCmsApiBooking = new NaverCmsApiBooking();

		$cmsId = mysql_result($rsList, 0, 0);
	//부분취소 불가

		$rtn_data = $objNaverCmsApiBooking->update_booking_npay($cmsId, $agencyBusinessId, $agencyBizItemId, $bookingId, $nPayProductOrderNumber, $json);
		$rtn_json = json_decode($rtn_data, true);


		//성공
		if( $rtn_json["result_code"] == "0000" )
		{
			$return_data = $rtn_json["json"];
			$http_status_code = 204;

			http_response_code($http_status_code);
//			echo $return_data;
		}
		//실패
		else
		{
			//대행사 오류 (ERROR)
			//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
			//예약 가능 수량 없음 (EXCEED)
			$errorBody = json_encode($rtn_json, JSON_UNESCAPED_UNICODE);

			$return_data["errorCode"] = "ERROR";
			$return_data["errorBody"] = $errorBody;
			$http_status_code = 409;

			http_response_code($http_status_code);
			echo_json_encode($return_data);
		}
	}
	//실패 409
	else
	{
		//대행사 오류 (ERROR)
		//예약 상품 정보 오류 (맞지 않음) (NOT_MATCH)
		//예약 가능 수량 없음 (EXCEED)

		$return_data["errorCode"] = "ERROR";
		$return_data["errorBody"] = array();
		$http_status_code = 409;

		http_response_code($http_status_code);
		echo_json_encode($return_data);
	}

	//로그...tail -f /var/log/httpd/naverapires.qpass.kr-error_log
	error_log("    ");
	error_log("    ");
	error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("[business update_order_npay response]");
	error_log("[bookingId ==> ".$bookingId."]");
	error_log("[http_status_code==>".$http_status_code."]");
	error_log("[json==>".json_encode($return_data, JSON_UNESCAPED_UNICODE)."]");
	error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
	error_log("    ");
	error_log("    ");
}

?>
