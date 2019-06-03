<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunExtendingItemMrssParameter extends VidiunObject
{
	/**
	 * XPath for the extending item
	 * @var string
	 */
	public $xpath;
	
	/**
	 * Object identifier
	 * @var VidiunObjectIdentifier
	 */
	public $identifier;
	
	/**
	 * Mode of extension - append to MRSS or replace the xpath content.
	 * @var VidiunMrssExtensionMode
	 */
	public $extensionMode;
	
	
	private static $map_between_objects = array(
			"xpath",
			"identifier",
			"extensionMode"
		);
		
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		$this->validate();
		if (!$dbObject)
			$dbObject = new vExtendingItemMrssParameter();

		return parent::toObject($dbObject, $propsToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbObject, $responseProfile);
		
		/* @var $dbObject vExtendingItemMrssParameter */
		if($this->shouldGet('identifier', $responseProfile))
		{
			$identifierType = get_class($dbObject->getIdentifier());
			VidiunLog::info("Creating identifier for DB identifier type $identifierType");
			switch ($identifierType)
			{
				case 'vEntryIdentifier':
					$this->identifier = new VidiunEntryIdentifier();
					break;
				case 'vCategoryIdentifier':
					$this->identifier = new VidiunCategoryIdentifier();
			}
			
			if ($this->identifier)
				$this->identifier->fromObject($dbObject->getIdentifier());
		}
	}
	
	protected function validate ()
	{
		//Should not allow any extending object but entries to be added in APPEND mode
		if ($this->extensionMode == VidiunMrssExtensionMode::APPEND && get_class($this->identifier) !== 'VidiunEntryIdentifier')
		{
			throw new VidiunAPIException(VidiunErrors::EXTENDING_ITEM_INCOMPATIBLE_COMBINATION);
		}
		
		if (!$this->xpath)
		{
			throw new VidiunAPIException(VidiunErrors::EXTENDING_ITEM_MISSING_XPATH);
		}
	}
}