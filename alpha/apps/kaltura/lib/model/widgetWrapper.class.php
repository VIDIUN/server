<?php
/**
 * @package Core
 * @subpackage model.wrappers
 */
class widgetWrapper extends objectWrapperBase
{
	protected $basic_fields = array ( "id" , "partnerId" );

	protected $regular_fields_ext = array ( "intId" , "sourceWidgetId" , "rootWidgetId" , "vshowId" , "entryId" ,
		"uiConfId", "customData" , "widgetHtml" , "partnerData" ,"securityType" , "securityPolicy");

	protected $detailed_fields_ext = array ( ) ;

	protected $detailed_objs_ext = array ( "vshow" , "entry" ,  "uiConf" );

	protected $objs_cache = array ( "vshow" => "vshow,vshowId" , "entry" => "entry,entryId" ,  "uiConf" => "uiConf,uiConfId" );

	protected $updateable_fields = array ( "vshowId" , "entryId" , "sourceWidgetId" , "uiConfId" , "customData" , "partnerData" , "securityType");
	
	public function describe() 
	{
		return 
			array (
				"display_name" => "Widget",
				"desc" => ""
			);
	}
	
	public function getUpdateableFields()
	{
		return $this->updateable_fields;
	}
}
?>