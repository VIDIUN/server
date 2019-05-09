<?php
/**
 * @package api
 * @subpackage filters.enum
 */
class VidiunPlayableEntryOrderBy extends VidiunBaseEntryOrderBy
{
	const PLAYS_ASC = "+plays";
	const PLAYS_DESC = "-plays";
	const VIEWS_ASC = "+views";
	const VIEWS_DESC = "-views";
	const LAST_PLAYED_AT_ASC = "+lastPlayedAt";
	const LAST_PLAYED_AT_DESC = "-lastPlayedAt";
	const DURATION_ASC = "+duration";
	const DURATION_DESC = "-duration";
}
