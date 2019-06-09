<?php
/**
 * Test page for adding JW player to VMC
 * 
 * @package    Core
 * @subpackage VMC
 */
class varpartnerlistAction extends vidiunAction
{
	public function execute ( ) 
	{
		$email = @$_GET['email'];
		$screenName = @$_GET['screen_name'];
		$partner_id = $this->getP('partner_id', null);
		if($partner_id === null)
		{
			header("Location: /index.php/vmc/varlogin");
			die;
		}
		
		sfView::SUCCESS;
		
		$this->me = PartnerPeer::retrieveByPK($this->getP('partner_id', null));
		if(!$this->me || $this->me->getPartnerGroupType() != PartnerGroupType::VAR_GROUP)
		{
			die('You are not an wuthorized VAR. If you are a VAR, Please contact us at support@vidiun.com');
		}
		
		$vs = vSessionUtils::crackVs($this->getP('vs'));
		$user = $vs->user;
		$res = vSessionUtils::validateVSession2(vSessionUtils::REQUIED_TICKET_ADMIN, $partner_id, $user, $this->getP('vs'), $vs);
		if($res != vs::OK)
		{
			header("Location: /index.php/vmc/varlogin");
			die;
		}
		
		$c = new Criteria;
		$c->addAnd(PartnerPeer::PARTNER_PARENT_ID, $this->me->getId());
		// add extra filtering if required
		//$c->addAnd(PartnerPeer::STATUS, 1);
		$partners = PartnerPeer::doSelect($c);
		$this->partners = array();
		$partner_id_param_name = 'pid';
		$subpid_param_name = 'subpid';
		if($this->me->getVmcVersion() == 1)
		{
			$partner_id_param_name = 'partner_id';
			$subpid_param_name = 'subp_id';
		}
		$vmc2Query = '?'.$partner_id_param_name.'='.$this->me->getId().'&'.$subpid_param_name.'='.($this->me->getId()*100).'&vs='.$_GET['vs'].'&email='.$email.'&screen_name='.$screenName;
		$this->varVmcUrl = 'http://'.vConf::get('www_host').'/index.php/vmc/vmc'.$this->me->getVmcVersion().$vmc2Query;
		foreach($partners as $partner)
		{
			$vs = null;
			vSessionUtils::createVSessionNoValidations ( $partner->getId() ,  $partner->getAdminUserId() , $vs , 30 * 86400 , 2 , "" , "*" );
			$adminUser_email = $partner->getAdminEmail();
			$partner_id_param_name = 'pid';
			$subpid_param_name = 'subpid';
			if($partner->getVmcVersion() == 1)
			{
				$partner_id_param_name = 'partner_id';
				$subpid_param_name = 'subp_id';
			}
			$vmc2Query = '?'.$partner_id_param_name.'='.$partner->getId().'&'.$subpid_param_name.'='.($partner->getId()*100).'&vs='.$vs.'&email='.$adminUser_email.'&screen_name=varAdmin';
			//$vmcLink = url_for('index.php/vmc/vmc2'.$vmc2Query);
//			$vmcLink = 'http://'.vConf::get('www_host').'/index.php/vmc/vmc'.$partner->getVmcVersion().$vmc2Query;
			$vmcLink = 'http://'.vConf::get('www_host')."/index.php/vmc/extlogin?vs=$vs&partner_id=" . $partner->getId();
			$this->partners[$partner->getId()] = array(
				'name' => $partner->getPartnerName(),
				'vmcLink' => $vmcLink,
			);
		}
	}
}
