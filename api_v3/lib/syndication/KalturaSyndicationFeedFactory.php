<?php
/**
 * @package api
 * @subpackage objects.factory
 */
class VidiunSyndicationFeedFactory
{
	static function getInstanceByType ($type)
	{
		switch ($type) 
		{
			case VidiunSyndicationFeedType::GOOGLE_VIDEO:
				$obj = new VidiunGoogleVideoSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::YAHOO:
				$obj = new VidiunYahooSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::ITUNES:
				$obj = new VidiunITunesSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::TUBE_MOGUL:
				$obj = new VidiunTubeMogulSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::VIDIUN:
				$obj = new VidiunGenericSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::VIDIUN_XSLT:
				$obj = new VidiunGenericXsltSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::OPERA_TV_SNAP:
				$obj = new VidiunOperaSyndicationFeed();
				break;
			case VidiunSyndicationFeedType::ROKU_DIRECT_PUBLISHER:
				$obj = new VidiunRokuSyndicationFeed();
				break;
			default:
				$obj = new VidiunBaseSyndicationFeed();
				break;
		}
		
		return $obj;
	}
	
	static function getRendererByType($type)
	{
		switch ($type)
		{
			case VidiunSyndicationFeedType::GOOGLE_VIDEO:
				$obj = new GoogleVideoFeedRenderer();
				break;
			case VidiunSyndicationFeedType::YAHOO:
				$obj = new YahooFeedRenderer();
				break;
			case VidiunSyndicationFeedType::ITUNES:
				$obj = new ITunesFeedRenderer();
				break;
			case VidiunSyndicationFeedType::TUBE_MOGUL:
				$obj = new TubeMogulFeedRenderer();
				break;
			case VidiunSyndicationFeedType::VIDIUN:
			case VidiunSyndicationFeedType::VIDIUN_XSLT:
			case VidiunSyndicationFeedType::OPERA_TV_SNAP:
			case VidiunSyndicationFeedType::ROKU_DIRECT_PUBLISHER:
			default:
				return new VidiunFeedRenderer();
				break;
		}
		return $obj;
	}
}
