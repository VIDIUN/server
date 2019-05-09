<?php
/**
 * @package plugins.verizonVcastDistribution
 * @subpackage lib
 */
class VerizonVcastFeedHelper
{

	/**
	 * @var DOMDocument
	 */
	protected $_doc;
	
	/**
	 * @var DOMXPath
	 */
	protected $_xpath;
	
	/**
	 * @var VidiunDistributionJobData
	 */
	protected $_distributionJobData;
	
	/**
	 * @var VidiunVerizonVcastDistributionProfile
	 */
	protected $_distributionProfile;
	
	/**
	 * @var VidiunVerizonVcastDistributionJobProviderData
	 */
	protected $_providerData;
	
	/**
	 * @var array
	 */
	protected $_fieldValues;
	
	/**
	 * DOMNode
	 */
	protected $_imageNode;
	
	/**
	 * DOMNode
	 */
	protected $_itemNode;
	
	/**
	 * @param string $templateName
	 * @param VidiunVerizonVcastDistributionProfile $distributionProfile
	 * @param VidiunVerizonVcastDistributionJobProviderData $providerData
	 */
	public function __construct($templateName, VidiunDistributionJobData $distributionJobData, VidiunVerizonVcastDistributionJobProviderData $providerData, array $flavorAssets, array $thumbnailAssets)
	{
		$this->_distributionJobData = $distributionJobData;
		$this->_distributionProfile = $distributionJobData->distributionProfile;
		$this->_providerData = $providerData;
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml/' . $templateName;
		$this->_doc = new VDOMDocument();
		$this->_doc->load($xmlTemplate);
		$this->_xpath = new DOMXPath($this->_doc);
		
		// image node template
		$node = $this->_xpath->query('//ns2:image')->item(0);
		$this->_imageNode = $node->cloneNode(true);
		$node->parentNode->removeChild($node);
		
		// item node template
		$node = $this->_xpath->query('//ns2:item')->item(0);
		$this->_itemNode = $node->cloneNode(true);
		$node->parentNode->removeChild($node);

		$this->_fieldValues = unserialize($this->_providerData->fieldValues);
		if (!$this->_fieldValues) 
			$this->_fieldValues = array();
		
		$this->setNodeValueFieldConfigId('//ns2:title', VidiunVerizonVcastDistributionField::TITLE);
		$this->setNodeValueFieldConfigId('//ns2:externalid', VidiunVerizonVcastDistributionField::EXTERNAL_ID);
		$this->setNodeValueFieldConfigId('//ns2:shortdescription', VidiunVerizonVcastDistributionField::SHORT_DESCRIPTION);
		$this->setNodeValueFieldConfigId('//ns2:description', VidiunVerizonVcastDistributionField::DESCRIPTION);
		$this->setNodeValueFieldConfigId('//ns2:keywords', VidiunVerizonVcastDistributionField::KEYWORDS);
		$this->setNodeValueShortDateFieldConfigId('//ns2:pubDate', VidiunVerizonVcastDistributionField::PUB_DATE);
		$this->setNodeValueFieldConfigId('//ns2:category', VidiunVerizonVcastDistributionField::CATEGORY);
		$this->setNodeValueFieldConfigId('//ns2:genre', VidiunVerizonVcastDistributionField::GENRE);
		$this->setNodeValueFieldConfigId('//ns2:rating', VidiunVerizonVcastDistributionField::RATING);
		$this->setNodeValueFieldConfigId('//ns2:copyright', VidiunVerizonVcastDistributionField::COPYRIGHT);
		$this->setNodeValueFieldConfigId('//ns2:entitlement', VidiunVerizonVcastDistributionField::ENTITLEMENT);
		
		$this->setNodeValueFullDateFieldConfigId('//ns2:liveDate', VidiunVerizonVcastDistributionField::LIVE_DATE);
		$this->setNodeValueFullDateFieldConfigId('//ns2:endDate', VidiunVerizonVcastDistributionField::END_DATE);
		$this->setNodeValueFieldConfigId('//ns2:priority', VidiunVerizonVcastDistributionField::PRIORITY);
		$this->setNodeValueFieldConfigId('//ns2:allowStreaming', VidiunVerizonVcastDistributionField::ALLOW_STREAMING);
		$this->setNodeValueFieldConfigId('//ns2:streamingPriceCode', VidiunVerizonVcastDistributionField::STREAMING_PRICE_CODE);
		$this->setNodeValueFieldConfigId('//ns2:allowDownload', VidiunVerizonVcastDistributionField::ALLOW_DOWNLOAD);
		$this->setNodeValueFieldConfigId('//ns2:downloadPriceCode', VidiunVerizonVcastDistributionField::DOWNLOAD_PRICE_CODE);
		$this->setNodeValueFieldConfigId('//ns2:provider', VidiunVerizonVcastDistributionField::PROVIDER);
		$this->setNodeValueFieldConfigId('//ns2:providerid', VidiunVerizonVcastDistributionField::PROVIDER_ID);
		$this->setOrRemoveNodeValueFieldConfigId('//ns2:alertCode', VidiunVerizonVcastDistributionField::ALERT_CODE);
		
		foreach($thumbnailAssets as $thumbnailAsset)
		{
			$imageNode = $this->_imageNode->cloneNode(true);
			$url = $this->getAssetUrl($thumbnailAsset);
			vXml::setNodeValue($this->_xpath,'ns2:url', $url, $imageNode);
			$priorityNode = $this->_xpath->query('//ns2:priority')->item(0);
			$channelNode = $this->_xpath->query('//ns2:channel')->item(0);
			$channelNode->insertBefore($imageNode, $priorityNode);
		}
		
		foreach($flavorAssets as $flavorAsset)
		{
			$itemNode = $this->_itemNode->cloneNode(true);
			$url = $this->getAssetUrl($flavorAsset);
			vXml::setNodeValue($this->_xpath,'ns2:enclosure/@url', $url, $itemNode);
			if ($this->shouldIngestFlavor($flavorAsset))
			{
				vXml::setNodeValue($this->_xpath,'ns2:encode', 'Y', $itemNode);
				vXml::setNodeValue($this->_xpath,'ns2:move', 'Y', $itemNode);
			}
			$channelNode = $this->_xpath->query('//ns2:channel')->item(0);
			$channelNode->appendChild($itemNode);
		}
	}
	

	protected function getAssetUrl(asset $asset)
	{
		$urlManager = DeliveryProfilePeer::getDeliveryProfile($asset->getEntryId());
		if($asset instanceof flavorAsset)
			$urlManager->initDeliveryDynamicAttributes(null, $asset);
		$url = $urlManager->getFullAssetUrl($asset);
		$url = preg_replace('/^https?:\/\//', '', $url);
		$url = 'http://' . $url . '/ext/' . $asset->getId() . '.' . $asset->getFileExt(); 
		return $url;
	}
	
	protected function shouldIngestFlavor(asset $flavorAsset)
	{
		// mediaFile array was not initialized meaning this is the first submit job
		if (!($this->_distributionJobData->mediaFiles instanceof VidiunDistributionRemoteMediaFileArray))
			return true;
		
		// find the mediaFile of our flavor
		$foundMediaFile = null;
		foreach($this->_distributionJobData->mediaFiles as $mediaFile)
		{
			if ($mediaFile->assetId == $flavorAsset->getId())
			{
				$foundMediaFile = $mediaFile;
				break;
			}
		}
		
		// this mediaFile was not sent yet
		if (is_null($foundMediaFile))
			return true;
			
		return ($foundMediaFile->version != $flavorAsset->getVersion());
	}
	
	/**
	 * @param string $xpath
	 * @param string $elementName
	 * @param string $fieldConfigId
	 */
	protected function createAndAppendByXPathFieldConfig($xpath, $elementName, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$this->createAndAppendByXPath($xpath, $elementName, $this->_fieldValues[$fieldConfigId]);
		}
	}
	
	/**
	 * @param string $xpath
	 * @param string $elementName
	 * @param string $value
	 */
	protected function createAndAppendByXPath($xpath, $elementName, $value)
	{
		$element = $this->_doc->createElement($elementName, $value);
		$this->appendElement($xpath, $element);
	}
	
	/**
	 * @param string $xpath
	 * @param string $elementName
	 * @param string $fieldConfigId
	 */
	protected function createAndAppendByXPathDate($xpath, $elementName, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$element = $this->_doc->createElement($elementName, date(DATE_ATOM, $this->_fieldValues[$fieldConfigId]));
			$this->appendElement($xpath, $element);
		}
	}
	
	protected function setNodeValueFullDateFieldConfigId($xpath, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId]) 
		{
			$dateTime = new DateTime('@'.$this->_fieldValues[$fieldConfigId]);
			// force time zone to EST
			$dateTime->setTimezone(new DateTimeZone('EST'));
			$date = $dateTime->format('c');
			vXml::setNodeValue($this->_xpath,$xpath, $date);
		}
	}
	
	protected function setNodeValueShortDateFieldConfigId($xpath, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]))
			vXml::setNodeValue($this->_xpath,$xpath, date('Y-m-d', $this->_fieldValues[$fieldConfigId]));
	}
	
	/**
	 * @param string $xpath
	 * @param string $fieldConfigId
	 */
	public function setNodeValueFieldConfigId($xpath, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]))
			vXml::setNodeValue($this->_xpath,$xpath, $this->_fieldValues[$fieldConfigId]);
	}
	
	/**
	 * @param string $xpath
	 * @param string $fieldConfigId
	 */
	public function setOrRemoveNodeValueFieldConfigId($xpath, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			vXml::setNodeValue($this->_xpath,$xpath, $this->_fieldValues[$fieldConfigId]);
		}
		else 
		{
			$node = $this->_xpath->query($xpath)->item(0);
			if ($node)
				$node->parentNode->removeChild($node);
		}
	}
	
	/**
	 * @param string $xpath
	 * @param string $value
	 * @param DOMNode $contextnode
	 */
	public function setNodeValue($xpath, $value, DOMNode $contextnode = null)
	{
		if ($contextnode)
			$node = $this->_xpath->query($xpath, $contextnode)->item(0);
		else 
			$node = $this->_xpath->query($xpath)->item(0);
		if (!is_null($node))
		{
			// if CDATA inside, set the value of CDATA
			if ($node->childNodes->length > 0 && $node->childNodes->item(0)->nodeType == XML_CDATA_SECTION_NODE)
				$node->childNodes->item(0)->nodeValue = $value;
			else
				$node->nodeValue = $value;
		}
	}
	
	/**
	 * @param string $xpath
	 * @param DOMNode $element
	 */
	public function appendElement($xpath, DOMNode $element)
	{
		$parentElement = $this->_xpath->query($xpath)->item(0);
		if ($parentElement && $parentElement instanceof DOMNode)
		{
			$parentElement->appendChild($element);
		}
	}
	
	/**
	 * @param string $xpath
	 */
	public function getNodeValue($xpath)
	{
		$node = $this->_xpath->query($xpath)->item(0);
		if (!is_null($node))
			return $node->nodeValue;
		else
			return null;
	}
	
	public function getXml()
	{
		return $this->_doc->saveXML();
	}
}