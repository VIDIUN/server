<?php
/**
 * @package api
 * @subpackage object
 */
class VidiunESearchLanguageItem extends VidiunObject
{
	/**
	 *  @var VidiunESearchLanguage
	 */
	public $eSerachLanguage;

	private static $map_between_objects = array(
		'eSerachLanguage',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}



}
?>