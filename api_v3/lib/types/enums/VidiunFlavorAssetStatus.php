<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunFlavorAssetStatus extends VidiunAssetStatus
{
	const CONVERTING = 1;
	const NOT_APPLICABLE = 4;
	const TEMP = 5;
	const WAIT_FOR_CONVERT = 6;
	const VALIDATING = 8;
}
