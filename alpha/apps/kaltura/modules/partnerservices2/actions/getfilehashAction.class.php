<?php
/**
 * @package api
 * @subpackage ps2
 */
class getfilehashAction extends defPartnerservices2Action
{
	public function describe()
	{
		return array();		
	}
	
	protected function needVuserFromPuser ( )
	{
		// will use the $puser_id for the hashcode no need to feth the vuser_id
		return self::VUSER_DATA_NO_VUSER;
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$count = $this->getP ( "count" , 1 );
		if ( $count < 1 ) $count =1;
		
		for ( $i=1 ; $i<=$count ; $i++ )
		{
			$hash =  md5 ( "getfilehashAction" . $partner_id . $puser_id . $i . time()) ;
			$this->addMsg ( "hash$i" , $hash );
		}
	}
}
?>