<?php

/**
 *
 * @service quizUserEntry
 * @package plugins.quiz
 * @subpackage api.services
 */
class QuizUserEntryService extends VidiunBaseService{

	/**
	 * Submits the quiz so that it's status will be submitted and calculates the score for the quiz
	 *
	 * @action submitQuiz
	 * @actionAlias userEntry.submitQuiz
	 * @param int $id
	 * @return VidiunQuizUserEntry
	 * @throws VidiunAPIException
	 */
	public function submitQuizAction($id)
	{
		$dbUserEntry = UserEntryPeer::retrieveByPK($id);
		if (!$dbUserEntry)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $id);
		
		if ($dbUserEntry->getType() != QuizPlugin::getCoreValue('UserEntryType',QuizUserEntryType::QUIZ))
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ, $id);
		
		$dbUserEntry->setStatus(QuizPlugin::getCoreValue('UserEntryStatus', QuizUserEntryStatus::QUIZ_SUBMITTED));
		$userEntry = new VidiunQuizUserEntry();
		$userEntry->fromObject($dbUserEntry, $this->getResponseProfile());
		$entryId = $dbUserEntry->getEntryId();
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $entryId);
		
		$vQuiz = QuizPlugin::getQuizData($entry);
		if (!$vQuiz)
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ, $entryId);
		
		list($score, $numOfCorrectAnswers) = $dbUserEntry->calculateScoreAndCorrectAnswers();
		$dbUserEntry->setScore($score);
		$dbUserEntry->setNumOfCorrectAnswers($numOfCorrectAnswers);	
		if ($vQuiz->getShowGradeAfterSubmission()== VidiunNullableBoolean::TRUE_VALUE || $this->getVs()->isAdmin() == true)
		{
			$userEntry->score = $score;
		}
		else
		{
			$userEntry->score = null;
		}

		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$c->add(CuePointPeer::ENTRY_ID, $dbUserEntry->getEntryId(), Criteria::EQUAL);
		$c->add(CuePointPeer::TYPE, QuizPlugin::getCoreValue('CuePointType', QuizCuePointType::QUIZ_QUESTION));
		$questions = CuePointPeer::doSelect($c);
		$dbUserEntry->setNumOfQuestions(count($questions));
		$relevantQuestionCount = 0;
		foreach($questions as $question)
		{
			/* @var QuestionCuePoint $question*/
			if (!$question->getExcludeFromScore())
			{
				$relevantQuestionCount++;
			}
		}
		$dbUserEntry->setNumOfRelevnatQuestions($relevantQuestionCount);
		$dbUserEntry->setStatus(QuizPlugin::getCoreValue('UserEntryStatus', QuizUserEntryStatus::QUIZ_SUBMITTED));
		$dbUserEntry->save();
		self::calculateScoreByScoreType($vQuiz,$userEntry, $dbUserEntry, $score);

		return $userEntry;
	}

	protected function calculateScoreByScoreType($vQuiz, $vidiunUserEntry, $dbUserEntry, $currentScore)
	{
		if ($dbUserEntry->getVersion() == 0)
		{
			$calculatedScore = $currentScore;
		}
		else
		{
			$scoreType = $vQuiz->getScoreType();
			//retrieve user entry list order by version desc
			$userEntryVersions = userEntryPeer::retriveUserEntriesSubmitted($dbUserEntry->getVuserId(), $dbUserEntry->getEntryId(), QuizPlugin::getCoreValue('UserEntryType', QuizUserEntryType::QUIZ));
			switch ($scoreType)
			{
				case VidiunScoreType::HIGHEST:
					$calculatedScore = self::getHighestScore($userEntryVersions);
					break;

				case VidiunScoreType::LOWEST:
					$calculatedScore = self::getLowestScore($userEntryVersions);
					break;

				case VidiunScoreType::LATEST:
					$calculatedScore = reset($userEntryVersions)->getScore();
					break;

				case VidiunScoreType::FIRST:
					$calculatedScore = end($userEntryVersions)->getScore();
					break;

				case VidiunScoreType::AVERAGE:
					$calculatedScore = self::getAverageScore($userEntryVersions);
					break;
			}
		}

		$dbUserEntry->setCalculatedScore($calculatedScore);
		$dbUserEntry->save();
		if ($vQuiz->getShowGradeAfterSubmission()== VidiunNullableBoolean::TRUE_VALUE || $this->getVs()->isAdmin() == true)
		{
			$vidiunUserEntry->calculatedScore = $calculatedScore;
		}
	}

	protected function getHighestScore($userEntryVersions)
	{
		$highest =  reset($userEntryVersions)->getScore();
		foreach ($userEntryVersions as $userEntry)
		{
			if ($userEntry->getScore() > $highest)
			{
				$highest = $userEntry->getScore();
			}
		}
		return $highest;
	}

	protected function getLowestScore($userEntryVersions)
	{
		$lowest =  reset($userEntryVersions)->getScore();
		foreach ($userEntryVersions as $userEntry)
		{
			if ($userEntry->getScore() < $lowest)
			{
				$lowest = $userEntry->getScore();
			}
		}
		return $lowest;
	}

	protected function getAverageScore($userEntryVersions)
	{
		$sumScores = 0;
		foreach ($userEntryVersions as $userEntry)
		{
			$sumScores += $userEntry->getScore();
		}
		$calculatedScore = floatval($sumScores / count($userEntryVersions));
		return $calculatedScore;
	}
}
