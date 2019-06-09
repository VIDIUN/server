<?php

class vDrmEntryContextPluginData extends PluginData {
	/**
    * @var string
    */
	protected $flavorData;

	public function getFlavorData()
	{
		return $this->flavorData;
	}

	public function setFlavorData($flavorData)
	{
		$this->flavorData = $flavorData;
	}

} // vDrmEntryContextPluginData
