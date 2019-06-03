<?php
/**
 * @package infra
 * @subpackage Plugins
 */
class VidiunDependency
{
	/**
	 * @var string
	 */
	protected $pluginName;
	
	/**
	 * @var VidiunVersion
	 */
	protected $minVersion;
	
	/**
	 * @param string $pluginName
	 * @param VidiunVersion $minVersion
	 */
	public function __construct($pluginName, VidiunVersion $minVersion = null)
	{
		$this->pluginName = $pluginName;
		$this->minVersion = $minVersion;
	}
	
	/**
	 * @return string plugin name
	 */
	public function getPluginName()
	{
		return $this->pluginName;
	}

	/**
	 * @return VidiunVersion minimum version
	 */
	public function getMinimumVersion()
	{
		return $this->minVersion;
	}
}