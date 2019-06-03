<?php
/**
 * @package Core
 * @subpackage events
 */
class vEventScope extends vScope
{
	/**
	 * @var VidiunEvent
	 */
	protected $event;
	
	/**
	 * @var int
	 */
	protected $partnerId;
	
	/**
	 * @var BatchJob
	 */
	protected $parentRaisedJob;
	
	/**
	 * @param VidiunEvent $v
	 */
	public function __construct(VidiunEvent $v = null)
	{
		parent::__construct();
		$this->event = $v;
	}
	
	/**
	 * @return VidiunEvent
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * @return BaseObject|null
	 */
	public function getObject()
	{
		if ($this->event instanceof IVidiunObjectRelatedEvent)
			return $this->event->getObject();
		else
			return null;
	}
	
	/**
	 * @return int $partnerId
	 */
	public function getPartnerId()
	{
	    if (! $this->partnerId)
	    {
	        return vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
	    }
		
	    return $this->partnerId;
	}

	/**
	 * @return BatchJob $parentRaisedJob
	 */
	public function getParentRaisedJob()
	{
		return $this->parentRaisedJob;
	}

	/**
	 * @param int $partnerId
	 */
	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
	}

	/**
	 * @param BatchJob $parentRaisedJob
	 */
	public function setParentRaisedJob(BatchJob $parentRaisedJob)
	{
		$this->parentRaisedJob = $parentRaisedJob;
	}

	
}