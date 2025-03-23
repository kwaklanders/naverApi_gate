<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/api/naver/class_naver_booking_api.php");?>
<?
//네이버 api 입금대기 조회

//60분전
$this_time = date("Y-m-d H:i:s");
//$start_date_format = date("Y-m-d H:i:s", strtotime("-10 days", strtotime($this_time)));
$start_date_format = date("Y-m-d H:i:s", strtotime("-80 minutes", strtotime($this_time)));
$end_date_format = date("Y-m-d H:i:s", strtotime("-5 minutes", strtotime($this_time)));

$strSql = "";
$strSql .= "  ";
$strSql .= " select ";
$strSql .= " naver_bookingId ";
$strSql .= " , naver_businessId";
$strSql .= " , order_num, buy_name ";
$strSql .= " , buy_hp ";
$strSql .= " , buy_date ";
$strSql .= " , order_num ";
$strSql .= " , idx ";
$strSql .= " from table ";
$strSql .= " where status = 0 ";	#입금대기
$strSql .= " and naver_bookingId <> '' and naver_bookingId is not null "; #네이버연동이 아닌경우 제외
$strSql .= " and naver_businessId <> '' and naver_businessId is not null "; #네이버연동이 아닌경우 제외
$strSql .= " and channel_code = 3004 "; #네이버예약
$strSql .= " and (buy_date >= '".$start_date_format."' and buy_date <= '".$end_date_format."') "; # 1시간전 ~ 5분전까지 
//$strSql .= " and (buy_date >= '2023-12-24 11:50:00' and buy_date <= '2023-12-24 12:00:00') "; # 1시간전 ~ 5분전까지
$strSql .= " order by buy_date desc ";
$strSql .= " limit 7 ";

echo $strSql."<br><br>";
//exit;

$rsList = mysql_query($strSql);
$rsCount = mysql_num_rows($rsList);

if( $rsCount > 0 )
{
	//네이버 연동
	$objNaverApi = new NaverApiBooking();

	while($rows=mysql_fetch_assoc($rsList))
	{
		unset($order_info);

		$order_info["buy_name"] = $rows["buy_name"];
		$order_info["buy_hp"] = aes256decode($rows["buy_hp"]);
		$order_info["order_num"] = $rows["order_num"];
		$order_info["buy_date"] = $rows["buy_date"];
		$order_info["bookingId"] = $rows["naver_bookingId"];
		$order_info["businessId"] = $rows["naver_businessId"];
		$order_info["error_time"] = date("Y-m-d H:i:s");
		$order_info["order_num"] = $rows["order_num"];

		$businessId = $rows["naver_businessId"];
		$bookingId = $rows["naver_bookingId"];

		$rtn_data = $objNaverApi->search_naver_booking($businessId, $bookingId);
		
		if( $rtn_data["result_code"] == "0000" )
		{			
			if( count($rtn_data["list"]) > 0 )
			{
				//결제완료만 처리....
				if( $rtn_data["list"]["payments"][0]["status"] == "PAID" && $rtn_data["list"]["npayChargedStatusCode"] == "CT02" )
				{
					//트랜잭션 시작
					mysql_query("SET AUTOCOMMIT=0");
					mysql_query("BEGIN");

					$order_chk = order_update($rows["idx"]);
					
					if($order_chk)
					{
						foreach($rtn_data["list"]["nPayOrderJson"] as $k_nPayOrderJson=>$v_nPayOrderJson)
						{
							//order_detail update
							$order_detail_chk = order_detail_update($order_info["bookingId"], $v_nPayOrderJson["nPayOrderTypeId"] , $v_nPayOrderJson["nPayOrderSeq"], $v_nPayOrderJson["nPayOrderNumber"], $v_nPayOrderJson["nPayProductOrderNumber"]);
														
							if($order_detail_chk)
							{
								//pos_detail update
								$pos_detail_chk = pos_detail_update($order_info["bookingId"], $v_nPayOrderJson["nPayOrderTypeId"] , $v_nPayOrderJson["nPayOrderSeq"], $v_nPayOrderJson["nPayOrderNumber"], $v_nPayOrderJson["nPayProductOrderNumber"]);

								if($pos_detail_chk)
								{
									$tran_chk = true;
								}
								else
								{
									$tran_chk = false;
									$echo_msg = "pos_detail update 실패";
								}
							}
							else
							{
								$tran_chk = false;
								$echo_msg = "order_detail update 실패";
							}
						}
					}
					else
					{
						$tran_chk = false;
						$echo_msg = "order update 실패";
					}
					
//					var_dump($tran_chk);
//					exit;

					if( $tran_chk )
					{
						$echo_msg = "등록성공";
						$echo_color = "#0100FF";
						echo "[등록성공]|".$order_info["order_num"]."|".$order_info["buy_name"]."<br>";

						$order_num = chk_order_num($order_info["bookingId"]);

						order_send($order_num);

						mysql_query("COMMIT");
					}
					else
					{
						$echo_color = "#FF0000";

						mysql_query("ROLLBACK");
					}

					mysql_query("SET AUTOCOMMIT=1");
				}
				else
				{
					echo "결제완료된 건이 없습니다. [예약번호 : ".$order_info["bookingId"]."]".$order_info["order_num"]."|".$order_info["buy_name"]."<br>";
				}
			}
			else
			{
				echo "주문 리스트가 없습니다|".$order_info["order_num"]."|".$order_info["buy_name"]."<br>";
			}
		}
		else
		{
			echo "네이버 주문정보 요청실패|".$order_info["order_num"]."|".$order_info["buy_name"]."<br>";
		}
	}
}
else
{
	echo "입금 대기 건이 없습니다.";
}

function chk_order_num($bookingId)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " select ";
	$strSql .= " order_num ";
	$strSql .= " from table ";
	$strSql .= " where naver_bookingId='".$bookingId."' ";

	$order_num = mysql_result(mysql_query($strSql),0,0);

	return $order_num;
}

function pos_detail_update($bookingId, $nPayOrderTypeId, $naver_nPayOrderSeq, $naver_nPayOrderNumber, $naver_nPayProductOrderNumber)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " idx ";
	$strSql .= " from table ";
	$strSql .= " where barcode = '".$nPayOrderTypeId."' ";
	
	$idx = mysql_result(mysql_query($strSql),0,0);
	
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " update table ";
	$strSql .= " set  ";
	$strSql .= "   naver_nPayOrderSeq = '".$naver_nPayOrderSeq."' ";
	$strSql .= " , naver_nPayOrderNumber = '".$naver_nPayOrderNumber."' ";
	$strSql .= " , naver_nPayProductOrderNumber = '".$naver_nPayProductOrderNumber."' ";
	$strSql .= " where idx = '".$idx."' ";

	$result = mysql_query($strSql);

	$update_count = mysql_affected_rows();

	if( $update_count == 0 || $update_count == 1)
	{
		$result = true;
	}
	else 
	{
		$result = false;
	}
	
	return $result;
}

function order_update($order_idx)
{
	$strSql = "";
	$strSql .= "  ";
	$strSql .= " update table set status = 11 where idx = '".$order_idx."' ";
	
	$result = mysql_query($strSql);

	$update_count = mysql_affected_rows();

	if( $update_count == 0 || $update_count == 1)
	{
		$result = true;
	}
	else 
	{
		$result = false;
	}

	return $result;
}

function order_detail_update($bookingId, $nPayOrderTypeId, $naver_nPayOrderSeq, $naver_nPayOrderNumber, $naver_nPayProductOrderNumber)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " idx ";
	$strSql .= " from table_detail ";
	$strSql .= " where barcode = '".$nPayOrderTypeId."' ";
	
	$idx = mysql_result(mysql_query($strSql),0,0);

	$strSql = "";
	$strSql .= "  ";
	$strSql .= " update table_detail ";
	$strSql .= " set  ";
	$strSql .= "   naver_nPayOrderSeq = '".$naver_nPayOrderSeq."' ";
	$strSql .= " , naver_nPayOrderNumber = '".$naver_nPayOrderNumber."' ";
	$strSql .= " , naver_nPayProductOrderNumber = '".$naver_nPayProductOrderNumber."' ";
	$strSql .= " where idx = '".$idx."' ";

	$result = mysql_query($strSql);
	
	$update_count = mysql_affected_rows();

	if( $update_count == 0 || $update_count == 1)
	{
		$result = true;
	}
	else 
	{
		$result = false;
	}
	
	return $result;
}

//알림톡 발송
function order_send($order_num)
{
	//구매완료 알림톡발송
	//주문정보
	$order_info = get_order_info_cms($order_num);

	//주문상세정보
	$order_detail_info = get_order_detail_info_cms($order_num);

	foreach($order_detail_info["list"] as $k=>$order_detail)
	{
		$product_code = $order_detail["product_code"];
		$option_name = $order_detail["option_name"];
		$barcode = $order_detail["barcode"];
	}

	//발송문자정보
	$sms_info = getProductSmsInfoNotiCms($product_code);

	//인포뱅크 발송건
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
		$subject			= $sms_info["subject"];


		$msg_arr["order_num"]		= $order_info["list"]["order_num"];
		$msg_arr["product_name"]	= $product_name;
		$msg_arr["option_name"]		= $option_name;
		$msg_arr["buy_name"]		= $order_info["list"]["buy_name"];
		$msg_arr["buy_hp"]			= aes256decode($order_info["list"]["buy_hp"]);
		$msg_arr["ticket_code"]		= $ticket_code;
		$msg_arr["callback"]		= $callback;
		$msg_arr["template_code"]	= $template_code;
		$msg_arr["button_yn"]		= $button_yn;
		$msg_arr["content"]			= $content;
		$msg_arr["subject"]			= $subject;

		$msg_arr["buy_date"]		= $order_info["list"]["buy_date"];


		$content = set_content_cms($content, $msg_arr);
		$button_url = set_content_cms($button_url, $msg_arr);

		if( $sms_info["sms_type"] == 2 )
		{
			$result = send_kakao_standard_cms($send_type, $sms_num, $sender_key, $callback, $order_info["list"]["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_info["list"]["order_num"], $send_num, $subject);
			echo "알림톡발송응답 : ".$result . "<br>";
		}
	}
	//LG CNS 발송건
	else if ( $sms_info["button_cns"] == "cns" )
	{
		$mail_order_info = get_mail_order_info($order_info["list"]["order_num"]);
		
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
		$subject			= $sms_info["subject"];
		$mem_code			= $sms_info["mem_code"];

		$msg_arr["order_num"]		= $order_info["list"]["order_num"];
		$msg_arr["product_name"]	= $product_name;
		$msg_arr["option_name"]		= $option_name;
		$msg_arr["buy_name"]		= $order_info["list"]["buy_name"];
		$msg_arr["buy_hp"]			= aes256decode($order_info["list"]["buy_hp"]);
		$msg_arr["ticket_code"]		= $ticket_code;
		$msg_arr["callback"]		= $callback;
		$msg_arr["template_code"]	= $template_code;
		$msg_arr["button_yn"]		= $button_yn;
		$msg_arr["content"]			= $content;
		$msg_arr["subject"]			= $subject;

		$msg_arr["buy_date"]		= $order_info["list"]["buy_date"];

		$content = set_content_cms($content, $msg_arr);
		$button_url = set_content_cms($button_url, $msg_arr);
		
		if( $sms_info["sms_type"] == 2 )
		{
			$result = send_kakao_lgcns_standard_cms($sms_type, $sms_num, $sender_key, $callback, $order_info["list"]["buy_hp"], $template_code, $button_yn, $button_name, $button_url, $content, $order_info["list"]["order_num"], $send_num, $service_num, $subject);
			echo "알림톡발송응답 : ".$result . "<br>";
		}
	}
}
?>