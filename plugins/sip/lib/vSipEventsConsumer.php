<?php
class vSipEventsConsumer implements vObjectDeletedEventConsumer
{	

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null) 
	{
		try 
		{
			$pexipConfig = vPexipUtils::initAndValidateConfig();
			vPexipHandler::deleteCallObjects($object, $pexipConfig);
		}
		catch(Exception $e)
		{
			VidiunLog::err('Failed to process Sip objectDeleted for liveEntry ['.$object->getId().'] - '.$e->getMessage());
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if ($object instanceof LiveStreamEntry && $object->getIsSipEnabled())
		{
			return true;
		}
		return false;
	}
	
}