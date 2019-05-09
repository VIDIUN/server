<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vContextDataResult 
{
	/**
	 * Array of messages as received from the invalidated rules 
	 * @var array<string>
	 */
	private $messages = array();
	
	/**
	 * Array of actions as received from the invalidated rules
	 * @var array<vRuleAction>
	 */
	private $actions = array();
	
	/**
	 * @return array<string>
	 */
	public function getMessages() 
	{
		return $this->messages;
	}

	/**
	 * @return array<vRuleAction>
	 */
	public function getActions() 
	{
		return $this->actions;
	}

	/**
	 * @param string $message
	 */
	public function addMessage($message) 
	{
		$this->messages[] = $message;
	}

	/**
	 * @param vRuleAction $action
	 */
	public function addAction(vRuleAction $action) 
	{
		$this->actions[] = $action;
	}

	/**
	 * code to messages map received from the invalidated rules
	 * @var array
	 */
	private $ruleCodeToMessages = array();

	/**
	 * @var bool
	 */
	private $shouldHandleRuleCodes = false;

	/**
	 * @param $flag
	 */
	public function setShouldHandleRuleCodes($flag)
	{
		$this->shouldHandleRuleCodes = $flag;
	}

	/**
	 * @param $ruleCode
	 * @param $message
	 */
	public function addCodeAndMessage($message, $ruleCode = "")
	{
		$this->ruleCodeToMessages[$ruleCode][] = $message;
	}

	/**
	 * @param array
	 */
	public function getRulesCodesMap()
	{
		return $this->ruleCodeToMessages;
	}

	public function shouldHandleRuleCodes()
	{
		return $this->shouldHandleRuleCodes;
	}
	
}
