<?php
/**
 * @package Core
 * @subpackage VMC
 */
class previewAction extends vidiunAction
{
	public function execute()
	{
		// Preview page moved into /extwidget/preview
		$https_enabled = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
		$protocol = ($https_enabled) ? 'https' : 'http';
		$url = $protocol . '://' . vConf::get('www_host') . '/index.php';
		$url .= str_replace('/vmc', '/extwidget', $_SERVER['PATH_INFO']);
		if( isset($_SERVER['QUERY_STRING']) ) {
			$url .= '?' . $_SERVER['QUERY_STRING'];
		}
		header("location: $url");
		die();
	}
}