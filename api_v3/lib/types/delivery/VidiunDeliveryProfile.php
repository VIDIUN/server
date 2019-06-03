<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeliveryProfile extends VidiunObject implements IFilterable 
{
	/**
	 * The id of the Delivery
	 *
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * The name of the Delivery
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * Delivery type
	 * @var VidiunDeliveryProfileType
	 */
	public $type;
	
	/**
	 * System name of the delivery
	 *
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * The description of the Delivery
	 *
	 * @var string
	 */
	public $description;
	
	/**
	 * Creation time as Unix timestamp (In seconds)
	 *
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * Update time as Unix timestamp (In seconds)
	 *
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * @var VidiunPlaybackProtocol
	 * @filter eq
	 */
	public $streamerType;
	
	/**
	 * @var string
	 */
	public $url;
	
	/**
	 * the host part of the url
	 * @var string
	 * @readonly
	 */
	public $hostName;

	/**
	 * @var VidiunDeliveryStatus
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var VidiunUrlRecognizer
	 */
	public $recognizer;
	
	/**
	 * @var VidiunUrlTokenizer
	 */
	public $tokenizer;
	
	/**
	 * True if this is the systemwide default for the protocol
	 *
	 * @var VidiunNullableBoolean
	 * @readonly
	 */
	public $isDefault;
	
	/**
	 * the object from which this object was cloned (or 0)
	 * @var int
	 * @readonly
	 */
	public $parentId;			
	
	/**
	 * Comma separated list of supported media protocols. f.i. rtmpe
	 * @var string
	 */
	public $mediaProtocols;
	
	/**
	 * priority used for ordering similar delivery profiles
	 * @var int
	 */
	public $priority;

	/**
	 * Extra query string parameters that should be added to the url
	 * @var string
	 */
	public $extraParams;
	
	/**
	 * A filter that can be used to include additional assets in the URL (e.g. captions)
	 * @var VidiunAssetFilter
	 */
	public $supplementaryAssetsFilter;

	private static $map_between_objects = array
	(
			"createdAt",
			"description",
			"hostName",
			"id",
			"isDefault",
			"name",
			"parentId",
			"partnerId",
			"streamerType",
			"recognizer",
			"status",
			"systemName",
			"tokenizer",
			"updatedAt",
			"url",
			"type",
			"mediaProtocols",
			"priority",
			"extraParams",
			"supplementaryAssetsFilter",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = VidiunDeliveryProfileFactory::getCoreDeliveryProfileInstanceByType($this->type);
	
		parent::toObject($dbObject, $skip);
		return $dbObject;
	}
	
	public function doFromObject($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		if(!$sourceObject)
			return;
			
		parent::doFromObject($sourceObject, $responseProfile);
		
		if($this->shouldGet('recognizer', $responseProfile))
			$this->recognizer = $this->transformRecognizer($sourceObject);
		if($this->shouldGet('tokenizer', $responseProfile))
			$this->tokenizer = $this->transformTokenizer($sourceObject);
	}
	
	protected function transformRecognizer($sourceObject) {
		$dbObject = $sourceObject->getRecognizer();
		if(is_null($dbObject))
			return null;
	
		$apiObject = VidiunDeliveryProfileFactory::getRecognizerByType(get_class($dbObject));
		if(!is_null($apiObject))
			$apiObject->fromObject($dbObject);
		return $apiObject;
	}
	
	protected function transformTokenizer($sourceObject) {
		$dbObject = $sourceObject->getTokenizer();
		if(is_null($dbObject))
			return null;
	
		$apiObject = VidiunDeliveryProfileFactory::getTokenizerInstanceByType(get_class($dbObject));
		if(!is_null($apiObject))
			$apiObject->fromObject($dbObject);
		return $apiObject;
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	*/
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	*/
	public function getFilterDocs()
	{
		return array();
	}
}