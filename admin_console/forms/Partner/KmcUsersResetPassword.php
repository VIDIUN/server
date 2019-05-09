<?php 
/**
 * @package Admin
 * @subpackage Users
 */
class Form_Partner_VmcUsersResetPassword extends Infra_Form
{
	public function init()
	{
		// Set the method for the display form to POST
		$this->setMethod('post');
		$this->setAttrib('id', 'frmVmcUsersResetPassword');
		
		$this->addElement('password', 'newPassword', array(
			'label' 		=> 'New Password:',
			'required'		=> true,
			'filters' 		=> array('StringTrim'),	
		));
	}
}

