<?php
/**
 * Enable the plugin to handle bulk upload additional data
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunImportHandler extends IVidiunBase
{
	/**
	 * This method makes an intermediate change to the imported file or its related data under certain conditions.
	 * @param VCurlHeaderResponse $curlInfo
	 * @param VidiunImportJobData $importData
	 * @param Object $params
	 */
	public static function handleImportContent($curlInfo, $importData, $params);	
}