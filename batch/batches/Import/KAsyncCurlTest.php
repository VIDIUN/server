<?php
/**
 * @package Scheduler
 * @subpackage Debug
 */
chdir(dirname( __FILE__ ) . "/../../");
require_once(__DIR__ . "/../../bootstrap.php");

/**
 * @package Scheduler
 * @subpackage Debug
 */
class VAsyncCurlTest extends PHPUnit_Framework_TestCase 
{
	public function testRedirectFileSize()
	{
		$curlWrapper = new VCurlWrapper();
		$curlHeaderResponse = $curlWrapper->getHeader('http://scoregolf.com/Web-Media/Video/2008/May/pumafinal.flv');
		var_dump($curlHeaderResponse);
	}
}

?>