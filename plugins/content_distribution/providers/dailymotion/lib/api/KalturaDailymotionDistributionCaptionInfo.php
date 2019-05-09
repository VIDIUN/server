<?php
/**
 * @package plugins.dailymotionDistribution
 * @subpackage api.objects
 *
 */
class VidiunDailymotionDistributionCaptionInfo extends VidiunObject{

	/**
	 * @var string
	 */
	public $language; 
	
	/**
	 * @var string
	 */
	public $filePath;
	
	/**
	 * @var string
	 */
	public $remoteId;
	
	/**
	 * @var VidiunDailymotionDistributionCaptionAction
	 */
	public $action;	
	
	/**
	 * @var string
	 */
	public $version;
	
	/**
	 * @var string
	 */
	public $assetId;
	
	/**
	 * @var VidiunDailymotionDistributionCaptionFormat
	 */
	public $format;
		
}