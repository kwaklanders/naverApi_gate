<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?
$cmd = $_REQUEST["cmd"];
$agencyBusinessId = $_REQUEST["agencyBusinessId"];	//CMS업체ID
$agencyBizItemId = $_REQUEST["agencyBizItemId"];	//CMS상품ID
$bookingId = $_REQUEST["bookingId"];	//예약ID
$nPayProductOrderNumber = $_REQUEST["nPayProductOrderNumber"];	//네이버페이상품
$json = $_REQUEST["json"];


//로그...tail -f /var/log/httpd/naverapires.qpass.kr-error_log
error_log("    ");
error_log("    ");
error_log(">>>>>>>>> START >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("agencyBusinessId==========>".$agencyBusinessId);
error_log("agencyBizItemId==========>".$agencyBizItemId);
error_log("json==========>".$json);
error_log(">>>>>>>>> END   >>>>>>>>> ".date("Y-m-d H:i:s")."---------------");
error_log("    ");
error_log("    ");

//예약생성
if( $cmd == "create_booking" )
{
	echo_json_encode(create_booking($agencyBusinessId, $agencyBizItemId, $json));
}
//예약 상태 업데이트(일괄)
else if( $cmd == "update_booking" )
{
	echo_json_encode(update_booking($agencyBusinessId, $agencyBizItemId, $bookingId, $json));
}
//예약 상태 업데이트(부분취소,사용)
else if( $cmd == "update_booking_npay" )
{
	echo_json_encode(update_booking_npay($agencyBusinessId, $agencyBizItemId, $nPayProductOrderNumber, $bookingId, $json));
}




//예약생성
function create_booking($agencyBusinessId, $agencyBizItemId, $json)
{
//	echo "시작함!";
	//네이버 연동상품 상태조회
	$naver_product_info = status_naver_product_info($agencyBusinessId, $agencyBizItemId);

	//네이버상품 조회실패
	if( $naver_product_info["result_code"] != "0000" )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = $naver_product_info["result_msg"];
		$return_data["json"];
	}
	else
	{
		$businessId = $naver_product_info["naver_product_info"]["businessId"];
		$bizItemId = $naver_product_info["naver_product_info"]["bizItemId"];



		//상품조회
		$product_info_array = get_product_info($businessId, $bizItemId);

		if( $product_info_array["count"] == 0 )
		{
			//상품조회안됨...
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = "상품 미조회";
			$return_data["json"] = "";
		}
		else
		{
			$product_info = $product_info_array["product_info"];

			//주문등록...
			$result = insert_order_naver($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $product_info, $naver_product_info);

			if( $result["result_code"] == "0000" )
			{
				$return_data["result_code"] = "0000";
				$return_data["result_msg"] = "주문등록 성공";
				$return_data["json"] = get_naver_order($result["bookingId"]);

			}
			else
			{
				$return_data["result_code"] = "9999";
				$return_data["result_msg"] = $result["result_msg"];
				$return_data["json"] = "";
			}
		}
	}

	return $return_data;
}



//예약 상태 업데이트(일괄)
function update_booking($agencyBusinessId, $agencyBizItemId, $bookingId, $json)
{
	$naver_json_object = json_decode($json, true);

	$return_data = update_order($bookingId, $naver_json_object);
	return $return_data;
}


//예약 상태 업데이트(부분취소, 사용)
function update_booking_npay($agencyBusinessId, $agencyBizItemId, $nPayProductOrderNumber, $bookingId, $json)
{
	$naver_json_object = json_decode($json, true);

	$return_data = update_order_pay($bookingId, $nPayProductOrderNumber, $naver_json_object);

	return $return_data;
}





//주문상태 업데이트(일괄)
function update_order($bookingId, $naver_json_object)
{
	$tran_chk = false;
	$send_chk = false;
	$error_msg = "";
	$result = false;
	$api_type_chk = false;

	$existsOrderBooking = existsOrderBooking($bookingId);

	if( $existsOrderBooking != 1 )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = "조회된 예약없음.";
	}
	else
	{
		//트랜잭션 시작
		//오토커밋 해제
		mysql_query("SET AUTOCOMMIT=0");
		mysql_query("BEGIN");

		//결제 paid(느려짐 때문에 사용안함)
		if( $naver_json_object["status"] == "paid" )
		{
				$error_msg = "ok";
				$tran_chk = true;

		}
		//결제확정 payCompleted, 결제확정, 부분취소 사용의 경우 payCompleted nPayOrderJson
		else if( $naver_json_object["status"] == "payCompleted" )
		{
			//주문 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update tabel ";
			$strSql .= " set  ";
			$strSql .= " status = 11 ";
			$strSql .= " , naver_nPayProductOrderNumber = '".$naver_json_object["nPayProductOrderNumber"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";
			$result = mysql_query($strSql);

			$update_count_order = mysql_affected_rows();

			$update_count_order_detail = 1;

			//부분사용,취소 불가일 경우
			if( count($naver_json_object["nPayOrderJson"]) == 0 )
			{
				//주문상세 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update tabel ";
				$strSql .= " set  ";
				$strSql .= " naver_nPayProductOrderNumber = '".$naver_json_object["nPayProductOrderNumber"]."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				$strSql .= "  ";
				$strSql .= "  ";
//				echo "[ok order_detail]".$strSql . "<br>";
				$result = mysql_query($strSql);

				if( $result )
				{
					//주문상세pkg 업데이트
					$strSql = "";
					$strSql .= "  ";
					$strSql .= " update tabel ";
					$strSql .= " set  ";
					$strSql .= " naver_nPayProductOrderNumber = '".$naver_json_object["nPayProductOrderNumber"]."' ";
					$strSql .= " where naver_bookingId = '".$bookingId."' ";
					$strSql .= "  ";
					$strSql .= "  ";
//					echo "[no pos_detail]".$strSql . "<br>";
					$result = mysql_query($strSql);

					if( $result )
					{
						$update_count_order_detail = 1;
					}
					else
					{
						$update_count_order_detail = 0;
					}
				}
				else
				{
					$update_count_order_detail = 0;
				}
			}
			//부분사용/취소일 경우
			else
			{
				foreach($naver_json_object["nPayOrderJson"] as $k=>$v)
				{
					//주문상세 업데이트
					$strSql = "";
					$strSql .= "  ";
					$strSql .= " update table ";
					$strSql .= " set  ";
					$strSql .= "   naver_nPayOrderSeq = '".$v["nPayOrderSeq"]."' ";
					$strSql .= " , naver_nPayOrderNumber = '".$v["nPayOrderNumber"]."' ";
					$strSql .= " , naver_nPayProductOrderNumber = '".$v["nPayProductOrderNumber"]."' ";
					$strSql .= " where naver_bookingId = '".$bookingId."' ";
					if( strlen($v["nPayOrderTypeId"]) > 6 )
					{
						$strSql .= " and barcode = '".$v["nPayOrderTypeId"]."' ";
					}
					echo "[ok order_detail]".$strSql . "<br>";

					$result = mysql_query($strSql);

					$update_count_order_detail = mysql_affected_rows();

					if( $update_count_order_detail != 1 )
					{
						break;
					}


					//주문상세pkg 업데이트
					$strSql = "";
					$strSql .= "  ";
					$strSql .= " update table ";
					$strSql .= " set  ";
					$strSql .= "   naver_nPayOrderSeq = '".$v["nPayOrderSeq"]."' ";
					$strSql .= " , naver_nPayOrderNumber = '".$v["nPayOrderNumber"]."' ";
					$strSql .= " , naver_nPayProductOrderNumber = '".$v["nPayProductOrderNumber"]."' ";
					$strSql .= " where naver_bookingId = '".$bookingId."' ";
					if( strlen($v["nPayOrderTypeId"]) > 6 )
					{
						$strSql .= " and barcode = '".$v["nPayOrderTypeId"]."' ";
					}

					$result = mysql_query($strSql);

					if( $result )
					{
						$update_count_order_detail = 1;
					}
					else
					{
						$update_count_order_detail = 0;
					}

				}
			}
			if( $update_count_order == 1 && $update_count_order_detail == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
				$send_chk = true;
			}
			else
			{
				$error_msg = "[][payCompleted]업데이트 실패[결제확정]";
			}

		}
		//결제실패 payFailed
		else if( $naver_json_object["status"] == "payFailed" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 12 ";
			$strSql .= " , naver_status = 'payFailed' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count = mysql_affected_rows();

			if( $update_count == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][payFailed]업데이트 실패[결제실패]";
			}
		}
		//파트너센터 노쇼 nosow
		else if( $naver_json_object["status"] == "noshow" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 5 ";
			$strSql .= " , naver_status = 'noshow cancelled' ";
			$strSql .= " , naver_cancelledBy = '".$naver_json_object["cancelledBy"]."' ";
			$strSql .= " , naver_cancelledDesc = '".$naver_json_object["cancelledDesc"]."' ";
			$strSql .= " , naver_cancelledDateTime = '".$naver_json_object["cancelledDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count = mysql_affected_rows();

			if( $update_count == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][noshow]업데이트 실패[파트너센터 노쇼]";
			}
		}
		//취소(구매자)
		else if( $naver_json_object["status"] == "cancelled" && $naver_json_object["cancelledBy"] == "user" )
		{
			//주문 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 5 ";
			$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " , naver_status = 'user cancelled' ";
			$strSql .= " , naver_cancelledBy = '".$naver_json_object["cancelledBy"]."' ";
			$strSql .= " , naver_cancelledDesc = '".$naver_json_object["cancelledDesc"]."' ";
			$strSql .= " , naver_cancelledDateTime = '".$naver_json_object["cancelledDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count_order = mysql_affected_rows();

			$update_count_order_detail = 1;

			//주문상세 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 2 ";
			$strSql .= " , detail_cancel_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			if( $result )
			{
				//주문상세pkg 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table ";
				$strSql .= " set  ";
				$strSql .= " status = 2 ";
				$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				$strSql .= "  ";
				$strSql .= "  ";

				$result = mysql_query($strSql);

				if( $result )
				{
					$update_count_order_detail = 1;
				}
				else
				{
					$update_count_order_detail = 0;
				}
			}
			else
			{
				$update_count_order_detail = 0;
			}




			if( $update_count_order == 1 && $update_count_order_detail == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][cancelled]업데이트 실패[취소(구매자)]";
			}

		}
		//1시간 미결제 미결제 취소
		else if( $naver_json_object["state"] == "cancelled" && $naver_json_object["status"] == "cancelled" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 5 ";
			$strSql .= " , naver_status = '1hour cancelled' ";
			$strSql .= " , naver_cancelledBy = '".$naver_json_object["cancelledBy"]."' ";
			$strSql .= " , naver_cancelledDesc = '".$naver_json_object["cancelledDesc"]."' ";
			$strSql .= " , naver_cancelledDateTime = '".$naver_json_object["cancelledDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count = mysql_affected_rows();

			if( $update_count == 1 )
			{
				$strSql = "";
				$strSql .= " select ";
				$strSql .= " table.barcode ";
				$strSql .= " from table ";
				$strSql .= " inner join table on table.order_num = table.order_num ";
				$strSql .= " where table.naver_bookingId = '".$bookingId."' ";

				$rs = mysql_query($strSql);
				$rscount = mysql_num_rows($rs);

				if ( $rscount > 0 )
				{
					while($rows = mysql_fetch_assoc($rs))
					{
						$barcode = $rows["barcode"];

						$order_detail_info = getOrderDetailInfo($barcode);

						$strSql = "";
						$strSql .= " select ";
						$strSql .= " count(*) ";
						$strSql .= " from table_block_ticket ";
						$strSql .= " where mem_code = '".$order_detail_info["mem_code"]."' ";

						$rsblock = mysql_query($strSql);
						$rscount = mysql_num_rows($rsblock);

						if ( $rscount > 0 )
						{
							$block_check = SlotBlockCheck_total($order_detail_info["ex_hours"], $order_detail_info["ex_minute"], $order_detail_info["ex_date"], $order_detail_info["mem_code"]);


							$result_block = (int)$block_check["block"] + $order_detail_info["ex_block"];

							
							$result_block_update = update_block_ticket_hour_cancel($result_block, $order_detail_info["ex_hours"], $order_detail_info["ex_minute"], $order_detail_info["ex_date"], $order_detail_info["mem_code"]);

							if ( $result_block_update )
							{
								$error_msg = "ok";
								$tran_chk = true;
							}
							else
							{
								$error_msg = "[][cancelled]블럭 취소 업데이트 실패[1시간 미결제 취소]";
								
							}
						}
						else
						{
							$error_msg = "ok";
							$tran_chk = true;

						}
					}
				}
				else
				{					
					$tran_chk = false;
					$error_msg = "[][cancelled]바코드 조회 실패[1시간미결제취소]";
				}
			}
			else
			{
				$tran_chk = false;
				$error_msg = "[][cancelled]업데이트 실패[1시간미결제취소]";
			}
		}
		//자동취소(시스템)
		else if( $naver_json_object["status"] == "cancelled" )
		{
			//주문 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 5 ";
			$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " , naver_status = 'auto cancelled' ";
			$strSql .= " , naver_cancelledBy = '".$naver_json_object["cancelledBy"]."' ";
			$strSql .= " , naver_cancelledDesc = '".$naver_json_object["cancelledDesc"]."' ";
			$strSql .= " , naver_cancelledDateTime = '".$naver_json_object["cancelledDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count_order = mysql_affected_rows();

			$update_count_order_detail = 1;

			//주문상세 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 2 ";
			$strSql .= " , detail_cancel_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";
//				echo "[no order_detail]".$strSql . "<br>";
			$result = mysql_query($strSql);

			if( $result )
			{
				//주문상세pkg 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table ";
				$strSql .= " set  ";
				$strSql .= " status = 2 ";
				$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				$strSql .= "  ";
				$strSql .= "  ";

				$result = mysql_query($strSql);

				if( $result )
				{
					$update_count_order_detail = 1;
				}
				else
				{
					$update_count_order_detail = 0;
				}
			}
			else
			{
				$update_count_order_detail = 0;
			}



			if( $update_count_order == 1 && $update_count_order_detail == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][cancelled]업데이트 실패[자동취소]";
			}
		}
		//이용 완료
		else if( $naver_json_object["status"] == "completed"  )
		{
			//주문상세 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 1 ";
			$strSql .= " , detail_use_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " , naver_status = 'completed' ";
			$strSql .= " , naver_completedBy = '".$naver_json_object["completedBy"]."' ";
			$strSql .= " , naver_completedDateTime = '".$naver_json_object["completedDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			if( $result )
			{
				//주문상세 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table ";
				$strSql .= " set  ";
				$strSql .= " status = 1 ";
				$strSql .= " , use_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " , naver_status = 'completed' ";
				$strSql .= " , naver_completedBy = '".$naver_json_object["completedBy"]."' ";
				$strSql .= " , naver_completedDateTime = '".$naver_json_object["completedDateTime"]."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				$strSql .= "  ";
				$strSql .= "  ";

				$result = mysql_query($strSql);

				if( $result )
				{
					$error_msg = "ok";
					$tran_chk = true;
				}
				else
				{
					$error_msg = "[][completed]업데이트 실패[이용 완료]";
				}

				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][completed]업데이트 실패[이용 완료]";
			}
		}
		//자동 이용 완료
		else if( $naver_json_object["status"] == "completed" && $naver_json_object["completedBy"] == "system"  )
		{
			//주문상세 업데이트
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " status = 1 ";
			$strSql .= " , detail_use_date = '".date("Y-m-d H:i:s")."' ";
			$strSql .= " , naver_status = 'completed' ";
			$strSql .= " , naver_completedBy = '".$naver_json_object["completedBy"]."' ";
			$strSql .= " , naver_completedDateTime = '".$naver_json_object["completedDateTime"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			if( $result )
			{
				//주문상세pkg 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table ";
				$strSql .= " set  ";
				$strSql .= " status = 1 ";
				$strSql .= " , use_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " , naver_status = 'completed' ";
				$strSql .= " , naver_completedBy = '".$naver_json_object["completedBy"]."' ";
				$strSql .= " , naver_completedDateTime = '".$naver_json_object["completedDateTime"]."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				$strSql .= "  ";
				$strSql .= "  ";

				$result = mysql_query($strSql);

				if( $result )
				{
					$error_msg = "ok";
					$tran_chk = true;
				}
				else
				{
					$error_msg = "[][completed]업데이트 실패[system 이용 완료]";
				}
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[][completed]업데이트 실패[system 이용 완료]";
			}

		}



		//회차형 예약수정(주소변경)
		if( count($naver_json_object["addressJson"]) > 0  )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table ";
			$strSql .= " set  ";
			$strSql .= " naver_addressName = '".$naver_json_object["details"]["addressName"]."' ";
			$strSql .= " ,naver_baseAddress = '".$naver_json_object["details"]["baseAddress"]."' ";
			$strSql .= " ,naver_detailAddress = '".$naver_json_object["details"]["detailAddress"]."' ";
			$strSql .= " ,naver_hash = '".$naver_json_object["details"]["hash"]."' ";
			$strSql .= " ,naver_isRecentlyUsed = '".$naver_json_object["details"]["isRecentlyUsed"]."' ";
			$strSql .= " ,naver_receiverName = '".$naver_json_object["details"]["receiverName"]."' ";
			$strSql .= " ,naver_roadNameYn = '".$naver_json_object["details"]["roadNameYn"]."' ";
			$strSql .= " ,naver_service = '".$naver_json_object["details"]["service"]."' ";
			$strSql .= " ,naver_telNo1 = '".$naver_json_object["details"]["telNo1"]."' ";
			$strSql .= " ,naver_zipCode = '".$naver_json_object["details"]["zipCode"]."' ";
			$strSql .= " where naver_bookingId = '".$bookingId."' ";
			$strSql .= "  ";
			$strSql .= "  ";

			$result = mysql_query($strSql);

			$update_count = mysql_affected_rows();

			if( $update_count == 1 )
			{
				$error_msg = "ok";
				$tran_chk = true;
			}
			else
			{
				$error_msg = "[]업데이트 실패[회차형 예약수정(주소변경)]";
			}
		}


		//DB커밋
		if( $tran_chk )
		{
			mysql_query("COMMIT");
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = $error_msg;
		}
		else
		{
			mysql_query("ROLLBACK");
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = $error_msg;
		}

		//오토커밋 설정
		mysql_query("SET AUTOCOMMIT=1");

		//결제성공시 문자발송
		if( $send_chk )
		{
			//api_type 체크
			if( $api_type_chk )
			{

			}
			else
			{
				//알림톡 발송
				order_send($bookingId);

				member_manager_send($bookingId);
			}
		}
	}

	return $return_data;
}












//주문상태 업데이트(부분취소, 사용)
function update_order_pay($bookingId, $nPayProductOrderNumber, $naver_json_object)
{
	$tran_chk = false;
	$send_chk = false;
	$error_msg = "";
	$result = false;

	$existsOrderBooking = existsOrderBooking($bookingId);

	//주문 없다면..
	if( $existsOrderBooking != 1 )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = "조회된 예약없음..";
	}
	else if($nPayProductOrderNumber == "" )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = "조회된 예약없음[nPayProductOrderNumber미확인]";
	}
	else
	{
		//트랜잭션 시작
		//오토커밋 해제
		mysql_query("SET AUTOCOMMIT=0");
		mysql_query("BEGIN");

		//부분사용,취소 취소
		if( $naver_json_object["status"] == "cancelled" )
		{

			//주문상태 확인
			$strSql = "";
			$strSql .= " select ";
			$strSql .= " status, barcode ";
			$strSql .= " from table ";
			$strSql .= " where naver_nPayProductOrderNumber = '".$nPayProductOrderNumber."' ";

			$order_detail_status = mysql_result(mysql_query($strSql), 0, 0);
			$order_detail_barcode = mysql_result(mysql_query($strSql), 0, 1);


			//기사용
			if( $order_detail_status == 1 )
			{
				$error_msg = "[][npay cancelled]업데이트 실패[부분 취소=>이미사용됨]";
			}
			//취소됨
//			else if( $order_detail_status == 2 )
//			{
//				$error_msg = "[]업데이트 실패[부분 취소=>이미취소됨]";
//			}
			//취소가능
			else
			{
				$order_detail_info = getOrderDetailInfo($order_detail_barcode);

				//주문상세 업데이트
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table ";
				$strSql .= " set  ";
				$strSql .= " status = 2 ";
				$strSql .= " , naver_status = '".$naver_json_object["status"]."' ";
				$strSql .= " , naver_cancelledDesc = '".$naver_json_object["cancelledDesc"]."' ";
				$strSql .= " , naver_cancelledBy = '".$naver_json_object["cancelledBy"]."' ";
				$strSql .= " , naver_refundPrice = '".$naver_json_object["refundPrice"]."' ";
				$strSql .= " , naver_refundRate = '".$naver_json_object["refundRate"]."' ";
				$strSql .= " , detail_cancel_date = '".date("Y-m-d H:i:s")."' ";
				$strSql .= " where naver_nPayProductOrderNumber = '".$nPayProductOrderNumber."' ";
				$strSql .= "  ";
				$strSql .= "  ";

				$result = mysql_query($strSql);

				$update_count = mysql_affected_rows();

				if( $update_count == 1 )
				{

					//주문상세 업데이트
					$strSql = "";
					$strSql .= "  ";
					$strSql .= " update table ";
					$strSql .= " set  ";
					$strSql .= " status = 2 ";
					$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
					$strSql .= " where naver_nPayProductOrderNumber = '".$nPayProductOrderNumber."' ";
					$strSql .= "  ";
					$strSql .= "  ";

					$result = mysql_query($strSql);

					if( $result )
					{
						$strSql = "";
						$strSql .= " select ";
						$strSql .= " count(*) ";
						$strSql .= " from table_block_ticket ";
						$strSql .= " where mem_code = '".$order_detail_info["mem_code"]."' ";

						$rsblock = mysql_query($strSql);
						$rscount = mysql_num_rows($rsblock);

						if ( $rscount > 0 )
						{
							$block_check = SlotBlockCheck_total($order_detail_info["ex_hours"], $order_detail_info["ex_minute"], $order_detail_info["ex_date"], $order_detail_info["mem_code"]);


							$result_block = (int)$block_check["block"] + $order_detail_info["ex_block"];

							
							$result_block_update = update_block_ticket_cancel($result_block, $order_detail_info["ex_hours"], $order_detail_info["ex_minute"], $order_detail_info["ex_date"], $order_detail_info["mem_code"]);

							if ( !$result_block_update )
							{
								$error_msg = "[]블럭 취소 업데이트 실패";
							}

						}
						//주문상태 업데이트
						$order_count = get_order_count("cancel", $bookingId);
						$order_detail_count = get_order_detail_count("cancel", $bookingId);

						if( $order_count == $order_detail_count )
						{
							$strSql = "";
							$strSql .= "  ";
							$strSql .= " update table ";
							$strSql .= " set ";
							$strSql .= " status = 5 ";
							$strSql .= " , cancel_date = '".date("Y-m-d H:i:s")."' ";
							$strSql .= " where 1=1 ";
							$strSql .= " and naver_bookingId = '".$bookingId."' ";

							$result = mysql_query($strSql);

							if ( $result )
							{
								$result = true;
							}
						}
						else
						{
							$result = true;
						}
					}

					if( $result )
					{
						$error_msg = "ok";
						$tran_chk = true;
					}
					else
					{
						$error_msg = "[]table 업데이트 실패[부분 취소]";
					}
				}
				else
				{
					$error_msg = "[]table 업데이트 실패[부분 취소]";
				}
			}
		}
		//부분사용,취소 이용완료
		else if( $naver_json_object["status"] == "completed" )
		{

			//주문상태 확인
			$strSql = "";
			$strSql .= " select ";
			$strSql .= " status ";
			$strSql .= " from table ";
			$strSql .= " where naver_nPayProductOrderNumber = '".$nPayProductOrderNumber."' ";

			$order_detail_status = mysql_result(mysql_query($strSql), 0, 0);

			//기사용
			if( $order_detail_status == 1 )
			{
				$error_msg = "[][npay completed]업데이트 실패[부분 이용완료=>이미사용됨]";
			}
			//취소됨
			else if( $order_detail_status == 2 )
			{
				$error_msg = "[][npay completed]업데이트 실패[부분 이용완료=>이미취소됨]";
			}
			//이용완료
			else
			{

				$error_msg = "ok";
				$tran_chk = true;
			}
		}



		//DB커밋
		if( $tran_chk )
		{
			mysql_query("COMMIT");
			$return_data["result_code"] = "0000";
			$return_data["result_msg"] = $error_msg;
		}
		else
		{
			mysql_query("ROLLBACK");
			$return_data["result_code"] = "9999";
			$return_data["result_msg"] = $error_msg;
		}

		mysql_query("ROLLBACK");

		//오토커밋 설정
		mysql_query("SET AUTOCOMMIT=1");
	}


	return $return_data;
}


//주문 카운트
function get_order_count($type, $bookingId)
{
	$count = 0;

	if( $type == "cancel" )
	{
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " select ";
		$strSql .= " buy_count ";
		$strSql .= " from table ";
		$strSql .= " where 1=1 ";
		$strSql .= " and naver_bookingId = '".$bookingId."' ";
		$strSql .= "  ";
		$strSql .= "  ";

		$count = mysql_result(mysql_query($strSql), 0, 0);
	}

	return $count;
}

//주문상세 카운트
function get_order_detail_count($type, $bookingId)
{
	$count = 0;

	if( $type == "cancel" )
	{
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " select ";
		$strSql .= " count(*) ";
		$strSql .= " from table ";
		$strSql .= " where 1=1 ";
		$strSql .= " and naver_bookingId = '".$bookingId."' ";
		$strSql .= " and status = 2 ";
		$strSql .= "  ";

		$count = mysql_result(mysql_query($strSql), 0, 0);
	}

	return $count;
}














//네이버 연동상품 상태조회
function status_naver_product_info($agencyBusinessId, $agencyBizItemId)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table.* ";
	$strSql .= " , table.businessTypeId ";
	$strSql .= " from table ";
	$strSql .= " inner join table on table.businessId = table.businessId ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.idx = ".$agencyBizItemId." ";
	$strSql .= "  ";
	$strSql .= "  ";
//echo $strSql;
//exit;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);


	//미조회 상품
	if( $rsCount == 0 )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = "미조회 상품";
		$return_data["list"] = array();
	}
	//중복된 상품
	else if( $rsCount > 1 )
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = "중복된 상품";
		$return_data["list"] = array();
	}
	else
	{
		$return_data["result_code"] = "0000";
		$return_data["result_msg"] = "ok";

		while($rows=mysql_fetch_assoc($rsList))
		{
			$return_data["naver_product_info"] = $rows;
			break;
		}
	}


	return $return_data;
}

//상품조회
function get_product_info($businessId, $bizItemId, $bizItemOptionId=NULL)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table.* ";
	$strSql .= " , '' as split ";
	$strSql .= " , table_detail.* ";
	$strSql .= " , table_detail.idx as product_detail_idx ";
	$strSql .= " from table_detail ";
	$strSql .= " inner join table on table_detail.product_code = table.product_code ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.naver_api_businessId = '".$businessId."' ";
	$strSql .= " and table_detail.naver_api_bizItemId = '".$bizItemId."' ";
	if( $bizItemOptionId != "" )
	{
		$strSql .= " and table_detail.naver_api_bizItem_optionId = '".$bizItemOptionId."' ";
	}

	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return["count"] = $rsCount;
	$return["product_info"] = array();

	if( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["product_info"] = $rows;
			break;
		}
	}

	return $return;
}


//상품상세pkg조회
function get_product_detail_pkg_info($product_detail_idx)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table.* ";
	$strSql .= " , table.idx as product_detail_pkg_idx ";

	$strSql .= " from table_detail ";
	$strSql .= " inner join table on table_detail.product_code = table.product_code ";
	$strSql .= " inner join table on table_detail.idx = table.product_detail_idx ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.product_detail_idx = '".$product_detail_idx."' ";


	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return["count"] = $rsCount;
	$return["product_detail_pkg_info"] = array();

	if( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["product_detail_pkg_info"][] = $rows;
		}
	}

	return $return;
}


//네이버 예약생성(네이버->대매사)
function insert_order_naver($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $product_info, $naver_product_info)
{
	$naver_json_object = json_decode($json, true);

	//트랜잭션 시작
	//오토커밋 해제
	mysql_query("SET AUTOCOMMIT=0");
	mysql_query("BEGIN");


	/* 네이버 업체유형 5:날짜선택형=>옵션미사용/12:회차형=>옵션사용 */
	//날짜선택형
	if( $naver_product_info["naver_product_info"]["businessTypeId"] == "5" )
	{
		//주문등록
		$result_data = insert_order_select($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $naver_json_object, $product_info, $naver_product_info);
	}
	//회차형
	else if( $naver_product_info["naver_product_info"]["businessTypeId"] == "12" )
	{
		//주문등록
		$result_data = insert_order_slot($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $naver_json_object, $product_info, $naver_product_info);
	}





	//주문등록성공
	if( $result_data["result"] )
	{
		$return_data["result_code"] = "0000";
		$return_data["result_msg"] = "ok";
		$return_data["bookingId"] = $result_data["bookingId"];

		mysql_query("COMMIT");

		//오토커밋 설정
		mysql_query("SET AUTOCOMMIT=1");
	}
	//주문등록실패
	else
	{
		$return_data["result_code"] = "9999";
		$return_data["result_msg"] = $result_data["msg"];

		$result = mysql_query("ROLLBACK");

		//오토커밋 설정
		mysql_query("SET AUTOCOMMIT=1");
	}


	return $return_data;

}

//주문등록-날짜선택형
function insert_order_select($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $naver_json_object, $product_info, $naver_product_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$result = false;
	$order_info = array();

	//주문중복 체크
	$existsOrderBooking = existsOrderBooking($naver_json_object["bookingId"]);

	//
	if( $existsOrderBooking != 0 )
	{
		$return["result"] = false;
		$return["msg"] = "중복된 예약";
	}
	else
	{
		//주문번호 생성
		$order_num = create_order_num($naver_json_object["confirmedDateTime"], $naver_json_object["phone"]);

		//주문 기본 정보
		$order_info["order_num"] = $order_num;
		$order_info["naver_bookingId"] = $naver_json_object["bookingId"];	//예약번호(주문번호)
		$order_info["mem_code"] = $product_info["mem_code"];	//
		$order_info["channel_code"] = C_NAVER_CHANNEL_CODE;	//
	//	$order_info["product_code"] = $product_info["product_code"];	//

		$order_info["status"] = 0;	//상태(결제전)
		$order_info["buy_name"] = $naver_json_object["name"];	//구매자명
		$order_info["buy_hp"] = aes256encode($naver_json_object["phone"]);	//구매자전화번호
		$order_info["buy_hp_prefix"] = right($naver_json_object["phone"], 4);	//구매자전화번호뒤4자리
		$order_info["buy_count"] = $naver_json_object["details"]["count"];	//구매수량
		$order_info["buy_date"] = $naver_json_object["confirmedDateTime"];	//예약확정일시(결제완료일시)
		$order_info["sale_price"] = $naver_json_object["details"]["price"]; //총 구매가격
		$order_info["actual_sale_price"] = $naver_json_object["details"]["price"]; //총 구매가격

		$order_info["start_date"] = $naver_json_object["startDate"];	//예약일
		$order_info["end_date"] = $naver_json_object["endDate"];	//예약일


		//주문 네이버 정보
		$order_info["naver_businessTypeId"] = $naver_product_info["businessTypeId"];	//네이버 업체유형
		$order_info["naver_businessId"] = $businessId;	//네이버 업체ID
		$order_info["naver_businessId_agencyKey"] = $agencyBusinessId;	//네이버 대행사 업체ID
		$order_info["naver_bizItemId"] = $bizItemId;	//네이버 상품ID
		$order_info["naver_bizItemId_agencyKey"] = $agencyBizItemId;	//네이버 대행사 상품ID
		$order_info["naver_regDateTime"] = $naver_json_object["regDateTime"];	//예약등록일시
		$order_info["naver_confirmedDateTime"] = $naver_json_object["confirmedDateTime"];	//예약확정일시
		$order_info["naver_isNpayUsed"] = boolean_check($naver_json_object["details"]["isNpayUsed"]);	//네이버 페이사용여부 true/false
		$order_info["naver_isPartialCancelUsed"] = boolean_check($naver_json_object["details"]["isPartialCancelUsed"]);	//부분취소가능여부 true/false
		$order_info["naver_json"] = $json; //네이버 예약생성 json


		$result_order = insert_order_select_query($order_info);

		if( $result_order["result"] )
		{
			//주문상세등록 table
			$result_order_detail = insert_order_detail_select($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info);


			if( $result_order_detail["result"] )
			{
				$return["result"] = true;
				$return["msg"] = "ok";
				$return["bookingId"] = $naver_json_object["bookingId"];
			}
			else
			{
				$return["result"] = false;
				$return["msg"] = $result_order_detail["msg"];
			}
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = $result_order["msg"];
		}
	}
	return $return;
}

//주문등록-회차형
function insert_order_slot($businessId, $bizItemId, $agencyBusinessId, $agencyBizItemId, $json, $naver_json_object, $product_info, $naver_product_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$result = false;
	$order_info = array();

	//주문중복 체크
	$existsOrderBooking = existsOrderBooking($naver_json_object["bookingId"]);

	//
	if( $existsOrderBooking != 0 )
	{
		$return["result"] = false;
		$return["msg"] = "중복된 예약";
	}
	else
	{

		//주문번호 생성
		$order_num = create_order_num($naver_json_object["confirmedDateTime"], $naver_json_object["phone"]);

		//주문 기본 정보
		$order_info["order_num"] = $order_num;
		$order_info["naver_bookingId"] = $naver_json_object["bookingId"];	//예약번호(주문번호)
		$order_info["mem_code"] = $product_info["mem_code"];	//
		$order_info["channel_code"] = C_NAVER_CHANNEL_CODE;	//
	//	$order_info["product_code"] = $product_info["product_code"];	//

		$order_info["status"] = 0;	//상태(결제전)
		$order_info["buy_name"] = $naver_json_object["name"];	//구매자명
		$order_info["buy_hp"] = aes256encode($naver_json_object["phone"]);	//구매자전화번호
		$order_info["buy_hp_prefix"] = right($naver_json_object["phone"], 4);	//구매자전화번호뒤4자리
		$order_info["buy_count"] = $naver_json_object["details"]["count"];	//구매수량
		$order_info["buy_date"] = $naver_json_object["confirmedDateTime"];	//예약확정일시(결제완료일시)
		$order_info["sale_price"] = $naver_json_object["details"]["price"]; //총 구매가격



		//주문 네이버 정보
		$order_info["naver_businessTypeId"] = $naver_product_info["businessTypeId"];	//네이버 업체유형
		$order_info["naver_businessId"] = $businessId;	//네이버 업체ID
		$order_info["naver_businessId_agencyKey"] = $agencyBusinessId;	//네이버 대행사 업체ID
		$order_info["naver_bizItemId"] = $bizItemId;	//네이버 상품ID
		$order_info["naver_bizItemId_agencyKey"] = $agencyBizItemId;	//네이버 대행사 상품ID
		$order_info["naver_regDateTime"] = $naver_json_object["regDateTime"];	//예약등록일시
		$order_info["naver_confirmedDateTime"] = $naver_json_object["confirmedDateTime"];	//예약확정일시
		$order_info["naver_isNpayUsed"] = boolean_check($naver_json_object["details"]["isNpayUsed"]);	//네이버 페이사용여부 true/false
		$order_info["naver_isPartialCancelUsed"] = boolean_check($naver_json_object["details"]["isPartialCancelUsed"]);	//부분취소가능여부 true/false

		//주소 네이버 정보
		$order_info["naver_addressName"] = $naver_json_object["details"]["addressName"];
		$order_info["naver_baseAddress"] = $naver_json_object["details"]["baseAddress"];
		$order_info["naver_detailAddress"] = $naver_json_object["details"]["detailAddress"];
		$order_info["naver_hash"] = $naver_json_object["details"]["hash"];
		$order_info["naver_isRecentlyUsed"] = $naver_json_object["details"]["isRecentlyUsed"];
		$order_info["naver_receiverName"] = $naver_json_object["details"]["receiverName"];
		$order_info["naver_roadNameYn"] = $naver_json_object["details"]["roadNameYn"];
		$order_info["naver_service"] = $naver_json_object["details"]["service"];
		$order_info["naver_telNo1"] = $naver_json_object["details"]["telNo1"];
		$order_info["naver_zipCode"] = $naver_json_object["details"]["zipCode"];


		$order_info["naver_json"] = $json; //네이버 예약생성 json

		//주문 네이버 회차형 정보
		$order_info["naver_scheduleId"] = $naver_json_object["scheduleId"];	//네이버 스케줄ID
		$order_info["naver_minute"] = get_minute_format($naver_json_object["minute"]);	//네이버 시간(930 ==> 15:30)
		$order_info["naver_shippingStatus"] = $naver_json_object["shippingStatus"];	//네이버 배송상태




		$result_order = insert_order_slot_query($order_info);


		if( $result_order["result"] )
		{
			//부가옵션등록
			$result_extra_order = insert_extra_order_slot($order_info, $naver_json_object);
			

			if( $result_extra_order["result"] )
			{
				//주문상세등록
				$result_order_detail = insert_order_detail_slot($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info);
				
//				exit;

				if( $result_order_detail["result"] )
				{
					$return["result"] = true;
					$return["msg"] = "ok";
					$return["bookingId"] = $naver_json_object["bookingId"];

				}
				else
				{
					$return["result"] = false;
					$return["msg"] = $result_order_detail["msg"];
				}
			}
			else
			{
				$return["result"] = false;
				$return["msg"] = $result_extra_order["msg"];
			}
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = $result_order["msg"];
		}
	}

	return $return;
}


//주문등록-날짜선택형 query
function insert_order_select_query($order_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	//주문중복체크
	$existsOrder = existsOrder($order_info["order_num"]);

	if( $existsOrder == 0 )
	{
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " insert into table ";
		$strSql .= " ( ";
		$strSql .= " ) values ( ";
		$strSql .= "   '".$order_info["order_num"]."' ";
		$strSql .= " , 2 ";	//0:무인발권기/1:포스/2:채널
		$strSql .= " ,  ".$order_info["mem_code"]." ";
		$strSql .= " ,  ".$order_info["channel_code"]." ";
		$strSql .= " , '".$order_info["buy_name"]."' ";
		$strSql .= " , '".$order_info["buy_hp"]."' ";
		$strSql .= " , '".$order_info["buy_hp_prefix"]."' ";
		$strSql .= " ,  ".$order_info["buy_count"]." ";
		$strSql .= " ,  ".$order_info["status"]." ";
		$strSql .= " , '".$order_info["buy_date"]."' ";
		$strSql .= " , '".$order_info["sale_price"]."' ";
		$strSql .= " , '".$order_info["actual_sale_price"]."' ";
		$strSql .= " , '".$order_info["naver_businessTypeId"]."' ";
		$strSql .= " , '".$order_info["naver_bookingId"]."' ";
		$strSql .= " , '".$order_info["naver_businessId"]."' ";
		$strSql .= " , '".$order_info["naver_businessId_agencyKey"]."' ";
		$strSql .= " , '".$order_info["naver_bizItemId"]."' ";
		$strSql .= " , '".$order_info["naver_bizItemId_agencyKey"]."' ";
		$strSql .= " , '".$order_info["naver_regDateTime"]."' ";
		$strSql .= " , '".$order_info["naver_confirmedDateTime"]."' ";
		$strSql .= " , '".$order_info["naver_isNpayUsed"]."' ";
		$strSql .= " , '".$order_info["naver_isPartialCancelUsed"]."' ";
		$strSql .= " , '".$order_info["naver_json"]."' ";
		$strSql .= " ) ";


		$result = mysql_query($strSql);

		if( $result )
		{
			$return["result"] = true;
			$return["msg"] = "날짜-주문등록 성공";
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]날짜형-주문등록 실패[order insert]";
		}
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]날짜형-주문등록 실패[exist order][".$order_info["order_num"]."]";
	}

	return $return;
}



//주문등록-회차형 query
function insert_order_slot_query($order_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	//주문중복체크
	$existsOrder = existsOrder($order_info["order_num"]);

	if( $existsOrder == 0 )
	{
		$strSql = "";
		$strSql .= "  ";
		$strSql .= " insert into table ";
		$strSql .= " ( ";
		$strSql .= " ) values ( ";
		$strSql .= "   '".$order_info["order_num"]."' ";
		$strSql .= " , 2 ";
		$strSql .= " ,  ".$order_info["mem_code"]." ";
		$strSql .= " ,  ".$order_info["channel_code"]." ";
		$strSql .= " , '".$order_info["buy_name"]."' ";
		$strSql .= " , '".$order_info["buy_hp"]."' ";
		$strSql .= " , '".$order_info["buy_hp_prefix"]."' ";
		$strSql .= " ,  ".$order_info["buy_count"]." ";
		$strSql .= " ,  ".$order_info["status"]." ";
		$strSql .= " , '".$order_info["buy_date"]."' ";
		$strSql .= " , '".$order_info["naver_businessTypeId"]."' ";
		$strSql .= " , '".$order_info["naver_bookingId"]."' ";
		$strSql .= " , '".$order_info["naver_businessId"]."' ";
		$strSql .= " , '".$order_info["naver_businessId_agencyKey"]."' ";
		$strSql .= " , '".$order_info["naver_bizItemId"]."' ";
		$strSql .= " , '".$order_info["naver_bizItemId_agencyKey"]."' ";
		$strSql .= " , '".$order_info["naver_regDateTime"]."' ";
		$strSql .= " , '".$order_info["naver_confirmedDateTime"]."' ";
		$strSql .= " , '".$order_info["naver_isNpayUsed"]."' ";
		$strSql .= " , '".$order_info["naver_isPartialCancelUsed"]."' ";
		$strSql .= " , '".$order_info["naver_scheduleId"]."' ";
		$strSql .= " , '".$order_info["naver_addressName"]."' ";
		$strSql .= " , '".$order_info["naver_baseAddress"]."' ";
		$strSql .= " , '".$order_info["naver_detailAddress"]."' ";
		$strSql .= " , '".$order_info["naver_hash"]."' ";
		$strSql .= " , '".$order_info["naver_isRecentlyUsed"]."' ";
		$strSql .= " , '".$order_info["naver_receiverName"]."' ";
		$strSql .= " , '".$order_info["naver_roadNameYn"]."' ";
		$strSql .= " , '".$order_info["naver_service"]."' ";
		$strSql .= " , '".$order_info["naver_telNo1"]."' ";
		$strSql .= " , '".$order_info["naver_zipCode"]."' ";
		$strSql .= " , '".$order_info["naver_shippingStatus"]."' ";
		$strSql .= " , '".$order_info["naver_json"]."' ";
		$strSql .= " ) ";

//		echo $strSql . "<br>";
//		exit;

		$result = mysql_query($strSql);

		if( $result )
		{
			$return["result"] = true;
			$return["msg"] = "주문등록 성공";
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]회차형-주문등록 실패[order insert]";
		}
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]회차형-주문등록 실패[exist order][".$order_info["order_num"]."]";
	}

	return $return;
}





//부가옵션등록
function insert_extra_order_slot($order_info, $naver_json_object)
{
	//부가옵션 등록
	$return_data["result"] = true;


	if( count($naver_json_object["details"]["options"]) > 0 )
	{
		//부가옵션상세 등록
		$extra_option_detail_info["order_num"] = $order_info["order_num"];
		$extra_option_detail_info["bookingId"] = $order_info["naver_bookingId"];

		foreach($naver_json_object["details"]["options"] as $k=>$v)
		{
			//네이버 옵션
			$extra_option_detail_info["optionId"] = $v["optionId"];
			$extra_option_detail_info["bookingCount"] = $v["bookingCount"];
			$extra_option_detail_info["price"] = $v["price"];

			$result = insert_extra_order_query_slot($extra_option_detail_info);

			if( !$result["result"] )
			{
				$return_data["result"] = false;
				$return_data["msg"] = "[]회차형-부가옵션 등록실패";
				break;
			}
		}
	}

	return $return_data;
}

//부가옵션 등록
function insert_extra_order_query_slot($extra_option_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " insert into table ";
	$strSql .= " ( ";
	$strSql .= " ) ";
	$strSql .= " values ";
	$strSql .= " ( ";
	$strSql .= " '".$extra_option_info["order_num"]."' ";
	$strSql .= " , '".$extra_option_info["bookingId"]."' ";
	$strSql .= " , '".$extra_option_info["optionId"]."' ";
	$strSql .= " , '".$extra_option_info["bookingCount"]."' ";
	$strSql .= " , '".$extra_option_info["price"]."' ";
	$strSql .= " ) ";


	$result = mysql_query($strSql);

	if( $result )
	{
		$return["result"] = true;
		$return["msg"] = "부가옵션등록 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]회차형-부가옵션등록 실패[extra_option insert]";
	}

	return $return;
}


//부가옵션상세 등록
function insert_extra_order_detail_query_slot($extra_option_detail_info)
{
}











//주문중복
function existsOrder($order_num)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " * ";
	$strSql .= " from table ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.order_num = '".$order_num."' ";
	//echo $strSql;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	return $rsCount;
}





//주문상세등록-날짜선택형
function insert_order_detail_select($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$order_detail_info = array();

	foreach($naver_json_object["details"]["prices"] as $k=>$v)
	{
		//상품상세정보
		$product_detail_info = get_product_detail_info($businessId, $bizItemId, $v["priceId"]);


		if( $product_detail_info["result_code"] == "0000" )
		{

			unset($order_detail_info);

			//바코드
			$barcode = set_inner_barcode("C", C_NAVER_CHANNEL_CODE);

			//유효기간
			$valid_start_date = "";
			$valid_end_date = "";



			//주문상세정보
			$order_detail_info["order_num"] = $order_num; //주문번호
			$order_detail_info["mem_code"] = $product_info["mem_code"];	//시설코드
			$order_detail_info["channel_code"] = C_NAVER_CHANNEL_CODE;	//채널코드
			$order_detail_info["product_code"] = $product_detail_info["list"]["product_code"];	//상품코드
			$order_detail_info["product_detail_code"] = $product_detail_info["list"]["product_detail_code"];	//기초상품코드
			$order_detail_info["pos_id"] = $product_detail_info["list"]["pos_id"];	//포스아이디
			$order_detail_info["ticket_option_code"] = $product_detail_info["list"]["ticket_option_code"];	//권종코드:대인/소인....
			$order_detail_info["option_name"] = $product_detail_info["list"]["option_name"];	//옵션명
			$order_detail_info["barcode"] = $barcode;	//바코드
			$order_detail_info["count"] = $v["bookingCount"];	//수량
			$order_detail_info["valid_start_date"] = $valid_start_date;	//유효기간 시작일
			$order_detail_info["valid_end_date"] = $valid_end_date;	//유효기간 종료일
			$order_detail_info["sale_price"] = $v["normalPrice"];	//정상금액
			$order_detail_info["dis_price"] = $v["price"];	//판매금액
			$order_detail_info["bill_price"] = $product_detail_info["list"]["bill_price"];	//채널정산금액
			$order_detail_info["remit_price"] = $product_detail_info["list"]["remit_price"];	//시설정산금액

			$order_detail_info["product_detail_idx"] = $product_detail_info["list"]["idx"];	//상품상세idx
			$order_detail_info["option_mapping_idx"] = $product_detail_info["list"]["option_mapping_idx"];	//맵핑idx..



			//네이버 주문상세정보
			$order_detail_info["naver_bookingId"] = $order_info["naver_bookingId"];	//네이버 예약ID
			$order_detail_info["naver_priceId"] = $product_detail_info["list"]["priceId"];	//네이버 가격ID
			$order_detail_info["naver_priceId_agencyKey"] = $product_detail_info["list"]["naver_api_bizItem_priceId"];	//네이버 대행사 가격ID
			$order_detail_info["naver_price"] = $v["price"];	//네이버 단가
			$order_detail_info["naver_normalPrice"] = $v["normalPrice"];	//네이버 정상단가
			$order_detail_info["naver_name"] = $v["name"];	//네이버 옵션명

			//네이버 주문정보
			$order_detail_info["naver_businessId"] = $order_info["naver_businessId"];	//네이버 업체ID
			$order_detail_info["naver_businessId_agencyKey"] = $order_info["naver_businessId_agencyKey"];	//네이버 대행사 업체ID
			$order_detail_info["naver_bizItemId"] = $order_info["naver_bizItemId"];	//네이버 상품ID
			$order_detail_info["naver_bizItemId_agencyKey"] = $order_info["naver_bizItemId_agencyKey"];	//네이버 대행사 상품ID
			$order_detail_info["naver_regDateTime"] = $order_info["naver_regDateTime"];	//예약등록일시
			$order_detail_info["naver_confirmedDateTime"] = $order_info["naver_confirmedDateTime"];	//예약확정일시
			$order_detail_info["naver_isNpayUsed"] = $order_info["naver_isNpayUsed"];	//네이버 페이사용여부 true/false
			$order_detail_info["naver_isPartialCancelUsed"] = $order_info["naver_isPartialCancelUsed"];	//부분취소가능여부 true/false
			$order_detail_info["naver_json"] = $json; //네이버 예약생성 json

//echo_json_encode($order_detail_info);
//exit;

			$result_order_detail = insert_order_detail_select_query($order_detail_info);


			if( $result_order_detail )
			{
				//주문상세pkg등록 table
				$result_pos_detail = insert_pos_detail_select($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info, $order_detail_info, $product_detail_info);


				//주문상세pkg 등록성공
				if( $result_pos_detail["result"] )
				{
					$return["result"] = true;
					$return["msg"] = "ok";
				}
				//주문상세pkg 등록실패
				else
				{
					$return["result"] = false;
					$return["msg"] = $result_pos_detail["msg"];
					break;
				}
			}
			else
			{
				//주문상세 등록실패
				$return["result"] = false;
				$return["msg"] = $result_order_detail["msg"];
				break;
			}



		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]상품상세조회-날짜형 실패[".$businessId."|".$bizItemId."|".$v["priceId"]."]";
			break;
		}
	}

	return $return;
}






//주문상세pkg등록-날짜선택형
function insert_pos_detail_select($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info, $order_detail_info, $product_detail_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$order_pos_info = array();
/*
$order_pos_info[""] = $;
*/


	//상품상세pkg정보
	$product_detail_pkg_info_array = get_product_detail_pkg_info($product_detail_info["list"]["idx"]);	//상품상세idx;

	if( $product_detail_pkg_info_array["count"] == 0 )
	{
		//상품조회안됨...
		$return["result"] = false;
		$return["msg"] = "상품상세pkg 미조회";
	}
	else
	{
		$product_detail_pkg_info = $product_detail_pkg_info_array["product_detail_pkg_info"];

		//pkg만큼 pos_detail에 주문 등록
		foreach($product_detail_pkg_info as $k_pkg=>$v_pkg)
		{
			unset($order_pos_info);

			//유효기간
			$valid_start_date = "";
			$valid_end_date = "";


			$confirm_status = 1;
			$confirm_date = date("Y-m-d H:i:s");

			//유효시작일 기간권
			if( $v_pkg["valid_start_type"] == 0 )
			{
				$valid_start_date = date("Y-m-d", strtotime(  date("Y-m-d" ) . $v_pkg["valid_start_count"]." days" ));
			}
			//유효시작일 날짜지정
			else if( $v_pkg["valid_start_type"] == 1 )
			{
				$valid_start_date = $v_pkg["valid_start_date"];
			}
			//유효시작일 예약일
			else if( $v_pkg["valid_start_type"] == 2 )
			{
				//채널옵션의 예약일 협의(옵션의 예약일의 구조를 정의해야함) 필요....
				$valid_start_date = $v_pkg["valid_start_date"];
			}

			//유효시작일 기간권
			if( $v_pkg["valid_end_type"] == 0 )
			{
				$valid_end_date = date("Y-m-d", strtotime(  date("Y-m-d" ) . $v_pkg["valid_end_count"]." days" ));
			}
			//유효시작일 날짜지정
			else if( $v_pkg["valid_end_type"] == 1 )
			{
				$valid_end_date = $v_pkg["valid_end_date"];
			}
			//유효시작일 예약일
			else if( $v_pkg["valid_end_type"] == 2 )
			{
				//채널옵션의 예약일 협의(옵션의 예약일의 구조를 정의해야함) 필요....
				$valid_end_date = $v_pkg["valid_end_date"];
			}


			//주문상세정보
			$order_pos_info["order_num"] = $order_detail_info["order_num"]; //주문번호
			$order_pos_info["mem_code"] = $order_detail_info["mem_code"]; //시설코드
			$order_pos_info["channel_code"] = $order_detail_info["channel_code"]; //채널코드
			$order_pos_info["barcode"] = $order_detail_info["barcode"];	//바코드
			$order_pos_info["count"] = $order_detail_info["count"];	//수량
			$order_pos_info["option_mapping_idx"] = $order_detail_info["option_mapping_idx"];	//맵핑idx..

			//네이버 주문상세정보
			$order_pos_info["naver_bookingId"] = $order_detail_info["naver_bookingId"];	//네이버 예약ID

			$order_pos_info["product_code"] = $v_pkg["product_code"];	//상품코드
			$order_pos_info["product_detail_code"] = $v_pkg["product_detail_code"];	//기초상품코드
			$order_pos_info["pos_id"] = $v_pkg["pos_id"];	//포스아이디
			$order_pos_info["ticket_option_code"] = $v_pkg["ticket_option_code"];	//권종코드:대인/소인....
			$order_pos_info["sale_price"] = $v_pkg["sale_price"];	//정상금액
			$order_pos_info["dis_price"] = $v_pkg["dis_price"];	//판매금액
			$order_pos_info["bill_price"] = $v_pkg["bill_price"];	//채널정산금액
			$order_pos_info["remit_price"] = $v_pkg["remit_price"];	//시설정산금액

			$order_pos_info["product_detail_idx"] = $v_pkg["product_detail_idx"];	//상품상세idx

			$order_pos_info["product_detail_pkg_idx"] = $v_pkg["product_detail_pkg_idx"];	//상품상세pkgidx
			$order_pos_info["pkg_type"] = $v_pkg["pkg_type"];	//패키지 타입
			$order_pos_info["enter_id"] = $v_pkg["enter_id"];	//입장소아이디
			$order_pos_info["option_name"] = $v_pkg["option_name"];	//옵션명

			$order_pos_info["valid_start_date"] = $valid_start_date;	//유효기간 시작일
			$order_pos_info["valid_end_date"] = $valid_end_date;	//유효기간 종료일

			$order_pos_info["ex_block"] = "1";



//echo_json_encode($order_pos_info);
//exit;

			$result_pos_detail = insert_pos_detail_select_query($order_pos_info);



			//주문상세 등록성공
			if( $result_pos_detail["result"] )
			{
				$return["result"] = true;
				$return["msg"] = "ok";
			}
			//주문상세 등록실패
			else
			{
				$return["result"] = false;
				$return["msg"] = $result_pos_detail["msg"];
				break;
			}
		}
	}

	return $return;
}






//주문상세등록-회차형
function insert_order_detail_slot($order_num, $json, $businessId, $bizItemId, $naver_json_object, $product_info, $order_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$order_detail_info = array();
/*
$order_detail_info[""] = $;
*/

	foreach($naver_json_object["details"]["prices"] as $k=>$v)
	{
		//상품상세정보
		$product_detail_info = get_product_detail_info_slot($businessId, $bizItemId, $v["priceId"], $naver_json_object["minute"]);


		if( $product_detail_info["result_code"] == "0000" )
		{

			unset($order_detail_info);

			$minute = get_minute_format($naver_json_object["minute"]);



			if ( $product_detail_info["list"]["ex_block"] == "S" )
			{
				$ex_block = '1';
			}
			else if ( $product_detail_info["list"]["ex_block"] == "D" )
			{
				$ex_block = '2';
			}
			else if ( $product_detail_info["list"]["ex_block"] == "O" )
			{
				$ex_block = '5';
			}
			else
			{
				$ex_block = '1';
			}



			//exhoure, ex_minute 나누기 위해
			$minute_explode = explode(":", $minute);

			//바코드
			$barcode = set_inner_barcode("C", C_NAVER_CHANNEL_CODE);

			//유효기간(예약일자로)
			$valid_start_date = $naver_json_object["startDate"];
			$valid_end_date = $naver_json_object["endDate"];
			$ex_hour = $minute_explode[0];
			$ex_minute = $minute_explode[1];
			$mem_code = $product_detail_info["list"]["mem_code"];


			$order_block_check = SlotBlockCheck_total($ex_hour, $ex_minute, $valid_start_date, $mem_code);

			//주문 블록이 등록된 불럭과 비교
			if ( $order_block_check["block"] >= $ex_block )
			{

				//주문상세정보
				$order_detail_info["order_num"] = $order_num; //주문번호
				$order_detail_info["mem_code"] = $product_info["mem_code"];	//시설코드
				$order_detail_info["channel_code"] = C_NAVER_CHANNEL_CODE;	//채널코드
				$order_detail_info["product_code"] = $product_detail_info["list"]["product_code"];	//상품코드
				$order_detail_info["product_detail_code"] = $product_detail_info["list"]["product_detail_code"];	//기초상품코드
				$order_detail_info["pos_id"] = $product_detail_info["list"]["pos_id"];	//포스아이디
				$order_detail_info["ticket_option_code"] = $product_detail_info["list"]["ticket_option_code"];	//권종코드:대인/소인....
				$order_detail_info["option_name"] = $product_detail_info["list"]["option_name"];	//옵션명
				$order_detail_info["barcode"] = $barcode;	//바코드
				$order_detail_info["count"] = $v["bookingCount"];	//수량
				$order_detail_info["valid_start_date"] = $valid_start_date;	//유효기간 시작일
				$order_detail_info["valid_end_date"] = $valid_end_date;	//유효기간 종료일
				$order_detail_info["sale_price"] = $v["normalPrice"];	//정상금액
				$order_detail_info["dis_price"] = $v["price"];	//판매금액
				$order_detail_info["bill_price"] = $product_detail_info["list"]["bill_price"];	//채널정산금액
				$order_detail_info["remit_price"] = $product_detail_info["list"]["remit_price"];	//시설정산금액

				$order_detail_info["product_detail_idx"] = $product_detail_info["list"]["idx"];	//상품상세idx
				$order_detail_info["option_mapping_idx"] = $product_detail_info["list"]["option_mapping_idx"];	//맵핑idx..
				$order_detail_info["product_detail_pkg_idx"] = $product_detail_info["list"]["detail_pkg_idx"];


				//네이버 주문상세정보
				$order_detail_info["naver_bookingId"] = $order_info["naver_bookingId"];	//네이버 업체ID
				$order_detail_info["naver_priceId"] = $product_detail_info["list"]["priceId"];	//네이버 가격ID
				$order_detail_info["naver_priceId_agencyKey"] = $product_detail_info["list"]["naver_api_bizItem_priceId"];	//네이버 대행사 가격ID
				$order_detail_info["naver_price"] = $v["price"];	//네이버 단가
				$order_detail_info["naver_normalPrice"] = $v["normalPrice"];	//네이버 정상단가
				$order_detail_info["naver_name"] = $v["name"];	//네이버 옵션명
				$order_detail_info["naver_minute"] = get_minute_format($order_info["minute"]);	//네이버 회차시간

				//네이버 주문정보
				$order_detail_info["naver_businessId"] = $order_info["naver_businessId"];	//네이버 업체ID
				$order_detail_info["naver_businessId_agencyKey"] = $order_info["naver_businessId_agencyKey"];	//네이버 대행사 업체ID
				$order_detail_info["naver_bizItemId"] = $order_info["naver_bizItemId"];	//네이버 상품ID
				$order_detail_info["naver_bizItemId_agencyKey"] = $order_info["naver_bizItemId_agencyKey"];	//네이버 대행사 상품ID
				$order_detail_info["naver_regDateTime"] = $order_info["naver_regDateTime"];	//예약등록일시
				$order_detail_info["naver_confirmedDateTime"] = $order_info["naver_confirmedDateTime"];	//예약확정일시
				$order_detail_info["naver_isNpayUsed"] = $order_info["naver_isNpayUsed"];	//네이버 페이사용여부 true/false
				$order_detail_info["naver_isPartialCancelUsed"] = $order_info["naver_isPartialCancelUsed"];	//부분취소가능여부 true/false
				$order_detail_info["naver_json"] = $json; //네이버 예약생성 json
				$order_detail_info["ex_block"] = $ex_block;			
				$order_detail_info["hours"] = $minute_explode[0];
				$order_detail_info["minute"] = $minute_explode[1];


				$result_blcok = $order_block_check["block"] - $ex_block;


				$result_order_detail = insert_order_detail_slot_query($order_detail_info);



				if( $result_order_detail )
				{
					$result_pos_detail = insert_pos_detail_slot_query($order_detail_info);


					//주문상세 등록성공
					if( $result_pos_detail["result"] )
					{	
						$result_block_check = update_block_ticket($result_blcok,$ex_hour, $ex_minute, $valid_start_date, $mem_code);

						if ( $result_block_check["result"] )
						{

							$return["result"] = true;
							$return["msg"] = "ok";
						}
						//블럭 업데이트 실패
						else
						{
							$return["result"] = false;
							$return["msg"] = "[] 블록 업데이트 실패".$result_block_check["msg"];
						}
					}
					//주문상세 등록실패
					else
					{
						$return["result"] = false;
						$return["msg"] = $result_pos_detail["msg"];
						break;
					}
				}

				else
				{
					//주문상세 등록실패
					$return["result"] = false;
					$return["msg"] = $result_order_detail["msg"];
					break;
				}
				
			}
			else
			{
				$return["result"] = false;
				$return["msg"] = "[qpos_systme]블록이 없습니다.";
				break;

			}
//			exit;


		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]상품상세조회-회차형 실패[".$businessId."|".$bizItemId."|".$v["priceId"]."]";
			break;
		}

	}

	return $return;
}


//주문상세등록-날짜선택형 query
function insert_order_detail_select_query($order_detail_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	//주문상세중복체크
	$existsOrder = existsOrderDetail($order_detail_info["barcode"]);

	if( $existsOrder == 0 )
	{
		$strSql = "";
		$strSql .= " insert into table ( ";
		$strSql .= " ) values ( ";
		$strSql .= "  ";
		$strSql .= "   '".$order_detail_info["order_num"]."' ";
		$strSql .= " ,  ".$order_detail_info["mem_code"]." ";
		$strSql .= " ,  ".C_NAVER_CHANNEL_CODE." ";
		$strSql .= " ,  ".$order_detail_info["product_code"]." ";
		$strSql .= " ,  ".$order_detail_info["product_detail_code"]." ";
		$strSql .= " , '".$order_detail_info["pos_id"]."' ";
		$strSql .= " , '".$order_detail_info["ticket_option_code"]."' ";
		$strSql .= " , '".$order_detail_info["option_name"]."' ";
		$strSql .= " , '".$order_detail_info["barcode"]."' ";
		$strSql .= " ,  ".$order_detail_info["count"]." ";
		$strSql .= " , '".$order_detail_info["valid_start_date"]."' ";
		$strSql .= " , '".$order_detail_info["valid_end_date"]."' ";
		$strSql .= " ,  ".$order_detail_info["sale_price"]." ";
		$strSql .= " ,  ".$order_detail_info["dis_price"]." ";
		$strSql .= " ,  ".$order_detail_info["bill_price"]." ";
		$strSql .= " ,  ".$order_detail_info["remit_price"]." ";
		$strSql .= " ,  ".$order_detail_info["product_detail_idx"]." ";
		$strSql .= " ,  ".$order_detail_info["option_mapping_idx"]." ";

		$strSql .= " , '".$order_detail_info["naver_businessId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_businessId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bizItemId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bizItemId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_regDateTime"]."' ";
		$strSql .= " , '".$order_detail_info["naver_confirmedDateTime"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bookingId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_priceId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_priceId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_price"]."' ";
		$strSql .= " , '".$order_detail_info["naver_normalPrice"]."' ";
		$strSql .= " , '".$order_detail_info["naver_name"]."' ";
		$strSql .= " , '".$order_detail_info["naver_json"]."' ";

		$strSql .= " ) ";


		$result = mysql_query($strSql);


		if( $result )
		{
			$return["result"] = true;
			$return["msg"] = "주문등록 성공";
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]주문등록-날짜선택형 실패[order_detail][".$strSql."]";
		}
	}

	return $return;
}

function update_block_deep($result_blcok,$ex_hour, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_deep ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

//	echo $strSql;


	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패(딥스테이션)[".$strSql."]";
	}

	return $return;

}

function update_block_samhak($result_blcok,$ex_hour, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_samhak ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

//	echo $strSql;


	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패(삼학도크루즈)[".$strSql."]";
	}

	return $return;

}

function SlotBlockCheck_samhak($ex_hours, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " block ";
	$strSql .= " from ";
	$strSql .= " table_block_samhak ";
	$strSql .= " where 1=1 ";
	$strSql .= " and date_format(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hours."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

//	echo $strSql;

//	echo $strSql;
//	exit;


	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$rtnArray = $rows;
		break;
	}

	return $rtnArray;
	
}

function SlotBlockCheck_deep($ex_hours, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " block ";
	$strSql .= " from ";
	$strSql .= " table_block_deep ";
	$strSql .= " where 1=1 ";
	$strSql .= " and date_format(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hours."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

//	echo $strSql;

//	echo $strSql;
//	exit;


	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$rtnArray = $rows;
		break;
	}

	return $rtnArray;
	
}

function insert_pos_detail_slot_query($order_detail_info)
{

	$return["result"] = false;
	$return["msg"] = "";

	$strSql = "";
	$strSql .= " insert into table ( ";
	$strSql .= "  ";
	$strSql .= " ) values ( ";
	$strSql .= "  ";
	$strSql .= "   '".$order_detail_info["order_num"]."' ";
	$strSql .= " ,  ".$order_detail_info["mem_code"]." ";
	$strSql .= " ,  ".$order_detail_info["channel_code"]." ";
	$strSql .= " ,  ".$order_detail_info["product_code"]." ";
	$strSql .= " ,  ".$order_detail_info["product_detail_code"]." ";
	$strSql .= " , '".$order_detail_info["pos_id"]."' ";
	$strSql .= " , '".$order_detail_info["enter_id"]."' ";
	$strSql .= " , '".$order_detail_info["ticket_option_code"]."' ";
	$strSql .= " , '".$order_detail_info["option_name"]."' ";
	$strSql .= " , '".$order_detail_info["barcode"]."' ";
	$strSql .= " ,  ".$order_detail_info["count"]." ";
	$strSql .= " , '".$order_detail_info["valid_start_date"]."' ";
	$strSql .= " , '".$order_detail_info["valid_end_date"]."' ";
	$strSql .= " ,  ".$order_detail_info["sale_price"]." ";
	$strSql .= " ,  ".$order_detail_info["dis_price"]." ";
	$strSql .= " ,  ".$order_detail_info["bill_price"]." ";
	$strSql .= " ,  ".$order_detail_info["remit_price"]." ";
	$strSql .= " ,  ".$order_detail_info["product_detail_idx"]." ";
	$strSql .= " ,  ".$order_detail_info["option_mapping_idx"]." ";

	$strSql .= " ,  ".$order_detail_info["product_detail_pkg_idx"]." ";

	$strSql .= " , '".$order_detail_info["naver_bookingId"]."' ";
	$strSql .= " , '".$order_detail_info["valid_start_date"]."' ";
	$strSql .= " , '".$order_detail_info["hours"]."' ";
	$strSql .= " , '".$order_detail_info["minute"]."' ";
	$strSql .= " , '".$order_detail_info["ex_block"]."' ";

	$strSql .= " ) ";


	$result = mysql_query($strSql);


	if( $result )
	{
		$return["result"] = true;
		$return["msg"] = "주문상세pkg등록 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]주문상세pkg등록-날짜선택형 실패[order_detail][".$strSql."]";
	}

	return $return;

}
function update_block_ticket($result_blcok,$ex_hour, $ex_minute, $valid_start_date, $mem_code)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_ticket ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";
	$strSql .= " and mem_code = '".$mem_code."' ";

	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";


		$strSql = "";
		$strSql .= " insert into ";
		$strSql .= " table_block_ticket_log ";
		$strSql .= " ( cmd, start_date, hour, minute, edit_block, result_block, mem_code ) ";
		$strSql .= " values ";
		$strSql .= " ( 'naver_buy_update' ,'".$valid_start_date."', '".$ex_hour."', '".$ex_minute."', '".$result_blcok."', '".$result_blcok."', '".$mem_code."' ) ";

		mysql_query($strSql);
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패[".$strSql."]";
	}

	return $return;
	
}

function update_block_ticket_hour_cancel($result_blcok,$ex_hour, $ex_minute, $ex_date, $mem_code)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_ticket ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$ex_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";
	$strSql .= " and mem_code = '".$mem_code."' ";

	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";


		$strSql = "";
		$strSql .= " insert into ";
		$strSql .= " table_block_ticket_log ";
		$strSql .= " ( cmd, start_date, hour, minute, edit_block, result_block, mem_code ) ";
		$strSql .= " values ";
		$strSql .= " ( 'naver_update_hour_cancel' ,'".$ex_date."', '".$ex_hour."', '".$ex_minute."', '".$result_blcok."', '".$result_blcok."', '".$mem_code."' ) ";

		mysql_query($strSql);
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패[".$strSql."]";
	}

	return $return;
	
}

function update_block_ticket_cancel($result_blcok,$ex_hour, $ex_minute, $valid_start_date, $mem_code)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_ticket ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";
	$strSql .= " and mem_code = '".$mem_code."' ";

	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";


		$strSql = "";
		$strSql .= " insert into ";
		$strSql .= " table_block_ticket_log ";
		$strSql .= " ( cmd, start_date, hour, minute, edit_block, result_block, mem_code ) ";
		$strSql .= " values ";
		$strSql .= " ( 'naver_update_cancel' ,'".$valid_start_date."', '".$ex_hour."', '".$ex_minute."', '".$result_blcok."', '".$result_blcok."', '".$mem_code."' ) ";

		mysql_query($strSql);
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패[".$strSql."]";
	}

	return $return;
	
}

function update_block_fly($result_blcok,$ex_hour, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= " update ";
	$strSql .= " table_block_fly ";
	$strSql .= " set block = '".$result_blcok."' ";
	$strSql .= " where DATE_FORMAT(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

	$result = mysql_query($strSql);

	if ( $result )
	{
		$return["result"] = true;
		$return["msg"] = "블럭 업데이트 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]블럭 업데이트 실패(플라이스테이션)[".$strSql."]";
	}

	return $return;

}



//주문상세pkg등록-날짜선택형 query
function insert_pos_detail_select_query($order_pos_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	$strSql = "";
	$strSql .= " insert into table ( ";
	$strSql .= " ) values ( ";
	$strSql .= "  ";
	$strSql .= "   '".$order_pos_info["order_num"]."' ";
	$strSql .= " ,  ".$order_pos_info["mem_code"]." ";
	$strSql .= " ,  ".$order_pos_info["channel_code"]." ";
	$strSql .= " ,  ".$order_pos_info["product_code"]." ";
	$strSql .= " ,  ".$order_pos_info["product_detail_code"]." ";
	$strSql .= " , '".$order_pos_info["pos_id"]."' ";
	$strSql .= " , '".$order_pos_info["enter_id"]."' ";
	$strSql .= " , '".$order_pos_info["ticket_option_code"]."' ";
	$strSql .= " , '".$order_pos_info["option_name"]."' ";
	$strSql .= " , '".$order_pos_info["barcode"]."' ";
	$strSql .= " ,  ".$order_pos_info["count"]." ";
	$strSql .= " , '".$order_pos_info["valid_start_date"]."' ";
	$strSql .= " , '".$order_pos_info["valid_end_date"]."' ";
	$strSql .= " ,  ".$order_pos_info["sale_price"]." ";
	$strSql .= " ,  ".$order_pos_info["dis_price"]." ";
	$strSql .= " ,  ".$order_pos_info["bill_price"]." ";
	$strSql .= " ,  ".$order_pos_info["remit_price"]." ";
	$strSql .= " ,  ".$order_pos_info["product_detail_idx"]." ";
	$strSql .= " ,  ".$order_pos_info["option_mapping_idx"]." ";

	$strSql .= " ,  ".$order_pos_info["product_detail_pkg_idx"]." ";

	$strSql .= " , '".$order_pos_info["naver_bookingId"]."' ";

	$strSql .= " , '".$order_pos_info["ex_block"]."' ";

	$strSql .= " ) ";


	$result = mysql_query($strSql);


	if( $result )
	{
		$return["result"] = true;
		$return["msg"] = "주문상세pkg등록 성공";
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]주문상세pkg등록-날짜선택형 실패[order_detail][".$strSql."]";
	}

	return $return;
}








//주문상세등록-회차형 query
function insert_order_detail_slot_query($order_detail_info)
{
	$return["result"] = false;
	$return["msg"] = "";

	//주문상세중복체크
	$existsOrder = existsOrderDetail($order_detail_info["barcode"]);

	if( $existsOrder == 0 )
	{
		$strSql = "";
		$strSql .= " insert into table ( ";
		$strSql .= "  ";
		$strSql .= " ) values ( ";
		$strSql .= "  ";
		$strSql .= "   '".$order_detail_info["order_num"]."' ";
		$strSql .= " ,  ".$order_detail_info["mem_code"]." ";
		$strSql .= " ,  ".C_NAVER_CHANNEL_CODE." ";
		$strSql .= " ,  ".$order_detail_info["product_code"]." ";
		$strSql .= " ,  ".$order_detail_info["product_detail_code"]." ";
		$strSql .= " , '".$order_detail_info["pos_id"]."' ";
		$strSql .= " , '".$order_detail_info["ticket_option_code"]."' ";
		$strSql .= " , '".$order_detail_info["option_name"]."' ";
		$strSql .= " , '".$order_detail_info["barcode"]."' ";
		$strSql .= " ,  ".$order_detail_info["count"]." ";
		$strSql .= " , '".$order_detail_info["valid_start_date"]."' ";
		$strSql .= " , '".$order_detail_info["valid_end_date"]."' ";
		$strSql .= " ,  ".$order_detail_info["sale_price"]." ";
		$strSql .= " ,  ".$order_detail_info["dis_price"]." ";
		$strSql .= " ,  ".$order_detail_info["bill_price"]." ";
		$strSql .= " ,  ".$order_detail_info["remit_price"]." ";
		$strSql .= " ,  ".$order_detail_info["product_detail_idx"]." ";
		$strSql .= " ,  ".$order_detail_info["option_mapping_idx"]." ";

		$strSql .= " , '".$order_detail_info["naver_businessId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_businessId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bizItemId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bizItemId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_regDateTime"]."' ";
		$strSql .= " , '".$order_detail_info["naver_confirmedDateTime"]."' ";
		$strSql .= " , '".$order_detail_info["naver_bookingId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_priceId"]."' ";
		$strSql .= " , '".$order_detail_info["naver_priceId_agencyKey"]."' ";
		$strSql .= " , '".$order_detail_info["naver_price"]."' ";
		$strSql .= " , '".$order_detail_info["naver_normalPrice"]."' ";
		$strSql .= " , '".$order_detail_info["naver_name"]."' ";
		$strSql .= " , '".$order_detail_info["naver_minute"]."' ";
		$strSql .= " , '".$order_detail_info["naver_json"]."' ";
		$strSql .= " ) ";

//		echo $strSql . "<br><br>";
//		exit;

		$result = mysql_query($strSql);


		if( $result )
		{
			$return["result"] = true;
			$return["msg"] = "주문등록 성공";
		}
		else
		{
			$return["result"] = false;
			$return["msg"] = "[]주문등록-회차형 실패[order_detail][".$strSql."]";
		}
	}
	else
	{
		$return["result"] = false;
		$return["msg"] = "[]주문등록-회차형 실패[바코드 중복[table]";
	}

	return $return;
}


//상품옵션조회-날짜
function get_product_detail_info($businessId, $bizItemId, $priceId_agencyKey)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table_detail.* ";
	$strSql .= " , table.priceId ";
	$strSql .= ", table.idx as option_mapping_idx ";
	$strSql .= " from table_detail ";
	$strSql .= " inner join table on table_detail.naver_api_bizItem_priceId = table.idx ";
	$strSql .= " inner join table on table_detail.idx = table.option_code ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table_detail.naver_api_businessId = '".$businessId."' ";
	$strSql .= " and table_detail.naver_api_bizItemId = '".$bizItemId."' ";
	$strSql .= " and table_detail.naver_api_bizItem_priceId = '".$priceId_agencyKey."' ";
	$strSql .= " and table.channel_code = '3004' ";
//echo $strSql . "<br>";
//exit;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return = array();

	if( $rsCount == 1 )
	{
		$return["result_code"] = "0000";
		$return["result_msg"] = "ok";

		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["list"] = $rows;
			break;
		}
	}
	else
	{
		$return["result_code"] = "9999";
		$return["result_msg"] = "조회된 옵션정보 없음[".$businessId."|".$bizItemId."|".$priceId_agencyKey."]";
	}

	return $return;
}


//상품옵션조회-회차
function get_product_detail_info_slot($businessId, $bizItemId, $priceId_agencyKey, $minute)
{
	$minute_format = get_minute_format($minute);

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table_detail.* ";
	$strSql .= ", table.idx as option_mapping_idx ";
	$strSql .= " , table.priceId ";
	$strSql .= " , table.idx as detail_pkg_idx ";
	$strSql .= " , table.fly_code as ex_block ";
	$strSql .= " , table.mem_code ";
	$strSql .= " from table_detail ";
	$strSql .= " inner join table on table_detail.naver_api_bizItem_priceId = table.idx ";
	$strSql .= " inner join table on table_detail.idx = table.product_detail_idx ";
	$strSql .= " left join table on table_detail.idx = table.option_code ";
	$strSql .= " inner join table on table_detail.product_code = table.product_code ";
	$strSql .= " where 1=1 ";
//	$strSql .= " and table_detail.naver_minute = '".$minute_format."' ";
	$strSql .= " and table_detail.naver_api_businessId = '".$businessId."' ";
	$strSql .= " and table_detail.naver_api_bizItemId = '".$bizItemId."' ";
	$strSql .= " and table_detail.naver_api_bizItem_priceId = '".$priceId_agencyKey."' ";
	$strSql .= " and table.channel_code = '3004' ";


	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$return = array();

	if( $rsCount == 1 )
	{
		$return["result_code"] = "0000";
		$return["result_msg"] = "ok";

		while($rows=mysql_fetch_assoc($rsList))
		{
			$return["list"] = $rows;
			break;
		}
	}
	else
	{
		$return["result_code"] = "9999";
		$return["result_msg"] = "조회된 옵션정보 없음[".$businessId."|".$bizItemId."|".$priceId_agencyKey."]";
	}

	return $return;
}

//회차형 시간포맷
function get_minute_format($minutes)
{
	if( $minutes == "" )
	{
		$return_time = "00:00";
	}
	else
	{
		$zero    = new DateTime('@0');
		$offset  = new DateTime('@' . $minutes * 60);
		$diff    = $zero->diff($offset);
		//echo $diff->format('%a Days, %h Hours, %i Minutes');

		$return_time = $diff->format('%h:%I');
	}
	return $return_time;
}

//주문상세중복
function existsOrderDetail($barcode)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " * ";
	$strSql .= " from table ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.barcode = '".$barcode."' ";
	//echo $strSql;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	return $rsCount;
}



//네이버 주문체크
function existsOrderBooking($bookingId)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " * ";
	$strSql .= " from table ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.naver_bookingId = '".$bookingId."' ";
//	echo $strSql;
//	exit;
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	return $rsCount;
}




//유효기간 셋팅
function set_valid_date($valid_type, $buy_date, $valid_count, $valid_date, $booking_date)
{
	//유효일 기간권
	if( $valid_type == 0 )
	{
		$valid_date = date("Y-m-d", strtotime(  date("Y-m-d", strtotime( $buy_date ) ) . $valid_count." days" ));
	}
	//유효일 예약일
	else if( $valid_type == 2 )
	{
		//채널옵션의 예약일 협의(옵션의 예약일의 구조를 정의해야함) 필요....
		$valid_date = date("Y-m-d", strtotime( $booking_date ) );
	}
	//유효일 날짜지정
	else
	{
		$valid_date = $valid_date;
	}

	return $valid_date;
}



//웹으로 문자발송( 디비 커넥션 문제로 직접 입력하지 않고 웸으로 발송
function send_lms_order_http_new($callback, $subject, $content, $recipient_num, $order_no, $status)
{

	require_once($_SERVER["DOCUMENT_ROOT"]."/config/lib/Snoopy.class.php");

	$snoopy = new Snoopy();
	$report_url = C_SMS_SEND_URL;

	$arr_form = array();

	$arr_form["mode"]			= "send_lms";
	$arr_form["callback"]		= $callback;
	$arr_form["subject"]		= $subject;
	$arr_form["content"]		= $content;
	$arr_form["recipient_num"]	= $recipient_num;
	$arr_form["order_num"]		= $order_no;
	$arr_form["db_name"]		= C_ADMIN_DB_NAME;
	$arr_form["test_mode"]		= "";	//1 : debug...var_dump
	$arr_form["status"]			= $status;

	//연동 키
	$key = "KRC7AUZUQF";
	$str_form = json_encode($arr_form);
	$encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $str_form, MCRYPT_MODE_CBC, md5(md5($key))));
	$arr_form = array();
	$arr_form["encoded"] = $encoded;
//echo $report_url."?".http_build_query($arr_form);
	$snoopy->submit($report_url,$arr_form);

	//return value ==> mt_pr
	return $snoopy->results;
}




//응답 주문정보 조회
function get_naver_order($bookingId)
{
	$json_array = array();
	$json_readableCodes = array();
	$json_readableCodes_array = array();

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= "   naver_bookingId as bookingId ";
	$strSql .= " , barcode as readableCodeId ";
	$strSql .= " , option_name as title ";
	$strSql .= " , naver_price as price ";
	$strSql .= " , naver_priceId as priceId ";
	$strSql .= " , count as qty ";
	$strSql .= " , table.naver_barcode_type ";
	$strSql .= " from table ";
	$strSql .= " inner join table on table.product_code = table.product_code ";
	$strSql .= " where 1=1 ";
	$strSql .= " and naver_bookingId = '".$bookingId."' ";
	$strSql .= "  ";
//echo $strSql . "<br>";
	$rsList = mysql_query($strSql);
	$rsCount = mysql_num_rows($rsList);

	$json_array["bookingId"] = $bookingId;

	if( $rsCount > 0 )
	{
		while($rows=mysql_fetch_assoc($rsList))
		{
			unset($json_readableCodes_array);

//			$json_readableCodes_array["type"] = "qrcode";
			if( $rows["naver_barcode_type"] == "B" )
			{
				$json_readableCodes_array["type"] = "barcode";
			}
			else
			{
				$json_readableCodes_array["type"] = "qrcode";
			}
			$json_readableCodes_array["readableCodeId"] = $rows["readableCodeId"];
			$json_readableCodes_array["title"] = $rows["title"];
			$json_readableCodes_array["price"] = $rows["price"];
			$json_readableCodes_array["priceId"] = $rows["priceId"];
			$json_readableCodes_array["qty"] = $rows["qty"];

			$json_readableCodes[] = $json_readableCodes_array;
		}
	}

	$json_array["readableCodes"] = $json_readableCodes;

	$json_string = json_encode($json_array, JSON_UNESCAPED_UNICODE);

	return $json_string;
}



//불리언체크
function boolean_check($boolean)
{
	$return = "";

	if( $boolean == "true" || $boolean || $boolean == true )
	{
		$return = 1;
	}
	else
	{
		$return = 0;
	}

	return $return;
}



function getOrderDetailInfo($barcode)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " * ";
	$strSql .= " , table.channel_code as order_detail_channel_code ";
	$strSql .= " , table.option_name as order_detail_option_name ";
	$strSql .= " , table.ex_date ";
	$strSql .= " , table.ex_minute ";
	$strSql .= " , table.ex_hours ";
	$strSql .= " , table.ex_block ";
	$strSql .= " , table.mem_code ";
	$strSql .= " from table ";
	$strSql .= " inner join table_detail on table.product_detail_idx = table_detail.idx ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.barcode = '".$barcode."' ";
//echo $strSql;
//exit;
	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$rtnArray = $rows;
		break;
	}

	return $rtnArray;
}



function getOrderDetailApiTypeInfo($bookingId)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " table_detail.api_type ";
	$strSql .= " from table ";
	$strSql .= " inner join table on table.order_num = table.order_num ";
	$strSql .= " inner join table_detail on table.product_detail_idx = table_detail.idx ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.naver_bookingId = '".$bookingId."' ";
	$strSql .= " limit 1 ";

//echo $strSql;
//exit;
	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$api_type = $rows["api_type"];
		break;
	}

	return $api_type;
}





//주문번호생성
function create_order_num()
{
	$rtnValue = "";

	$tmp_order_num = "N" . date("YmdHis") . mt_rand(1111, 9999);

	$rtnValue = $tmp_order_num;

	return $rtnValue;
}

function SlotBlockCheck_total($ex_hour, $ex_minute, $valid_start_date, $mem_code)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " block ";
	$strSql .= " from table_block_ticket ";
	$strSql .= " where 1=1 ";
	$strSql .= " and date_format(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";
	$strSql .= " and mem_code = '".$mem_code."' ";

	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$rtnArray = $rows;
		break;
	}

	return $rtnArray;

}


//블럭 체크 플라이스테이션
function SlotBlockCheck_fly($ex_hour, $ex_minute, $valid_start_date)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " block ";
	$strSql .= " from ";
	$strSql .= " table_block_fly ";
	$strSql .= " where 1=1 ";
	$strSql .= " and date_format(start_date, '%Y-%m-%d') = '".$valid_start_date."' ";
	$strSql .= " and hour = '".$ex_hour."' ";
	$strSql .= " and minute = '".$ex_minute."' ";

//	echo $strSql;
//	exit;


	$rtnValue = mysql_query($strSql);

	while($rows = mysql_fetch_array($rtnValue))
	{
		$rtnArray = $rows;
		break;
	}

	return $rtnArray;

}


function member_manager_send($bookingId)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " table.mem_code ";
	$strSql .= " , salti_member.send_hp ";
	$strSql .= " , salti_member.mem_mgr_hp ";
	$strSql .= " , table.order_num ";
	$strSql .= " from table ";
	$strSql .= " inner join salti_member ";
	$strSql .= " on salti_member.mem_code = table.mem_code ";
	$strSql .= " where 1=1 ";
	$strSql .= " and table.naver_bookingId = '".$bookingId."' ";

	$mem_code	= mysql_result(mysql_query($strSql),0,0);
	$send_hp	= mysql_result(mysql_query($strSql),0,1);
	$mem_mgr_hp = mysql_result(mysql_query($strSql),0,2);
	$order_num 	= mysql_result(mysql_query($strSql),0,3);

	if($send_hp == "Y")
	{
		$mem_mgr_hp_array = explode(",",$mem_mgr_hp);
		$callback = "1522-0197";
		$template_code = "1111";
		$sender_key = "b6aac7c762a30e1a1650ad2e21ce5a48c6171b3b";

		$order_info			= get_order_info_cms($order_num);
		$order_detail_info  = get_order_detail_info_cms($order_num);
		$buy_name			= $order_info["list"]["buy_name"];

		foreach($order_detail_info["list"] as $k=>$order_detail)
		{
			$product_code = $order_detail["product_code"];
			$option_name = $order_detail["option_name"];
		}

		$product_name = get_order_product_name_cms($product_code);

		$content = "";
		$content .= "구매 건 확인 바랍니다.\n\n";
		$content .= "주문번호:".$order_num."\n";
		$content .= "이름:".$buy_name."\n";
		$content .= "상품명:".$product_name."\n";
		$content .= "옵션:".$option_name."";

		foreach($mem_mgr_hp_array as $k=>$v)
		{
			$mem_mgr_hp = aes256encode($v);

			$result = send_kakao_standard_cms("product", $sms_num, $sender_key, $callback, $mem_mgr_hp, $template_code, $button_yn, $button_name, $button_url, $content, $order_info["list"]["order_num"], $send_num);
		}
	}
}




//알림톡 발송
function order_send($bookingId)
{
	//구매완료 알림톡발송
	//주문정보
	$order_info = get_order_info_naver_cms($bookingId);

	//발송문자정보
	$sms_info = getNaverProductSmsInfoNotiCms($bookingId);

	if ( $sms_info["button_cns"] == "info" )
	{
		$send_type			= $sms_info["send_type"];
		$sms_num			= $sms_info["sms_num"];
		$sms_type			= $sms_info["sms_type"];
		$callback			= $sms_info["callback"];
		$template_code		= $sms_info["template_code"];
		$button_yn			= $sms_info["button_yn"];
		$content			= $sms_info["content"];
		$button_name		= $sms_info["button_name"];
		$button_url			= $sms_info["button_url"];
		$sender_key			= $sms_info["sender_key"];
		$mem_code			= $sms_info["mem_code"];


		$msg_arr["order_num"]		= $order_info["order_num"];
		$msg_arr["ticket_code"]		= $ticket_code;
		$msg_arr["callback"]		= $callback;
		$msg_arr["recipient_num"]	= aes256decode($order_info["buy_hp"]);
		$msg_arr["template_code"]	= $template_code;
		$msg_arr["button_yn"]		= $button_yn;
		$msg_arr["content"]			= $content;

		$msg_arr["buy_date"]		= $order_info["buy_date"];


		$content = set_content_cms($content, $msg_arr);
		$button_url = set_content_cms($button_url, $msg_arr);

		if( $sms_info["sms_type"] == 2 )
		{
			send_kakao_standard_cms($send_type, $sms_num, $sender_key, $callback, $order_info["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_info["order_num"], $send_num);
		}
	}
	//LG CNS 발송건
	else if ( $sms_info["button_cns"] == "cns" )
	{
		$send_type			= $sms_info["send_type"];
		$sms_num			= $sms_info["sms_num"];
		$sms_type			= $sms_info["sms_type"];
		$callback			= $sms_info["callback"];
		$template_code		= $sms_info["template_code"];
		$button_yn			= $sms_info["button_yn"];
		$content			= $sms_info["content"];
		$button_name		= $sms_info["button_name"];
		$button_url			= $sms_info["button_url"];
		$sender_key			= $sms_info["sender_key"];
		$service_num		= $sms_info["service_num"];
		$mem_code			= $sms_info["mem_code"];
		$subject			= $sms_info["subject"];



		$msg_arr["order_num"]		= $order_info["order_num"];
		$msg_arr["product_name"]	= $product_name;
		$msg_arr["option_name"]		= $option_name;
		$msg_arr["buy_name"]		= $order_info["buy_name"];
		$msg_arr["buy_hp"]			= aes256decode($order_info["buy_hp"]);
		$msg_arr["ticket_code"]		= $ticket_code;
		$msg_arr["callback"]		= $callback;
		$msg_arr["template_code"]	= $template_code;
		$msg_arr["button_yn"]		= $button_yn;
		$msg_arr["content"]			= $content;

		$msg_arr["buy_date"]		= $order_info["buy_date"];

		$content = set_content_cms($content, $msg_arr);
		$button_url = set_content_cms($button_url, $msg_arr);
		

		if( $sms_info["sms_type"] == 2 )
		{
			$result = send_kakao_lgcns_standard_cms($sms_type, $sms_num, $sender_key, $callback, $order_info["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_info["order_num"], $send_num, $service_num, $subject);
//			echo "알림톡발송응답 : ".$result . "<br>";
			if( $mem_code == '47')
			{
				SendMail($order_info);
			}
		}

	}

}

function SendMail($json_array)
{
	$post_data["send_info"] = json_encode($json_array, JSON_UNESCAPED_UNICODE);

	$url = "";


	$rtnValue = curl_post($url, $post_data);
	
	$return_json = json_decode($rtnValue, true);

	return $return_json;
}

?>