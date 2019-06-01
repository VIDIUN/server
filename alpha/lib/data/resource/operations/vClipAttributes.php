<?php
/**
 * Base class to all operation attributes types
 *
 * @package Core
 * @subpackage model.data
 */
class vClipAttributes extends vOperationAttributes 
{
	const SYSTEM_DEFAULT_FLAVOR_PARAMS_ID = -1;
	
	/**
	 * Offset in milliseconds
	 * @var int
	 */
	private $offset;
	
	/**
	 * Duration in milliseconds
	 * @var int
	 */
	private $duration;

	/**
	 * global Offset In Destination in milliseconds
	 * @var int
	 */
	private $globalOffsetInDestination;

	/**
	 * effectsAttributes
	 * @var array vEffect
	 */
	private $effectArray;


	/* (non-PHPdoc)
	 * @see vOperationAttributes::toArray()
	 */
	public function toArray()
	{
		return array(
			'ClipOffset' => $this->offset,
			'ClipDuration' => $this->duration,
			'globalOffsetInDestination' => $this->globalOffsetInDestination,
			'effectArray' => $this->effectArray,
		);
	}
	
	/* (non-PHPdoc)
	 * @see vOperationAttributes::getApiType()
	 */
	public function getApiType()
	{
		return 'VidiunClipAttributes';
	}

	/* (non-PHPdoc)
	 * @see vOperationAttributes::getAssetParamsId()
	 */
	public function getAssetParamsId()
	{
		return self::SYSTEM_DEFAULT_FLAVOR_PARAMS_ID;
	}

	/* (non-PHPdoc)
	 * @see vOperationAttributes::getSourceType()
	 */
	public function getSourceType()
	{
		return EntrySourceType::CLIP;
	}

	/**
	 * @return int $offset
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @return int $duration
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}

	/**
	 * @param int $duration
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	/**
	 * @return int $globalOffsetInDestination
	 */
	public function getGlobalOffsetInDestination()
	{
		return $this->globalOffsetInDestination;
	}

	/**
	 * @param int $globalOffsetInDestination
	 */
	public function setGlobalOffsetInDestination($globalOffsetInDestination)
	{
		$this->globalOffsetInDestination = $globalOffsetInDestination;
	}

	/**
	 * @return vEffect[]
	 */
	public function getEffectArray()
	{
		return $this->effectArray;
	}

	/**
	 * @param array $effectArray
	 */
	public function setEffectArray($effectArray)
	{
		$this->effectArray = $effectArray;
	}


}