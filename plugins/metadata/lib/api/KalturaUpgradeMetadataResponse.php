<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 */
class VidiunUpgradeMetadataResponse extends VidiunObject
{
	/**
	 * @var int
	 * @readonly
	 */
	public $totalCount;

	/**
	 * @var int
	 * @readonly
	 */
	public $lowerVersionCount;
}