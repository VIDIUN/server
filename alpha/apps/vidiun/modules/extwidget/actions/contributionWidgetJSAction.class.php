<?php
/**
 * @package Core
 * @subpackage externalWidgets
 */
require_once ( MODULES . "/partnerservices2/actions/startsessionAction.class.php" );

/**
 * @package Core
 * @subpackage externalWidgets
 */
class contributionWidgetJSAction extends vidiunAction
{
	public function execute()
	{
		$this->getResponse()->setHttpHeader("Content-Type", "application/x-javascript");
		
		$vshow_id = $this->getRequestParameter('vshow_id', 0);
		$uid = vuser::ANONYMOUS_PUSER_ID;
		$vshow = vshowPeer::retrieveByPK($vshow_id);
	
		if (!$vshow)
			return sfView::ERROR;
		
		// vshow_id might be a string (something like "15483str") but it will be returned using retriveByPK anyways
		// lets make sure we pass just the id to the contribution wizard
		$vshow_id = $vshow->getId();
		
		$partner_id = $vshow->getPartnerId();
		
		$partner = PartnerPeer::retrieveByPK($partner_id);
		$subp_id = $vshow->getSubpId();
		$partner_secret = $partner->getSecret();
		$partner_name = $partner->getPartnerName();
				
		$vidiun_services = new startsessionAction();
		$vidiun_services->setInputParams( 
			array (
				"format" => vidiunWebserviceRenderer::RESPONSE_TYPE_PHP_ARRAY, 
				"partner_id" => $partner_id, 
				"subp_id" => $subp_id, 
				"uid" => $uid, 
				"secret" => $partner_secret
			)
		);
		
		$result = $vidiun_services->internalExecute() ;
		
		$this->vs = @$result["result"]["vs"];
		$this->widget_host = requestUtils::getHost();
		$this->vshow_id = $vshow_id;
		$this->uid = $uid;
		$this->partner_id = $partner_id;
		$this->subp_id = $subp_id;
		$this->partner_name  = $partner_name;
	
		return sfView::SUCCESS;
	}
}
