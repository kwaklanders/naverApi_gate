<?
function echo_json_encode($json)
{
	$string = json_encode($json, JSON_UNESCAPED_UNICODE);

	echo unescape_slashes($string);	//역슬래쉬 제거
}

function unescape_slashes($string)
{
	return str_replace('\\/', '/', $string);
}
?>