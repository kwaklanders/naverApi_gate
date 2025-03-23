<?
class NaverApiBooking {
//날짜선택형


/* 업체 시작 ============================================================================================================================*/
	//업체조회
	public function search_business($businessId)
	{
		$post_data["cmd"] = "search_business";
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["businessId"] = $businessId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//업체리스트조회
	public function search_business_list($account)
	{
		$post_data["cmd"] = "search_business_list";
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["account"] = $account;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//업체수정
	public function edit_business($businessId, $json)
	{
		$post_data["cmd"] = "edit_business";
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["businessId"] = $businessId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//업체생성
	public function create_business($agencyKey, $json)
	{
		$post_data["cmd"] = "create_business";
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["agencyKey"] = $agencyKey;
		$post_data["json"] = $json;
//echo C_NAVER_API_URL;
//echo "<br><br>";
//echo http_build_query($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

/* 업체 끝 ============================================================================================================================*/




/* 상품 시작 ============================================================================================================================*/
	//상품생성
	public function create_bizItem($businessId, $agencyKey, $json)
	{
		$post_data["cmd"] = "create_bizItem";
		$post_data["businessId"] = $businessId;
		$post_data["agencyKey"] = $agencyKey;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//상품목록조회
	public function search_bizItem_list($businessId, $projections)
	{
		$post_data["cmd"] = "search_bizItem_list";
		$post_data["businessId"] = $businessId;
		$post_data["projections"] = $projections;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//상품조회
	public function search_bizItem($businessId, $bizItemId, $projections)
	{
		$post_data["cmd"] = "search_bizItem";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["projections"] = $projections;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//상품수정
	public function edit_bizItem($businessId, $bizItemId, $json)
	{
		$post_data["cmd"] = "edit_bizItem";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//휴무 스케줄 생성

	public function create_holiday($businessId, $bizItemId, $json)
	{
		$post_data["cmd"] = "create_holiday";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;
		
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;

	}


/* 상품 끝 ============================================================================================================================*/

/* 옵션카테고리 시작 ============================================================================================================================*/
	//옵션카테고리 목록 조회
	public function search_naver_optionCategory_list($businessId)
	{
		$post_data["cmd"] = "search_naver_optionCategory_list";
		$post_data["businessId"] = $businessId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
	//옵션카테고리 생성
	public function create_businessOptionCategory($businessId, $agencyKey, $json)
	{
		$post_data["cmd"] = "create_businessOptionCategory";
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["agencyKey"] = $agencyKey;
		$post_data["businessId"] = $businessId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//옵션카테고리 삭제
	public function delete_optionCategory($businessId, $categoryId)
	{
		$post_data["cmd"] = "delete_optionCategory";
		$post_data["businessId"] = $businessId;
		$post_data["categoryId"] = $categoryId;
//echo_json_encode($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//옵션카테고리 수정
	public function edit_optionCategory($businessId, $categoryId, $json)
	{
		$post_data["cmd"] = "edit_optionCategory";
		$post_data["businessId"] = $businessId;
		$post_data["categoryId"] = $categoryId;
		$post_data["json"] = $json;
//echo_json_encode($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
	//옵션카테고리 단건조회

/* 옵션카테고리 끝 ============================================================================================================================*/




/* 옵션 시작 ============================================================================================================================*/
	//옵션 목록 조회
	public function search_option_list($businessId)
	{
		$post_data["cmd"] = "search_option_list";
		$post_data["businessId"] = $businessId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
	//옵션 조회
	public function search_option($businessId, $optionId)
	{
		$post_data["cmd"] = "search_option";
		$post_data["businessId"] = $businessId;
		$post_data["optionId"] = $optionId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
	//옵션 생성
	public function create_option($businessId, $categoryId, $json)
	{
		$post_data["cmd"] = "create_option";
		$post_data["businessId"] = $businessId;
		$post_data["cmsId"] = C_NAVER_API_CMS_ID;
		$post_data["agencyKey"] = $agencyKey;
		$post_data["json"] = $json;
//echo C_NAVER_API_URL;
//echo "<br>";
//echo http_build_query($post_data);
//echo "<br>";
//echo $rtn_data;
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//옵션 삭제
	public function delete_option($businessId, $optionId)
	{
		$post_data["cmd"] = "delete_option";
		$post_data["businessId"] = $businessId;
		$post_data["optionId"] = $optionId;
//echo_json_encode($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//옵션 수정
	public function edit_option($businessId, $optionId, $json)
	{
		$post_data["cmd"] = "edit_option";
		$post_data["businessId"] = $businessId;
		$post_data["optionId"] = $optionId;
		$post_data["json"] = $json;
//echo_json_encode($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

/* 옵션 끝 ============================================================================================================================*/







/* 가격 시작 ============================================================================================================================*/
	//가격/권종 생성
	public function create_bizItemPrice($businessId, $bizItemId, $agencyKey, $json)
	{
		$post_data["cmd"] = "create_bizItemPrice";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["agencyKey"] = $agencyKey;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//가격/권종 여러 건 조회
	public function search_bizItemPrice_list($businessId, $bizItemId)
	{
		$post_data["cmd"] = "search_bizItemPrice_list";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//가격/권종 한 건 조회

	//가격/권종 수정
	public function edit_bizItemPrice($businessId, $bizItemId, $priceId, $json)
	{
		$post_data["cmd"] = "edit_bizItemPrice";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["priceId"] = $priceId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//가격/권종 삭제
	public function delete_bizItemPrice($businessId, $bizItemId, $priceId)
	{
		$post_data["cmd"] = "delete_bizItemPrice";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["priceId"] = $priceId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}


/* 가격 끝 ============================================================================================================================*/

/* 스케줄 시작 ============================================================================================================================*/
	//스케줄 생성
	public function create_bizItemSchedule($businessId, $bizItemId, $json)
	{
		$post_data["cmd"] = "create_bizItemSchedule";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//스케줄 여러 건 조회
	public function search_bizItemSchedule_list($businessId, $bizItemId)
	{
		$post_data["cmd"] = "search_bizItemSchedule_list";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//스케줄 한 건 조회
	public function search_bizItemSchedule($businessId, $bizItemId, $scheduleId)
	{
		$post_data["cmd"] = "search_bizItemSchedule";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//스케줄 삭제
	public function delete_bizItemSchedule($businessId, $bizItemId, $scheduleId)
	{
		$post_data["cmd"] = "delete_bizItemSchedule";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//스케줄 수정
	public function edit_bizItemSchedule($businessId, $bizItemId, $scheduleId, $json)
	{
		$post_data["cmd"] = "edit_bizItemSchedule";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
/* 스케줄 끝 ============================================================================================================================*/








/* 일회성 스케줄 시작 ============================================================================================================================*/
	//일회성 스케줄 생성
	public function create_bizItemSchedule_slot($businessId, $bizItemId, $json)
	{
		$post_data["cmd"] = "create_bizItemSchedule_slot";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;
//echo "businessId : " . $businessId . "<br>";
//echo "bizItemId : " . $bizItemId . "<br>";
//echo_json_encode($post_data);
//exit;
		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//일회성 스케줄 여러 건 조회
	public function search_bizItemSchedule_list_slot($businessId, $bizItemId)
	{
		$post_data["cmd"] = "search_bizItemSchedule_list_slot";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//일회성 스케줄 한 건 조회
	public function search_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId)
	{
		$post_data["cmd"] = "search_bizItemSchedule_slot";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//일회성 스케줄 삭제
	public function delete_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId)
	{
		$post_data["cmd"] = "delete_bizItemSchedule_slot";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//일회성 스케줄 수정
	public function edit_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId, $json)
	{
		$post_data["cmd"] = "edit_bizItemSchedule_slot";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["scheduleId"] = $scheduleId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
/* 스케줄 끝 ============================================================================================================================*/






/* 환불정책 시작============================================================================================================================*/
	//환불정책갱신
	public function edit_refundPolicy($businessId, $json)
	{
		$post_data["cmd"] = "edit_refundPolicy";
		$post_data["businessId"] = $businessId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//환불정책조회
	public function search_businessRefundPolicy_list($businessId)
	{
		$post_data["cmd"] = "search_businessRefundPolicy_list";
		$post_data["businessId"] = $businessId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
/* 환불정책 끝 ============================================================================================================================*/



/* 바코드 시작 ============================================================================================================================*/
	//바코드 목록 조회 GET
	public function search_naver_barcode_list($businessId, $bookingId)
	{
		$post_data["cmd"] = "search_naver_barcode_list";
		$post_data["businessId"] = $businessId;
		$post_data["bookingId"] = $bookingId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//바코드 조회 GET
	public function search_naver_barcode($businessId, $bookingId, $readableCodeId)
	{
		$post_data["cmd"] = "search_naver_barcode";
		$post_data["businessId"] = $businessId;
		$post_data["bookingId"] = $bookingId;
		$post_data["readableCodeId"] = $readableCodeId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
/* 바코드 끝 ============================================================================================================================*/


/* 예약 시작 ============================================================================================================================*/
	//예약 조회 GET
	public function search_naver_booking($businessId, $bookingId)
	{
		$post_data["cmd"] = "search_naver_booking";
		$post_data["businessId"] = $businessId;
		$post_data["bookingId"] = $bookingId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
	//예약 리스트 조회 GET
	public function search_naver_booking_list($businessId)
	{
		$post_data["cmd"] = "search_naver_booking_list";
		$post_data["businessId"] = $businessId;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

/* 예약 끝 ============================================================================================================================*/



/* 예약 트랜잭션 시작 ============================================================================================================================*/
	//예약생성(네이버->대행사) POST

	//예약 상태 업데이트(네이버->대행사) PATCH

	//예약 상태 업데이트(대행사->네이버) PATCH
	public function update_naver_booking($businessId, $bizItemId, $bookingId, $json)
	{
		$post_data["cmd"] = "update_naver_booking";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["bookingId"] = $bookingId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

/* 예약 트랜잭션 끝 ============================================================================================================================*/

/* 부분취소,부분사용 시작 ============================================================================================================================*/
	//네이버페이 주문 상태 업데이트(네이버->대행사) PATCH

	//네이버페이 주문 상태 업데이트 by nPayProductOrderNumber(대행사->네이버) PATCH
	public function update_naver_booking_nPayProductOrderNumber($businessId, $bizItemId, $bookingId, $readableCodeId, $nPayProductOrderNumber, $json)
	{
		$post_data["cmd"] = "update_naver_booking_nPayProductOrderNumber";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["bookingId"] = $bookingId;
		$post_data["readableCodeId"] = $readableCodeId;
		$post_data["nPayProductOrderNumber"] = $nPayProductOrderNumber;
		$post_data["json"] = $json;

//		echo_json_encode($post_data);

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

	//네이버페이 주문 상태 업데이트 by bookingId(대행사->네이버) PATCH
	public function update_naver_booking_bookingId($businessId, $bizItemId, $bookingId, $json)
	{
		$post_data["cmd"] = "update_naver_booking_bookingId";
		$post_data["businessId"] = $businessId;
		$post_data["bizItemId"] = $bizItemId;
		$post_data["bookingId"] = $bookingId;
		$post_data["json"] = $json;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}

/* 부분취소,부분사용 끝 ============================================================================================================================*/



/* 기타 시작 ============================================================================================================================*/
	//주소검색
	public function search_address($query)
	{
		$post_data["cmd"] = "search_address_new";
		$post_data["query"] = $query;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);

		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}


	//이미지업로드
	public function image_upload($imageUrl)
	{
		$post_data["cmd"] = "image_upload";
		$post_data["imageUrl"] = C_IMAGE_NAVER_PATH.$imageUrl;

		$rtn_data = curl_post(C_NAVER_API_URL, $post_data);
//echo C_NAVER_API_URL;
//echo "<br>";
//echo http_build_query($post_data);
//echo "<br>";
//echo $rtn_data;
//exit;
		$rtn_data = json_decode($rtn_data, true);

		return $rtn_data;
	}
/* 기타 끝 ============================================================================================================================*/
}
?>