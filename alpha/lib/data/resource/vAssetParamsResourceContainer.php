<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAssetParamsResourceContainer extends vResource 
{
	/**
	 * The content resource to associate with asset params
	 * @var vContentResource
	 */
	private $resource;
	
	/**
	 * The asset params to associate with the reaource
	 * @var int
	 */
	private $assetParamsId;
	
	/**
	 * @return vContentResource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @return int
	 */
	public function getAssetParamsId()
	{
		return $this->assetParamsId;
	}

	/**
	 * @param vContentResource $resource
	 */
	public function setResource(vContentResource $resource)
	{
		$this->resource = $resource;
	}

	/**
	 * @param int $assetParamsId
	 */
	public function setAssetParamsId($assetParamsId)
	{
		$this->assetParamsId = $assetParamsId;
	}
}