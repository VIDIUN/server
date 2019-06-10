<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 */
class VidiunTransformMetadataResponse extends VidiunObject
{
	/**
	 * @var VidiunMetadataArray
	 * @readonly
	 */
	public $objects;

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