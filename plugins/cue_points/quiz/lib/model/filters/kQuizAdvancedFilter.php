<?php
/**
 * @package plugins.quiz
 * @subpackage model.filters
 */
class vQuizAdvancedFilter extends AdvancedSearchFilterItem
{
	/**
	 * @var boolean
	 */
	protected $isQuiz = false;

	/**
	 * @return boolean
	 */
	public function getIsQuiz()
	{
		return $this->isQuiz;
	}

	/**
	 * @param boolean $isQuiz
	 */
	public function setIsQuiz($isQuiz)
	{
		$this->isQuiz = $isQuiz;
	}

	/* (non-PHPdoc)
	 * @see AdvancedSearchFilterItem::applyCondition()
	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
		$query->addCondition(entryIndex::DYNAMIC_ATTRIBUTES . '.' .  QuizPlugin::getDynamicAttributeName() . ' = ' . ( $this->isQuiz ?  '1' : '0' ) );
	}

}