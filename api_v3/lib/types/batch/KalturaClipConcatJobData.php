<?php
/**
 * @package api
 * @subpackage objects
 */

class VidiunClipConcatJobData extends VidiunJobData
{

	/**$destEntryId
	 * @var string
	 */
	public $destEntryId;

	/**$tempEntryId
	 * @var string
	 */
	public $tempEntryId;

	/**$tempEntryId
	 * @var string
	 */
	public $sourceEntryId;

	/**$importUrl
	 * @var string
	 */
	public $importUrl;

	/** $partnerId
	 * @var int
	 */
	public $partnerId;

	/** $priority
	 * @var int
	 */
	public $priority;

	/** clip operations
	 * @var VidiunOperationAttributesArray $operationAttributes
	 */
	public $operationAttributes;


	private static $map_between_objects = array
	(
		'destEntryId',
		'tempEntryId',
		'partnerId',
		'priority',
		'operationAttributes',
		'sourceEntryId',
		'importUrl'
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
			$dbData = new vClipConcatJobData();

		return parent::toObject($dbData, $props_to_skip);
	}
}