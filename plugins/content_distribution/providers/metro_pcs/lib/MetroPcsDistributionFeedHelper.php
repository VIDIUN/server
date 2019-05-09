<?php
/**
 * @package plugins.metroPcsDistribution
 * @subpackage lib
 */
class MetroPcsDistributionFeedHelper
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
	 * @var AttUverseDistributionProfile
	 */
	protected $distributionProfile;

	/**
	 * @var VidiunEntryDistribution
	 */
	protected $entryDistribution;
	
	/**
	 * @var VidiunMetroPcsDistributionJobProviderData
	 */
	protected $providerData;
	
	/**
	 * @var array
	 */
	protected $fieldValues;
		
	
	/**
	 * @param $templateName
	 * @param $distributionProfile
	 */
	public function __construct($templateName, $entryDistribution, VidiunMetroPcsDistributionProfile $distributionProfile, VidiunMetroPcsDistributionJobProviderData $providerData) 
	{
		$this->entryDistribution = $entryDistribution;
		$this->distributionProfile = $distributionProfile;
		$this->providerData = $providerData;
		$this->fieldValues = unserialize($providerData->fieldValues);
		if (!$this->fieldValues) {
		    $this->fieldValues = array();
		}
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml_templates/' . $templateName;
		$this->doc = new VDOMDocument();
		$this->doc->load($xmlTemplate);		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('msdp', 'http://www.real.com/msdp');	
		
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:title', $this->getValueForField(VidiunMetroPcsDistributionField::TITLE));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:link', $this->getValueForField(VidiunMetroPcsDistributionField::LINK));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:externalId', $this->getValueForField(VidiunMetroPcsDistributionField::EXTERNAL_ID));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:providerId', $this->getValueForField(VidiunMetroPcsDistributionField::PROVIDER_ID));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:shortDescription', $this->getValueForField(VidiunMetroPcsDistributionField::SHORT_DESCRIPTION));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:description', $this->getValueForField(VidiunMetroPcsDistributionField::DESCRIPTION));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:language', $this->getValueForField(VidiunMetroPcsDistributionField::LANGUAGE));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:copyright', $this->getValueForField(VidiunMetroPcsDistributionField::COPYRIGHT));		
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:managingEditor', $this->getValueForField(VidiunMetroPcsDistributionField::MANAGING_EDITOR));
		
		$pubDate = $this->getValueForField(VidiunMetroPcsDistributionField::PUB_DATE);
		if ($pubDate) 
		{
		   vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:pubDate',date('D M j G:i:s T Y', intval($pubDate))); 
		}
					
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:category', $this->getValueForField(VidiunMetroPcsDistributionField::CATEGORY));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:upc', $this->getValueForField(VidiunMetroPcsDistributionField::UPC));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:isrc', $this->getValueForField(VidiunMetroPcsDistributionField::ISRC));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:program', $this->getValueForField(VidiunMetroPcsDistributionField::PROGRAM));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:seasonId', $this->getValueForField(VidiunMetroPcsDistributionField::SEASON_ID));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:episodicId', $this->getValueForField(VidiunMetroPcsDistributionField::EPISODIC_ID));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:chapterId', $this->getValueForField(VidiunMetroPcsDistributionField::CHAPTER_ID));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:artist', $this->getValueForField(VidiunMetroPcsDistributionField::ARTIST));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:performer', $this->getValueForField(VidiunMetroPcsDistributionField::PERFORMER));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:director', $this->getValueForField(VidiunMetroPcsDistributionField::DIRECTOR));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:studio', $this->getValueForField(VidiunMetroPcsDistributionField::STUDIO));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:originalRelease', $this->getValueForField(VidiunMetroPcsDistributionField::ORIGINAL_RELEASE));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:topStory', $this->getValueForField(VidiunMetroPcsDistributionField::TOP_STORY));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:sortOrder', $this->getValueForField(VidiunMetroPcsDistributionField::SORT_ORDER));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:sortName', $this->getValueForField(VidiunMetroPcsDistributionField::SORT_NAME));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:genre', $this->getValueForField(VidiunMetroPcsDistributionField::GENRE));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:keywords', $this->getValueForField(VidiunMetroPcsDistributionField::KEYWORDS));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:localCode', $this->getValueForField(VidiunMetroPcsDistributionField::LOCAL_CODE));
		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:entitlements', $this->getValueForField(VidiunMetroPcsDistributionField::ENTITLEMENTS));
				
		$startDate = new DateTime('@'.$this->getValueForField(VidiunMetroPcsDistributionField::START_DATE));
		if ($startDate) 
		{	
			// force time zone to EST
			$startDate->setTimezone(new DateTimeZone('EST'));
			$date = $startDate->format('c');			
		    vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:startDate',$date);  
		}		
		
		$endDate = new DateTime('@'.$this->getValueForField(VidiunMetroPcsDistributionField::END_DATE));
		if ($endDate) 
		{
			// force time zone to EST
			$endDate->setTimezone(new DateTimeZone('EST'));
		    $date = $endDate->format('c');			
		    vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:endDate',$date); 
		}	

		vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:rating', $this->getValueForField(VidiunMetroPcsDistributionField::RATING));
	}
				
	/**
	 * @param string $xpath
	 * @param string $value
	 * @param DOMNode $contextnode
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
				$node->childNodes->item(0)->nodeValue = $value;
			else
				$node->nodeValue = $value;
		}
	}
	
	private function getValueForField($fieldName)
	{
	    if (isset($this->fieldValues[$fieldName])) {
	        return $this->fieldValues[$fieldName];
	    }
	    return null;
	}	
		
	/**
	 * set flavors in XML
	 * @param VidiunThumbAsset $thumbAssets
	 */
	public function setThumbnails($thumbAssets, $thumbUrls)
	{	
		$templateImageNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:image')->item(0);		
		if (count($thumbAssets) && count($thumbUrls))	
		{										
			foreach ($thumbAssets as $thumbAsset)
			{
				$url = $thumbUrls[$thumbAsset->id];
				vXml::setNodeValue($this->xpath,'msdp:url', $url, $templateImageNode);
				vXml::setNodeValue($this->xpath,'msdp:width', $thumbAsset->width , $templateImageNode);
				vXml::setNodeValue($this->xpath,'msdp:height', $thumbAsset->height , $templateImageNode);
				//$this->cloneNode instead the DOMNode cloneNode since the cloneNode doesn't deep copy the namespaces inside the tags
				$newImageNode = $this->cloneNode($templateImageNode, $this->doc);
				$templateImageNode->parentNode->insertBefore($newImageNode, $templateImageNode);
			}			
			$templateImageNode->parentNode->removeChild($templateImageNode);		
		}
		else
		{
			//ignore image element	
			vXml::setNodeValue($this->xpath,'@ignore', "Y", $templateImageNode);
		}
	}
	
	/**
	 * set flavors in XML
	 * @param VidiunFlavorAsset $flavorAssets
	 */
	public function setFlavor ($flavorAsset, $entryDuration, $currenTime)
	{	
		$templateItemNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:item')->item(0);
		if($flavorAsset)
		{	
			$itemTitle = $this->getValueForField(VidiunMetroPcsDistributionField::ITEM_TITLE);
			$itemDescription= $this->getValueForField(VidiunMetroPcsDistributionField::ITEM_DESCRIPTION);
			$itemType= $this->getValueForField(VidiunMetroPcsDistributionField::ITEM_TYPE);
			//$url = $this->getAssetUrl($flavorAsset);
			$url = $this->flavorAssetUniqueName($flavorAsset, $currenTime);			
			vXml::setNodeValue($this->xpath,'msdp:title', $itemTitle, $templateItemNode);
			vXml::setNodeValue($this->xpath,'msdp:description', $itemDescription, $templateItemNode);
			vXml::setNodeValue($this->xpath,'msdp:type', $itemType, $templateItemNode);				
			vXml::setNodeValue($this->xpath,'msdp:width', $flavorAsset->width , $templateItemNode);		
			vXml::setNodeValue($this->xpath,'msdp:height', $flavorAsset->height , $templateItemNode);					
			vXml::setNodeValue($this->xpath,'msdp:enclosure/@url', $url, $templateItemNode);
			vXml::setNodeValue($this->xpath,'msdp:enclosure/@length', $entryDuration, $templateItemNode);
			//$this->cloneNode instead the DOMNode cloneNode since the cloneNode doesn't deep copy the namespaces inside the tags
			$newItemNode = $this->cloneNode($templateItemNode, $this->doc);
			$templateItemNode->parentNode->insertBefore($newItemNode, $templateItemNode);
			$templateItemNode->parentNode->removeChild($templateItemNode);
		}
		else
		{
			//ignore image element	
			vXml::setNodeValue($this->xpath,'@ignore', "Y", $templateItemNode);
		}
	}
	
	/**
	 * Setting the start and end dates to passed dates while maintaining startDate<endDate
	 */
	public function setTimesForDelete()
	{
		//two days ago
		$startDate = time() - 48*60*60;  
		$startDate = new DateTime('@'.$startDate);
		if ($startDate) 
		{	
			// force time zone to EST
			$startDate->setTimezone(new DateTimeZone('EST'));
			$date = $startDate->format('c');			
		    vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:startDate',$date);  
		}		
		
		//yesterday
		$endDate = time() - 24*60*60;  
		$endDate = new DateTime('@'.$endDate);
		if ($endDate) 
		{
			// force time zone to EST
			$endDate->setTimezone(new DateTimeZone('EST'));
		    $date = $endDate->format('c');			
		    vXml::setNodeValue($this->xpath,'/msdp:rss/msdp:channel/msdp:endDate',$date); 
		}	
	}
	
	public function setImageIgnore()
	{
		$imageNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:image')->item(0);	
		vXml::setNodeValue($this->xpath,'@ignore', "Y", $imageNode);		
	}
	
	
	public function setItemIgnore()
	{		
		$itemNode = $this->xpath->query('/msdp:rss/msdp:channel/msdp:item')->item(0);
		vXml::setNodeValue($this->xpath,'msdp:type', $this->getValueForField(VidiunMetroPcsDistributionField::ITEM_TYPE), $itemNode);
		vXml::setNodeValue($this->xpath,'@ignore', "Y", $itemNode);		
	}
	
	public function getXmlString()
	{
		return $this->doc->saveXML();
	}
		
	/**
	 * creates unique name for flavor asset
	 * @param VidiunFlavorAsset $flavorAsset
	 */
	public function flavorAssetUniqueName($flavorAsset, $currentTime)
	{
		$path = $this->distributionProfile->ftpPath;
		$fileExt = $flavorAsset->fileExt;	
		//$uniqueName = $path.'/'.$currentTime.'_'.$this->entryDistribution->id.'_'.$flavorAsset->entryId.'_'.$flavorAsset->id.'.'.$fileExt;
		$uniqueName = $currentTime.'_'.$this->entryDistribution->id.'_'.$flavorAsset->entryId.'_'.$flavorAsset->id.'.'.$fileExt;
		return $uniqueName;		
	}
	
	private function cloneNode($node,$doc)
	{
	    $nd = $doc->createElement($node->nodeName);           
	    foreach($node->attributes as $value)
	        $nd->setAttribute($value->nodeName,$value->value);
	           
	    if(!$node->childNodes)
	        return $nd;
	               
	    foreach($node->childNodes as $child) {
	        if($child->nodeName=="#text")
	            $nd->appendChild($doc->createTextNode($child->nodeValue));
	        else
	            $nd->appendChild($this->cloneNode($child,$doc));
	    }          
    	return $nd;
	}
	
}