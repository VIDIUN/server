<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.data
 */
abstract class vAssetDistributionCondition
{
	/**
	 * @param asset $asset
	 */
	abstract public function fulfilled(asset $asset);
}