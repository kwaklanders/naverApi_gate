<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/api/naver/class_naver_booking_api.php");?>
<?
//네이버 api 부분사용 대상 조회

$strSql = "";
$strSql .= "  ";
$strSql .= " select ";
$strSql .= " table.buy_name ";
$strSql .= " , table.buy_date ";
$strSql .= " , table.buy_hp ";
$strSql .= " , table.naver_businessId ";
$strSql .= " , table.naver_bizItemId ";
$strSql .= " , table.naver_bookingId ";
$strSql .= " , table.product_name ";
$strSql .= " , table.naver_nPayProductOrderNumber ";
$strSql .= " , table.barcode ";
$strSql .= " , table.option_name ";
$strSql .= " , table_detail.idx ";
$strSql .= " from table_detail ";
$strSql .= " inner join table on table_detail.order_num = table.order_num ";
$strSql .= " inner join table_detail on table_detail.idx = table_detail.product_detail_idx ";
$strSql .= " inner join table on table.barcode = table_detail.barcode ";
$strSql .= " inner join table on table.product_code = table_detail.product_code ";
$strSql .= " where ( table.agent_code is null or table.agent_code not in ('plusn') ) ";
$strSql .= " and table_detail.channel_code = '3004' ";
//$strSql .= " and table_detail.status = '1' ";
$strSql .= " and table.status = '11' ";
$strSql .= " and table.status = '1' ";
$strSql .= " and table_detail.channel_send_yn = 'N' ";
$strSql .= " and table_detail.naver_api_partial = 'partial' ";
$strSql .= " order by table_detail.detail_use_date asc ";
$strSql .= " limit 1000 ";
echo $strSql."<br>";
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
		//트랜잭션 시작
		$result = mysql_query("SET AUTOCOMMIT=0");
		$result = mysql_query("BEGIN");


		$order_info["buy_name"] = $rows["buy_name"];
		$order_info["buy_hp"] = $rows["buy_hp"];
		$order_info["order_num"] = $rows["order_num"];
		$order_info["barcode"] = $rows["barcode"];
		$order_info["buy_date"] = $rows["buy_date"];
		$order_info["bookingId"] = $rows["naver_bookingId"];
		$order_info["nPayProductOrderNumber"] = $rows["naver_nPayProductOrderNumber"];
		$order_info["product_name"] = $rows["product_name"];
		$order_info["option_name"] = $rows["option_name"];


		$businessId = $rows["naver_businessId"];
		$bizItemId = $rows["naver_bizItemId"];
		$bookingId = $rows["naver_bookingId"];
		$readableCodeId = $rows["barcode"];
		$nPayProductOrderNumber = $rows["naver_nPayProductOrderNumber"];
		$json = "{\"status\": \"completed\"}";	//사용처리시 고정
		$idx = $rows["idx"];

		$rtn_data = $objNaverApi->update_naver_booking_nPayProductOrderNumber($businessId, $bizItemId, $bookingId, $readableCodeId, $nPayProductOrderNumber, $json);


		$order_info["error_time"] = date("Y-m-d H:i:s");

		if( $rtn_data["result_code"] == "0000" )
		{
			$strSql = "";
			$strSql .= "  ";
			$strSql .= " update table_detail ";
			$strSql .= " set ";
			$strSql .= " channel_send_yn = 'Y' ";
			$strSql .= " where 1=1 ";
			$strSql .= " and idx = '".$idx."' ";
			$result = mysql_query($strSql);


			$update_count = mysql_affected_rows();
			//업데이트 실패
			if( $update_count != 1 )
			{
				$result = false;

				//잔디 오류발송
				$order_info["result_code"] = $rtn_data["result_code"];
				$order_info["result_msg"] = "[DB업데이트실패(update_count:".$update_count.")]".$rtn_data["result_msg"];
			}
		}
		else
		{
			//이미 사용처리됨
			if( strpos($rtn_data["result_msg"], "RT33") !== false )
			{
				echo "[이미 사용처리 됨]<br>";
				echo "buy_name: ".$order_info["buy_name"]."<br>";
				echo "product_name: ".$order_info["product_name"]."<br>";
				echo "buy_date: ".$order_info["buy_date"]."<br>";
				echo "readableCodeId: ".$readableCodeId."<br>";
				echo "nPayProductOrderNumber: ".$nPayProductOrderNumber."<br>";

				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table_detail ";
				$strSql .= " set ";
				$strSql .= " channel_send_yn = 'Y' ";
				$strSql .= " where idx = '".$idx."' ";

				mysql_query($strSql);

				$update_count = mysql_affected_rows();


				//업데이트 실패
				if( $update_count != 1 )
				{
					$result = false;

					//잔디 오류발송
					$order_info["result_code"] = $rtn_data["result_code"];
					$order_info["result_msg"] = "[DB업데이트실패(update_count:".$update_count.")]".$rtn_data["result_msg"];
				}
			}
			else if ( strpos($rtn_data["result_msg"], "RT75") !== false )
			{
				$strSql = "";
				$strSql .= "  ";
				$strSql .= " update table_detail ";
				$strSql .= " set ";
				$strSql .= " channel_send_yn = 'Y' ";
				$strSql .= " where idx = '".$idx."' ";

				mysql_query($strSql);

				$strSql = "";
				$strSql .= " select ";
				$strSql .= " memo ";
				$strSql .= " , order_num ";
				$strSql .= " from table ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";

				$memo = mysql_result(mysql_query($strSql),0,0);
				$order_num = mysql_result(mysql_query($strSql),0,1);

				$new_memo = $memo." / 네이버 예약에서 취소 된 주문. 확인 요망 ";

				$strSql = "";
				$strSql .= " update ";
				$strSql .= " table ";
				$strSql .= " set memo = '".$new_memo."' ";
				$strSql .= " where naver_bookingId = '".$bookingId."' ";
				
				$result = mysql_query($strSql);

				send_webhook($order_num, $readableCodeId);

			}
						//그외...
			else
			{
				//잔디 오류발송
				$order_info["result_code"] = $rtn_data["result_code"];
				$order_info["result_msg"] = "[사용처리실패!]".$rtn_data["result_msg"];
			}
		}


		if( $result )
		{
//echo "COMMIT";
//echo "<br><br>";
			mysql_query("COMMIT");
		}
		else
		{
//echo "ROLLBACK";
//echo "<br><br>";
			mysql_query("ROLLBACK");
		}


		mysql_query("SET AUTOCOMMIT=1");
	}
}



//웹훅
function send_webhook($order_num, $barcode)
{

	$send_list .= "주문번호 : ".$order_num."\n";
	$send_list .= "바코드 : ".$barcode."\n";
	$send_list .= "사용처리 된 주문번호가 \n";
	$send_list .= "취소 되었습니다. \n";
	$send_list .= "확인이 필요합니다. ";

	$send_info_json["text"] = $send_list;
	$url = "";

	$json_data_string = json_encode($send_info_json, JSON_UNESCAPED_UNICODE);
	
	$return = jandi_curl_post_ssl_json($url, $json_data_string);

}





?>