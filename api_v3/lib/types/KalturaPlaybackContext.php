<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlaybackContext extends VidiunObject{

	/**
	 * @var VidiunPlaybackSourceArray
	 */
	public $sources;

	/**
	 * @var VidiunCaptionPlaybackPluginDataArray
	 */
	public $playbackCaptions;

	/**
	 * @var VidiunFlavorAssetArray
	 */
	public $flavorAssets;

	/**
	 * Array of actions as received from the rules that invalidated
	 * @var VidiunRuleActionArray
	 */
	public $actions;

	/**
	 * Array of actions as received from the rules that invalidated
	 * @var VidiunAccessControlMessageArray
	 */
	public $messages;

	private static $mapBetweenObjects = array
	(
		'playbackCaptions',
		'flavorAssets',
		'sources',
		'messages',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
