<?php
/**
 * @package plugins.youtubeApiDistribution
 * @subpackage api.objects
 *
 */
class VidiunYouTubeApiCaptionDistributionInfo extends VidiunObject{

	/**
	 * @var string
	 */
	public $language; 
	
	/**
	 * @var string
	 */
	public $label; 
	
	/**
	 * @var string
	 */
	public $filePath;

	/**
	 * @var string
	 */
	public $remoteId;
	
	/**
	 * @var VidiunYouTubeApiDistributionCaptionAction
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
		
}