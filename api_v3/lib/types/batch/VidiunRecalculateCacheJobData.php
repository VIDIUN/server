<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunRecalculateCacheJobData extends VidiunJobData
{
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('RecalculateCacheType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('RecalculateCacheType', $subType);
	}
}
