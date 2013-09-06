<?php

/**
 * @param bool $array
 * @param bool $vardump
 * @param bool $description
 * @param bool $debug_print_trace
 * @return bool
 */
function pre($array = false, $vardump = false, $description = false, $debug_print_trace = false)
{
	$debug_trace = debug_backtrace();
	if($debug_print_trace){
		$backtracel = "";
		foreach($debug_trace as $k=>$v){
			if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
				$backtracel .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]<br />";
			}else{
				$backtracel .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]<br />";
			}
		}
		echo "<br /><b>".$backtracel."</b><br />";
	}
	else {
		print( "<br /><b>".$debug_trace[0]["file"].": ".$debug_trace[0]["line"]."</b><br />");
	}

	if($description)
		echo "<b>".$description."</b><br />";
	echo "<pre>";

	if ($vardump)
		var_dump($array);
	else
		print_r($array);

	echo "</pre>";
	return true;
}

/**
 * @param bool $array
 * @param bool $vardump
 * @param bool $description
 * @param bool $debug_print_trace
 * @return bool
 */
function pre_comment($array = false, $vardump = false, $description = false, $debug_print_trace = false)
{
	print "\r\n<!--\r\n";
	pre($array, $vardump, $description, $debug_print_trace);
	print "\r\n-->\r\n";

	return true;
}
