<?php
/**
 * @package Core
 * @subpackage model.wrappers
 */
class PuserVuserWrapper extends objectWrapperBase
{
	// the PuserVuser id make more harm than good - 
	protected $basic_fields = array ( /*"id", */ "puserName" , "partnerId" , "subpId" );
	
	protected $regular_fields_ext = array ( "puserId" , "vuserId" , "customData" ,  "context" ,  "createdAt" );
	
	protected $detailed_fields_ext = array (  ) ;
	
	protected $detailed_objs_ext = array ( "vuser" );
	
	protected $objs_cache = array ( "vuser" => "vuser,vuserId" , );

	public function getUpdateableFields()
	{
		return array ( );
	}		
}
?>