<?php
/**
 * @package plugins.captionSphinx
 * @subpackage lib
 */
class vSphinxCaptionAssetFlowManager implements vObjectDeletedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		if($object instanceof CaptionAssetItem)
			return true;
			
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		$sphinxSearchManager = new vSphinxSearchManager();
		return true;
	}
}
