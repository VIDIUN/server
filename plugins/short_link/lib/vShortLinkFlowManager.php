<?php
class vShortLinkFlowManager implements vObjectDeletedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof vuser)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		$shortLinks = ShortLinkPeer::retrieveByVuserId($object->getId(), $object->getPartnerId());
		foreach($shortLinks as $shortLink)
		{
			$shortLink->setStatus(ShortLinkStatus::DELETED);
			$shortLink->save();
		}
		
		return true;
	}
}