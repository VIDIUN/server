<?php
/**
 * @package plugins.fileSync
 * @subpackage api.filters.enum
 */
class VidiunFileSyncOrderBy extends VidiunStringEnum
{
	const VERSION_ASC = "+version";
	const VERSION_DESC = "-version";
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
	const READY_AT_ASC = "+readyAt";
	const READY_AT_DESC = "-readyAt";
	const SYNC_TIME_ASC = "+syncTime";
	const SYNC_TIME_DESC = "-syncTime";
	const FILE_SIZE_ASC = "+fileSize";
	const FILE_SIZE_DESC = "-fileSize";
}
