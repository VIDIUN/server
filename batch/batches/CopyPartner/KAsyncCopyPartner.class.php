<?php
/**
 * Copy an entire partner to and new one
 *
 * @package Scheduler
 * @subpackage CopyPartner
 */
class VAsyncCopyPartner extends VJobHandlerWorker
{
	protected $fromPartnerId;
	protected $toPartnerId;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::COPY_PARTNER;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 * @return VidiunBatchJob
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->doCopyPartner($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 * @return VidiunBatchJob
	 */
	protected function doCopyPartner(VidiunBatchJob $job, VidiunCopyPartnerJobData $jobData)
	{
		$this->log( "doCopyPartner job id [$job->id], From PID: $jobData->fromPartnerId, To PID: $jobData->toPartnerId" );

		$this->fromPartnerId = $jobData->fromPartnerId;
		$this->toPartnerId = $jobData->toPartnerId;
		
		// copy permssions before trying to copy additional objects such as distribution profiles which are not enabled yet for the partner
 		$this->copyAllEntries();
		
 		return $this->closeJob($job, null, null, "doCopyPartner finished", VidiunBatchJobStatus::FINISHED);
	}
	
	/**
	 * copyAllEntries()
	 */
	protected function copyAllEntries()
	{
		$entryFilter = new VidiunBaseEntryFilter();
 		$entryFilter->order = VidiunBaseEntryOrderBy::CREATED_AT_ASC;
		
		$pageFilter = new VidiunFilterPager();
		$pageFilter->pageSize = 50;
		$pageFilter->pageIndex = 1;
		
		/* @var $this->getClient() VidiunClient */
		do
		{
			// Get the source partner's entries list
			self::impersonate( $this->fromPartnerId );
			$entriesList = $this->getClient()->baseEntry->listAction( $entryFilter, $pageFilter );

			$receivedObjectsCount = $entriesList->objects ? count($entriesList->objects) : 0;
			$pageFilter->pageIndex++;
			
			if ( $receivedObjectsCount > 0 )
			{
				// Write the source partner's entries to the destination partner 
				self::impersonate( $this->toPartnerId );
				foreach ( $entriesList->objects as $entry )
				{
					$newEntry = $this->getClient()->baseEntry->cloneAction( $entry->id );
				}
			}			
		} while ( $receivedObjectsCount );
	
		self::unimpersonate();
	}	
}
