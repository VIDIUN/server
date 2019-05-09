<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vPlaybackContext {

	/**
	 * @var array<vPlaybackSource>
	 */
	protected $sources;

	/**
	 * @var array<vPlaybackCaption>
	 */
	protected $playbackCaptions;

	/**
	 * @var array
	 */
	protected $flavorAssets;

	/**
	 * Array of actions as received from the invalidated rules
	 * @var array<vRuleAction>
	 */
	protected $actions;

	/**
	 * Array of actions as received from the invalidated rules
	 * @var array<vAccessControlMessage>
	 */
	protected $messages;


	/**
	 * @return array<vPlaybackSource>
	 */
	public function getSources()
	{
		return $this->sources;
	}

	/**
	 * @param array $sources
	 */
	public function setSources($sources)
	{
		$this->sources = $sources;
	}

	/**
	 * @return array
	 */
	public function getFlavorAssets()
	{
		return $this->flavorAssets;
	}

	/**
	 * @param array $flavorAssets
	 */
	public function setFlavorAssets($flavorAssets)
	{
		$this->flavorAssets = $flavorAssets;
	}

	/**
	 * @return array
	 */
	public function getPlaybackCaptions()
	{
		return $this->playbackCaptions;
	}

	/**
	 * @param array $playbackCaptions
	 */
	public function setPlaybackCaptions($playbackCaptions)
	{
		$this->playbackCaptions = $playbackCaptions;
	}

	/**
	 * @return array<string>
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * @param array $messages
	 */
	public function setMessages($messages)
	{
		$this->messages = $messages;
	}

	/**
	 * @return array<vRuleAction>
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param array $actions
	 */
	public function setActions($actions)
	{
		$this->actions = $actions;
	}

}
