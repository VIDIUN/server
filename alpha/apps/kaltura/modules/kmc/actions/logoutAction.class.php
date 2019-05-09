<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class logoutAction extends vidiunAction
{
	public function execute ( ) 
	{
		$vsStr = $this->getP("vs");
		if($vsStr) {
			$vsObj = null;
			try
			{
				$vsObj = vs::fromSecureString($vsStr);
			}
			catch(Exception $e)
			{				
			}
				
			if ($vsObj)
			{
				$partner = PartnerPeer::retrieveByPK($vsObj->partner_id);
				if (!$partner)
					VExternalErrors::dieError(VExternalErrors::PARTNER_NOT_FOUND);
						
				if (!$partner->validateApiAccessControl())
					VExternalErrors::dieError(VExternalErrors::SERVICE_ACCESS_CONTROL_RESTRICTED);
				
				$vsObj->kill();
			}
			VidiunLog::info("Killing session with vs - [$vsStr], decoded - [".base64_decode($vsStr)."]");
		}
		else {
			VidiunLog::err('logoutAction called with no VS');
		}
		
		setcookie('pid', "", 0, "/");
		setcookie('subpid', "", 0, "/");
		setcookie('vmcvs', "", 0, "/");

		return sfView::NONE; //redirection to vmc/vmc is done from java script
	}
}
