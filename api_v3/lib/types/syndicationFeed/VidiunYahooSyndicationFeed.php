<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunYahooSyndicationFeed extends VidiunBaseSyndicationFeed
{
        /**
         *
         * @var VidiunYahooSyndicationFeedCategories
         * @readonly
         */
        public $category;

        /**
         *
         * @var VidiunYahooSyndicationFeedAdultValues
         */
        public $adultContent;
        
        /**
         * feed description
         * 
         * @var string
         */
        public $feedDescription;
        
        /**
         * feed landing page (i.e publisher website)
         * 
         * @var string
         */
        public $feedLandingPage;        
        
	private static $mapBetweenObjects = array
	(
                "adultContent",
                "feedDescription",
                "feedLandingPage",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}    
	function __construct()
	{
		$this->type = VidiunSyndicationFeedType::YAHOO;
	}
}