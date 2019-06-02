<?php
/**
 * @package plugins.caption
 * @subpackage api.objects
 */
class VidiunCaptionPlaybackPluginData extends VidiunObject{

	/**
	 * @var string
	 */
	public $label;
    
	/**
	 * @var string
	 */
	public $format;

	/**
	 * @var string
	 */
	public $language;

	/**
	 * @var string
	 */
	public $webVttUrl;

	/**
	 * @var string
	 */
	public $url;


	/**
	 * @var bool
	 */
	public $isDefault;

	/**
	 * @var string
	 */
	public $languageCode;


	private static $map_between_objects = array
	(
		"format",
		"label",
		"language",
		"url",
		"webVttUrl",
		"isDefault",
		"languageCode"
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}