<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCuePointItemData extends VidiunESearchItemData
{

	/**
	 * @var string
	 **/
	public $cuePointType;

	/**
	 * @var string
	 **/
	public $id;

	/**
	 * @var string
	 **/
	public $name;

	/**
	 * @var string
	 **/
	public $text;

	/**
	 * @var VidiunStringArray
	 **/
	public $tags;

	/**
	 * @var string
	 **/
	public $startTime;

	/**
	 * @var string
	 **/
	public $endTime;

	/**
	 * @var string
	 **/
	public $subType;

	/**
	 * @var string
	 **/
	public $question;

	/**
	 * @var VidiunStringArray
	 **/
	public $answers;

	/**
	 * @var string
	 **/
	public $hint;

	/**
	 * @var string
	 **/
	public $explanation;

	/**
	 * @var string
	 **/
	public $assetId;

	private static $map_between_objects = array(
		'cuePointType',
		'id',
		'name',
		'text',
		'tags',
		'startTime',
		'endTime',
		'subType',
		'question',
		'answers',
		'hint',
		'explanation',
		'assetId',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchCuePointItemData();
		return parent::toObject($object_to_fill, $props_to_skip);
	}


}
