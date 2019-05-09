<?php
/**
 * @package plugins.podcastDistribution
 * @subpackage lib
 */
class PodcastDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineReport,
	IDistributionEngineDelete,
	IDistributionEngineCloseUpdate,
	IDistributionEngineCloseSubmit,
	IDistributionEngineCloseDelete
{
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		return true;
	}


	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		return true;
	}


	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineReport::fetchReport()
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		return true;
	}

}