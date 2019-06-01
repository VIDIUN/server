<?php

class AkamaiDeliveryUtils {
	
	protected static function generateCsmilUrl(array $flavors)
	{
		$urls = array();
		foreach ($flavors as $flavor)
		{
			$urls[] = $flavor['url'];
		}
		$urls = array_unique($urls);
	
		if (count($urls) == 1)
		{
			$baseUrl = reset($urls);
			return '/' . ltrim($baseUrl, '/');
		}
	
		$prefix = vString::getCommonPrefix($urls);
		$prefixLen = strlen($prefix);
		$postfix = vString::getCommonPostfix($urls);
		$postfixLen = strlen($postfix);
		$middlePart = ',';
		foreach ($urls as $url)
		{
			$middlePart .= substr($url, $prefixLen, strlen($url) - $prefixLen - $postfixLen) . ',';
		}
		$baseUrl = $prefix . $middlePart . $postfix;
	
		return '/' . ltrim($baseUrl, '/') . '.csmil';
	}
	
	public static function getHDN2ManifestUrl(array $flavors, $mediaProtocol, $urlPrefix, $urlSuffix, $protocolFolder, array $params)
	{
		$url = self::generateCsmilUrl($flavors);
		$url .= $urlSuffix;
	
		// move any folders on the url prefix to the url part, so that the protocol folder will always be first
		$urlPrefixWithProtocol = $urlPrefix;
		if (strpos($urlPrefix, '://') === false)
			$urlPrefixWithProtocol = 'http://' . $urlPrefix;
	
		$urlPrefixPath = parse_url($urlPrefixWithProtocol, PHP_URL_PATH);
		if ($urlPrefixPath && substr($urlPrefix, -strlen($urlPrefixPath)) == $urlPrefixPath)
		{
			$urlPrefix = substr($urlPrefix, 0, -strlen($urlPrefixPath));
			$url = rtrim($urlPrefixPath, '/') . '/' . ltrim($url, '/');
		}
		
		$paramsStr = http_build_query($params,'','&');
		if(!empty($paramsStr))
			$url .= "?" . $paramsStr;
		
		if (strpos($urlPrefix, '://') === false)
			$urlPrefix = $mediaProtocol . '://' . $urlPrefix;
	
		return array('url' => $protocolFolder . $url, 'urlPrefix' => $urlPrefix);
	}
}

