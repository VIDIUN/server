<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRokuSyndicationFeed extends VidiunConstantXsltSyndicationFeed
{

    function __construct()
	{
		$this->type = VidiunSyndicationFeedType::ROKU_DIRECT_PUBLISHER;
		$this->xsltPath =  __DIR__."/xslt/roku_syndication.xslt";
	}
}