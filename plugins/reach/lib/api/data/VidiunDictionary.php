<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 * @abstract
 */

class VidiunDictionary extends VidiunObject
{
	/**
	 * @var VidiunCatalogItemLanguage
	 */
	public $language;

	/**
	 *  @var string
	 */
	public $data;

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	private static $map_between_objects = array (
		'language',
		'data',
	);

	/* (non-PHPdoc)
 	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
 	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vDictionary();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}

	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('data');
		$this->validatePropertyNotNull('language');
		parent::validateForInsert($propertiesToSkip);
	}
}