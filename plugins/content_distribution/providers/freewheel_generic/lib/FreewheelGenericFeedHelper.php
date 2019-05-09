<?php
/**
 * @package plugins.freewheelGenericDistribution
 * @subpackage lib
 */
class FreewheelGenericFeedHelper
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
	 * @var VidiunFreewheelGenericDistributionProfile
	 */
	protected $_distributionProfile;
	
	/**
	 * @var VidiunFreewheelGenericDistributionJobProviderData
	 */
	protected $_providerData;
	
	/**
	 * @var array
	 */
	protected $_fieldValues;
	
	/**
	 * @param string $templateName
	 * @param VidiunFreewheelGenericDistributionProfile $distributionProfile
	 * @param VidiunFreewheelGenericDistributionJobProviderData $providerData
	 */
	public function __construct($templateName, VidiunFreewheelGenericDistributionProfile $distributionProfile, VidiunFreewheelGenericDistributionJobProviderData $providerData)
	{
		$this->_distributionProfile = $distributionProfile;
		$this->_providerData = $providerData;
		$xmlTemplate = realpath(dirname(__FILE__) . '/../') . '/xml/' . $templateName;
		$this->_doc = new VDOMDocument();
		$this->_doc->load($xmlTemplate);
		$this->_xpath = new DOMXPath($this->_doc);

		$this->_fieldValues = unserialize($this->_providerData->fieldValues);
		if (!$this->_fieldValues) 
			$this->_fieldValues = array();
		
		$this->setReplaceGroup();
		$this->setReplaceAirDates();
		
		
		switch ($this->_distributionProfile->contentOwner)
		{
			case 'OutFwContentOwner':
				$contentOwner = $this->_doc->createElement('OutFwContentOwner');
				$contentOwner->setAttribute('upstream_video_id', $this->_distributionProfile->upstreamVideoId);
				$contentOwner->setAttribute('upstream_network_name', $this->_distributionProfile->upstreamNetworkName);
				$contentOwner->setAttribute('category_id', $this->_distributionProfile->categoryId);
				break; 
			case 'InFwContentOwner':
				$contentOwner = $this->_doc->createElement('InFwContentOwner');
				$contentOwner->setAttribute('upstream_video_id', $this->_distributionProfile->upstreamVideoId);
				$contentOwner->setAttribute('upstream_network_id', $this->_distributionProfile->upstreamNetworkId);
				$contentOwner->setAttribute('category_id', $this->_distributionProfile->categoryId);
				break;
			case 'SelfContentOwner':
				$contentOwner = $this->_doc->createElement('SelfContentOwner');
				break;
			default:
				$contentOwner = $this->_doc->createElement('UnassignContentOwner');
				break; 
		}
		$this->appendElement('/FWCoreContainer/FWVideoDocument/fwContentOwner', $contentOwner);
		
		vXml::setNodeValue($this->_xpath,'/FWCoreContainer/@contact_email', $this->_distributionProfile->email);
		vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/@video_id', $this->_fieldValues[VidiunFreewheelGenericDistributionField::VIDEO_ID]);
		
		$this->addTitleItem('Episode Title1', VidiunFreewheelGenericDistributionField::FWTITLES_EPISODE_TITLE1);
		$this->addTitleItem('Episode Title2', VidiunFreewheelGenericDistributionField::FWTITLES_EPISODE_TITLE2);
		$this->addTitleItem('Series', VidiunFreewheelGenericDistributionField::FWTITLES_SERIES);
		$this->addTitleItem('Season', VidiunFreewheelGenericDistributionField::FWTITLES_SEASON);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP1);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP2);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP3);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP4);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP5);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP6);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP7);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP8);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP9);
		$this->addTitleItem('Group', VidiunFreewheelGenericDistributionField::FWTITLES_GROUP10);
		
		$this->addDescriptionItem('Episode', VidiunFreewheelGenericDistributionField::FWDESCRIPTIONS_EPISODE);
		$this->addDescriptionItem('Series', VidiunFreewheelGenericDistributionField::FWDESCRIPTIONS_SERIES);
		
		$this->addGenreItem(VidiunFreewheelGenericDistributionField::GENRE);
		
		$this->createAndSetByXPathDate('/FWCoreContainer/FWVideoDocument/fwDateAvailable', 'dateAvailableStart', VidiunFreewheelGenericDistributionField::DATE_AVAILABLE_START);
		$this->createAndSetByXPathDate('/FWCoreContainer/FWVideoDocument/fwDateAvailable', 'dateAvailableEnd', VidiunFreewheelGenericDistributionField::DATE_AVAILABLE_END);
		$this->createAndSetByXPathDate('/FWCoreContainer/FWVideoDocument/fwAirDates', 'dateLastAired', VidiunFreewheelGenericDistributionField::DATE_LAST_AIRED);
		$this->createAndSetByXPathDate('/FWCoreContainer/FWVideoDocument', 'fwDateIssued', VidiunFreewheelGenericDistributionField::DATE_ISSUED);
		
		$this->createAndSetByXPath('/FWCoreContainer/FWVideoDocument', 'fwRating', VidiunFreewheelGenericDistributionField::RATING);
		
		vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/fwDuration', $this->_fieldValues[VidiunFreewheelGenericDistributionField::DURATION]);
		$this->addDynamicMetadata();
		
		$this->addCuePoints($this->_providerData->cuePoints, $this->_fieldValues[VidiunFreewheelGenericDistributionField::DURATION]);
	}
	
	/**
	 * @param array $cuePoints
	 */
	protected function addCuePoints(array $cuePoints, $videoDuration)
	{
		$segments = array();
		foreach($cuePoints as $cuePoint)
		{
			$cuePointNode = $this->createCuePointNode($cuePoint, $videoDuration);
			$this->appendElement('/FWCoreContainer/FWVideoDocument/fwCuePoints/cuePoints', $cuePointNode);
		}
	}
	
	protected function createCuePointNode(VidiunAdCuePoint $cuePoint, $videoDuration)
	{
		$seconds = floor($cuePoint->startTime / 1000);
		$cuePointNode = $this->_doc->createElement('cuePoint');
		
		$contentTimePositionNode = $this->_doc->createElement('contentTimePosition', $seconds);
		$cuePointNode->appendChild($contentTimePositionNode);
		
		$type = 'MIDROLL';
		if ($cuePoint->adType == VidiunAdType::OVERLAY)
		{
			$type = 'OVERLAY';
		}
		else
		{
			if ($seconds == $videoDuration)
				$type = 'POSTROLL';
			elseif ($seconds == 0)
				$type = 'PREROLL';
		}
		
		$timePositionClassNode = $this->_doc->createElement('timePositionClass', $type);
		$cuePointNode->appendChild($timePositionClassNode);
		
		return $cuePointNode;
	}
	
	protected function setReplaceGroup()
	{
		if ($this->_distributionProfile->replaceGroup === false)
			vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/fwReplaceGroup', 'false');
		else
			vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/fwReplaceGroup', 'true');
	}
	
	protected function setReplaceAirDates()
	{
		if ($this->_distributionProfile->replaceAirDates === false)
			vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/fwReplaceAirDates', 'false');
		else
			vXml::setNodeValue($this->_xpath,'/FWCoreContainer/FWVideoDocument/fwReplaceAirDates', 'true');
	}
	
	protected function addTitleItem($titleType, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$titleItemElement = $this->createTitleItem($this->_fieldValues[$fieldConfigId], $titleType);
			$this->appendElement('/FWCoreContainer/FWVideoDocument/fwTitles', $titleItemElement);
		}
	}
	
	protected function createTitleItem($title, $titleType)
	{
		$titleItemElement = $this->_doc->createElement('titleItem');
		$cdata = $this->_doc->createCDATASection($title);
		$titleElement = $this->_doc->createElement('title');
		$titleElement->appendChild($cdata);
		$titleTypeElement = $this->_doc->createElement('titleType', $titleType);
		
		$titleItemElement->appendChild($titleElement);
		$titleItemElement->appendChild($titleTypeElement);
		
		return $titleItemElement;
	}
	
	protected function addDescriptionItem($descriptionType, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$descriptionItemElement = $this->createDescriptionItem($this->_fieldValues[$fieldConfigId], $descriptionType);
			$this->appendElement('/FWCoreContainer/FWVideoDocument/fwDescriptions', $descriptionItemElement);
		}
	}
	
	protected function createDescriptionItem($description, $descriptionType)
	{
		$descriptionItemElement = $this->_doc->createElement('descriptionItem');
		$cdata = $this->_doc->createCDATASection($description);
		$descriptionElement = $this->_doc->createElement('description');
		$descriptionElement->appendChild($cdata);
		$descriptionTypeElement = $this->_doc->createElement('descriptionType', $descriptionType);
		$descriptionItemElement->appendChild($descriptionElement);
		$descriptionItemElement->appendChild($descriptionTypeElement);
		
		return $descriptionItemElement;
	}
	
	protected function addGenreItem($fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$genreItemElement = $this->_doc->createElement('genreItem', $this->_fieldValues[$fieldConfigId]);
			$this->appendElement('/FWCoreContainer/FWVideoDocument/fwGenres', $genreItemElement);
		}
	}
	
	protected function createAndSetByXPath($xpath, $elementName, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$element = $this->_doc->createElement($elementName, $this->_fieldValues[$fieldConfigId]);
			$this->appendElement($xpath, $element);
		}
	}
	
	protected function createAndSetByXPathDate($xpath, $elementName, $fieldConfigId)
	{
		if (isset($this->_fieldValues[$fieldConfigId]) && $this->_fieldValues[$fieldConfigId])
		{
			$element = $this->_doc->createElement($elementName, date(DATE_ATOM, $this->_fieldValues[$fieldConfigId]));
			$this->appendElement($xpath, $element);
		}
	}
	
	protected function createMetadataItem($label, $value)
	{
		$datumItemElement = $this->_doc->createElement('datumItem');
		$labelElement = $this->_doc->createElement('label', $label);
		$cdata = $this->_doc->createCDATASection($value);
		$valueElement = $this->_doc->createElement('value');
		$valueElement->appendChild($cdata);
		
		$datumItemElement->appendChild($labelElement);
		$datumItemElement->appendChild($valueElement);
		
		return $datumItemElement;
	}
	
	protected function addDynamicMetadata()
	{
		$fieldConfigArray = $this->_distributionProfile->fieldConfigArray;
		foreach($fieldConfigArray as $fieldConfig)
		{
			/* @var $fieldConfig VidiunDistributionFieldConfig */
			if (strpos($fieldConfig->fieldName, 'FWMETADATA_') !== 0)
				continue;
				
			$label = $fieldConfig->userFriendlyFieldName;
			if (!$label)
				continue;
				
			$value = isset($this->_fieldValues[$fieldConfig->fieldName]) ? $this->_fieldValues[$fieldConfig->fieldName] : '';
			if (!$value)
				continue;
			$metadataElement = $this->createMetadataItem($label, $value);
			
			$this->appendElement('/FWCoreContainer/FWVideoDocument/fwMetaData', $metadataElement);
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
		$this->dynamiclyCreatePath($xpath);
		$parentElement = $this->_xpath->query($xpath)->item(0);
		if ($parentElement && $parentElement instanceof DOMNode)
		{
			$parentElement->appendChild($element);
		}
	}
	
	public function dynamiclyCreatePath($xpath)
	{
		$element = $this->_xpath->query($xpath)->item(0);
		if (is_null($element))
		{
			$exploded = explode('/', $xpath);
			$lastElementName = array_pop($exploded);
			$element = $this->dynamiclyCreatePath(implode('/', $exploded));
			$newElement = $this->_doc->createElement($lastElementName);
			$element->appendChild($newElement);
			return $newElement;
		}
		else
		{
			return $element;
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