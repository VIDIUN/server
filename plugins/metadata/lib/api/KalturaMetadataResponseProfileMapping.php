<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 */
class VidiunMetadataResponseProfileMapping extends VidiunResponseProfileMapping
{
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object = null, $propertiesToSkip = array())
	{
		if(is_null($object))
		{
			$object = new vMetadataResponseProfileMapping();
		}

		return parent::toObject($object, $propertiesToSkip);
	}

	public function apply(VidiunRelatedFilter $filter, VidiunObject $parentObject)
	{
		$filterProperty = $this->filterProperty;
		$parentProperty = $this->parentProperty;

		VidiunLog::info("Mapping XPath $parentProperty to " . get_class($filter) . "::$filterProperty");
	
		if(!$parentObject instanceof VidiunMetadata)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_TYPE, get_class($parentObject));
		}

		if(!property_exists($filter, $filterProperty))
		{
			throw new VidiunAPIException(VidiunErrors::PROPERTY_IS_NOT_DEFINED, $filterProperty, get_class($filter));
		}

		$xml = $parentObject->xml;
		$doc = new VDOMDocument();
		$doc->loadXML($xml);
		$xpath = new DOMXPath($doc);
		$metadataElements = $xpath->query($parentProperty);
		if ($metadataElements->length == 1)
		{
			$filter->$filterProperty = $metadataElements->item(0)->nodeValue;
		}
		elseif ($metadataElements->length > 1)
		{
			$values = array();
			foreach($metadataElements as $element)
				$values[] = $element->nodeValue;
			$filter->$filterProperty = implode(',', $values);
		}
		elseif (!$this->allowNull)
		{
			return false;
		}
		return true;
	}
}