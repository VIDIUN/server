<?php


class VidiunMediaServerSplitRecordingNowResponse extends SoapObject
{				
	public function getType()
	{
		return 'http://services.api.server.media.vidiun.com/:SplitRecordingNowResponse';
	}
					
	protected function getAttributeType($attributeName)
	{
		switch($attributeName)
		{	
			default:
				return parent::getAttributeType($attributeName);
		}
	}
					
	public function __toString()
	{
		return print_r($this, true);	
	}

	
	/**
	 *
	 * @var boolean
	 **/
	public $return;
	
}
































