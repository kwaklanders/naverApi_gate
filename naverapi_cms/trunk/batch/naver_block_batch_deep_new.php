<?require_once($_SERVER["DOCUMENT_ROOT"]."/skin/common.php");?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/api/naver/class_naver_slot_api.php");?>
<?

$start_date = date("Y-m-d");
$now_month = date("Y-m");

$api_use_businessId = api_use_businessId();

$last_array = array();

foreach ( $api_use_businessId as $v )
{
	if ( $v == "864275")
	{
		$bizitem_list = search_bizitem_naver($start_date, $now_month, $v);

		$schedule_list = search_schedule($bizitem_list, $start_date);

		if ( !$schedule_list )
		{
			echo "스케줄 없으므로 제외";
			continue;
		}
		else
		{
			$schedule_array = change_schedule($schedule_list);

			//mem_code 찾기
			$mem_code = find_mem_code($v);

			//블럭 수량 확인하기.
			$block_list = search_block_detail($schedule_list, $mem_code);

			//20개씩 나누기. 네이버에서 20개씩 요청 가능 하도록 막아둠.
			$slice_array = slice_array($block_list);

			//나눠진 스케줄id를 묶기 위해 다시 배열에 담기.
			foreach ( $slice_array as $k_a=>$v_a )
			{
				unset($last_detail);
				unset($schedule_detail);

				$last_detail["bizItemId"] = $v_a["bizItemId"];

				foreach ( $v_a["schedule"] as $k_s=>$v_s )
				{

					$schedule_detail[] = $v_s["scheduleId"];
				}

				$last_detail["schedule"] = $v_a["schedule"];
				$last_detail["schedule_array"] = $schedule_detail;

				$last_array[] = $last_detail;
			}

			//다시 반복문으로 풀어서 차례대로 요청하기.
			foreach ( $last_array as $l_k=>$l_v)
			{
				//url = /v3.1/businesses/{businessId}/biz-items/{bizItemId}/schedules/{scheduleIds},{scheduleIds}........(최대 20개)
				$businessId = $v;
				$bizItemId = $l_v["bizItemId"];

				//주소에 요청 할 데이터라 string으로 변환.
				$schedule_array = implode(',', $l_v["schedule_array"]);

				$block_array = $l_v["schedule"];

				$objNaverApi = new NaverApiSlot();

				$rtn_data = $objNaverApi->edit_bizItemSchedule_slot_qpos($businessId, $bizItemId, $schedule_array, $block_array);

				echo_json_encode($rtn_data);

			}

		}
	}

}

exit;

function slice_array($block_list)
{
	$chunksize = 20;

	$chunkedArrays = array();

	foreach($block_list as $v )
	{
		$scheduleChunks = array_chunk($v["schedule"], $chunksize);

		foreach ( $scheduleChunks as $chunk )
		{
			unset($chunkdetail);

			$chunkdetail["bizItemId"] = $v["bizItemId"];
			$chunkdetail["schedule"] = $chunk;

			$chunkedArrays[] = $chunkdetail;
		}
	}

	return $chunkedArrays;
}

function find_mem_code($businessId)
{
	$strSql = "";
	$strSql .= " select ";
	$strSql .= " product_code ";
	$strSql .= " from salti_product_detail ";
	$strSql .= " where naver_api_businessId = '".$businessId."' ";
	$strSql .= " limit 1 ";

	$product_code = mysql_result(mysql_query($strSql),0,0);


	$strSql = "";
	$strSql .= " select ";
	$strSql .= " mem_code ";
	$strSql .= " from salti_product ";
	$strSql .= " where product_code = '".$product_code."' ";

	$mem_code = mysql_result(mysql_query($strSql),0,0);

	return $mem_code;
}


function api_use_businessId()
{
	$business_list = array();

	$strSql = "";
	$strSql .= " select ";
	$strSql .= " table ";
	$strSql .= " from salti_business_naver ";
	$strSql .= " where businessTypeId = 12 ";

	$search = mysql_query($strSql);
	$list = mysql_num_rows($search);

	if ( $list > 0 )
	{
		while($rows = mysql_fetch_assoc($search))
		{
			if ( $rows["businessId"] != "653350" )
			{
				$business_list[] = $rows["businessId"];
			}
		}
	}

	return $business_list;
}

function search_schedule($bizitem_list, $start_date)
{
	$schedule_array = array();
	$real_schedule = array();
	foreach ( $bizitem_list as $v )
	{
		$strSql = "";
		$strSql .= " select ";
		$strSql .= " scheduleId ";
		$strSql .= " , schedule_date ";
		$strSql .= " from table ";
		$strSql .= " where bizItemId = '".$v."' ";
		$strSql .= " and date_format(schedule_date, '%Y-%m-%d') >= '".$start_date."' ";

		$search_schedule = mysql_query($strSql);
		$search_count = mysql_num_rows($search_schedule);

		if ( $search_count > 0 )
		{
			while($rows = mysql_fetch_assoc($search_schedule))
			{
				$array["bizItemId"] = $v;
				$array["scheduleId"] = $rows["scheduleId"];
				$array["date"] = $rows["schedule_date"];

				$schedule_array[] = $array;
			}
		}
		else
		{
			continue;
		}
	}

	if ( empty($schedule_array))
	{
		$result = false;
	}
	else
	{
		$groupedData = [];

		// 데이터 순회
		foreach ($schedule_array as $entry) {
			$bizItemId = $entry['bizItemId'];
			$schedule = [
				"scheduleId" => $entry['scheduleId'],
				"date" => $entry['date']
			];

			// bizItemId가 이미 존재하는 경우, 해당 스케줄을 추가
			if (isset($groupedData[$bizItemId])) {
				$groupedData[$bizItemId]['schedule'][] = $schedule;
			} else {
				// 새로운 bizItemId인 경우, 새로운 배열로 초기화
				$groupedData[$bizItemId] = [
					"bizItemId" => $bizItemId,
					"schedule" => [$schedule]
				];
			}
		}

		// 결과 배열을 인덱스 배열로 변환
		$result = array_values($groupedData);
	}

	return $result;
}


function search_bizitem_schedule($bizitem_list, $start_date, $now_month)
{
	$schedule_array = array();
	$real_schedule = array();

	foreach ( $bizitem_list as $k=>$v)
	{
		$strSql = "";
		$strSql .= " select ";
		$strSql .= " businessId ";
		$strSql .= " , bizItemId ";
		$strSql .= " , scheduleId ";
		$strSql .= " , schedule_date ";
		$strSql .= " from table ";
		$strSql .= " where businessId = '".$v["businessId"]."' ";
		$strSql .= " and bizItemId = '".$v["bizItemId"]."' ";
		$strSql .= " and date_format(schedule_date, '%Y-%m-%d') >= '".$start_date."' ";


		$search_schedule = mysql_query($strSql);
		$search_count = mysql_num_rows($search_schedule);

		if ( $search_count > 0)
		{
			while($rows = mysql_fetch_assoc($search_schedule))
			{
				if ( $rows["scheduleId"] == "883237768" )
				{
					continue;
				}
				$array["bizItemId"] = $rows["bizItemId"];
				$array["scheduleId"] = $rows["scheduleId"];
				$array["date"] = $rows["schedule_date"];

				$schedule_array[] = $array;
			}
		}
		else
		{
			continue;
		}
	}

	if ( empty($schedule_array) )
	{
		$result = false;
	}
	else
	{
		$groupedData = [];

		// 데이터 순회
		foreach ($schedule_array as $entry) {
			$bizItemId = $entry['bizItemId'];
			$schedule = [
				"scheduleId" => $entry['scheduleId'],
				"date" => $entry['date']
			];

			// bizItemId가 이미 존재하는 경우, 해당 스케줄을 추가
			if (isset($groupedData[$bizItemId])) {
				$groupedData[$bizItemId]['schedule'][] = $schedule;
			} else {
				// 새로운 bizItemId인 경우, 새로운 배열로 초기화
				$groupedData[$bizItemId] = [
					"bizItemId" => $bizItemId,
					"schedule" => [$schedule]
				];
			}
		}

		// 결과 배열을 인덱스 배열로 변환
		$result = array_values($groupedData);
		}

	return $result;

}


function search_bizitem_naver($start_date, $now_month, $businessId)
{

	$biz_array = array();


	$strSql = "";
	$strSql .= " select ";
	$strSql .= " bizItemId ";
	$strSql .= " from table ";
	$strSql .= " where date_format(end_date, '%Y-%m-%d') >= '".$start_date."' ";
	$strSql .= " and businessId = '".$businessId."' ";

	$item_list = mysql_query($strSql);
	$item_count = mysql_num_rows($item_list);

	if ( $item_count > 0 )
	{
		while($item_rows = mysql_fetch_assoc($item_list))
		{
			$biz_array[] = $item_rows["bizItemId"];
		}
	}

	return $biz_array;
}

function search_block_detail($schedule_array, $mem_code)
{
	foreach ( $schedule_array as &$bizItem )
	{
		foreach ($bizItem["schedule"] as &$schedule) 
		{
			//DB 스케줄에 있는 time format 변경
			$date = $schedule["date"];

			$date_ex = explode(" ", $date);

			$start_date = $date_ex[0];

			$time_ex = explode(":", $date_ex[1]);

			$hour_b = $time_ex[0];
			$minute_b = $time_ex[1];

			if ( substr($hour_b, 0, 1) == '0' )
			{
				$hour = substr($hour_b, 1);
			}
			else
			{
				$hour = $hour_b;
			}

			if ( substr($minute_b, 0, 1) == '0' )
			{
				$minute = substr($minute_b, 1);
			}
			else
			{
				$minute = $minute_b;
			}


			$block_date_array = array();
			$block_date = array();

			$strSql = "";
			$strSql .= " select ";
			$strSql .= " block ";
			$strSql .= " from table ";
			$strSql .= " where start_date = '".$start_date."' ";
			$strSql .= " and hour = '".$hour."' ";
			$strSql .= " and minute = '".$minute."' ";
			$strSql .= " and mem_code = '".$mem_code."' ";
			
			$now_block = mysql_result(mysql_query($strSql),0,0);


			if ( $bizItem["bizItemId"] == "6120592" )
			{
				$result_block = (int)$now_block / 5;
			}
			else
			{
				$result_block = (int)$now_block;
			}

			if ( (int)$result_block < 1 )
			{
				$schedule["remainStock"] = 0;
			}
			else
			{

				$schedule["remainStock"] = (int)$result_block;

			}

			unset($schedule["date"]);

		}
	}

	return $schedule_array;

}


function search_block($schedule_array)
{
	foreach ( $schedule_array as &$bizItem )
	{
		foreach ($bizItem["schedule"] as &$schedule) 
		{
			//DB 스케줄에 있는 time format 변경
			$date = $schedule["date"];

			$date_ex = explode(" ", $date);

			$start_date = $date_ex[0];

			$time_ex = explode(":", $date_ex[1]);

			$hour_b = $time_ex[0];
			$minute_b = $time_ex[1];

			if ( substr($hour_b, 0, 1) == '0' )
			{
				$hour = substr($hour_b, 1);
			}
			else
			{
				$hour = $hour_b;
			}

			if ( substr($minute_b, 0, 1) == '0' )
			{
				$minute = substr($minute_b, 1);
			}
			else
			{
				$minute = $minute_b;
			}


			$block_date_array = array();
			$block_date = array();

			$strSql = "";
			$strSql .= " select ";
			$strSql .= " block ";
			$strSql .= " from table ";
			$strSql .= " where start_date = '".$start_date."' ";
			$strSql .= " and hour = '".$hour."' ";
			$strSql .= " and minute = '".$minute."' ";

			echo "블럭 조회 쿼리 <br>";
			echo $strSql."<br>";
			
			$now_block = mysql_result(mysql_query($strSql),0,0);

			$strSql = "";
			$strSql .= " select ";
			$strSql .= " count(table.count) as count ";
			$strSql .= " from ";
			$strSql .= " table ";
			$strSql .= " inner join table on table.order_num = table.order_num ";
			$strSql .= " where ";
			$strSql .= " table.status in (0,1) ";
			$strSql .= " and salti_order.status = 11 ";
			$strSql .= " and table.ex_date = '".$start_date."' ";
			$strSql .= " and table.ex_hours = '".$hour."' ";
			$strSql .= " and table.ex_minute = '".$minute."' ";

			echo "구매건 조회 쿼리 <br>";
			echo $strSql."<br>";
			

			$buy_count = mysql_result(mysql_query($strSql),0,0);

			$result_block = (int)$now_block - (int)$buy_count;

			if ( (int)$result_block < 0 )
			{
				$schedule["remainStock"] = 0;
			}
			else
			{

				$schedule["remainStock"] = (int)$result_block;

			}

			unset($schedule["date"]);

		}
	}

	return $schedule_array;
}

function change_schedule($schedule_list)
{
	$result = $schedule_list;

	$newResult = [];

	// 데이터 순회
	foreach ($result as $bizItem) {
		$bizItemId = $bizItem['bizItemId'];
		$scheduleIds = [];

		// schedule 항목의 scheduleId 수집
		foreach ($bizItem['schedule'] as $schedule) {
			$scheduleIds[] = $schedule['scheduleId'];
		}

		// scheduleIds를 콤마로 구분된 문자열로 변환
		$scheduleIdsString = implode(",", $scheduleIds);

		// 새로운 배열에 추가
		$newResult[] = [
			"bizItemId" => $bizItemId,
			"schedule" => $scheduleIdsString
		];
	}

	return $newResult;
}

?>
