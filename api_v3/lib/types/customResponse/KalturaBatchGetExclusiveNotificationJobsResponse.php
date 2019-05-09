<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBatchGetExclusiveNotificationJobsResponse extends VidiunObject
{
	/**
	 * @var VidiunBatchJobArray
	 * @readonly
	 */
	public $notifications;

	/**
	 * @var VidiunPartnerArray
	 * @readonly
	 */
	public $partners;
}