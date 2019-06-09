<?php
/**
 * A resource that perform operation (transcoding, clipping, cropping) before the flavor is ready.
 *
 * @package Core
 * @subpackage model.data
 */
class vOperationResource extends vContentResource 
{
	/**
	 * @var vContentResource
	 */
	private $resource;
	
	/**
	 * @var array<vOperationAttributes>
	 */
	private $operationAttributes;
	
	/**
	 * ID of alternative asset params to be used instead of the system default flavor params 
	 * @var int
	 */
	private $assetParamsId;
	
	/**
	 * @return vContentResource $resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @return array $operationAttributes
	 */
	public function getOperationAttributes()
	{
		return $this->operationAttributes;
	}
	
	/**
	 * Return enum value from EntrySourceType
	 */
	public function getSourceType()
	{
		foreach($this->operationAttributes as $operationAttributes)
		{
			/* @var $operationAttributes vOperationAttributes */
			$sourceType = $operationAttributes->getSourceType();
			if($sourceType)
				return $sourceType;
		}
		
		return null;
	}

	/**
	 * @return int $assetParamsId
	 */
	public function getAssetParamsId()
	{
		if($this->assetParamsId)
			return $this->assetParamsId;
	
		foreach($this->operationAttributes as $operationAttributes)
		{
			/* @var $operationAttributes vOperationAttributes */
			$assetParamsId = $operationAttributes->getAssetParamsId();
			if($assetParamsId)
				return $assetParamsId;
		}
		
		return null;
	}

	/**
	 * @param vContentResource $resource
	 */
	public function setResource(vContentResource $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * @param array $operationAttributes
	 */
	public function setOperationAttributes(array $operationAttributes)
	{
		$this->operationAttributes = $operationAttributes;
	}

	/**
	 * @param int $assetParamsId
	 */
	public function setAssetParamsId($assetParamsId)
	{
		$this->assetParamsId = $assetParamsId;
	}
}