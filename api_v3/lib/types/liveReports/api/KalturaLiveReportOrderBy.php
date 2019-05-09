<?php

/**
 * @package api
 * @subpackage model.enum
 */
class VidiunLiveReportOrderBy extends VidiunStringEnum
{
	const EVENT_TIME_DESC = "-eventTime";
	const PLAYS_DESC = "-plays";
	const AUDIENCE_DESC = "-audience";
	const NAME_ASC = "+name";
}