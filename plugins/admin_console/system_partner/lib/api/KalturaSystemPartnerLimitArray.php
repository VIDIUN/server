<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.objects
 */
class VidiunSystemPartnerLimitArray extends VidiunTypedArray
{
	/**
	 * @param Partner $partner
	 * @return VidiunSystemPartnerLimitArray
	 */
	public static function fromPartner(Partner $partner)
	{
		$arr = new VidiunSystemPartnerLimitArray();
		$reflector = VidiunTypeReflectorCacher::get('VidiunSystemPartnerLimitType');
		$types = $reflector->getConstants();
		foreach($types as $typeInfo) {
		    $typeValue = $typeInfo->getDefaultValue();
		    $arr[] = VidiunSystemPartnerOveragedLimit::fromPartner($typeValue, $partner);
		}
			
			
		return $arr;
	} 
	
	public function __construct()
	{
		return parent::__construct("VidiunSystemPartnerLimit");
	}
}