<?php
/**
 * @package plugins.venodr
 * @subpackage model.zoomOauth
 */
interface vVendorOauth
{

	/**
	 * @param bool $forceNewToken
	 * @return mixed
	 */
	function retrieveTokensData($forceNewToken = false);

	/**
	 * @param string $oldRefreshToken
	 * @param VendorIntegration $vendorIntegration
	 * @return string newAccessToken
	 */
	function refreshTokens($oldRefreshToken, $vendorIntegration);


}