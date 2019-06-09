<?php
/**
 * @package plugins.ftpDistribution
 * @subpackage model.data
 */
class vFtpDistributionJobProviderData extends vDistributionJobProviderData
{
	public function __construct(vDistributionJobData $distributionJobData = null)
	{
		parent::__construct($distributionJobData);
	}
}