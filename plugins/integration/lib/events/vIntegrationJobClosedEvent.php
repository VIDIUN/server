<?php
/**
 * @package plugins.integration
 * @subpackage lib.events
 */
class vIntegrationJobClosedEvent extends VidiunEvent implements IVidiunObjectRelatedEvent, IVidiunBatchJobRelatedEvent, IVidiunContinualEvent
{
	const EVENT_CONSUMER = 'vIntegrationJobClosedEventConsumer';

	/**
	 * @var BatchJob
	 */
	private $batchJob;
	
	/**
	 * @param BaseObject $object
	 */
	public function __construct(BatchJob $batchJob)
	{
		$this->batchJob = $batchJob;
		
		VidiunLog::debug("Event [" . get_class($this) . "] batch-job id [" . $batchJob->getId() . "] status [" . $batchJob->getStatus() . "]");
	}
	
	/* (non-PHPdoc)
	 * @see VidiunEvent::getConsumerInterface()
	 */
	public function getConsumerInterface()
	{
		return self::EVENT_CONSUMER;
	}

	/* (non-PHPdoc)
	 * @see VidiunEvent::doConsume()
	 */
	protected function doConsume(VidiunEventConsumer $consumer)
	{
		if(!$consumer->shouldConsumeIntegrationCloseEvent($this->object, $this->modifiedColumns))
			return true;
			
		VidiunLog::debug('consumer [' . get_class($consumer) . '] started handling [' . get_class($this) . '] batch-job id [' . $this->batchJob->getId() . '] status [' . $this->batchJob->getStatus() . ']');
		$result = $consumer->integrationJobClosed($this->batchJob);
		VidiunLog::debug('consumer [' . get_class($consumer) . '] finished handling [' . get_class($this) . '] batch-job id [' . $this->batchJob->getId() . '] status [' . $this->batchJob->getStatus() . ']');
		return $result;
	}

	/**
	 * @return BatchJob
	 */
	public function getBatchJob()
	{
		return $this->batchJob;
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectRelatedEvent::getObject()
	 */
	public function getObject()
	{
		return $this->batchJob->getObject();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunEvent::getScope()
	 */
	public function getScope()
	{
		$scope = parent::getScope();
		$scope->setPartnerId($this->batchJob->getPartnerId());
		
		return $scope;
	}
}
