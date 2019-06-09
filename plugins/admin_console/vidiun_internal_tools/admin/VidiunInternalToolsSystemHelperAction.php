<?php
/**
 * @package plugins.VidiunInternalTools
 * @subpackage admin
 */
class VidiunInternalToolsPluginSystemHelperAction extends VidiunApplicationPlugin
{
	
	public function __construct()
	{
		$this->action = 'VidiunInternalToolsPluginSystemHelper';
		$this->label = 'System Helper';
		$this->rootLabel = 'Developer';
	}
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Vidiun_Client_Enum_PermissionName::SYSTEM_INTERNAL);
	}

	
	public function doAction(Zend_Controller_Action $action)
	{
		
		$request = $action->getRequest();
		
		$SystemHelperForm = new Form_SystemHelper();
		$SystemHelperFormResult = new Form_SystemHelperResult();
		
		$algo ="";
		$secret = "";
		$str = $request->getParam('StringToManipulate', false);
		$algo = $request->getParam('Algorithm', false);
		$key = $request->getParam('des_key',false);
		$secret = $request->getParam('secret',false);
		$res = "";
		
		
		if ( $algo == "wiki_encode" )
		{
			$res = str_replace ( array ( "|" , "/") , array ( "|01" , "|02" ) , base64_encode ( serialize ( $str ) ) ) ; 
		}
		elseif ( $algo == "wiki_decode_no_serialize" )
		{
			$res = base64_decode (str_replace ( array ( "|02" , "|01" ) , array ( "/" , "|" ) , $str ) ) ;
		}
		elseif ( $algo == "base64_encode" )
		{
			$res = base64_encode($str )		;
		}
		elseif ( $algo == "base64_decode" )
		{
			$res = base64_decode($str )		;
		}
		elseif ( $algo == "base64_3des_encode" )
		{
			$encrypted_data = VCryptoWrapper::encrypt_3des($str, $key);
			$res = base64_encode($encrypted_data)		;
			$this->des_key = $key;
		}
		elseif ( $algo == "base64_3des_decode" )
		{
			$input = base64_decode ($str);
	   		$decrypted_data = VCryptoWrapper::decrypt_3des($input, $key);
			$res = ($decrypted_data);
			$this->des_key = $key;
		}
		elseif ( $algo == "vs" )
		{			
			//$vs = vs::fromSecureString ( $str ); // to do ->api Extension
			$client = Infra_ClientHelper::getClient();
			$internalToolsPlugin = Vidiun_Client_VidiunInternalTools_Plugin::get($client);
			$vs = null;
			
			try{
				$vs = $internalToolsPlugin->vidiunInternalToolsSystemHelper->fromSecureString($str);
				$res = print_r ( $vs , true );
			}
			catch(Vidiun_Client_Exception $e){
				$res = $e->getMessage();
			}
			 
			if (!is_null($vs))
			{
				$expired = $vs->valid_until;
				$expired_str = self::formatThisData($expired); 
				$now = time();
				$now_str = self::formatThisData($now);
				$res .= "\n" . "VS valid until: " . $expired_str . "\nTime now: $now ($now_str)";
			} 
		}
		elseif ( $algo == "vwid" )
		{
			$vwid_str = @base64_decode( $str );
			if ( ! $vwid_str)
			{
				// invalid string
				return "";
			}
			$cracked = @explode ( "|" , $vwid_str );
			$names = array ( "vshow_id" , "partner_id" , "subp_id" , "article_name" , "widget_id" , "hash" );
			$combined = array_combine( $names , $cracked );
			
			$md5 = md5 ( $combined["vshow_id"]  . $combined["partner_id"]  . $combined["subp_id"] . $combined["article_name"] . 
				$combined["widget_id"] .  $secret );
				
			$combined["secret"] = $secret;
			$combined["calculated hash"] = substr ( $md5 , 1 , 10 );
			
			$res = print_r ( $combined , true );
		}
		elseif ( $algo == "ip" )
		{
			//$ip_geo = new myIPGeocoder();// to do ->api Extension
			$client = Infra_ClientHelper::getClient();
			$internalToolsPlugin = Vidiun_Client_VidiunInternalTools_Plugin::get($client);
			if ( $str )
				$remote_addr = $str;
			else
			{
				$remote_addr = $internalToolsPlugin->vidiunInternalToolsSystemHelper->getRemoteAddress();
			} 
			$res = $internalToolsPlugin->vidiunInternalToolsSystemHelper->iptocountry($remote_addr);
		}
		
				
		$action->view->key = $key;
		$action->view->secret = $secret;
		$action->view->str = $str;
		$SystemHelperFormResult->getElement('results')->setValue($res);
		$action->view->SystemHelperFormResult = $SystemHelperFormResult;
		$action->view->algo = $algo;
		
		
	}
	
	private static function formatThisData ( $time )
	{
		return strftime( "%d/%m %H:%M:%S" , $time );	
	}
}

