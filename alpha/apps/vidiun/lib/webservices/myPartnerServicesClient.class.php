<?php
require_once ( MODULES . "/partnerservices2/actions/startsessionAction.class.php" );
require_once ( MODULES . "/partnerservices2/actions/addvshowAction.class.php" );
class myPartnerServicesClient
{
	public static function createVidiunSession ( $uid, $privileges = null)
	{
		$vidiun_services = new startsessionAction();
		
		$params = array ( "format" => vidiunWebserviceRenderer::RESPONSE_TYPE_PHP_ARRAY , 
			"partner_id" => 0 , "subp_id" => 100 , "uid" => $uid , "secret" => "11111" );
		
		if ($privileges)
			$params["privileges"] = $privileges;
		
		$vidiun_services->setInputParams( $params );
		$result = $vidiun_services->internalExecute () ;
		return @$result["result"]["vs"];		
	}
	
	public static function createVshow ( $vs , $uid , $name , $partner_id = 0 , $subp_id = 100, $extra_params = null )
	{
		$vidiun_services = new addvshowAction();
		
		$params = array ( "format" => vidiunWebserviceRenderer::RESPONSE_TYPE_RAW , 
			"partner_id" => $partner_id , "subp_id" => $subp_id , "uid" => $uid , "vs" => $vs , "vshow_name" => $name ,
			"allow_duplicate_names" => "1" ) ;
		if ( $extra_params ) $params = array_merge( $params , $extra_params );
		
		$vidiun_services->setInputParams( $params );
		$result = $vidiun_services->internalExecute ( ) ;
		
		$vshow_wrapper = @$result["result"]["vshow"];
		
		if ( $vshow_wrapper )
		{
			$vshow = $vshow_wrapper->getWrappedObj();
			return 	$vshow	;
		}
		else
		{
			return null;
		}
	}
	

}
?>