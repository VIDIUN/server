<?php
/**
 * @package plugins.cue_points
 * @subpackage api.objects
 */
class VidiunCopyCuePointsJobData extends VidiunJobData
{
	/**
	 * destination Entry Id
	 * @var string
	 */
	public $destinationEntryId = null;

	/**
	 * source Entry Id
	 * @var string
	 */
	public $sourceEntryId = null;

	private static $map_between_objects = array
	(
		'destinationEntryId',
		'sourceEntryId',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array())
	{
		if(is_null($dbData))
			$dbData = new vCopyCuePointsJobData();

		return parent::toObject($dbData, $props_to_skip);
	}
}