<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.filters.enum
 */
class VidiunGenericDistributionProviderOrderBy extends VidiunDistributionProviderOrderBy
{
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
}
