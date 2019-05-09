<?php

/**
 * @package plugins.scheduledTask
 * @subpackage api.objects
 */
class VidiunScheduledTaskJobData extends VidiunJobData
{
	/**
	 * @var int
	 */
	public $maxResults;

	/**
	 * @var int
	 */
	public $totalCount;

	/**
	 * @var VidiunDryRunFileType
	 */
	public $fileFormat;

	/**
	 * @var string
	 */
	public $resultsFilePath;

	/**
	 * @var time
	 */
	public $referenceTime;

	private static $map_between_objects = array
	(
		'maxResults' ,
		'resultsFilePath',
		'referenceTime',
		'totalCount',
		'fileFormat'
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}


	public function toObject($objectToFill = null, $propsToSkip = array())
	{
		if (is_null($objectToFill))
			$objectToFill = new vScheduledTaskJobData();

		/** @var vScheduledTaskJobData $objectToFill */
		$objectToFill = parent::toObject($objectToFill, $propsToSkip);

		return $objectToFill;
	}
} 