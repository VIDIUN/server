<?php
/**
 * If this class used as the template data, the fields will be taken from the content parameters
 * @package plugins.httpNotification
 * @subpackage api.objects
 */
class VidiunHttpNotificationDataFields extends VidiunHttpNotificationData
{
	/**
	 * It's protected on purpose, used by getData
	 * @see VidiunHttpNotificationDataFields::getData()
	 * @var string
	 */
	protected $data;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new vHttpNotificationDataFields();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($srcObj)
	 */
	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $srcObj vHttpNotificationDataFields */
		parent::doFromObject($srcObj, $responseProfile);
		
		if($this->shouldGet('data', $responseProfile))
			$this->data = $srcObj->getData();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunHttpNotificationData::getData()
	 */
	public function getData(vHttpNotificationDispatchJobData $jobData = null)
	{
		return $this->data;
	}
}
