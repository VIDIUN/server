<?php
/**
 * @package Core
 * @subpackage model.wrappers
 */
class vuserWrapper extends objectWrapperBase
{
	protected $basic_fields = array ( "id" , "screenName" , "partnerId"  );
	
	protected $regular_fields_ext = array ( "puserId" , "fullName", "firstName", "lastName", "email" , "dateOfBirth" , "picturePath" , "pictureUrl" , 
		"icon" , "aboutMe" , "tags" , "gender" , "createdAt" , "createdAtAsInt" , "partnerData" , "storageSize", "isAdmin", "lastLoginTime" );
	
	protected $detailed_fields_ext = array ( "country" , "state" , "city"  , "zip" , "urlList" , "networkHighschool" , "networkCollege" , "views" , "fans" , "entries" , "producedVshows" ) ;
	
	protected $detailed_objs_ext = array ( "vshows" , "entrys" );
	
	protected $objs_cache = array ( "vshows" => "*vshow,id" , "entrys" => "*entry,id" );
	
	
	protected $read_only_fields = array ( "id" , "picturePath" , "icon" , "createdAt" , "views" , "fans" , "entries" , "producedVshows", "loginEnabled" );
	
	protected $updateable_fields = array ( "screenName"  , "fullName" , "email" , "dateOfBirth" ,  "aboutMe" , "tags" , "gender"  ,
			 "country" , "state" , "city"  , "zip" , "urlList" , "networkHighschool" , "networkCollege" , "partnerData" );
	
	public function describe() 
	{
		return 
			array (
				"display_name" => "User",
				"desc" => ""
			);
	}
	
	public function getUpdateableFields()
	{
		return $this->updateable_fields;
	}
}
?>