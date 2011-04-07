<?php

class GooAPI{

	public function shorten($url){
		$url = trim($url);
		$postData = array(
			'longUrl' => $url,
			'key' => 'AIzaSyA8F0vI2rr4fwj_TTOw3d0KX_N_jzuNJA8',
		);
		$jsonData = json_encode($postData);
		$curlObj = curl_init();
		$options = array(
			CURLOPT_URL => 'https://www.googleapis.com/urlshortener/v1/url',
			CURLOPT_POST => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER => 0,
			CURLOPT_HTTPHEADER => array('Content-type:application/json'),
			CURLOPT_POSTFIELDS => $jsonData,
		);
		curl_setopt_array($curlObj, $options);
		$response = curl_exec($curlObj);
		
		$response_code = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
		curl_close($curlObj);
		if ($response_code >= 400){
			return 'FALSE';
		}
		
		$json = json_decode($response);
		return $json->id;
	}
	
	public function expand($short){
		$short = trim($short);
		$curlObj = curl_init();
		$options = array(
			CURLOPT_URL => 'https://www.googleapis.com/urlshortener/v1/url?shortUrl='.$short,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => 0,
		);
		curl_setopt_array($curlObj, $options);
		$response = curl_exec($curlObj);
		
		$response_code = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
		curl_close($curlObj);
		if ($response_code >= 400){
			return 'FALSE';
		};
		
		$json = json_decode($response);
		return $json->longUrl;
	}

}

?>