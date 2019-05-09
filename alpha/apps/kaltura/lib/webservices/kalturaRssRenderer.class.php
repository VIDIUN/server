<?php
class vidiunRssRenderer
{
	const TYPE_YAHOO = 1;
	const TYPE_TABOOLA = 2;
	
	public function __construct ( $type = self::TYPE_YAHOO )
	{
		$this->type = $type;
	}
	
	public function startMrss ( )
	{
		if ( $this->type == self::TYPE_YAHOO )
			return '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:vidiun="http://vidiun.com/playlist/1.0" >';
		if ( $this->type == self::TYPE_TABOOLA )
			return '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:vidiun="http://vidiun.com/playlist/1.0" xmlns:tv="http://taboola.com/schema/taboolavideo/1.0">';
			
	}

	public function endMrss ( )
	{
		return '</rss>';
	}
	
	// see http://search.yahoo.com/mrss
	// will create a good mRSS output for an entry
/*
 * 
 <media:content 
               url="http://www.foo.com/movie.mov" 
               fileSize="12216320" 
               type="video/quicktime"
               medium="video"
               isDefault="true" 
               expression="full" 
               bitrate="128" 
               framerate="25"
               samplingrate="44.1"
               channels="2"
               duration="185" 
               height="200"
               width="300" 
               lang="en" />
 */	

// TODO - add width & height after fixinf entry->getWidth mechanism 
	public function renderEntry ( $entry )
	{
		if  ( ! $entry instanceof  entry )
			return "";
		
		$entry_id = $entry->getId();
		
		$vidiun_elements =
			"<vidiun:entryId>" . $entry->getId() . "</vidiun:entryId>";
		
		if (isset(vCurrentContext::$partner_id) &&
			!PermissionPeer::isValidForPartner(PermissionName::FEATURE_HIDE_SENSITIVE_DATA_IN_RSS_FEED, vCurrentContext::$partner_id))
		{
			$vidiun_elements .=
				"<vidiun:views>" . ($entry->getViews() ? $entry->getViews() : "0"). "</vidiun:views>" .  
				"<vidiun:plays>" . ($entry->getPlays() ? $entry->getPlays() : "0"). "</vidiun:plays>" .
				"<vidiun:userScreenName>" . vString::xmlEncode ($entry->getUserScreenName()) . "</vidiun:userScreenName>" . 
				"<vidiun:puserId>" . $entry->getPuserId() . "</vidiun:puserId>" .
				"<vidiun:userLandingPage>" . $entry->getUserLandingPage() . "</vidiun:userLandingPage>";
		}
		else
		{
			$vidiun_elements .=
				"<vidiun:views>0</vidiun:views>" .  
				"<vidiun:plays>0</vidiun:plays>" .
				"<vidiun:userScreenName></vidiun:userScreenName>" . 
				"<vidiun:puserId></vidiun:puserId>" .
				"<vidiun:userLandingPage></vidiun:userLandingPage>";
		}
		
		$vidiun_elements .=
			"<vidiun:partnerLandingPage>" . $entry->getPartnerLandingPage() . "</vidiun:partnerLandingPage>" .
			"<vidiun:tags>" . vString::xmlEncode ($entry->getTags()) . "</vidiun:tags>" .
			"<vidiun:adminTags>" . vString::xmlEncode ($entry->getAdminTags()) . "</vidiun:adminTags>" .
			"<vidiun:votes>" . ($entry->getVotes() ? $entry->getVotes() : "0") . "</vidiun:votes>" .
			"<vidiun:rank>" . ($entry->getRank() ? $entry->getRank() : "0") . "</vidiun:rank>" .	
			"<vidiun:createdAt>" . $entry->getCreatedAt() . "</vidiun:createdAt>" .
			"<vidiun:createdAtInt>" . $entry->getCreatedAt(null) . "</vidiun:createdAtInt>" .
			"<vidiun:sourceLink>" . $entry->getSourceLink() . "</vidiun:sourceLink>" .
			"<vidiun:credit>" . $entry->getCredit() . "</vidiun:credit>" ;
		
		
		if ( $this->type == self::TYPE_TABOOLA )
		{			
			// TODO - use entry->getDisplayScope();
			$taboola_elements = $entry->getDisplayInSearch() >= 2 ? 
				"<tv:label>_KN_</tv:label>" .
				"<tv:uploader>" . $entry->getPartnerId() . "</tv:uploader>" 
				: '';
		}
		else
		{
			$taboola_elements = "";
		}
		
		// for now the partner_id & entry_id are set in the guid elementy of the item..
		// TODO - move the partner_id to be part of the primary key of the entry so entry will not appear in wrong partners
		 $mrss = '<item>' . 
		 	'<description>Vidiun Item</description>' . 
		 	'<guid isPermaLink="false">' . $entry->getPartnerId() . "|" . $entry_id . '</guid>' . 
		 	'<link>' . $entry->getPartnerLandingPage()  . '</link>'.
		 	'<pubDate>' . $entry->getCreatedAt() . '</pubDate>' . 
		 	'<media:content ' . 
               'url="' . $entry->getDataUrl() . '/ext/flv" ' .  
		 		( $entry->getMediaType() == entry::ENTRY_MEDIA_TYPE_VIDEO ? 'type="video/x-flv" ' : '  ' ) . 
               'medium="' . $entry->getTypeAsString() . '" ' . 
//               'isDefault="true" 
//               'expression="full" 
//               'bitrate="128" ' .  
//              'framerate="25" ' . 
//               'samplingrate="44.1" ' . 
//              'channels="2" ' . 
               	'duration="' . (int)( $entry->getLengthInMsecs() / 1000 ) . '" ' . 
//               	'height="' . $entry->getHeight() . '" ' .
//              	'width="' . $entry->getWidth() . '" ' .  
               	'lang="en"' .  
               	'/> '.
               	'<media:title type="plain">' .  vString::xmlEncode ( $entry->getName()) . "</media:title>" .
               	'<media:description>'. vString::xmlEncode ( $entry->getDescription() ) . '</media:description>'.
               	'<media:keywords>' . vString::xmlEncode ( $entry->getSearchText() ) . '</media:keywords>' .
               	'<media:thumbnail url="'. $entry->getThumbnailUrl() . '/width/640/height/480"/>' . 
               '<media:credit role="vidiun partner">' . $entry->getPartnerId() . '</media:credit>' .
		 		$vidiun_elements . 
               	$taboola_elements .
               '</item>';
		 
		 return $mrss; 
	}
	
	private function recursiveRenderMrssFeed ( $list , $depth )
	{
//echo __METHOD__ . ":[$depth] class:" . ( is_array ( $list ) ? "array" : get_class ( $list ) ) . "<br>" ;
		$str = "";
		if ( is_array ( $list ))
		{

//echo print_r ( $list , true ) . "<br><br>";
			if ( $depth <=  0 ) return "";
			foreach ( $list as $name => $element )
			{
				$str .= $this->recursiveRenderMrssFeed ( $element , $depth-1);
			}
		}
		else
		{
			if ( $list instanceof entryWrapper )
				$str .= $this->renderEntry( $list->getWrappedObj() );
			else
				$str .= $this->renderEntry( $list );
		}		
		return $str;
	}
	
	public function renderMrssFeed ( $list , $page=null  , $result_count=null )
	{
//print_r ( $list );		
		$str = $this->startMrss() ;
		$str .= "<channel>";
		$str .= "<description>Vidiun's mRss" . 
			( $page ? ", page: {$page}" : "" ) . 
			( $result_count ? ", results: {$result_count}" : ""  ). 
			"</description>" .
			"<title>Vidiun's mRss</title>" .
			"<link>" . vString::xmlEncode ( $_SERVER["REQUEST_URI"] ) . "</link>"	;
		
		$str .= $this->recursiveRenderMrssFeed ( $list , 3 );
		$str .= "</channel>" ;
		$str .= $this->endMrss() ;
		return $str;
	}
}
?>
