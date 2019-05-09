<?php


/**
 * Skeleton subclass for performing query and update operations on the 'short_link' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package plugins.shortLink
 * @subpackage model
 */
class ShortLinkPeer extends BaseShortLinkPeer {

	/**
	 * Retrieve all objects by vuser id
	 *
	 * @param      int $vuserId the vuser id.
	 * @param      PropelPDO $con the connection to use
	 * @return     array<ShortLink>
	 */
	public static function retrieveByVuserId($vuserId, $partnerId = null, PropelPDO $con = null)
	{
		$criteria = new Criteria();
		$criteria->add(ShortLinkPeer::VUSER_ID, $vuserId);
		if ($partnerId)
			$criteria->add(ShortLinkPeer::PARTNER_ID, $partnerId);

		return ShortLinkPeer::doSelect($criteria, $con);
	}
	
} // ShortLinkPeer
