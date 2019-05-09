<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage lib
 */
class YouTubeDistributionEngineSelector extends DistributionEngine implements
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
		$engine = $this->getEngineByProfile($data);
		return $engine->submit($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->closeSubmit($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->delete($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->closeDelete($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->update($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->closeUpdate($data);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineReport::fetchReport()
	 */
	public function fetchReport(VidiunDistributionFetchReportJobData $data)
	{
		$engine = $this->getEngineByProfile($data);
		return $engine->fetchReport($data);
	}

	protected function getEngineByProfile(VidiunDistributionJobData $data)
	{
		if (!$data->distributionProfile instanceof VidiunYouTubeDistributionProfile)
			throw new Exception('Distribution profile is not of type VidiunYouTubeDistributionProfile for entry distribution #'.$data->entryDistributionId);

		switch ( $data->distributionProfile->feedSpecVersion )
		{
			case VidiunYouTubeDistributionFeedSpecVersion::VERSION_1:
			{
				$engine = new YouTubeDistributionLegacyEngine();
				break;
			}
			case VidiunYouTubeDistributionFeedSpecVersion::VERSION_2:
			{
				$engine = new YouTubeDistributionRightsFeedEngine();
				break;
			}
			case VidiunYouTubeDistributionFeedSpecVersion::VERSION_3:
			{
				$engine = new YouTubeDistributionCsvEngine();
				break;
			}
			default:
				throw new Exception('Distribution profile feedSpecVersion does not match existing versions');
		}

		if (VBatchBase::$taskConfig)
			$engine->configure();
		$engine->setClient();

		return $engine;
	}
}