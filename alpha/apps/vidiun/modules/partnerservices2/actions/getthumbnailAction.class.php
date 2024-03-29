<?php
/**
 * @package api
 * @subpackage ps2
 */
class getthumbnailAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 	
			array (
				"display_name" => "getThumbnail",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"filename" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"result_ok" => array ("type" => "string", "desc" => "")
					),
				"errors" => array (
				)
			); 
	}
	
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_NO_VUSER;	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
  		$filename = $this->getPM ('filename');
		// strip the filename from invalid characters
		$token = $this->getVsUniqueString();
		
		// should upload the file with the token as the prefix
		$res = myUploadUtils::uploadFileByToken ( $_FILES['Filedata'] , $token , $filename ,null , true );
		
		$this->addMsg( "result_ok" , $res );
	}
}
?>