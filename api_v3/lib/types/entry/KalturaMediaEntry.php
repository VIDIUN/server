<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunMediaEntry extends VidiunPlayableEntry {
	/**
	 * The media type of the entry
	 * 
	 * @var VidiunMediaType
	 * @insertonly
	 * @filter eq,in,order
	 */
	public $mediaType;
	
	/**
	 * Override the default conversion quality  
	 * 
	 * @var string
	 * @insertonly
	 * @deprecated use conversionProfileId instead
	 */
	public $conversionQuality;
	
	/**
	 * The source type of the entry 
	 *
	 * @var VidiunSourceType
	 * @insertonly
	 * @filter eq,not,in,notin
	 */
	public $sourceType;
	
	/**
	 * The search provider type used to import this entry
	 *
	 * @var VidiunSearchProviderType
	 * @insertonly
	 */
	public $searchProviderType;
	
	/**
	 * The ID of the media in the importing site
	 *
	 * @var string
	 * @insertonly
	 */
	public $searchProviderId;
	
	/**
	 * The user name used for credits
	 *
	 * @var string
	 */
	public $creditUserName;
	
	/**
	 * The URL for credits
	 *
	 * @var string
	 */
	public $creditUrl;
	
	/**
	 * The media date extracted from EXIF data (For images) as Unix timestamp (In seconds)
	 *
	 * @var time
	 * @readonly
	 * @filter gte,lte
	 */
	public $mediaDate;
	
	/**
	 * The URL used for playback. This is not the download URL.
	 *
	 * @var string
	 * @readonly
	 */
	public $dataUrl;
	
	/**
	 * Comma separated flavor params ids that exists for this media entry
	 * 
	 * @var string
	 * @readonly
	 * @filter matchor,matchand
	 */
	public $flavorParamsIds;

	/**
	 * True if trim action is disabled for this entry
	 *
	 * @var VidiunNullableBoolean
	 * @readonly
	 */
	public $isTrimDisabled;

	/**
	 * Array of streams that exists on the entry
	 * @var VidiunStreamContainerArray
	 */
	public $streams;

	private static $map_between_objects = array ("mediaType", "conversionQuality", "sourceType" , "searchProviderType", // see special logic for this field below
	//"searchProviderType", // see special logic for this field below
	"searchProviderId" => "sourceId", "creditUserName" => "credit", "creditUrl" => "siteUrl", "partnerId", "mediaDate", "dataUrl", "flavorParamsIds", "isTrimDisabled", "streams" );
	
	public function __construct() {
		$this->type = VidiunEntryType::MEDIA_CLIP;
	}
	
	public function getMapBetweenObjects() {
		return array_merge ( parent::getMapBetweenObjects (), self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntry::toObject()
	 */
	public function toObject($entry = null, $props_to_skip = array()) {
		if (is_null ( $entry )) {
			$entry = new entry ();
		}
		
		$entry = parent::toObject($entry, $props_to_skip);
		
		/* @var $entry entry */
		if ($this->msDuration && ($entry->getMediaType () == VidiunMediaType::IMAGE || $this->mediaType == VidiunMediaType::IMAGE && $this->msDuration)) {
			throw new VidiunAPIException ( VidiunErrors::PROPERTY_VALIDATION_NOT_UPDATABLE, "msDuration" );
		}
		return $entry;
	}
	
}