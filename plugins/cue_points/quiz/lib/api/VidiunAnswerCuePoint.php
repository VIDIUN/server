<?php
/**
 * @package plugins.quiz
 * @subpackage api.objects
 */
class VidiunAnswerCuePoint extends VidiunCuePoint
{
	/**
	 * @var string
	 * @filter eq,in
	 * @insertonly
	 */
	public $parentId;

	/**
	 * @var string
	 * @filter eq,in
	 * @insertonly
	 */
	public $quizUserEntryId;

	/**
	 * @var string
	 */
	public $answerKey;

	/**
	* @var string
	* @maxLength 1024
	*/
	public $openAnswer;

	/**
	 * @var VidiunNullableBoolean
	 * @readonly
	 */
	public $isCorrect;

	/**
	 * Array of string
	 * @var VidiunStringArray
	 * @readonly
	 */
	public $correctAnswerKeys;

	/**
	 * @var string
	 * @readonly
	 */
	public $explanation;

	/**
	* @var string
	* @maxLength 1024
	*/
	public $feedback;


	public function __construct()
	{
		$this->cuePointType = QuizPlugin::getApiValue(QuizCuePointType::QUIZ_ANSWER);
	}

	private static $map_between_objects = array
	(
		"quizUserEntryId",
		"answerKey",
		"parentId",
		"correctAnswerKeys",
		"isCorrect",
		"explanation",
		"openAnswer",
		"feedback"
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
			$dbObject = new AnswerCuePoint();
		}

		return parent::toObject($dbObject, $propsToSkip);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($dbObject, $responseProfile);

		$dbEntry = entryPeer::retrieveByPK($dbObject->getEntryId());
		if ( !vEntitlementUtils::isEntitledForEditEntry($dbEntry))
		{
			/**
			 * @var vQuiz $vQuiz
			 */
			$vQuiz = QuizPlugin::validateAndGetQuiz( $dbEntry );

			$dbUserEntry = UserEntryPeer::retrieveByPK($this->quizUserEntryId);
			if ($dbUserEntry && $dbUserEntry->getStatus() == QuizPlugin::getCoreValue('UserEntryStatus', QuizUserEntryStatus::QUIZ_SUBMITTED))
			{
				if (!$vQuiz->getShowCorrectAfterSubmission())
				{
					$this->isCorrect = null;
					$this->correctAnswerKeys = null;
					$this->explanation = null;
				}
			}
			else
			{
				if (!$vQuiz->getShowCorrect()) {
					$this->isCorrect = null;
				}
				if (!$vQuiz->getShowCorrectKey())
				{
					$this->correctAnswerKeys = null;
					$this->explanation = null;
				}
			}
		}
	}

	/*
	 * @param string $cuePointId
	 * @throw VidiunAPIException - when parent cue points is missing or not a question cue point or doesn't belong to the same entry
	 */
	public function validateParentId($cuePointId = null)
	{
		if ($this->isNull('parentId'))
			throw new VidiunAPIException(VidiunQuizErrors::PARENT_ID_IS_MISSING);

		$dbParentCuePoint = CuePointPeer::retrieveByPK($this->parentId);
		if (!$dbParentCuePoint)
			throw new VidiunAPIException(VidiunCuePointErrors::PARENT_CUE_POINT_NOT_FOUND, $this->parentId);

		if (!($dbParentCuePoint instanceof QuestionCuePoint))
			throw new VidiunAPIException(VidiunQuizErrors::WRONG_PARENT_TYPE, $this->parentId);

		if ($dbParentCuePoint->getEntryId() != $this->entryId)
			throw new VidiunAPIException(VidiunCuePointErrors::PARENT_CUE_POINT_DO_NOT_BELONG_TO_THE_SAME_ENTRY);

	}

	protected function validateUserEntry()
	{
		$dbUserEntry = UserEntryPeer::retrieveByPK($this->quizUserEntryId);
		if (!$dbUserEntry)
			throw new VidiunAPIException(VidiunErrors::USER_ENTRY_NOT_FOUND, $this->quizUserEntryId);
		if ($dbUserEntry->getEntryId() !== $this->entryId)
		{
			throw new VidiunAPIException(VidiunCuePointErrors::USER_ENTRY_DOES_NOT_MATCH_ENTRY_ID, $this->quizUserEntryId);
		}
		if (!vCurrentContext::$is_admin_session)
		{
			if ($dbUserEntry->getStatus() === QuizPlugin::getCoreValue('UserEntryStatus', QuizUserEntryStatus::QUIZ_SUBMITTED))
			{
				throw new VidiunAPIException(VidiunQuizErrors::USER_ENTRY_QUIZ_ALREADY_SUBMITTED);
			}
			if ($dbUserEntry->getVuserId() != vCurrentContext::getCurrentVsVuserId()) 
			{
			    throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
			}
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
		$this->validateParentId();
		$this->validateUserEntry();
		if ($this->feedback != null && !vEntitlementUtils::isEntitledForEditEntry($dbEntry) )
		{
			VidiunLog::debug('Insert feedback on answer cue point is allowed only with admin VS or entry owner or co-editor');
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
	}

	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUpdate($sourceObject, $propertiesToSkip);
		if(!$this->entryId)
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'VidiunAnswerCuePoint:entryId');
		}
		$dbEntry = entryPeer::retrieveByPK($this->entryId);
		$vQuiz = QuizPlugin::validateAndGetQuiz($dbEntry);
		$this->validateUserEntry();
		if ( !$vQuiz->getAllowAnswerUpdate() && !vCurrentContext::$is_admin_session) 
		{
			throw new VidiunAPIException(VidiunQuizErrors::ANSWER_UPDATE_IS_NOT_ALLOWED, $sourceObject->getEntryId());
		}
		if ($this->feedback != null && !vEntitlementUtils::isEntitledForEditEntry($dbEntry) )
		{
			VidiunLog::debug('Update feedback on answer cue point is allowed only with admin VS or entry owner or co-editor');
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
	}
}
