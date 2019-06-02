<?php
/**
 * @package plugins.uverseDistribution
 * @subpackage lib
 */
class UverseFeed
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
	 * @var UverseDistributionProfile
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
		
		// thumbnail node template
		$node = $this->xpath->query('media:thumbnail', $this->item)->item(0);
		$this->thumbnail = $node->cloneNode(true);
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
	 * @param UverseDistributionProfile $profile
	 */
	public function setDistributionProfile(UverseDistributionProfile $profile)
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
	
	public function getItemXml(array $values, asset $flavorAsset, $flavorAssetRemoteUrl, array $thumbAssets = null)
	{
		$item = $this->getItem($values, $flavorAsset, $thumbAssets);
		return $this->doc->saveXML($item);
	}
	
	/**
	 * @param array $values
	 * @param asset $flavorAsset
	 * @param array $thumbAssets
	 * @param entry $entry
	 */
	public function getItem(array $values, asset $flavorAsset, $flavorAssetRemoteUrl, array $thumbAssets = null)
	{		
		$item = $this->item->cloneNode(true);
		vXml::setNodeValue($this->xpath,'guid', $values[UverseDistributionField::ITEM_GUID], $item);
		vXml::setNodeValue($this->xpath,'title', $values[UverseDistributionField::ITEM_TITLE], $item);
		vXml::setNodeValue($this->xpath,'link', $values[UverseDistributionField::ITEM_LINK], $item);
		vXml::setNodeValue($this->xpath,'description', $values[UverseDistributionField::ITEM_DESCRIPTION], $item);
		$pubDate = date('r', $values[UverseDistributionField::ITEM_PUB_DATE]);
		vXml::setNodeValue($this->xpath,'pubDate', $pubDate, $item);
		$endTime = date('r', $values[UverseDistributionField::ITEM_EXPIRATION_DATE]);
		vXml::setNodeValue($this->xpath,'expirationDate', $endTime, $item);
		$origReleaseDate = date('r', $values[UverseDistributionField::ITEM_LIVE_ORIGINAL_RELEASE_DATE]);
		vXml::setNodeValue($this->xpath,'live:origReleaseDate',$origReleaseDate, $item);
		vXml::setNodeValue($this->xpath,'media:title', $values[UverseDistributionField::ITEM_MEDIA_TITLE], $item);
		vXml::setNodeValue($this->xpath,'media:description', $values[UverseDistributionField::ITEM_MEDIA_DESCRIPTION], $item);
		vXml::setNodeValue($this->xpath,'media:keywords', $values[UverseDistributionField::ITEM_MEDIA_KEYWORDS], $item);
		vXml::setNodeValue($this->xpath,'media:rating', $values[UverseDistributionField::ITEM_MEDIA_RATING], $item);		
		if (!is_null($flavorAsset))
			$this->setFlavorAsset($item, $flavorAsset, $flavorAssetRemoteUrl, $values[UverseDistributionField::ITEM_CONTENT_LANG]);
			
		if (is_array($thumbAssets))
			foreach($thumbAssets as $thumbAsset)
				$this->addThumbAsset($item, $thumbAsset, $values[UverseDistributionField::ITEM_THUMBNAIL_CREDIT]);
							
		$this->addCategory($item,$values[UverseDistributionField::ITEM_MEDIA_CATEGORY]);
		vXml::setNodeValue($this->xpath,'media:copyright', $values[UverseDistributionField::ITEM_MEDIA_COPYRIGHT], $item);	
		vXml::setNodeValue($this->xpath,'media:copyright/@url', $values[UverseDistributionField::ITEM_MEDIA_COPYRIGHT_URL], $item);
		
		return $item;			
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
	
	public function setFlavorAsset(DOMElement $item, asset $flavorAsset, $flavorAssetRemoteUrl, $lang)
	{
		vXml::setNodeValue($this->xpath,'media:content/@url', $flavorAssetRemoteUrl, $item);
		vXml::setNodeValue($this->xpath,'media:content/@width', $flavorAsset->getWidth(), $item);
		vXml::setNodeValue($this->xpath,'media:content/@height', $flavorAsset->getHeight(), $item);
		vXml::setNodeValue($this->xpath,'media:content/@type', $this->getContentType($flavorAssetRemoteUrl), $item);
		if ($lang)
			vXml::setNodeValue($this->xpath,'media:content/@lang', $lang, $item);
	}
	
	protected function getContentType($url)
	{
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		switch($ext)
		{
			case 'mp4':
				return 'video/mp4';
			case 'flv':
				return 'video/x-flv';
		}
	}
	
	public function addThumbAsset(DOMElement $item, asset $thumbAsset, $thumbnailCredit)
	{
		$thumbnailNode = $this->thumbnail->cloneNode(true);
		$item->appendChild($thumbnailNode);
		$url = $this->getAssetUrl($thumbAsset);
		
		vXml::setNodeValue($this->xpath,'@url', $url, $thumbnailNode);
		vXml::setNodeValue($this->xpath,'@width', $thumbAsset->getWidth(), $thumbnailNode);
		vXml::setNodeValue($this->xpath,'@height', $thumbAsset->getHeight(), $thumbnailNode);
		
		if ($thumbnailCredit)
			vXml::setNodeValue($this->xpath,'@credit', $thumbnailCredit, $thumbnailNode);
	}
}