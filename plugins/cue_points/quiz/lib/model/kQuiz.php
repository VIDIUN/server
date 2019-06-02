<?php
/**
 * Quiz data on entry
 *
 * @package plugins.quiz
 * @subpackage model
 *
 */

class vQuiz {

	/**
	 *
	 * @var int
	 */
	protected $version = 0;


	/**
	 * Array of key value ui related objects
	 * @var VidiunKeyValueArray
	 */
	protected $uiAttributes;

	/**
	 * @var boolean
	 */
	protected $showCorrect;

	/**
	 * @var boolean
	 */
	protected $showCorrectKey;

	/**
	 * @var boolean
	 */
	protected $allowAnswerUpdate;

	/**
	 * @var boolean
	 */
	protected $showCorrectAfterSubmission;
	/**
	 * @var boolean
	 */
	protected $allowDownload;

	/**
	 * @var boolean
	 */
	protected $showGradeAfterSubmission;

	/**
	 * @var int
	 */
	protected $attemptsAllowed;

	/**
	 * @var VidiunScoreType
	 */
	protected $scoreType;

	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param int $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}

	/**
	 * @return VidiunKeyValueArray
	 */
	public function getUiAttributes()
	{
		return $this->uiAttributes;
	}

	/**
	 * @param VidiunKeyValueArray $uiAttributes
	 */
	public function setUiAttributes($uiAttributes)
	{
		$this->uiAttributes = $uiAttributes;
	}

	/**
	 * @return boolean
	 */
	public function getShowCorrect()
	{
		return $this->showCorrect;
	}

	/**
	 * @param boolean $showCorrect
	 */
	public function setShowCorrect($showCorrect)
	{
		$this->showCorrect = $showCorrect;
	}

	/**
	 * @return boolean
	 */
	public function getShowCorrectKey()
	{
		return $this->showCorrectKey;
	}

	/**
	 * @param boolean $showCorrectKey
	 */
	public function setShowCorrectKey($showCorrectKey)
	{
		$this->showCorrectKey = $showCorrectKey;
	}

	/**
	 * @return boolean
	 */
	public function getAllowAnswerUpdate()
	{
		return $this->allowAnswerUpdate;
	}

	/**
	 * @param boolean $allowAnswerUpdate
	 */
	public function setAllowAnswerUpdate($allowAnswerUpdate)
	{
		$this->allowAnswerUpdate = $allowAnswerUpdate;
	}

	/**
	 * @return boolean
	 */
	public function getShowCorrectAfterSubmission()
	{
		return $this->showCorrectAfterSubmission;
	}

	/**
	 * @param boolean $showCorrectAfterSubmission
	 */
	public function setShowCorrectAfterSubmission($showCorrectAfterSubmission)
	{
		$this->showCorrectAfterSubmission = $showCorrectAfterSubmission;
	}

	/**
	 * @return boolean
	 */
	public function getAllowDownload()
	{
		return $this->allowDownload;
	}

	/**
	 * @param boolean $allowDownloadQuiz
	 */
	public function setAllowDownload($allowDownload)
	{
		$this->allowDownload = $allowDownload;
	}

	/**
	 * @return boolean
	 */
	public function getShowGradeAfterSubmission()
	{
		return $this->showGradeAfterSubmission;
	}

	/**
	 * @param boolean $showAfterSubmit
	 */
	public function setShowGradeAfterSubmission($showAfterSubmit)
	{
		$this->showGradeAfterSubmission = $showAfterSubmit;
	}

	/**
	 * @return int
	 */
	public function getAttemptsAllowed()
	{
		return $this->attemptsAllowed;
	}

	/**
	 * @param int $attemptsAllowed
	 */
	public function setAttemptsAllowed($attemptsAllowed)
	{
		$this->attemptsAllowed = $attemptsAllowed;
	}

	/**
	 * @return VidiunScoreType
	 */
	public function getScoreType()
	{
		return $this->scoreType;
	}

	/**
	 * @param VidiunScoreType
	 */
	public function setScoreType($scoreType)
	{
		$this->scoreType = $scoreType;
	}

}
