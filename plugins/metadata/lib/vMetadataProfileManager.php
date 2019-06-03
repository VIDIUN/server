<?php

class vMetadataProfileManager
{
    public static function validateXsdData($xsdData, &$errorMessage)
    {
        // validates the xsd
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        
        $xml = new VDOMDocument();
        if(!$xml->loadXML($xsdData))
        {
            $errorMessage = vXml::getLibXmlErrorDescription($xsdData);
            return false;
        }
        
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        
        return true;
    }
}