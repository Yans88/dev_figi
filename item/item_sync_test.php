<?php

/*
 * item_sync_test.php
 *
 * test functions in item_sync
 *
 */

$base_url = 'http://demo.figi.sg/item/item_sync.php?cmd=';
$test = new HttpClient();

$is_cli = isset($_SERVER['argv']);
echo '<pre>';
if (!$is_cli){
	echo '<pre>';
	$eol = '<br><br>';
} //else 
$eol = "\r\n";
$tab = "\t";

// return 1 -> ok, -1 -> failed 
$url = $base_url . 'authenticate';
echo 'Authentication: ' . $eol;
$response = $test->post($url, array('uid'=>'user','pwd'=>'pass'));
echo 'Failed: ' . $response . $eol;
$response = $test->post($url, array('uid'=>'admin','pwd'=>'admin'));
echo 'Succeed: ' . $response . $eol . $eol;

/*
return 
    >0 -> ok
    -1 -> upload failed 
    -2 -> authentication failed
    -3 -> csv file format unkown
    -4 -> file handling problem
    -9 -> unknown command
*/
$url = $base_url . 'start-stocktake';
$response = $test->post($url, array('uid'=>'admin','pwd'=>'admin'));
echo 'Start Stocktake: ' . $response . $eol . $eol;

/*
return 
1 -> ok, -1 -> failed 
*/
$url = $base_url . 'end-stocktake';
$response = $test->postfile($url, dirname(__FILE__). '/store-item.csv', array('stocktake'=>'yes','uid'=>'ictadmin','pwd'=>'peanut'));
echo 'End Stocktake: ' . $response . $eol . $eol;

/*
$url = $base_url . 'get-dept';
$response = $test->get($url);
echo 'Departments: ' . $eol . $response . $eol . $eol;

$url = $base_url . 'get-cat';
$response = $test->get($url);
echo 'Categories: ' . $eol . $response . $eol . $eol;

$url = $base_url . 'get-status';
$response = $test->get($url);
echo 'Statuses: ' . $eol . $response . $eol . $eol;

$url = $base_url . 'get-loc';
$response = $test->get($url);
echo 'Locations: ' . $eol . $response . $eol . $eol;

$url = $base_url . 'get-item';
$response = $test->get($url);
echo 'Items: ' . $eol . $response . $eol . $eol;

*/
class HttpClient{

	function _request($url, $method='get', $data = null, $path=null)
	{
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		if (substr($method, 0, 4) == 'post'){
			curl_setopt($c, CURLOPT_POST, 1);
			if (!empty($data))
				curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		}
		if ($method == 'postfile' && !empty($path)){
			if (is_array($data)) {
				$data['file'] = '@'.$path;
				curl_setopt($c, CURLOPT_POSTFIELDS, $data);
			} else {
				//$data .= '&file=@'.$path;
				//curl_setopt($c, CURLOPT_POSTFIELDS, $data);
			}
			/*
			clearstatcache();
			if (file_exists($path)){
				curl_setopt($c, CURLOPT_POST, 1);
				curl_setopt($c, CURLOPT_INFILESIZE, filesize($path));
				$fp = fopen($path, 'r');
				curl_setopt($c, CURLOPT_INFILE, $fp);

			} 
			*/
		}
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($c);
        error_log('curl_error: '.curl_error($c));
		curl_close($c);
		if (isset($fp))	fclose($fp);
		return $result;
	}

	function get($url, $data=null)
	{
		return $this->_request($url, 'get', $data);
	}

	function post($url, $data=null)
	{
		return $this->_request($url, 'post', $data);
	}

	function postfile($url, $path, $data=null)
	{
		return $this->_request($url, 'postfile', $data, $path);
	}
	
	function _format_query($data)
	{
		$result = null;
		if (!empty($data) && is_array($data)){
			$result = http_build_query($data);
		}
		return $result;
	}
}

?>
