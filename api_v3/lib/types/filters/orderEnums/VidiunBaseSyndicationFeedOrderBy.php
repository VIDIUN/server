<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBaseSyndicationFeedOrderBy extends VidiunStringEnum
{
	const PLAYLIST_ID_ASC = "+playlistId";
	const PLAYLIST_ID_DESC = "-playlistId";
	const NAME_ASC = "+name";
	const NAME_DESC = "-name";
	const TYPE_ASC = "+type";
	const TYPE_DESC = "-type";
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
}
