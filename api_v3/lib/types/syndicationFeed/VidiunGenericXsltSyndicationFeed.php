<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunGenericXsltSyndicationFeed extends VidiunGenericSyndicationFeed
{
	/**
	*
	* @var string
	*/
	public $xslt;

	/**
	 * @var VidiunExtendingItemMrssParameterArray
	 */
	public $itemXpathsToExtend;
	
	private static $mapBetweenObjects = array
	(
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
        
    function __construct()
	{
		$this->type = VidiunSyndicationFeedType::VIDIUN_XSLT;
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);

		if($this->shouldGet('xslt', $responseProfile))
		{
			$key = $source_object->getSyncKey(genericSyndicationFeed::FILE_SYNC_SYNDICATION_FEED_XSLT);
			$this->xslt = vFileSyncUtils::file_get_contents($key, true, false);
		}

		if($this->shouldGet('itemXpathsToExtend', $responseProfile))
		{
			$mrssParams = $source_object->getMrssParameters();
			$this->itemXpathsToExtend = new VidiunExtendingItemMrssParameterArray();
			if ($mrssParams && $mrssParams->getItemXpathsToExtend())
			{
				$this->itemXpathsToExtend = VidiunExtendingItemMrssParameterArray::fromDbArray($mrssParams->getItemXpathsToExtend());
			}
		}
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		parent::toObject($dbObject, $skip);
		
		$mrssParams = $dbObject->getMrssParameters();
		if (!$mrssParams)
		{
			$mrssParams = new vMrssParameters;
		}
		
		if ($this->itemXpathsToExtend)
		{
			$itemXpathsToExtend = $this->itemXpathsToExtend->toObjectsArray();
			$mrssParams->setItemXpathsToExtend($itemXpathsToExtend);
		}
		
		$dbObject->setMrssParameters($mrssParams);
		return $dbObject;
	}
	
	/**
	 * @param SyndicationDistributionProfile $object_to_fill
	 * @param array $props_to_skip
	 * @return genericSyndicationFeed
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(is_null($object_to_fill))
			$object_to_fill = new genericSyndicationFeed();
		
		if($this->xslt)
			vSyndicationFeedManager::validateXsl($this->xslt);
		
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/**
	 * @param SyndicationDistributionProfile $object_to_fill
	 * @param array $props_to_skip
	 * @return genericSyndicationFeed
	 */
	public function toUpdatableObject ( $object_to_fill , $props_to_skip = array() )
	{
		if(is_null($object_to_fill))
			$object_to_fill = new genericSyndicationFeed();
		
		if($this->xslt)
			vSyndicationFeedManager::validateXsl($this->xslt);
		
		return parent::toUpdatableObject($object_to_fill, $props_to_skip );
	}

	public function getPropertiesToValidate()
	{
		return array_merge(parent::getPropertiesToValidate(), array('xslt' => true));
	}


}