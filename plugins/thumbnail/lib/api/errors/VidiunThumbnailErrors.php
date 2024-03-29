<?php
/**
 * @package plugins.thumbnail
 * @subpackage api.errors
 */
class VidiunThumbnailErrors extends VidiunErrors
{
	const MISSING_PARTNER_PARAMETER_IN_URL = "MISSING_PARTNER_PARAMETER_IN_URL;;Missing partner parameter in url";
	const FAILED_TO_PARSE_ACTION = "FAILED_TO_PARSE_ACTION;actionString;Failed to parse action \"@actionString@\"";
	const FAILED_TO_PARSE_SOURCE = "FAILED_TO_PARSE_SOURCE;sourceString;Failed to parse source \"@sourceString@\"";
	const MISSING_SOURCE_ACTIONS_FOR_TYPE = "MISSING_SOURCE_ACTIONS_FOR_TYPEl;entryType;Missing source actions for type \"@entryType@\"";
	const EMPTY_IMAGE_TRANSFORMATION = "EMPTY_IMAGE_TRANSFORMATION;;No steps in the transformation";
	const FIRST_STEP_CANT_USE_COMP_ACTION = "FIRST_STEP_CANT_USE_COMP_ACTION;;The first step in the transformation cant use composite action";
	const MISSING_COMPOSITE_ACTION = "MISSING_COMPOSITE_ACTION;;Missing composite action for multiply steps transformation";
	const TRANSFORMATION_RUNTIME_ERROR = "TRANSFORMATION_RUNTIME_ERROR;;There was an error running the image transformation";
	const BAD_QUERY = "BAD_QUERY;errorString;Bad query \"@errorString@\"";
}