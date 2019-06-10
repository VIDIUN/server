<?php
/**
 * @package plugins.ndnDistribution
 * @subpackage lib
 */
class NdnFeed
{

	/**
	 * @var DOMDocument
	 */
	protected $doc;
	
	/**
	 * @var DOMXPath
	 */
	protected $xpath;
	
	/**
	 * @var DOMElement
	 */
	protected $item;
	
	/**
	 * @var DOMElement
	 */
	protected $thumbnail;
	
	/**
	 * @var DOMElement
	 */
	protected $category;
	
	/**
	 * @var NdnDistributionProfile
	 */
	protected $distributionProfile;
	
	/**
	 * @param $templateName
	 * @param $distributionProfile
	 */
	public function __construct($templateName)
	{
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml/' . $templateName;
		$this->doc = new VDOMDocument();
		$this->doc->formatOutput = true;
		$this->doc->preserveWhiteSpace = false;
		$this->doc->load($xmlTemplate);
		
		//namespaces
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('live', 'http://live.com/schema/media/');
		$this->xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');
		$this->xpath->registerNamespace('abcnews', 'http://abcnews.com/content/');
				
		// item node template
		$node = $this->xpath->query('/rss/channel/item')->item(0);
		$this->item = $node->cloneNode(true);
		$node->parentNode->removeChild($node);

		// category node template
		$node = $this->xpath->query('media:category', $this->item)->item(0);
		$this->category = $node->cloneNode(true);
		$node->parentNode->removeChild($node);
				
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function setNodeValue($xpath, $value, DOMNode $contextnode = null)
	{
		if ($contextnode)
			$node = $this->xpath->query($xpath, $contextnode)->item(0);
		else 
			$node = $this->xpath->query($xpath)->item(0);
		if (!is_null($node))
		{
			// if CDATA inside, set the value of CDATA
			if ($node->childNodes->length > 0 && $node->childNodes->item(0)->nodeType == XML_CDATA_SECTION_NODE)
				$node->childNodes->item(0)->nodeValue = htmlentities($value);
			else
				$node->nodeValue = htmlentities($value);
		}
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 */
	public function getNodeValue($xpath)
	{
		$node = $this->xpath->query($xpath)->item(0);
		if (!is_null($node))
			return $node->nodeValue;
		else
			return null;
	}
	
	/**
	 * @param NdnDistributionProfile $profile
	 */
	public function setDistributionProfile(NdnDistributionProfile $profile)
	{
		$this->distributionProfile = $profile;
	}
	
	public function setChannelFields ()
	{
		vXml::setNodeValue($this->xpath,'/rss/channel/title', $this->distributionProfile->getChannelTitle());
		vXml::setNodeValue($this->xpath,'/rss/channel/link', $this->distributionProfile->getChannelLink());
		vXml::setNodeValue($this->xpath,'/rss/channel/description', $this->distributionProfile->getChannelDescription());
		vXml::setNodeValue($this->xpath,'/rss/channel/language', $this->distributionProfile->getChannelLanguage());
		vXml::setNodeValue($this->xpath,'/rss/channel/copyright', $this->distributionProfile->getChannelCopyright());
		vXml::setNodeValue($this->xpath,'/rss/channel/image/title', $this->distributionProfile->getChannelImageTitle());
		vXml::setNodeValue($this->xpath,'/rss/channel/image/url', $this->distributionProfile->getChannelImageUrl());
		vXml::setNodeValue($this->xpath,'/rss/channel/image/link', $this->distributionProfile->getChannelImageLink());				
		vXml::setNodeValue($this->xpath,'/rss/channel/pubDate', date('r',$this->distributionProfile->getCreatedAt(null)));
		
	}
	
	public function setChannelLastBuildDate($lastBuildDate)
	{
		vXml::setNodeValue($this->xpath,'/rss/channel/lastBuildDate', date('r', $lastBuildDate));
	}
	
	public function addItemXml($xml)
	{
		$tempDoc = new DOMDocument('1.0', 'UTF-8');
		$tempDoc->loadXML($xml);
	
		$importedItem = $this->doc->importNode($tempDoc->firstChild, true);
		$channelNode = $this->xpath->query('/rss/channel')->item(0);
		$channelNode->appendChild($importedItem);
	}
	
	public function getItemXml(array $values, array $flavorAssets = null, array $thumbAssets = null, $entry)
	{
		$item = $this->getItem($values, $flavorAssets, $thumbAssets, $entry);
		return $this->doc->saveXML($item);
	}
	
	/**
	 * @param array $values
	 * @param array $flavorAssets
	 * @param array $thumbAssets
	 */
	public function getItem(array $values, array $flavorAssets = null, array $thumbAssets = null, $entry)
	{		
		$item = $this->item->cloneNode(true);
		//if no thumbnail exists take the default one
		$thumbnailUrl = '';
		if (!$thumbAssets)
		{
			$thumbnailUrl = $entry->getThumbnailUrl();
		}
		vXml::setNodeValue($this->xpath,'guid', $values[NdnDistributionField::ITEM_GUID], $item);
		vXml::setNodeValue($this->xpath,'title', $values[NdnDistributionField::ITEM_TITLE], $item);
		vXml::setNodeValue($this->xpath,'link', $values[NdnDistributionField::ITEM_LINK], $item);
		vXml::setNodeValue($this->xpath,'description', $values[NdnDistributionField::ITEM_DESCRIPTION], $item);
		$pubDate = date('r', $values[NdnDistributionField::ITEM_PUB_DATE]);
		vXml::setNodeValue($this->xpath,'pubDate', $pubDate, $item);
		$endTime = date('r', $values[NdnDistributionField::ITEM_EXPIRATION_DATE]);
		vXml::setNodeValue($this->xpath,'expirationDate', $endTime, $item);
		$origReleaseDate = date('r', $values[NdnDistributionField::ITEM_LIVE_ORIGINAL_RELEASE_DATE]);
		vXml::setNodeValue($this->xpath,'live:origReleaseDate',$origReleaseDate, $item);
		vXml::setNodeValue($this->xpath,'media:title', $values[NdnDistributionField::ITEM_MEDIA_TITLE], $item);
		vXml::setNodeValue($this->xpath,'media:description', $values[NdnDistributionField::ITEM_MEDIA_DESCRIPTION], $item);
		vXml::setNodeValue($this->xpath,'media:keywords', $values[NdnDistributionField::ITEM_MEDIA_KEYWORDS], $item);
		vXml::setNodeValue($this->xpath,'media:rating', $values[NdnDistributionField::ITEM_MEDIA_RATING], $item);		
		if (!is_null($flavorAssets) && is_array($flavorAssets) && count($flavorAssets)>0)
		{
			$this->setFlavorAsset($item, $flavorAssets, $values[NdnDistributionField::ITEM_CONTENT_LANG]);
		}			
		if (!is_null($thumbAssets) && is_array($thumbAssets) && count($thumbAssets)>0)
		{
			$this->setThumbAsset($item, $thumbAssets, $values[NdnDistributionField::ITEM_THUMBNAIL_CREDIT]);			
		}
		else
		{
			$this->setThumbAssetUrl($item, $thumbnailUrl, $values[NdnDistributionField::ITEM_THUMBNAIL_CREDIT]);
		}		
		$this->addCategory($item,$values[NdnDistributionField::ITEM_MEDIA_CATEGORY]);
		vXml::setNodeValue($this->xpath,'media:copyright', $values[NdnDistributionField::ITEM_MEDIA_COPYRIGHT], $item);	
		vXml::setNodeValue($this->xpath,'media:copyright/@url', $values[NdnDistributionField::ITEM_MEDIA_COPYRIGHT_URL], $item);			
		
		return $item;
	}
	
	private function getMediaTypeString($mediaType)
	{
		$mediaTypeString=null;
		switch($mediaType){
			case(1):
				$mediaTypeString = 'video';
				break;			
			case(5):
				$mediaTypeString = 'audio';
				break;		
			default:
				break;		
		}
		return $mediaTypeString;
	}
	
	public function getAssetUrl(asset $asset)
	{
		$urlManager = DeliveryProfilePeer::getDeliveryProfile($asset->getEntryId());
		if($asset instanceof flavorAsset)
			$urlManager->initDeliveryDynamicAttributes(null, $asset);
		$url = $urlManager->getFullAssetUrl($asset);
		$url = preg_replace('/^https?:\/\//', '', $url);
		return 'http://' . $url;
	}
	
	public function getXml()
	{
		return $this->doc->saveXML();
	}
	
	public function addCategory($item, $categoryValue)
	{	
		$categories = explode(',', $categoryValue);
		if ($categories)
		{			
			foreach ($categories as $category)
			{				
				if ($category){	
					$categoryNode = $this->category->cloneNode(true);				
					$categoryNode->nodeValue = $category;	
					$beforeNode = $this->xpath->query('media:copyright', $item)->item(0);		
					$item->insertBefore($categoryNode, $beforeNode);
				}
			}
		}	
	}
	
	public function setFlavorAsset(DOMElement $item, array $flavorAssets, $flavorLang)
	{
		$flavorAsset = $flavorAssets[0];		
		/* @var $flavorAsset flavorAsset */
		$url = $this->getAssetUrl($flavorAsset);
		vXml::setNodeValue($this->xpath,'media:content/@url', $url, $item);
		vXml::setNodeValue($this->xpath,'media:content/@width', $flavorAsset->getWidth(), $item);
		vXml::setNodeValue($this->xpath,'media:content/@height', $flavorAsset->getHeight(), $item);
		//setting mime type
		$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		if(vFileSyncUtils::fileSync_exists($syncKey))
		{
			$filePath = vFileSyncUtils::getLocalFilePathForKey($syncKey, false);
			$mimeType = vFile::mimeType($filePath);
		}				
		vXml::setNodeValue($this->xpath,'media:content/@type', $mimeType, $item);
							
		if (!empty($flavorLang))
		{
			vXml::setNodeValue($this->xpath,'media:content/@lang', $flavorLang, $item);
		}
	}
	
	public function setThumbAsset(DOMElement $item, array $thumbAssets, $thumbnailCredit)
	{
		$templateNode = $this->xpath->query('./media:thumbnail', $item)->item(0);
		$parentNode = $templateNode->parentNode;		
		foreach ($thumbAssets as $thumbAsset)
		{
			if ($thumbAsset){
				$url = $this->getAssetUrl($thumbAsset);
				$thumbnailNode = $templateNode->cloneNode(true);	
				vXml::setNodeValue($this->xpath,'./@url', $url, $thumbnailNode);
				vXml::setNodeValue($this->xpath,'./@width', $thumbAsset->getWidth(), $thumbnailNode);
				vXml::setNodeValue($this->xpath,'./@height', $thumbAsset->getHeight(), $thumbnailNode);
				if (!empty($thumbnailCredit))
				{
					vXml::setNodeValue($this->xpath,'./@credit', $thumbnailCredit, $item);
				}													
				$parentNode->insertBefore($thumbnailNode,$templateNode);
			}
		}
		$parentNode->removeChild($templateNode);
	}
	
	public function setThumbAssetUrl($item, $thumbnailUrl, $thumbnailCredit)
	{
		vXml::setNodeValue($this->xpath,'media:thumbnail/@url', $thumbnailUrl, $item);
		vXml::setNodeValue($this->xpath,'media:thumbnail/@width', '320', $item);
		vXml::setNodeValue($this->xpath,'media:thumbnail/@height', '240', $item);
		if (!empty($thumbnailCredit))
		{
			vXml::setNodeValue($this->xpath,'media:thumbnail/@credit', $thumbnailCredit, $item);
		}
	}
		
	/**
	 * ndn used Z for UTC timezone in their example (2008-04-11T12:30:00Z)
	 * @param int $time
	 */
	protected function formatNdnDate($time)
	{
		$date = new DateTime('@'.$time, new DateTimeZone('UTC'));
		return str_replace('+0000', 'Z', $date->format(DateTime::ISO8601)); 
	}
	
}