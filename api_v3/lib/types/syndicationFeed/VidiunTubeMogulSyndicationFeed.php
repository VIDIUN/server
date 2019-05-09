<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunTubeMogulSyndicationFeed extends VidiunBaseSyndicationFeed
{
        /**
         *
         * @var VidiunTubeMogulSyndicationFeedCategories
         * @readonly
         */
        public $category;
        
	function __construct()
	{
		$this->type = VidiunSyndicationFeedType::TUBE_MOGUL;
	}
        
	private static $mapBetweenObjects = array
	(
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
        
        public function toObject($object_to_fill = null , $props_to_skip = array())
        {
            $categories = explode(',', $this->categories);
            $numCategories = array();
            foreach($categories as $category)
            {
                $numCategories[] = $this->getCategoryId($category);
            }
            $this->categories = implode(',', $numCategories);
            parent::toObject($object_to_fill);
            $this->categories = implode(',', $categories);
        }
        
        public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
        {
            parent::doFromObject($source_object, $responseProfile);
            $categories = explode(',', $this->categories);
            $strCategories = array();
            foreach($categories as $category)
            {
                $strCategories[] = $this->getCategoryName($category);
            }
            $this->categories = implode(',', $strCategories);
        }
        
        private static $mapCategories = array(
            VidiunTubeMogulSyndicationFeedCategories::ARTS_AND_ANIMATION => 1,
            VidiunTubeMogulSyndicationFeedCategories::COMEDY => 3,
            VidiunTubeMogulSyndicationFeedCategories::ENTERTAINMENT => 4,
            VidiunTubeMogulSyndicationFeedCategories::MUSIC => 5,
            VidiunTubeMogulSyndicationFeedCategories::NEWS_AND_BLOGS => 6,
            VidiunTubeMogulSyndicationFeedCategories::SCIENCE_AND_TECHNOLOGY => 7,
            VidiunTubeMogulSyndicationFeedCategories::SPORTS => 8,
            VidiunTubeMogulSyndicationFeedCategories::TRAVEL_AND_PLACES => 9,
            VidiunTubeMogulSyndicationFeedCategories::VIDEO_GAMES => 10,
            VidiunTubeMogulSyndicationFeedCategories::ANIMALS_AND_PETS => 11,
            VidiunTubeMogulSyndicationFeedCategories::AUTOS => 12,
            VidiunTubeMogulSyndicationFeedCategories::VLOGS_PEOPLE => 13,
            VidiunTubeMogulSyndicationFeedCategories::HOW_TO_INSTRUCTIONAL_DIY => 14,
            VidiunTubeMogulSyndicationFeedCategories::COMMERCIALS_PROMOTIONAL => 15,
            VidiunTubeMogulSyndicationFeedCategories::FAMILY_AND_KIDS => 16,
        );
	public static function getCategoryId( $category )
	{
            return self::$mapCategories[$category];
	}
        
        public static function getCategoryName( $id )
        {
            $arrCategories = array_flip(self::$mapCategories);
            return $arrCategories[$id];
        }
}