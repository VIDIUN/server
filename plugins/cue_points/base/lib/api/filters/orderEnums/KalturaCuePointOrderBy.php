<?php
/**
 * @package plugins.cuePoint
 * @subpackage api.filters.enum
 */
class VidiunCuePointOrderBy extends VidiunStringEnum
{
	const INT_ID_ASC = "+intId";
	const INT_ID_DESC = "-intId";
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
	const TRIGGERED_AT_ASC = "+triggeredAt";
	const TRIGGERED_AT_DESC = "-triggeredAt";
	const START_TIME_ASC = "+startTime";
	const START_TIME_DESC = "-startTime";
	const PARTNER_SORT_VALUE_ASC = "+partnerSortValue";
	const PARTNER_SORT_VALUE_DESC = "-partnerSortValue";
}
