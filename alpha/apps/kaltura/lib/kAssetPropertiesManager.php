<?php
class vAssetPropertiesManager implements vObjectChangedEventConsumer
{

	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		$propertiesListToHandle = array ('language','label','default');
		if($object instanceof asset)
		{
			if (in_array(assetPeer::CUSTOM_DATA, $modifiedColumns))
			{
				foreach($propertiesListToHandle as $propertyItem)
				{
					if($object->isCustomDataModified($propertyItem))
						return true;
				}
			}
		}
		return false;
	}


	/* (non-PHPdoc)
	 * @see vObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		/* @var $object Asset */
		$entry = entryPeer::retrieveByPK($object->getEntryId());
		if ($entry)
		{
			$entry->setCacheFlavorVersion($entry->getCacheFlavorVersion() + 1);
			$entry->save();
		}
		return true;
	}

}