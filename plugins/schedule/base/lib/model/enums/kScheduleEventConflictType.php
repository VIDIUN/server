<?php
/**
 * @package plugins.schedule
 * @subpackage model.enum
 */
interface vScheduleEventConflictType extends BaseEnum
{
	const RESOURCE_CONFLICT = 1;
	const BLACKOUT_CONFLICT = 2;
	const BOTH = 3;
}