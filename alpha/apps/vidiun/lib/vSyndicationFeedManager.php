<?php
class vSyndicationFeedManager
{
	/*
	 * @param string $xsltStr
	 */
	public static function validateXsl($xsltStr)
	{
		$xsl = new DOMDocument();
		if(!@$xsl->loadXML($xsltStr))
		{
			VidiunLog::err("Invalid XSLT structure");
			throw new vCoreException("Invalid XSLT", vCoreException::INVALID_XSLT);
		}
		
		$xpath = new DOMXPath($xsl);
		
		$xslStylesheet = $xpath->query("//xsl:stylesheet");
		$rss = $xpath->query("//xsl:template[@name='rss']");
		if ($rss->length == 0)
		    throw new vCoreException("Invalid XSLT structure - missing template rss", vCoreException::INVALID_XSLT);
		
		$item = $xpath->query("//xsl:template[@name='item']");
		if ($item->length == 0)
		    throw new vCoreException("Invalid XSLT structure - missing template item", vCoreException::INVALID_XSLT);
		
		$items = $xpath->query("//xsl:apply-templates[@name='item']"); 
		if ($items->length == 0)
		    throw new vCoreException("Invalid XSLT structure - missing template apply-templates item", vCoreException::INVALID_XSLT);

		return true;
	}
}