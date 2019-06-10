<?php
/**
 * @package plugins.shortLink
 * @subpackage api.filters.enum
 */
class VidiunShortLinkOrderBy extends VidiunStringEnum
{
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
	const EXPIRES_AT_ASC = "+expiresAt";
	const EXPIRES_AT_DESC = "-expiresAt";
}
