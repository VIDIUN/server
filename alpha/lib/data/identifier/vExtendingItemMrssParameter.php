<?php
/**
 * @package Core
 * @subpackage model	
 */
class vExtendingItemMrssParameter
{
	/**
	 * @var string
	 */
	protected $xpath;
	
	/**
	 * @var vObjectIdentifier
	 */
	protected $identifier;
	
	/**
	 * @var int
	 */
	protected $extensionMode;
	
	/**
	 * @return the $xpath
	 */
	public function getXpath() {
		return $this->xpath;
	}

	/**
	 * @param string $xpath
	 */
	public function setXpath($xpath) {
		$this->xpath = $xpath;
	}
	
	/**
	 * @return VObjectIdentifier
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param VObjectIdentifier $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}
	
	/**
	 * @return int
	 */
	public function getExtensionMode() {
		return $this->extensionMode;
	}

	/**
	 * @param int $extensionMode
	 */
	public function setExtensionMode($extensionMode) {
		$this->extensionMode = $extensionMode;
	}



}