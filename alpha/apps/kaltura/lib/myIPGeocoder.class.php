<?php

require_once(dirname(__FILE__) . '/vGeoCoder.php');
require_once(dirname(__FILE__) . '/request/vIP2Location.php');

class myIPGeocoder extends vGeoCoder
{
	/* (non-PHPdoc)
	 * @see vGeoCoder::getCountry()
	 */
	public function getCountry($ip)
	{
		return $this->iptocountry($ip);
	}
	
	/* (non-PHPdoc)
	 * @see vGeoCoder::getCoordinates()
	 */
	public function getCoordinates($ip)
	{
		return vIP2Location::ipToCoordinates($ip);
	}

	public function getAnonymousInfo($ip)
	{
		return array("undefined");
	}

	function iptocountry($ip) 
	{   
		return vIP2Location::ipToCountry($ip);
	}
	
	function iptocountryAndCode($ip) 
	{
		return vIP2Location::ipToCountryAndCode($ip);
	}
}
