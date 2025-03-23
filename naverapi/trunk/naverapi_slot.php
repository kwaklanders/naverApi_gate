<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/api/class_naver_api_slot.php");?>
<?
// 네이버 날짜선택형 테스트

$cmd = $_REQUEST["cmd"];


/* 업체 시작 ============================================================================================================================*/
//업체조회
if( $cmd == "search_business" )
{
	$cmsId = $_REQUEST["cmsId"];
	$businessId = $_REQUEST["businessId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_business($cmsId, $businessId);
}
//업체리스트조회
else if( $cmd == "search_business_list" )
{
	$cmsId = $_REQUEST["cmsId"];
	$account = $_REQUEST["account"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_business_list($cmsId, $account);

//	echo_json_encode($rtn_data);

}
//업체생성
else if( $cmd == "create_business" )
{
	$cmsId = $_REQUEST["cmsId"];
	$agencyKey = $_REQUEST["agencyKey"];
	$json = $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_business($cmsId, $agencyKey, $json);
}
//업체수정
else if( $cmd == "edit_business" )
{
	$cmsId = $_REQUEST["cmsId"];
	$businessId = $_REQUEST["businessId"];
	$json = $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_business($cmsId, $businessId, $json);
}
/* 업체 끝 ============================================================================================================================*/


/* 상품 시작 ============================================================================================================================*/
//상품생성
else if( $cmd == "create_bizItem" )
{
	$businessId = $_REQUEST["businessId"];
	$agencyKey = $_REQUEST["agencyKey"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_bizItem($businessId, $agencyKey, $json);
}

//상품목록조회
else if( $cmd == "search_bizItem_list" )
{
	$businessId = $_REQUEST["businessId"];
	$projections = $_REQUEST["projections"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItem_list($businessId, $projections);
}

//상품조회
else if( $cmd == "search_bizItem" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$projections = $_REQUEST["projections"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItem($businessId, $bizItemId, $projections);
}

//상품수정
else if( $cmd == "edit_bizItem" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_bizItem($businessId, $bizItemId, $json);
}
/* 상품 끝 ============================================================================================================================*/


/* 옵션카테고리 시작 ============================================================================================================================*/
	//옵션카테고리 목록 조회
else if( $cmd == "search_naver_optionCategory_list" )
{
	$businessId = $_REQUEST["businessId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_naver_optionCategory_list($businessId);
}
	//옵션카테고리 생성
else if( $cmd == "create_businessOptionCategory" )
{
	$cmsId = $_REQUEST["cmsId"];
	$agencyKey = $_REQUEST["agencyKey"];
	$businessId = $_REQUEST["businessId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_businessOptionCategory($cmsId, $agencyKey, $businessId, $json);
}

	//옵션카테고리 삭제
else if( $cmd == "delete_optionCategory" )
{
	$businessId = $_REQUEST["businessId"];
	$categoryId = $_REQUEST["categoryId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->delete_optionCategory($businessId, $categoryId);
}
	//옵션카테고리 수정
else if( $cmd == "edit_optionCategory" )
{
	$businessId = $_REQUEST["businessId"];
	$categoryId = $_REQUEST["categoryId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_optionCategory($businessId, $categoryId, $json);
}

	//옵션카테고리 단건조회

/* 옵션카테고리 끝 ============================================================================================================================*/


/* 옵션 시작 ============================================================================================================================*/
	//옵션생성
else if( $cmd == "create_option" )
{
	$cmsId = $_REQUEST["cmsId"];
	$agencyKey = $_REQUEST["agencyKey"];
	$businessId = $_REQUEST["businessId"];
	$categoryId = $_REQUEST["categoryId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_option($cmsId, $agencyKey, $businessId, $categoryId, $json);
}

	//옵션 목록 조회
else if( $cmd == "search_option_list" )
{
	$businessId = $_REQUEST["businessId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_option_list($businessId);
}
	//옵션 조회
else if( $cmd == "search_option" )
{
	$businessId = $_REQUEST["businessId"];
	$optionId = $_REQUEST["optionId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_option($businessId, $optionId);
}

	//옵션 수정
else if( $cmd == "edit_option" )
{
	$businessId = $_REQUEST["businessId"];
	$optionId = $_REQUEST["optionId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_option($businessId, $optionId, $json);
}

	//옵션 삭제
else if( $cmd == "delete_option" )
{
	$businessId = $_REQUEST["businessId"];
	$optionId = $_REQUEST["optionId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->delete_option($businessId, $optionId);
}
/* 옵션 끝 ============================================================================================================================*/




/* 가격/권종 시작 ============================================================================================================================*/
//가격/권종 생성
else if( $cmd == "create_bizItemPrice" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$agencyKey = $_REQUEST["agencyKey"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_bizItemPrice($businessId, $bizItemId, $agencyKey, $json);
}

//가격/권종 여러 건 조회
else if( $cmd == "search_bizItemPrice_list" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItemPrice_list($businessId, $bizItemId);
}

//가격/권종 한 건 조회

//가격/권종 수정
else if( $cmd == "edit_bizItemPrice" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$priceId = $_REQUEST["priceId"];
	$json = $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_bizItemPrice($businessId, $bizItemId, $priceId, $json);
}

//가격/권종 삭제
else if( $cmd == "delete_bizItemPrice" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$priceId = $_REQUEST["priceId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->delete_bizItemPrice($businessId, $bizItemId, $priceId);
}

/* 가격/권종 끝 ============================================================================================================================*/

/* 반복성 스케줄 시작 ============================================================================================================================*/
//반복성 스케줄 생성 POST
else if( $cmd == "create_bizItemSchedule" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$json = $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_bizItemSchedule($businessId, $bizItemId, $json);
}
//반복성 스케줄 여러 건 조회 GET
else if( $cmd == "search_bizItemSchedule_list" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItemSchedule_list($businessId, $bizItemId);
}

//반복성 스케줄 한 건 조회 GET
else if( $cmd == "search_bizItemSchedule" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItemSchedule($businessId, $bizItemId, $scheduleId);
}

//반복성 스케줄 삭제 DELETE
else if( $cmd == "delete_bizItemSchedule" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->delete_bizItemSchedule($businessId, $bizItemId, $scheduleId);
}

//반복성 스케줄 수정 PATCH
else if( $cmd == "edit_bizItemSchedule" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_bizItemSchedule($businessId, $bizItemId, $scheduleId, $json);
}

/* 반복성 스케줄 끝 ============================================================================================================================*/


/* 일회성 스케줄 시작 ============================================================================================================================*/
//일회성 스케줄 생성 POST
else if( $cmd == "create_bizItemSchedule_slot" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$json = $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->create_bizItemSchedule_slot($businessId, $bizItemId, $json);
}
//일회성 스케줄 여러 건 조회 GET
else if( $cmd == "search_bizItemSchedule_list_slot" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItemSchedule_list_slot($businessId, $bizItemId);
}

//일회성 스케줄 한 건 조회 GET
else if( $cmd == "search_bizItemSchedule_slot" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId);
}

//일회성 스케줄 삭제 DELETE
else if( $cmd == "delete_bizItemSchedule_slot" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->delete_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId);
}

//일회성 스케줄 수정 PATCH
else if( $cmd == "edit_bizItemSchedule_slot" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$scheduleId = $_REQUEST["scheduleId"];
	$json_encode = $_REQUEST["json"];

	$json = json_decode($json_encode, true);


	$objNaverApiSlot = new NaverApiSlot();

//	$rtn_data = "businessId : ".$businessId." bizItemId : ".$bizItemId." scheduleId : ".$scheduleId."<br>";
	$rtn_data = $objNaverApiSlot->edit_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId, $json);
}

else if ( $cmd == "edit_bizItemSchedule_slot_new" )
{

	$businessId = $_REQUEST["businessId"];
	$scheduleId = $_REQUEST["scheduleList"];
	$json		= $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

//	$rtn_data = "businessId : ".$businessId." bizItemId : ".$bizItemId." scheduleId : ".$scheduleId."<br>";
	$rtn_data = $objNaverApiSlot->edit_bizItemSchedule_slot_new($businessId, $scheduleId, $json);
}

//신규 스케줄 수정 qpos 버전
else if ( $cmd == "edit_bizItemSchedule_slot_qpos" )
{
	$businessId 	= $_REQUEST["businessId"];
	$bizItemId 		= $_REQUEST["bizItemId"];
	$scheduleId 	= $_REQUEST["scheduleId"];
	$json			= $_REQUEST["json"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_bizItemSchedule_slot_qpos($businessId, $bizItemId, $scheduleId, $json);

	echo_json_encode($rtn_data);
}

/* 일회성 스케줄 끝 ============================================================================================================================*/



/* 환불정책 시작============================================================================================================================*/
//환불정책갱신
else if( $cmd == "edit_refundPolicy" )
{
	$businessId = $_REQUEST["businessId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->edit_refundPolicy($businessId, $json);
}

//환불정책조회
else if( $cmd == "search_businessRefundPolicy_list" )
{
	$businessId = $_REQUEST["businessId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_businessRefundPolicy_list($businessId);
}

/* 환불정책 끝 ============================================================================================================================*/


/* 바코드 시작 ============================================================================================================================*/
//바코드 목록 조회 GET
else if( $cmd == "search_naver_barcode_list" )
{
	$businessId = $_REQUEST["businessId"];
	$bookingId = $_REQUEST["bookingId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_naver_barcode_list($businessId, $bookingId);
}

	//바코드 조회 GET
else if( $cmd == "search_naver_barcode" )
{
	$businessId = $_REQUEST["businessId"];
	$bookingId = $_REQUEST["bookingId"];
	$readableCodeId = $_REQUEST["readableCodeId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_naver_barcode($businessId, $bookingId, $readableCodeId);
}
/* 바코드 끝 ============================================================================================================================*/


/* 예약 시작 ============================================================================================================================*/
	//예약 조회 GET
else if( $cmd == "search_naver_booking" )
{
	$businessId = $_REQUEST["businessId"];
	$bookingId = $_REQUEST["bookingId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_naver_booking($businessId, $bookingId);
}

	//예약 리스트 조회 GET
else if( $cmd == "search_naver_booking_list" )
{
	$businessId = $_REQUEST["businessId"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_naver_booking_list($businessId);
}


/* 예약 끝 ============================================================================================================================*/



/* 예약 트랜잭션 시작 ============================================================================================================================*/
	//예약생성(네이버->대행사) POST

	//예약 상태 업데이트(네이버->대행사) PATCH

	//예약 상태 업데이트(대행사->네이버) PATCH
else if( $cmd == "update_naver_booking" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$bookingId = $_REQUEST["bookingId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->update_naver_booking($businessId, $bizItemId, $bookingId, $json);
}

/* 예약 트랜잭션 끝 ============================================================================================================================*/

/* 부분취소,부분사용 시작 ============================================================================================================================*/
	//네이버페이 주문 상태 업데이트(네이버->대행사) PATCH

	//네이버페이 주문 상태 업데이트 by nPayProductOrderNumber(대행사->네이버) PATCH
else if( $cmd == "update_naver_booking_nPayProductOrderNumber" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$bookingId = $_REQUEST["bookingId"];
	$readableCodeId = $_REQUEST["readableCodeId"];
	$nPayProductOrderNumber = $_REQUEST["nPayProductOrderNumber"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->update_naver_booking_nPayProductOrderNumber($businessId, $bizItemId, $bookingId, $readableCodeId, $nPayProductOrderNumber, $json);
}

	//네이버페이 주문 상태 업데이트 by bookingId(대행사->네이버) PATCH
else if( $cmd == "update_naver_booking_bookingId" )
{
	$businessId = $_REQUEST["businessId"];
	$bizItemId = $_REQUEST["bizItemId"];
	$bookingId = $_REQUEST["bookingId"];
	$json = $_REQUEST["json"];


	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->update_naver_booking_bookingId($businessId, $bizItemId, $bookingId, $json);
}

/* 부분취소,부분사용 끝 ============================================================================================================================*/


/* 기타 시작 ============================================================================================================================*/
//주소검색
else if( $cmd == "search_address" )
{
	$query = $_REQUEST["query"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->search_address($query);
}
//이미지 업로드
else if( $cmd == "image_upload" )
{
	$imageUrl = $_REQUEST["imageUrl"];

	$objNaverApiSlot = new NaverApiSlot();

	$rtn_data = $objNaverApiSlot->image_upload($imageUrl);
}
/* 기타 끝 ============================================================================================================================*/

else
{
	$rtn_data["result_code"] = "9999";
	$rtn_data["result_msg"] = "no cmd..";
}

echo_json_encode($rtn_data);

?>