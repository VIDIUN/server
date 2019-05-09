<?php
/**
 * @package plugins.quiz
 * @subpackage api.objects
 */
class VidiunQuestionCuePoint extends VidiunCuePoint
{

	/**
	 * Array of key value answerKey->optionAnswer objects
	 * @var VidiunOptionalAnswersArray
	 */
	public $optionalAnswers;


	/**
	 * @var string
	 */
	public $hint;


	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $question;

	/**
	 * @var string
	 */
	public $explanation;


	/**
	 * @var VidiunQuestionType.
	 */
	public $questionType;

	/**
	 * @var int
	 */
	public $presentationOrder;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $excludeFromScore;

	public function __construct()
	{
		$this->cuePointType = QuizPlugin::getApiValue(QuizCuePointType::QUIZ_QUESTION);
	}

	private static $map_between_objects = array
	(
		"optionalAnswers",
		"hint",
		"question" => "name",
		"explanation",
		"questionType",
		"presentationOrder",
		"excludeFromScore"
	);

	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
	* @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	*/
	public function toObject($dbObject = null, $propsToSkip = array())
	{
		if (!$dbObject)
		{
			$dbObject = new QuestionCuePoint();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbObject, $responseProfile);
		$this->optionalAnswers = VidiunOptionalAnswersArray::fromDbArray($dbObject->getOptionalAnswers(), $responseProfile);
		$dbEntry = entryPeer::retrieveByPK($dbObject->getEntryId());
		if ( !vEntitlementUtils::isEntitledForEditEntry($dbEntry) ) {
			foreach ( $this->optionalAnswers as $answer ) {
				$answer->isCorrect = VidiunNullableBoolean::NULL_VALUE;
			}
			$this->explanation = null;
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
		$dbEntry = entryPeer::retrieveByPK($this->entryId);
		QuizPlugin::validateAndGetQuiz($dbEntry);
		if ( !vEntitlementUtils::isEntitledForEditEntry($dbEntry) ) {
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
	}

}
