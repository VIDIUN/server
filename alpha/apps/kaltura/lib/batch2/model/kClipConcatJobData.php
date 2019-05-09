<?php
/**
 * @package Core
 * @subpackage model.data
 */

class vClipConcatJobData extends vJobData
{
	/**$destEntryId
	 * @var string
	 */
	private $destEntryId;

	/**$tempEntryId
	 * @var string
	 */
	private $tempEntryId;

	/**sourceEntryId
	 * @var string
	 */
	private $sourceEntryId;

	/**importUrl
	 * @var string
	 */
	private $importUrl;

	/** $partnerId
	 * @var int
	 */
	private $partnerId;

	/** $priority
	 * @var int
	 */
	private $priority;

	/** clip operations
	 * @var array $operationAttributes
	 */
	private $operationAttributes;

	/**
	 * @bool clipManagerState
	 */
	private $importNeeded;


	public function __construct($importUrl = null)
	{
		if($importUrl)
		{
			$this->importUrl = $importUrl;
			$this->importNeeded = true;
		}
		else
		{
			$this->importNeeded = false;
		}
	}

	/**
	 * @return string $entryId
	 */
	public function getDestEntryId()
	{
		return $this->destEntryId;
	}

	/**
	 * @param string $entryId
	 */
	public function setDestEntryId($entryId)
	{
		$this->destEntryId = $entryId;
	}

	/**
	 * @return string $entryId
	 */
	public function getTempEntryId()
	{
		return $this->tempEntryId;
	}

	/**
	 * @param string $sourceEntryId
	 */
	public function setSourceEntryId($sourceEntryId)
	{
		$this->sourceEntryId = $sourceEntryId;
	}

	/**
	 * @return string $sourceEntryId
	 */
	public function getSourceEntryId()
	{
		return $this->sourceEntryId;
	}

	/**
	 * @param string $importUrl
	 */
	public function setImportUrl($importUrl)
	{
		$this->importUrl = $importUrl;
	}

	/**
	 * @return string $importUrl
	 */
	public function getImportUrl()
	{
		return $this->importUrl;
	}

	/**
	 * @param string $entryId
	 */
	public function setTempEntryId($entryId)
	{
		$this->tempEntryId = $entryId;
	}

	/**
	 * @return string $partnerId
	 */
	public function getPartnerId()
	{
		return $this->partnerId;
	}

	/**
	 * @param string $partnerId
	 */
	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
	}


	/**
	 * @return string $priority
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param string $priority
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * @return vOperationAttributes[] $operationAttributes
	 */
	public function getOperationAttributes()
	{
		return $this->operationAttributes;
	}

	/**
	 * @param vOperationAttributes[] $operationAttributes
	 */
	public function setOperationAttributes($operationAttributes)
	{
		$this->operationAttributes = $operationAttributes;
	}

	public function setImportNeeded($isNeeded)
	{
		$this->importNeeded = $isNeeded;
	}

	public function getImportNeeded()
	{
		return $this->importNeeded;
	}



}