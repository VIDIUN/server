<?php

/**
 * extservices actions.
 *
 * @package    Core
 * @subpackage externalServices
 */
class flickrAction extends vidiunAction
{
	public function execute()
	{
		$this->followRedirectCookie();
		
		$frob = @$_REQUEST['frob'];
			
		$vidi_token = @$_COOKIE['flickr_viditoken'];

		if (!$vidi_token)
		{
			$vuserId = $this->getLoggedInUserId();
			if ($vuserId)
				$vidi_token = $vuserId.':';
		}
		else
			$vidi_token = base64_decode($vidi_token);

		if (!$frob || !$vidi_token)
			return;

		myFlickrServices::setVuserToken($vidi_token, $frob);

		return;
	}
}
