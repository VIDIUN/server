<?php
/**
 * Allows user to handle quizzes
 *
 * @service quiz
 * @package plugins.quiz
 * @subpackage api.services
 */

class QuizService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if(!QuizPlugin::isAllowedPartner($this->getPartnerId()))
		{
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, QuizPlugin::PLUGIN_NAME);
		}
	}

	/**
	 * Allows to add a quiz to an entry
	 *
	 * @action add
	 * @param string $entryId
	 * @param VidiunQuiz $quiz
	 * @return VidiunQuiz
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::INVALID_USER_ID
	 * @throws VidiunQuizErrors::PROVIDED_ENTRY_IS_ALREADY_A_QUIZ
	 */
	public function addAction( $entryId, VidiunQuiz $quiz )
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ( !is_null( QuizPlugin::getQuizData($dbEntry) ) )
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_ALREADY_A_QUIZ, $entryId);

		return $this->validateAndUpdateQuizData( $dbEntry, $quiz );
	}

	/**
	 * Allows to update a quiz
	 *
	 * @action update
	 * @param string $entryId
	 * @param VidiunQuiz $quiz
	 * @return VidiunQuiz
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::INVALID_USER_ID
	 * @throws VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ
	 */
	public function updateAction( $entryId, VidiunQuiz $quiz )
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		$vQuiz = QuizPlugin::validateAndGetQuiz( $dbEntry );
		return $this->validateAndUpdateQuizData( $dbEntry, $quiz, $vQuiz->getVersion(), $vQuiz );
	}

	/**
	 * if user is entitled for this action will update quizData on entry
	 * @param entry $dbEntry
	 * @param VidiunQuiz $quiz
	 * @param int $currentVersion
	 * @param vQuiz|null $newQuiz
	 * @return VidiunQuiz
	 * @throws VidiunAPIException
	 */
	private function validateAndUpdateQuizData( entry $dbEntry, VidiunQuiz $quiz, $currentVersion = 0, vQuiz $newQuiz = null )
	{
		if ( !vEntitlementUtils::isEntitledForEditEntry($dbEntry) ) {
			VidiunLog::debug('Update quiz allowed only with admin VS or entry owner or co-editor');
			throw new VidiunAPIException(VidiunErrors::INVALID_USER_ID);
		}
		$quizData = $quiz->toObject($newQuiz);
		$quizData->setVersion( $currentVersion+1 );
		QuizPlugin::setQuizData( $dbEntry, $quizData );
		$dbEntry->save();
		$quiz->fromObject( $quizData );
		return $quiz;
	}

	/**
	 * Allows to get a quiz
	 *
	 * @action get
	 * @param string $entryId
	 * @return VidiunQuiz
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 *
	 */
	public function getAction( $entryId )
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$vQuiz = QuizPlugin::getQuizData($dbEntry);
		if ( is_null( $vQuiz ) )
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ, $entryId);

		$quiz = new VidiunQuiz();
		$quiz->fromObject( $vQuiz );
		return $quiz;
	}

	/**
	 * List quiz objects by filter and pager
	 *
	 * @action list
	 * @param VidiunQuizFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunQuizListResponse
	 */
	function listAction(VidiunQuizFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if (!$filter)
			$filter = new VidiunQuizFilter;

		if (! $pager)
			$pager = new VidiunFilterPager ();

		return $filter->getListResponse($pager, $this->getResponseProfile());
	}

	/**
	 * creates a pdf from quiz object
	 * The Output type defines the file format in which the quiz will be generated
	 * Currently only PDF files are supported
	 * @action serve
	 * @param string $entryId
	 * @param VidiunQuizOutputType $quizOutputType
	 * @return file
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ
	 */
	public function serveAction($entryId, $quizOutputType)
	{
		VidiunLog::debug("Create a PDF Document for entry id [ " .$entryId. " ]");
		$dbEntry = entryPeer::retrieveByPK($entryId);

		//validity check
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		//validity check
		$vQuiz = QuizPlugin::getQuizData($dbEntry);
		if ( is_null( $vQuiz ) )
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ, $entryId);

		//validity check
		if (!$vQuiz->getAllowDownload())
		{
			throw new VidiunAPIException(VidiunQuizErrors::QUIZ_CANNOT_BE_DOWNLOAD);
		}
		//create a pdf
		$vp = new vQuizPdf($entryId);
		$vp->createQuestionPdf();
		$resultPdf = $vp->submitDocument();
		$fileName = $dbEntry->getName().".pdf";
		header('Content-Disposition: attachment; filename="'.$fileName.'"');
		return new vRendererString($resultPdf, 'application/x-download');
	}


	/**
	 * sends a with an api request for pdf from quiz object
	 *
	 * @action getUrl
	 * @param string $entryId
	 * @param VidiunQuizOutputType $quizOutputType
	 * @return string
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ
	 * @throws VidiunQuizErrors::QUIZ_CANNOT_BE_DOWNLOAD
	 */
	public function getUrlAction($entryId, $quizOutputType)
	{
		VidiunLog::debug("Create a URL PDF Document download for entry id [ " .$entryId. " ]");

		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$vQuiz = QuizPlugin::getQuizData($dbEntry);
		if ( is_null( $vQuiz ) )
			throw new VidiunAPIException(VidiunQuizErrors::PROVIDED_ENTRY_IS_NOT_A_QUIZ, $entryId);

		//validity check
		if (!$vQuiz->getAllowDownload())
		{
			throw new VidiunAPIException(VidiunQuizErrors::QUIZ_CANNOT_BE_DOWNLOAD);
		}

		$finalPath ='/api_v3/service/quiz_quiz/action/serve/quizOutputType/';

		$finalPath .="$quizOutputType";
		$finalPath .= '/entryId/';
		$finalPath .="$entryId";
		$vsObj = $this->getVs();
		$vsStr = ($vsObj) ? $vsObj->getOriginalString() : null;
		$finalPath .= "/vs/".$vsStr;

		$partnerId = $this->getPartnerId();
		$downloadUrl = myPartnerUtils::getCdnHost($partnerId) . $finalPath;

		return $downloadUrl;
	}
}
