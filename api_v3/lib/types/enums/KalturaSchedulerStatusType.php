<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunSchedulerStatusType extends VidiunEnum
{
	const RUNNING_BATCHES_COUNT = 1;
	const RUNNING_BATCHES_CPU = 2;
	const RUNNING_BATCHES_MEMORY = 3;
	const RUNNING_BATCHES_NETWORK = 4;
	const RUNNING_BATCHES_DISC_IO = 5;
	const RUNNING_BATCHES_DISC_SPACE = 6;
	const RUNNING_BATCHES_IS_RUNNING = 7;
	const RUNNING_BATCHES_LAST_EXECUTION_TIME = 8;
}