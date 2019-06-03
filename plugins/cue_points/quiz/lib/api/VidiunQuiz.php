<?php
/**
 * @package plugins.quiz
 * @subpackage api.objects
 */
class VidiunQuiz extends VidiunObject
{
	/**
	 *
	 * @var int
	 * @readonly
	 */
	public $version;

	/**
	 * Array of key value ui related objects
	 * @var VidiunKeyValueArray
	 */
	public $uiAttributes;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $showResultOnAnswer;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $showCorrectKeyOnAnswer;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $allowAnswerUpdate;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $showCorrectAfterSubmission;


	/**
	 * @var VidiunNullableBoolean
	 */
	public $allowDownload;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $showGradeAfterSubmission;

	/**
	 * @var int
	 */
	public $attemptsAllowed;

	/**
	 * @var VidiunScoreType
	 */
	public $scoreType;


	private static $mapBetweenObjects = array
	(
		"version",
		"uiAttributes",
		"showResultOnAnswer" => "showCorrect",
		"showCorrectKeyOnAnswer" => "showCorrectKey",
		"allowAnswerUpdate",
		"showCorrectAfterSubmission",
		"allowDownload",
		"showGradeAfterSubmission",
		"attemptsAllowed",
		"scoreType",
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new vQuiz();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}
}
