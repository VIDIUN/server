<?php
/**
 * @package Core
 * @subpackage model.wrappers
 */
class moderationWrapper extends objectWrapperBase
{
	protected $basic_fields = array ( "id" , "partnerId" );
	
	protected $regular_fields_ext = array ( "objectId" , "objectType" , /*"puserId" , "vuserId" ,*/  "status" , "comments" , 
		"createdAt", "groupId" , "reportCode" );
	
	protected $detailed_fields_ext = array ( ) ;
	
	protected $detailed_objs_ext = array ( /*"vuser" ,*/ "object" , );
	
	protected $objs_cache = array ( ) ;//"vuser" => "vuser,vuserId" , ); 

	protected $updateable_fields = array ( "comments" , /*"puserId" */ "objectType" , "objectId" , "reportCode"  );
	
	protected $updateable_fields_ext = array ( "status"  );
	
	public function describe() 
	{
		return 
			array (
				"display_name" => "Moderation",
				"desc" => ""
			);
	}
	
	public function getUpdateableFields( $level = 1 )
	{
		if ( $level <= 1 )
			return $this->updateable_fields;
		if ( $level == 2)
			return array_merge ( $this->updateable_fields , $this->updateable_fields_ext );
	}	
}
?>