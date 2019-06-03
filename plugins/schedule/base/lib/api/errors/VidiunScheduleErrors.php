<?php
/**
 * @package plugins.schedule
 * @subpackage api.errors
 */
class VidiunScheduleErrors extends VidiunErrors
{
	const INVALID_SCHEDULE_END_BEFORE_START = "INVALID_SCHEDULE_END_BEFORE_START;START,END;End time [@END@] must be after start time [@START@]";
	const MAX_SCHEDULE_DURATION_REACHED = "MAX_SCHEDULE_DURATION_REACHED;MAX;Maximum schedule duration [@MAX@] reached";
	const MAX_SCHEDULE_DURATION_MUST_MATCH_END_TIME = "MAX_SCHEDULE_DURATION_MUST_MATCH_END_TIME;;Duration must match end time, keep one of them null to calculate automatically";
	const RECURRENCE_CANT_BE_DELETE = "RECURRENCE_CANT_BE_DELETE;ID,PARENT_ID;Recurence [@ID@] cannot be deleted, use cancel or delete the parent recurring event [@PARENT_ID@]";
	const INVALID_SCHEDULE_EVENT_TYPE_TO_UPDATE = "SCHEDULE_EVENT_TYPE_CANT_BE_UPDATED;SOURCE_TYPE,TARGET_TYPE; Can't update from type [@SOURCE_TYPE@] to type [@TARGET_TYPE@]";

}