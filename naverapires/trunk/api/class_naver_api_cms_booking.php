<?
// 네이버->대행사 연동 테스트 클래스
class NaverCmsApiBooking
{
	//예약트랜잭션 > 예약생성(네이버->대행사)
	public function create_booking($cmsId, $agencyBusinessId, $agencyBizItemId, $json)
	{
		//이지웰CMS agecykey 50000번 부터
		if( $cmsId == "ezwel_cms" )
		{
			$url = NAVER_EZWEL_CMD_URL;
		}
		//큐패스코리아(웹센) agecykey 1번 부터
		else if( $cmsId == "websen_cms" )
		{
			$url = NAVER_QPASS_CMD_URL;
		}
		//양지파인리조트
		else if( $cmsId == "yjpine_cms" )
		{
			$url = NAVER_YJPINE_CMD_URL;
		}
		//설악파인리조트
		else if( $cmsId == "srpine_cms" )
		{
			$url = NAVER_SRPINE_CMD_URL;
		}
		//아레나수상레저
		else if( $cmsId == "aw_cms" )
		{
			$url = NAVER_AW_CMD_URL;
		}
		//큐포스 agecykey 100000번 부터
		else if( $cmsId == "qpos_system" )
		{
			$url = NAVER_QPOS_CMD_URL;
		}
		else if( $cmsId == "napple" )
		{
			$url = NAVER_NAPPLE_CMD_URL;
		}
		//라디칼
		else if( $cmsId == "radical_cms" )
		{
			$url = NAVER_RADICAL_CMD_URL;
		}
		else if( $cmsId == "momo_cms" )
		{
			$url = NAVER_MOMO_CMD_URL;
		}
		else if( $cmsId == "bonghwa_cms")
		{
			$url = NAVER_BONGHWA_CMD_URL;
		}
		else if ( $cmsId == "playtica" )
		{
			$url = NAVER_PLAYTICA_CMD_URL;
		}
		else if ( $cmsId == "gurye_zipline" )
		{
			$url = NAVER_GURYE_ZIPLINE_CMD_URL;
		}
		else if ( $cmsId == "zipline" )
		{
			$url = NAVER_ZIPLINE_CMD_URL;
		}
		//https
		if ( $cmsId == "jisan" || $cmsId == "bango_cms" || $cmsId == "eanland_cms" || $cmsId == "jamsa_cms" || $cmsId == "gun_power" || $cmsId == "doja_cms" || $cmsId == "gsurfing_cms" || $cmsId == "taebaek" || $cmsId == "daebudo" || $cmsId == "fishing_cms" || $cmsId == "boyang_cms" || $cmsId == "tb_cms" || $cmsId == "yy_cms" )
		{
			$post_data["cmd"] = "create_booking";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["json"] = $json;

			$rtn_data = $this->curl_post_ssl($url, $post_data);

		}
		//http
		else
		{
			$post_data["cmd"] = "create_booking";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["json"] = $json;
			
			$rtn_data = $this->curl_post($url, $post_data);

		}

		return $rtn_data;
	}

	//예약 상태 업데이트
	public function update_booking($cmsId, $agencyBusinessId, $agencyBizItemId, $bookingId, $json)
	{
		//이지웰CMS
		if( $cmsId == "ezwel_cms" )
		{
			$url = NAVER_EZWEL_CMD_URL;
		}
		//큐패스코리아
		else if( $cmsId == "websen_cms" )
		{
			$url = NAVER_QPASS_CMD_URL;
		}
		//양지파인리조트
		else if( $cmsId == "yjpine_cms" )
		{
			$url = NAVER_YJPINE_CMD_URL;
		}
		//설악파인리조트
		else if( $cmsId == "srpine_cms" )
		{
			$url = NAVER_SRPINE_CMD_URL;
		}
		//아레나수상레저
		else if( $cmsId == "aw_cms" )
		{
			$url = NAVER_AW_CMD_URL;
		}
		//큐포스
		else if( $cmsId == "qpos_system" )
		{
			$url = NAVER_QPOS_CMD_URL;
		}
		else if( $cmsId == "napple" )
		{
			$url = NAVER_NAPPLE_CMD_URL;
		}
		//라디칼
		else if( $cmsId == "radical_cms" )
		{
			$url = NAVER_RADICAL_CMD_URL;
		}
		//모모
		else if( $cmsId == "momo_cms" )
		{
			$url = NAVER_MOMO_CMD_URL;
		}
		else if( $cmsId == "bonghwa_cms")
		{
			$url = NAVER_BONGHWA_CMD_URL;
		}
		else if ( $cmsId == "playtica" )
		{
			$url = NAVER_PLAYTICA_CMD_URL;
		}
		else if ( $cmsId == "gurye_zipline" )
		{
			$url = NAVER_GURYE_ZIPLINE_CMD_URL;
		}
		else if ( $cmsId == "zipline" )
		{
			$url = NAVER_ZIPLINE_CMD_URL;
		}
		//https
		if ( $cmsId == "jisan" || $cmsId == "bango_cms" || $cmsId == "eanland_cms" || $cmsId == "jamsa_cms" || $cmsId == "gun_power" || $cmsId == "doja_cms" || $cmsId == "gsurfing_cms" || $cmsId == "taebaek" || $cmsId == "daebudo" || $cmsId == "fishing_cms" || $cmsId == "boyang_cms" || $cmsId == "tb_cms" || $cmsId == "yy_cms" )
		{
			$post_data["cmd"] = "update_booking";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["bookingId"] = $bookingId;
			$post_data["json"] = $json;

			$rtn_data = $this->curl_post_ssl($url, $post_data);
		}
		else
		{

			$post_data["cmd"] = "update_booking";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["bookingId"] = $bookingId;
			$post_data["json"] = $json;
			
			$rtn_data = $this->curl_post($url, $post_data);

		}

		return $rtn_data;
	}



	//예약 상태 업데이트(부분취소,사용)
	public function update_booking_npay($cmsId, $agencyBusinessId, $agencyBizItemId, $bookingId, $nPayProductOrderNumber, $json)
	{
		//이지웰CMS
		if( $cmsId == "ezwel_cms" )
		{
			$url = NAVER_EZWEL_CMD_URL;
		}
		//큐패스코리아
		else if( $cmsId == "websen_cms" )
		{
			$url = NAVER_QPASS_CMD_URL;
		}
		//양지파인리조트
		else if( $cmsId == "yjpine_cms" )
		{
			$url = NAVER_YJPINE_CMD_URL;
		}
		//설악파인리조트
		else if( $cmsId == "srpine_cms" )
		{
			$url = NAVER_SRPINE_CMD_URL;
		}
		//아레나수상레저
		else if( $cmsId == "aw_cms" )
		{
			$url = NAVER_AW_CMD_URL;
		}
		//큐포스
		else if( $cmsId == "qpos_system" )
		{
			$url = NAVER_QPOS_CMD_URL;
		}
		else if( $cmsId == "napple" )
		{
			$url = NAVER_NAPPLE_CMD_URL;
		}
		//라디칼
		else if( $cmsId == "radical_cms" )
		{
			$url = NAVER_RADICAL_CMD_URL;
		}
		//모모
		else if( $cmsId == "momo_cms" )
		{
			$url = NAVER_MOMO_CMD_URL;
		}
		else if( $cmsId == "bonghwa_cms")
		{
			$url = NAVER_BONGHWA_CMD_URL;
		}
		else if ( $cmsId == "playtica" )
		{
			$url = NAVER_PLAYTICA_CMD_URL;
		}
		else if ( $cmsId == "gurye_zipline" )
		{
			$url = NAVER_GURYE_ZIPLINE_CMD_URL;
		}

		else if ( $cmsId == "zipline" )
		{
			$url = NAVER_ZIPLINE_CMD_URL;
		}
		//https
		if ( $cmsId == "jisan" || $cmsId == "bango_cms" || $cmsId == "eanland_cms" || $cmsId == "jamsa_cms" || $cmsId == "gun_power" || $cmsId == "doja_cms" || $cmsId == "gsurfing_cms" || $cmsId == "taebaek" || $cmsId == "daebudo" || $cmsId == "fishing_cms" || $cmsId == "boyang_cms" || $cmsId == "tb_cms" || $cmsId == "yy_cms" )
		{

			$post_data["cmd"] = "update_booking_npay";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["bookingId"] = $bookingId;
			$post_data["nPayProductOrderNumber"] = $nPayProductOrderNumber;
			$post_data["json"] = $json;

			$rtn_data = $this->curl_post_ssl($url, $post_data);

		}
		else
		{
			$post_data["cmd"] = "update_booking_npay";
			$post_data["agencyBusinessId"] = $agencyBusinessId;
			$post_data["agencyBizItemId"] = $agencyBizItemId;
			$post_data["bookingId"] = $bookingId;
			$post_data["nPayProductOrderNumber"] = $nPayProductOrderNumber;
			$post_data["json"] = $json;
			
			$rtn_data = $this->curl_post($url, $post_data);

		}

		return $rtn_data;
	}
	// http API연동 POST방식..
	public function curl_post($url, $post_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 180);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Post 값  Get 방식처럼적는다.

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

//		$return["httpcode"] = $httpcode;
//		$return["body"] = $res;

		$return = $res;

		return $return;
	}
	// http API연동 POST방식..
	public function curl_post_ssl($url, $post_data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 180);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Post 값  Get 방식처럼적는다.
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$res = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo "httpcode : ". $httpcode;
		curl_close($ch);

		$res = str_replace("\r", "", str_replace("\n", "", $res));

//		$return["httpcode"] = $httpcode;
//		$return["body"] = $res;

		$return = $res;

		return $return;
	}

}
?>