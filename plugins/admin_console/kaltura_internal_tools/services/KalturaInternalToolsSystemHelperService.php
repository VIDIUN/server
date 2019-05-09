<?php
/**
 * 
 * Internal Tools Service
 * 
 * @service vidiunInternalToolsSystemHelper
 * @package plugins.VidiunInternalTools
 * @subpackage api.services
 */
class VidiunInternalToolsSystemHelperService extends VidiunBaseService
{

	/**
	 * VS from Secure String
	 * @action fromSecureString
	 * @param string $str
	 * @return VidiunInternalToolsSession
	 * 
	 */
	public function fromSecureStringAction($str)
	{
		$vs =  vs::fromSecureString ( $str );
		
		$vsFromSecureString = new VidiunInternalToolsSession();
		$vsFromSecureString->fromObject($vs, $this->getResponseProfile());
		
		return $vsFromSecureString;
	}
	
	/**
	 * from ip to country
	 * @action iptocountry
	 * @param string $remote_addr
	 * @return string
	 * 
	 */
	public function iptocountryAction($remote_addr)
	{
		$ip_geo = new myIPGeocoder();
		$res = $ip_geo->iptocountry($remote_addr); 
		return $res;
	}
	
	/**
	 * @action getRemoteAddress
	 * @return string
	 * 
	 */
	public function getRemoteAddressAction()
	{
		$remote_addr = requestUtils::getRemoteAddress();
		return $remote_addr;	
	}
}