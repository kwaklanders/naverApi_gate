<?
// 네이버 날짜선택형 클래스
class NaverApiSlot
{


/* 업체 시작 ============================================================================================================================*/
	//업체조회 GET
	public function search_business($cmsId, $businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();
//		$post_data["agen"] = $imageUrl;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//업제리스트 조회 GET
	public function search_business_list($cmsId, $account)
	{
		$url = NAVER_API_URL_REAL."/v3.0/businesses";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

//		echo_json_encode($headers);


		$post_data = array();
		$post_data["account"] = $account;
//		$post_data["page"] = 0;
		$post_data["size"] = 1000;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$business_list = $this->search_cms_chk($cmsId, $json_data);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $business_list;

		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}



	//업체생성 POST
	public function create_business($cmsId, $agencyKey, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;
//echo $url;
//echo "<br><br>";
//echo $json;
//exit;

		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " insert into business_naver ";
			$strSql .= " (cmsId, agencyKey, businessId) ";
			$strSql .= " values ";
			$strSql .= " ( ";
			$strSql .= " '".$cmsId."' ";
			$strSql .= " , '".$agencyKey."' ";
			$strSql .= " , '".$json_data["businessId"]."' ";
			$strSql .= " ) ";
			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//업체수정 PATCH
	public function edit_business($cmsId, $businessId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{

			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update business_naver ";
			$strSql .= " set ";
			$strSql .= " cmsId = '".$cmsId."' ";
			$strSql .= " where businessId = '".$businessId."' ";
			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//CMS 정보조회
	public function search_cms_chk($cmsId, $json_data)
	{
		//CMS별 업체조회
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " select ";
		$strSql .= " businessId ";
		$strSql .= " from business_naver ";
		$strSql .= " where cmsId = '".$cmsId."' ";

		$rsList = mysql_query($strSql);
		$rsCount = mysql_num_rows($rsList);

		$businessList = array();
		if( $rsCount > 0 )
		{
			while($rows=mysql_fetch_assoc($rsList))
			{
				$businessList[] = $rows["businessId"];
			}
		}

		$business_list = array();

		if( count($json_data) > 0 )
		{
			//네이버업체조회리스트
			foreach($json_data as $k_naver=>$v_naver)
			{
				//CMS업체리스트
				foreach($businessList as $k_cms=>$v_cms)
				{
					if( $v_naver["businessId"] == $v_cms )
					{
						$business_list[] = $v_naver;
						break;
					}
				}
			}
		}

		return $business_list;
	}







/* 업체 끝 ============================================================================================================================*/


/* 상품 시작 ============================================================================================================================*/
	//상품생성 POST
	public function create_bizItem($businessId, $agencyKey, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " insert into bizItem_naver ";
			$strSql .= " (cmsId, businessId, bizItemId, agencyKey) ";
			$strSql .= " values ";
			$strSql .= " ( ";
			$strSql .= " '".$cmsId."' ";
			$strSql .= " , '".$businessId."' ";
			$strSql .= " , '".$json_data["bizItemId"]."' ";
			$strSql .= " , '".$agencyKey."' ";
			$strSql .= " ) ";
			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//상품 수정 PATCH
	public function edit_bizItem($businessId, $bizItemId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//상품목록조회 GET
	public function search_bizItem_list($businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["projections"] = $projections;
//		$post_data["size"] = 20;

//echo $url;
//echo "<br><br>";
//echo http_build_query($post_data);
//exit;

		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//상품조회 GET
	public function search_bizItem($businessId, $bizItemId, $projections)
	{

		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."?projections=".$projections;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
		$post_data["projections"] = $projections;
//		$post_data["size"] = 20;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
/* 상품 끝 ============================================================================================================================*/



/* 옵션카테고리 시작 ============================================================================================================================*/
	//옵션카테고리 목록 조회
	public function search_naver_optionCategory_list($businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/option-categories";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["projections"] = $projections;
//		$post_data["size"] = 20;

//echo $url;
//echo "<br><br>";
//echo http_build_query($post_data);
//exit;

		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//옵션카테고리 생성
	public function create_businessOptionCategory($cmsId, $agencyKey, $businessId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/option-categories";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = $json;

		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " insert into category_naver ";
			$strSql .= " (cmsId, agencyKey, businessId, categoryId) ";
			$strSql .= " values ";
			$strSql .= " ( ";
			$strSql .= " '".$cmsId."' ";
			$strSql .= " , '".$agencyKey."' ";
			$strSql .= " , '".$businessId."' ";
			$strSql .= " , '".$json_data[0]["categoryId"]."' ";
			$strSql .= " ) ";

			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//옵션카테고리 삭제 DELETE
	public function delete_optionCategory($businessId, $categoryId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/option-categories/".$categoryId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_delete($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//옵션카테고리 수정 PATCH
	public function edit_optionCategory($businessId, $categoryId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/option-categories/".$categoryId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
	//옵션카테고리 단건조회

/* 옵션카테고리 끝 ============================================================================================================================*/





/* 옵션 시작 ============================================================================================================================*/
	//옵션생성
	public function create_option($cmsId, $agencyKey, $businessId, $categoryId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/options";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = $json;

		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " insert into option_naver ";
			$strSql .= " (cmsId, agencyKey, businessId, categoryId, optionId) ";
			$strSql .= " values ";
			$strSql .= " ( ";
			$strSql .= " '".$cmsId."' ";
			$strSql .= " , '".$agencyKey."' ";
			$strSql .= " , '".$businessId."' ";
			$strSql .= " , '".$categoryId."' ";
			$strSql .= " , '".$json_data[0]["optionId"]."' ";
			$strSql .= " ) ";

			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//옵션 목록 조회
	public function search_option_list($businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/options";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["projections"] = $projections;
//		$post_data["size"] = 20;

//echo $url;
//echo "<br><br>";
//echo http_build_query($post_data);
//exit;

		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//옵션 조회
	public function search_option($businessId, $optionId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/options/".$optionId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["projections"] = $projections;
//		$post_data["size"] = 20;

//echo $url;
//echo "<br><br>";
//echo http_build_query($post_data);
//exit;

		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//옵션 수정
	public function edit_option($businessId, $optionId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/options/".$optionId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
	//옵션 삭제
	public function delete_option($businessId, $optionId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/options/".$optionId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_delete($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
/* 옵션 끝 ============================================================================================================================*/











/* 가격/권종 시작 ============================================================================================================================*/
	//가격/권종 생성 POST
	public function create_bizItemPrice($businessId, $bizItemId, $agencyKey, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/prices";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = $json;


		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " insert into price_naver ";
			$strSql .= " (cmsId, agencyKey, businessId, bizItemId, priceId) ";
			$strSql .= " values ";
			$strSql .= " ( ";
			$strSql .= " '".$cmsId."' ";
			$strSql .= " , '".$agencyKey."' ";
			$strSql .= " , '".$businessId."' ";
			$strSql .= " , '".$bizItemId."' ";
			$strSql .= " , '".$json_data[0]["priceId"]."' ";
			$strSql .= " ) ";

			mysql_query($strSql);

			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//가격/권종 여러 건 조회 GET
	public function search_bizItemPrice_list($businessId, $bizItemId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/prices";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
		$post_data["size"] = 50;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//가격/권종 한 건 조회 GET

	//가격/권종 수정 PATCH
	public function edit_bizItemPrice($businessId, $bizItemId, $priceId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/prices/".$priceId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//가격/권종 삭제 DELETE
	public function delete_bizItemPrice($businessId, $bizItemId, $priceId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/prices/".$priceId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_delete($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 가격/권종 끝 ============================================================================================================================*/


/* 반복성 스케줄 시작 ============================================================================================================================*/
	//반복성 스케줄 생성 POST
	public function create_bizItemSchedule($businessId, $bizItemId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;


		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//반복성 스케줄 여러 건 조회 GET
	public function search_bizItemSchedule_list($businessId, $bizItemId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["size"] = 20;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//반복성 스케줄 한 건 조회 GET
	public function search_bizItemSchedule($businessId, $bizItemId, $scheduleId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["size"] = 20;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
	//반복성 스케줄 삭제 DELETE
	public function delete_bizItemSchedule($businessId, $bizItemId, $scheduleId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_delete($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//반복성 스케줄 수정 PATCH
	public function edit_bizItemSchedule($businessId, $bizItemId, $scheduleId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 반복성 스케줄 끝 ============================================================================================================================*/




/* 일회성 스케줄 시작 ============================================================================================================================*/
	//일회성 스케줄 생성 POST
	public function create_bizItemSchedule_slot($businessId, $bizItemId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;


		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//일회성 스케줄 여러 건 조회 GET
	public function search_bizItemSchedule_list_slot($businessId, $bizItemId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["size"] = 20;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//일회성 스케줄 한 건 조회 GET
	public function search_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);


		$post_data = array();
//		$post_data["size"] = 20;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
	//일회성 스케줄 삭제 DELETE
	public function delete_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_delete($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}


	//일회성 스케줄 수정 PATCH
	public function edit_bizItemSchedule_slot($businessId, $bizItemId, $scheduleId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);
		
		$post_data_decode["scheduleId"] = $scheduleId;
		$post_data_decode["remainStock"] = (int) $json;
//		$post_data_decode["stock"] = (int) $json;

		$post_data = json_encode($post_data_decode, JSON_UNESCAPED_UNICODE);


		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $post_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

//		$return_data["resule_msg"] = $url."<br>".$post_data;
		return $return_data;
	}

	//일회성 스케줄 수정 PATCH
	public function edit_bizItemSchedule_slot_new($businessId, $scheduleId, $json)
	{

		$post_array = array();

		foreach ( $scheduleId as $k_s=>$v_s )
		{
			$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$v_s["bizItemId"]."/schedules/".$v_s["schedule"];

			foreach ( $json as &$data )
			{
				unset($post_array);

				if ( $v_s["bizItemId"] == $data["bizItemId"] )
				{
					foreach ( $data["schedule"] as $k=>$v )
					{
						$ahah["scheduleId"] = $v["scheduleId"];
						$ahah["remainStock"] = (int)$v["remainStock"];

						$post_array[] = $ahah;
					}

					$post_data_decode = $post_array;
				}
			}


			$headers = array(
				"X-Booking-Naver-Role: AGENCY",
				"Authorization:".NAVER_API_TOKEN,
				"Content-Type: application/json"
			);

			$post_data = json_encode($post_data_decode, JSON_UNESCAPED_UNICODE);


			$rtn_data = $this->curl_patch($url, $post_data, $headers);

			$httpcode = $rtn_data["httpcode"];

			echo $httpcode."<br>";
			$json_data_string = $rtn_data["body"];
			$json_data = json_decode($json_data_string, true);

			if( $httpcode == "204" )
			{
				$return_data["result_code"] = "0000";
				$return_data["result_msg"] = "ok[".$httpcode."]";
				$return_data["list"] = $post_data;
			}
			//통신실패
			else
			{
				$return_data["result_code"] = "9999";
				$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
			}

		}

	
			return $return_data;
	}

	public function edit_bizItemSchedule_slot_qpos($businessId, $bizItemId, $scheduleId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/schedules/".$scheduleId;

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		foreach ( $json as $k=>$v )
		{
			$post_array["scheduleId"] = $v["scheduleId"];
			$post_array["remainStock"] = (int)$v["remainStock"];

			$post_json[] = $post_array;
		}



		$post_data = json_encode($post_json, JSON_UNESCAPED_UNICODE);

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];

		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		if( $httpcode == "204" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $post_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;

	}


/* 일회성 스케줄 끝 ============================================================================================================================*/






/* 환불 정책 시작 ============================================================================================================================*/
	//환불 정책 갱신 POST
	public function edit_refundPolicy($businessId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/refund-policies";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;


		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//환불 정책 조회 GET
	public function search_businessRefundPolicy_list($businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/refund-policies";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 환불 정책 끝 ============================================================================================================================*/


/* 바코드 시작 ============================================================================================================================*/
	//바코드 목록 조회 GET
	public function search_naver_barcode_list($businessId, $bookingId)
	{
		$url = NAVER_API_URL_REAL."/v3.0/businesses/".$businessId."/bookings/".$bookingId."/readable-codes";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		//테스트 응답json
//		$httpcode = "200";
//		$json_data_string = '[{"readableCodeId":"495110644985","bookingId":"t202003231251000001","typeCode":"qrcode","codeInformationJson":[],"statusCode":"completed","regDateTime":"2018-07-27T17:02:05+09:00","completedDateTime":"2018-07-30T15:29:30+09:00","title":"대인"}]';

		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
	//바코드 조회 GET
	public function search_naver_barcode($businessId, $bookingId, $readableCodeId)
	{
		$url = NAVER_API_URL_REAL."/v3.0/businesses/".$businessId."/bookings/".$bookingId."/readable-codes/".$readableCodeId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		//테스트 응답json
//		$httpcode = "200";
//		$json_data_string = '{"readableCodeId":"495110644985","bookingId":"t202003231251000001","typeCode":"qrcode","codeInformationJson":[],"statusCode":"completed","regDateTime":"2018-07-27T17:02:05+09:00","completedDateTime":"2018-07-30T15:29:30+09:00","title":"대인"}';

		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}
/* 바코드 끝 ============================================================================================================================*/


/* 예약 시작 ============================================================================================================================*/
	//예약 조회 GET
	public function search_naver_booking($businessId, $bookingId)
	{
		$url = NAVER_API_URL_REAL."/v3.0/businesses/".$businessId."/bookings/".$bookingId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		//테스트 응답json
//		$httpcode = "200";
//		$json_data_string = '{"addressJson":{},"adminBookingStatusCode":"AB00","agency":{"account":"nvqa_sa036","address":"제이플라츠14층 대행사주소입니다","agencyCode":"AT02","agencyId":5424,"apiVersion":"3.1","attachedFileJson":[],"bizNumber":"12345678","cbizNumber":"네이버-123456","desc":[],"email":"test@naver.com","isDeleted":false,"isPartialCancelUsed":true,"isPayCompleteUsed":true,"isPreRequest":false,"nPayInformationJson":[{"ownerName":"검색광고036","shopId":"npay036","ownerId":377,"mctNo":"300033304","certiKey":"274424B1-786C-4035-969E-18E1B6828DD4"}],"name":"날짜 지정형","notifyPhoneList":"010","ownerId":5879,"phone":"010-0000-0000","reprName":"김영진","websiteUrl":"http://"},"agencyId":5424,"arsResultCode":-1,"bizItem":{"additionalPropertyJson":{"ageRatingSetting":{"ageRating":"0","ageRatingDetail":null,"ageRatingType":null,"isAllAvailable":null,"isKoreanAge":null,"monthRating":null},"openingHoursSetting":null,"parkingInfoSetting":null,"runningTime":0,"ticketingTypeSetting":null},"addressJson":{"address":null,"detail":null,"globalAddress":null,"jibun":null,"placeName":null,"posLat":null,"posLong":null,"roadAddr":null,"zoomLevel":11},"agencyKey":"10017971.8","bannerInformationJson":{"src":null,"url":null},"bizItemId":119173,"bizItemType":"STANDARD","bookableSettingJson":{},"bookingAvailableCode":"RI01","bookingAvailableEndValue":30,"bookingAvailableStartValue":0,"bookingAvailableValue":0,"bookingCancelGuideJson":[],"bookingConfirmCode":"CF01","bookingCountSettingJson":{"maxBookingCount":100,"maxPersonBookingCount":100,"minBookingCount":1},"bookingGuideJson":[],"bookingPrecautionJson":[],"bookingTimeUnitCode":"RT03","businessId":26038,"customFormJson":[],"desc":"즐거움과 힐링을 동시에~!!","editedDateTime":"2019-05-20T02:03:54+09:00","editorId":"0","extraDescJson":[],"extraFeeSettingJson":{},"hasSlot":false,"impEndDateTime":"2019-05-31T14:59:00+09:00","impStartDateTime":"2019-04-30T01:35:00+09:00","inspectionStatusCode":"IS00","isAgencyKeyUsed":true,"isArsNightReceived":false,"isArsReceived":false,"isClosedBooking":false,"isCommissionTermsAgreed":false,"isCompletedButtonImp":false,"isDeducted":false,"isDeleted":false,"isEmsReceived":false,"isImp":false,"isImpRefund":false,"isImpStock":false,"isNPayUsed":true,"isOnsitePayment":false,"isPeriodFixed":false,"isSeatUsed":false,"isSmsNightReceived":false,"isSmsReceived":false,"isWaiting":false,"maxBookingCount":100,"minBookingCount":1,"nPayRegStatusCode":"PA01","name":"날짜지정형, 부분취소 가능","npayRegStatusCode":"PA01","order":1,"orderType":false,"price":0,"regDateTime":"2019-05-13T16:42:48+09:00","reviewPropertyJson":{},"stock":0,"translationStatusJson":{"en":null,"ja":null,"ko":null,"zh":null},"uncompletedBookingProcessCode":"UC01","uncompletedBookingRefundRate":0},"bizItemId":119173,"bizItemType":"STANDARD","bookedCount":{"cancelledCount":2,"completedCount":2,"noshowCount":0},"bookingCount":1,"bookingId":109020,"bookingPageUrl":"http://test2.booking.naver.com/booked/check/26038/null","bookingStatusCode":"RC03","bookingType":"STANDARD","business":{"additionalPropertyJson":{"parkingInfoSetting":{"isFreeParking":true,"isParkingSupported":true,"parkingCharge":{"basicCharge":{"hours":0,"isFree":false,"minutes":30},"chargingTypeCode":"HOURS","extraCharge":{"hours":0,"isFree":false,"minutes":30}},"parkingInfoDetail":"","valetParking":{"valetParkingType":"FREE"}}},"addressJson":{},"agencyKey":"1111","alarmSettingJson":{"receivers":{"ars":["02-111-1111"],"email":["test@naver.com"],"mms":[]}},"arsTurnoffEndTime":"22:00:00","arsTurnoffStartTime":"09:00:00","bookingAlarmCode":"RS02","bookingAvailableCode":"RI01","bookingAvailableValue":0,"bookingCancelGuideJson":[{"isActive":true,"words":"• 미사용티켓 환불 가능\n• 유효기간 이후 미사용티켓 환불 가능\n• 사용한 티켓 환불 불가\n• 해당 상품은 중복 할인 적용 불가합니다."}],"bookingConfirmCode":"CF01","bookingGuideJson":[{"isActive":false,"words":""}],"bookingTerm":"예약","bookingTimeUnitCode":"RT03","bookingUrl":"https://test2.booking.naver.com/booking/5/bizes/","businessCategory":"DL15","businessId":26038,"businessTypeId":5,"customFormJson":[],"desc":"서울 도심에서 30분 거리에 위치","editedDateTime":"2019-05-20T00:01:04+09:00","editorId":"0","email":"test@test.com","eventDescJson":[],"extraDescJson":[],"genresJson":[],"inspectionDateTime":"2019-05-14T10:09:48+09:00","inspectionDesc":"업체정보 검수가 통과","inspectionStatusCode":"IS01","isAgencyKeyUsed":true,"isAllRefundAgreed":false,"isArsNightReceived":false,"isArsReceived":false,"isCommissionTermsAgreed":false,"isCompletedButtonImp":false,"isContentRelated":true,"isDeleted":false,"isEmsReceived":false,"isEventEmailReceived":false,"isEventSmsReceived":false,"isImp":false,"isImpBenefitAtMain":false,"isImpMain":false,"isImpMyOwnPlace":false,"isImpRefund":false,"isNaverCubeRelated":false,"isNaverTalkChannelActivated":false,"isNaverTalkRelated":false,"isNmbRelated":false,"isPartialCancelUsed":true,"isPlaceHidden":true,"isPossibleUserCancel":true,"isRequestMessageUsed":false,"isSmsNightReceived":false,"isSmsReceived":false,"isWaiting":false,"legalInfoJson":{},"mainPanInfoJson":{},"npayCertificationKey":"274424B1-786C-4035-969E-18E1B6828DD4","nPayMerchantNumber":"300033304","nPayRegStatusCode":"PA01","nPayShopAccount":"osa318","name":"날짜지정형","npayRegStatusCode":"PA01","ownerId":5879,"phoneInformationJson":{"phoneList":["02-111-1111"],"reprPhone":"031-320-5000","wiredPhone":"031-320-5000"},"placeAccessorsUserJson":["leisure"],"placeCategoryId":"1000248","placeCategoryName":"기타","placeId":"1469240702","placeObjectId":"5cd91ff19d5d630c9676c707","placeStatusCode":"approved","promotionDesc":"홍보문구입니다아아아","refundTimeOffset":0,"regDateTime":"2019-05-13T16:42:41+09:00","reprOwnerName":"대표자명","reviewPropertyJson":{},"serviceName":"날짜지정형","smsTurnoffEndTime":"22:00:00","smsTurnoffStartTime":"09:00:00","translationStatusJson":{},"uncompletedBookingProcessCode":"UC01","uncompletedBookingRefundRate":100,"websiteUrl":"http://","websiteUrlJson":[{"isDefault":true,"websiteUrl":"http://","websiteUrlType":"normal"}]},"businessId":26038,"cancelledBookingCount":1,"cancelledExtraFeeJson":{},"confirmedDateTime":"2019-05-15T11:52:04+09:00","confirmedId":682,"couponPrice":0,"customFormInputJson":[],"editedDateTime":"2019-05-15T11:52:21+09:00","email":"test@naver.com","endDate":"2019-05-17","extraFeeJson":{},"isAgencyRequestSent":true,"isAllRefunded":false,"isCompleted":false,"isCompletedMmsSent":false,"isConfirmedMmsSent":true,"isOwnerImp":true,"isPostPayment":false,"isSmsAlarm":false,"isUserImp":true,"language":"ko","nPayChargedDateTime":"2019-05-15T11:52:02+09:00","nPayChargedName":"신용카드","nPayChargedStatusCode":"CT02","nPayOrderJson":[{"bookingId":109020,"cancelledExtraFeeJson":{},"count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051512205350","nPayOrderSeq":29061,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012514","nPayProductOrderNumber":"2019051512986300","price":40000,"regDateTime":"2019-05-15T02:52:03Z"},{"bookingId":109020,"cancelledDateTime":"2019-05-15T02:52:19Z","cancelledExtraFeeJson":{},"cancelledPrice":14000,"count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051512205350","nPayOrderSeq":29062,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012515","nPayProductOrderNumber":"2019051512986310","price":20000,"refundPrice":6000,"refundRate":30,"regDateTime":"2019-05-15T02:52:03Z"}],"nPayOrderNumber":"2019051512205350","nPayPoint":0,"nPayProductOrderNumber":"2019051512986300","name":"김영진","npayChargedDateTime":"2019-05-15T11:52:02+09:00","npayChargedName":"신용카드","npayChargedStatusCode":"CT02","npayOrderNumber":"2019051512205350","npayProductOrderNumber":"2019051512986300","orderSettingJson":{},"phone":"01051986486","price":60000,"refundPolicyId":29952,"regDateTime":"2019-05-15T11:51:50+09:00","reviewId":0,"shippingStatus":"NONE","snapshotJson":{"agencyId":5424,"allRefundSettingJson":{},"bizItemAddressJson":{"address":null,"detail":null,"globalAddress":null,"jibun":null,"placeName":null,"posLat":null,"posLong":null,"roadAddr":null,"zoomLevel":11},"bizItemDailyPriceJson":[],"bizItemName":"날짜지정형, 부분취소 가능","bizItemPrice":60000,"bizItemThumbImage":"https://beta.ssl.phinf.net/naverbooking/20190513_133/1557733361964QNy5C_JPEG/image.jpg","bookingConfirmCode":"CF01","bookingCount":2,"bookingGuide":"","bookingId":109020,"bookingOptionJson":[],"bookingPage":"http://test2.booking.naver.com/booked/check/26038/null","bookingPrecautionJson":[],"bookingTimeUnitCode":"RT03","businessAddressJson":{"address":"경기도 부천시 조마루로 425","detail":"","jibun":"경기도 부천시 원미동 35-3","posLat":37.4974157,"posLong":126.7911223,"roadAddr":"경기도 부천시 조마루로 425","zoomLevel":8},"businessId":26038,"businessName":"날짜지정형","businessThumbImage":"https://beta.ssl.phinf.net/ldb/20190513_299/15577333608616Gv5Y_JPEG/image.jpg","businessTypeId":5,"customFormInputJson":[],"deliveryAddressJson":{},"email":"test@naver.com","endDateTime":"2019-05-16T15:00:00Z","extraFeeSettingJson":{},"globalTimezone":"Asia/Seoul","isAdminBooking":false,"isNPayUsed":true,"isOnsitePayment":false,"isPartialCancelUsed":true,"isPeriodFixed":false,"isPossibleUserCancel":true,"isSeatUsed":false,"isTodayDeal":false,"language":"ko","name":"김영진","npayRegStatusCode":"PA01","phone":"01051986486","price":60000,"priceTypeJson":[{"agencyKey":"1111","bookingCount":1,"desc":"","isDefault":false,"name":"성인권","order":0,"price":40000,"priceId":79217},{"agencyKey":"1112","bookingCount":1,"desc":"","isDefault":false,"name":"소인권","order":1,"price":20000,"priceId":79218}],"refundTimeOffset":0,"requestMessage":"","resourceSubId":119173,"seatGroupJson":[],"seatJson":[],"serviceName":"날짜지정형","startDateTime":"2019-05-16T15:00:00Z","termsVersion":"2017-12-14","todayDealJson":[],"translateStatusJson":{},"uncompletedBookingProcessCode":"UC01","uncompletedBookingRefundRate":100,"userAgentJson":{"device":"unknown","os":"windows","os_version":"windows-7","raw":"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36"}},"startDate":"2019-05-17","term":"예약","userId":682}';

		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//예약 리스트 조회 GET
	public function search_naver_booking_list($businessId)
	{
		$url = NAVER_API_URL_REAL."/v3.0/businesses/".$businessId."/bookings";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];

//		//테스트 응답json
//		$httpcode = "200";
//		$json_data_string = '[{"adminBookingStatusCode":"AB00","areaName":"네이버 - 기타","bizItemId":119173,"bizItemName":"날짜지정형, 부분취소 가능","bizItemType":"STANDARD","bookingCount":1,"bookingId":108765,"bookingOptionJson":[],"bookingStatusCode":"RC08","bookingType":"STANDARD","businessCategory":"DL15","businessId":26038,"businessName":"날짜지정형 업체","businessTypeId":5,"cancelledBookingCount":1,"cancelledCount":2,"cancelledExtraFeeJson":{},"completedCount":2,"completedDateTime":"2019-05-14T14:59:04+09:00","confirmedDateTime":"2019-05-14T11:50:29+09:00","couponPrice":0,"email":"syh4233@naver.com","endDate":"2019-05-14","extraFeeJson":{},"isAllRefunded":false,"isBlacklist":false,"isCompleted":true,"isImpMyOwnPlace":false,"isNPayUsed":true,"isNonmember":false,"isPartialCancelUsed":true,"isPeriodFixed":false,"isPlaceHidden":true,"isPostPayment":false,"nPayChargedName":"신용카드","nPayChargedStatusCode":"CT02","nPayOrderJson":[{"bookingId":108765,"cancelledDateTime":"2019-05-14T05:13:52Z","cancelledExtraFeeJson":{"cancelledCommission":null,"cancelledShippingFee":null},"cancelledPrice":40000,"count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051412201530","nPayOrderSeq":28895,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012083","nPayProductOrderNumber":"2019051412980910","price":40000,"refundPrice":0,"refundRate":0,"regDateTime":"2019-05-14T02:50:29Z"},{"bookingId":108765,"cancelledExtraFeeJson":{},"completedDateTime":"2019-05-14T05:59:03Z","count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051412201530","nPayOrderSeq":28896,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012084","nPayProductOrderNumber":"2019051412980920","price":20000,"regDateTime":"2019-05-14T02:50:29Z"}],"nPayOrderNumber":"2019051412201530","nPayProductOrderNumber":"2019051412980910","name":"홍길동","noShowCount":0,"phone":"01056750162","price":60000,"refundPolicyId":29952,"refundPrice":0,"refundRate":0,"regDateTime":"2019-05-14T11:50:17+09:00","serviceName":"날짜지정형 업체","shippingStatus":"NONE","snapshotJson":{"agencyId":5424,"allRefundSettingJson":{},"bizItemAddressJson":{"address":null,"detail":null,"globalAddress":null,"jibun":null,"placeName":null,"posLat":null,"posLong":null,"roadAddr":null,"zoomLevel":11},"bizItemDailyPriceJson":[],"bizItemId":119173,"bizItemName":"날짜지정형, 부분취소 가능","bizItemPrice":60000,"bizItemThumbImage":"https://beta.ssl.phinf.net/naverbooking/20190513_133/1557733361964QNy5C_JPEG/image.jpg","bookingConfirmCode":"CF01","bookingCount":2,"bookingId":108765,"bookingOptionJson":[],"bookingPage":"http://test2.booking.naver.com/booked/check/26038/null","bookingPrecautionJson":[],"bookingTimeUnitCode":"RT03","businessAddressJson":{"address":"경기도 부천시 조마루로 425","detail":"","jibun":"경기도 부천시 원미동 35-3","posLat":37.4974157,"posLong":126.7911223,"roadAddr":"경기도 부천시 조마루로 425","zoomLevel":8},"businessId":26038,"businessName":"날짜지정형 업체","businessThumbImage":"https://beta.ssl.phinf.net/ldb/20190513_299/15577333608616Gv5Y_JPEG/image.jpg","businessTypeId":5,"customFormInputJson":[],"deliveryAddressJson":{},"email":"syh4233@naver.com","endDateTime":"2019-05-13T15:00:00Z","extraFeeSettingJson":{},"globalTimezone":"Asia/Seoul","isAdminBooking":false,"isNPayUsed":true,"isOnsitePayment":false,"isPartialCancelUsed":true,"isPeriodFixed":false,"isPossibleUserCancel":true,"isSeatUsed":false,"isTodayDeal":false,"language":"ko","name":"홍길동","npayRegStatusCode":"PA01","phone":"01056750162","price":60000,"priceTypeJson":[{"agencyKey":"10013618@100052490","bookingCount":1,"desc":"","isDefault":false,"name":"성인권","order":0,"price":40000,"priceId":79209},{"agencyKey":"10013619@100052490","bookingCount":1,"desc":"","isDefault":false,"name":"소인권","order":1,"price":20000,"priceId":79210}],"refundTimeOffset":0,"seatGroupJson":[],"seatJson":[],"serviceName":"날짜지정형 업체","startDateTime":"2019-05-13T15:00:00Z","termsVersion":"2017-12-14","todayDealJson":[],"translateStatusJson":{},"uncompletedBookingProcessCode":"UC01","uncompletedBookingRefundRate":100,"userAgentJson":{"device":"unknown","os":"windows","os_version":"windows-10","raw":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Whale/1.5.71.15 Safari/537.36"}},"startDate":"2019-05-14","totalPrice":60000,"userId":8651},{"adminBookingStatusCode":"AB00","areaName":"네이버 - 기타","bizItemId":119173,"bizItemName":"날짜지정형, 부분취소 가능","bizItemType":"STANDARD","bookingCount":1,"bookingId":108765,"bookingOptionJson":[],"bookingStatusCode":"RC08","bookingType":"STANDARD","businessCategory":"DL15","businessId":26038,"businessName":"날짜지정형 업체","businessTypeId":5,"cancelledBookingCount":1,"cancelledCount":2,"cancelledExtraFeeJson":{},"completedCount":2,"completedDateTime":"2019-05-14T14:59:04+09:00","confirmedDateTime":"2019-05-14T11:50:29+09:00","couponPrice":0,"email":"syh4233@naver.com","endDate":"2019-05-14","extraFeeJson":{},"isAllRefunded":false,"isBlacklist":false,"isCompleted":true,"isImpMyOwnPlace":false,"isNPayUsed":true,"isNonmember":false,"isPartialCancelUsed":true,"isPeriodFixed":false,"isPlaceHidden":true,"isPostPayment":false,"nPayChargedName":"신용카드","nPayChargedStatusCode":"CT02","nPayOrderJson":[{"bookingId":108765,"cancelledDateTime":"2019-05-14T05:13:52Z","cancelledExtraFeeJson":{"cancelledCommission":null,"cancelledShippingFee":null},"cancelledPrice":40000,"count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051412201530","nPayOrderSeq":28895,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012083","nPayProductOrderNumber":"2019051412980910","price":40000,"refundPrice":0,"refundRate":0,"regDateTime":"2019-05-14T02:50:29Z"},{"bookingId":108765,"cancelledExtraFeeJson":{},"completedDateTime":"2019-05-14T05:59:03Z","count":1,"extraFeeJson":{},"nPayOrderNumber":"2019051412201530","nPayOrderSeq":28896,"nPayOrderType":"BARCODE","nPayOrderTypeId":"10012084","nPayProductOrderNumber":"2019051412980920","price":20000,"regDateTime":"2019-05-14T02:50:29Z"}],"nPayOrderNumber":"2019051412201530","nPayProductOrderNumber":"2019051412980910","name":"홍길동","noShowCount":0,"phone":"01056750162","price":60000,"refundPolicyId":29952,"refundPrice":0,"refundRate":0,"regDateTime":"2019-05-14T11:50:17+09:00","serviceName":"날짜지정형 업체","shippingStatus":"NONE","snapshotJson":{"agencyId":5424,"allRefundSettingJson":{},"bizItemAddressJson":{"address":null,"detail":null,"globalAddress":null,"jibun":null,"placeName":null,"posLat":null,"posLong":null,"roadAddr":null,"zoomLevel":11},"bizItemDailyPriceJson":[],"bizItemId":119173,"bizItemName":"날짜지정형, 부분취소 가능","bizItemPrice":60000,"bizItemThumbImage":"https://beta.ssl.phinf.net/naverbooking/20190513_133/1557733361964QNy5C_JPEG/image.jpg","bookingConfirmCode":"CF01","bookingCount":2,"bookingId":108765,"bookingOptionJson":[],"bookingPage":"http://test2.booking.naver.com/booked/check/26038/null","bookingPrecautionJson":[],"bookingTimeUnitCode":"RT03","businessAddressJson":{"address":"경기도 부천시 조마루로 425","detail":"","jibun":"경기도 부천시 원미동 35-3","posLat":37.4974157,"posLong":126.7911223,"roadAddr":"경기도 부천시 조마루로 425","zoomLevel":8},"businessId":26038,"businessName":"날짜지정형 업체","businessThumbImage":"https://beta.ssl.phinf.net/ldb/20190513_299/15577333608616Gv5Y_JPEG/image.jpg","businessTypeId":5,"customFormInputJson":[],"deliveryAddressJson":{},"email":"syh4233@naver.com","endDateTime":"2019-05-13T15:00:00Z","extraFeeSettingJson":{},"globalTimezone":"Asia/Seoul","isAdminBooking":false,"isNPayUsed":true,"isOnsitePayment":false,"isPartialCancelUsed":true,"isPeriodFixed":false,"isPossibleUserCancel":true,"isSeatUsed":false,"isTodayDeal":false,"language":"ko","name":"홍길동","npayRegStatusCode":"PA01","phone":"01056750162","price":60000,"priceTypeJson":[{"agencyKey":"10013618@100052490","bookingCount":1,"desc":"","isDefault":false,"name":"성인권","order":0,"price":40000,"priceId":79209},{"agencyKey":"10013619@100052490","bookingCount":1,"desc":"","isDefault":false,"name":"소인권","order":1,"price":20000,"priceId":79210}],"refundTimeOffset":0,"seatGroupJson":[],"seatJson":[],"serviceName":"날짜지정형 업체","startDateTime":"2019-05-13T15:00:00Z","termsVersion":"2017-12-14","todayDealJson":[],"translateStatusJson":{},"uncompletedBookingProcessCode":"UC01","uncompletedBookingRefundRate":100,"userAgentJson":{"device":"unknown","os":"windows","os_version":"windows-10","raw":"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Whale/1.5.71.15 Safari/537.36"}},"startDate":"2019-05-14","totalPrice":60000,"userId":8651}]';

		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 예약 끝 ============================================================================================================================*/



/* 예약 트랜잭션 시작 ============================================================================================================================*/
	//예약생성(네이버->대행사) POST

	//예약 상태 업데이트(네이버->대행사) PATCH

	//예약 상태 업데이트(대행사->네이버) PATCH
	public function update_naver_booking($businessId, $bizItemId, $bookingId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/bookings/".$bookingId."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 예약 트랜잭션 끝 ============================================================================================================================*/

/* 부분취소,부분사용 시작 ============================================================================================================================*/
	//네이버페이 주문 상태 업데이트(네이버->대행사) PATCH

	//네이버페이 주문 상태 업데이트 by nPayProductOrderNumber(대행사->네이버) PATCH
	public function update_naver_booking_nPayProductOrderNumber($businessId, $bizItemId, $bookingId, $readableCodeId, $nPayProductOrderNumber, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/bookings/".$bookingId."/npay-product-order-numbers/".$nPayProductOrderNumber."";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

	//네이버페이 주문 상태 업데이트 by bookingId(대행사->네이버) PATCH
	public function update_naver_booking_bookingId($businessId, $bizItemId, $bookingId, $json)
	{
		$url = NAVER_API_URL_REAL."/v3.1/businesses/".$businessId."/biz-items/".$bizItemId."/bookings/".$bookingId."/npay-product-order-numbers";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = $json;

		$rtn_data = $this->curl_patch($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 부분취소,부분사용 끝 ============================================================================================================================*/


/* 기타 시작 ============================================================================================================================*/
	//주소검색
	public function search_address($query)
	{
		$url = NAVER_API_URL_REAL."/v3.1/addresses";

		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: application/json"
		);

		$post_data = array();
		$post_data["query"] = $query;
		$post_data["pageSize"] = 100;


		$rtn_data = $this->curl_get($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "200" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["list"] = $json_data;
		}
		//조회된 주소가 없음
		else if( $httpcode == "409" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "조회된 주소가 없습니다.";
			$return_data["list"] = $json_data;
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}








	//이미지업로드
	public function image_upload($imageUrl)
	{
		$url = NAVER_API_URL_REAL."/v3.0/images";


		$headers = array(
			"X-Booking-Naver-Role: AGENCY",
			"Authorization:".NAVER_API_TOKEN,
			"Content-Type: multipart/form-data"
		);

		$post_data = array();
		//$post_data["imageFile"] = "";
		$post_data["imageUrl"] = $imageUrl;


		$rtn_data = $this->curl_post($url, $post_data, $headers);

		$httpcode = $rtn_data["httpcode"];
		$json_data_string = $rtn_data["body"];
		$json_data = json_decode($json_data_string, true);

		$return_data = array();

		//통신성공
		if( $httpcode == "201" )
		{
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = "ok[".$httpcode."]";
			$return_data["imageUrl"] = $json_data["imageUrl"];
		}
		//통신실패
		else
		{
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "[".$httpcode."]".$json_data_string;
		}

		return $return_data;
	}

/* 기타 끝 ============================================================================================================================*/






	// http API연동 POST방식..
	public function curl_post($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

	// http API연동 GET방식..
	public function curl_get($url, $post_data, $headers)
	{

		$url = $url . "?" . http_build_query($post_data);
		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch,CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers); //header 지정하기

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

	// http API연동 PATCH방식..
	public function curl_patch($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

// http API연동 DELETE방식..
	public function curl_delete($url, $post_data, $headers)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION,1); // SSL 버젼 (https 접속시에 필요)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //header 지정하기
//		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // patch, put, delete 등
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

		$return["httpcode"] = $httpcode;
		$return["body"] = $res;

		return $return;
	}

}
?>