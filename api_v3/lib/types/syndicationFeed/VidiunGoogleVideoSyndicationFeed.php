<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunGoogleVideoSyndicationFeed extends VidiunBaseSyndicationFeed
{
        /**
         *
         * @var VidiunGoogleSyndicationFeedAdultValues
         */
        public $adultContent;
	
	private static $mapBetweenObjects = array
	(
    	"adultContent",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
        
        function __construct()
	{
		$this->type = VidiunSyndicationFeedType::GOOGLE_VIDEO;
	}
}