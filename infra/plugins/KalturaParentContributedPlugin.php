<?php

/**
* Enable the plugin to add additional XML nodes and attributes to entry MRSS
* @package infra
* @subpackage Plugins
*/
abstract class VidiunParentContributedPlugin extends VidiunPlugin implements IVidiunMrssContributor{

    /**
     * @param BaseObject $object
     * @param SimpleXMLElement $mrss
     * @param vMrssParameters $mrssParams
     * @return SimpleXMLElement
     */
    public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
    {
		VidiunLog::debug("using ParentContributedPlugin");
		if(!($object instanceof entry)){
			return;
		}

		$children = entryPeer::retrieveChildEntriesByEntryIdAndPartnerId($object->getId(), $object->getPartnerId());
		if(!count($children)){
			return;
		}
		$childrenNode = $mrss->addChild('children');
		$childrenDom = dom_import_simplexml($childrenNode);
		foreach ($children as $child)
		{
			$childXML = vMrssManager::getEntryMrssXml($child);
			$childDom = dom_import_simplexml($childXML);
			$childDom = $childrenDom->ownerDocument->importNode($childDom, true);
			$childrenDom->appendChild($childDom);
		}
    }

    /**
     * Function returns the object feature type for the use of the VmrssManager
     *
     * @return int
     */
    public function getObjectFeatureType()
    {
        $value = $this->getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . ParentObjectFeatureType::PARENT;
        return vPluginableEnumsManager::apiToCore('ObjectFeatureType', $value);
    }
}