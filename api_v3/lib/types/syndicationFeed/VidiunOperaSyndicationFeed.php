<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunOperaSyndicationFeed extends VidiunConstantXsltSyndicationFeed
{

    function __construct()
	{
		$this->type = VidiunSyndicationFeedType::OPERA_TV_SNAP;
		$this->xsltPath =  __DIR__."/xslt/opera_syndication.xslt";
	}
}