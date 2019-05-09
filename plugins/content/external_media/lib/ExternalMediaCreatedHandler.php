<?php
/**
 * @package plugins.externalMedia
 * @subpackage lib
 */
class ExternalMediaCreatedHandler implements vObjectAddedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::shouldConsumeAddedEvent()
	 */
	public function shouldConsumeAddedEvent(BaseObject $object)
	{
		if($object instanceof ExternalMediaEntry)
			return true;
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectAddedEventConsumer::objectAdded()
	 */
	public function objectAdded(BaseObject $object, BatchJob $raisedJob = null)
	{
		/* @var $object ExternalMediaEntry */
		$object->setStatus(entryStatus::READY);
		$object->save();
		
		return true;
	}
}