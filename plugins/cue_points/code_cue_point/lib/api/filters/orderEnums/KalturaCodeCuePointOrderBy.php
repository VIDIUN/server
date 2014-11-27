<?php
/**
 * @package plugins.codeCuePoint
 * @subpackage api.filters.enum
 */
class KalturaCodeCuePointOrderBy extends KalturaCuePointOrderBy
{
	const END_TIME_ASC = "+endTime";
	const END_TIME_DESC = "-endTime";
	const DURATION_ASC = "+duration";
	const DURATION_DESC = "-duration";
	const TRIGGERED_AT_ASC = "+triggeredAt";
	const TRIGGERED_AT_DESC = "-triggeredAt";
}
