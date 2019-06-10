<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class updateLoginDataAction extends vidiunAction
{

	public function execute() 
	{
		// Disable layout
		$this->setLayout(false);
		$this->success = false;

		$this->type = $this->getRequestParameter('type');
		if(!$this->type)
			VExternalErrors::dieError(VExternalErrors::MISSING_PARAMETER, 'type');

		$validTypes = array('name', 'email' ,'password');
		if(! in_array($this->type, $validTypes))
			VExternalErrors::dieError( VExternalErrors::INVALID_SETTING_TYPE );

		$vs = $this->getP ( "vmcvs" );
		if(!$vs)
			VExternalErrors::dieError(VExternalErrors::MISSING_PARAMETER, 'vs');

		// Get partner & user info from VS
		$vsObj = vSessionUtils::crackVs($vs);
		$partnerId = $vsObj->partner_id;
		$userId = $vsObj->user;
		
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (!$partner)
			VExternalErrors::dieError(VExternalErrors::PARTNER_NOT_FOUND);
		
		if (!$partner->validateApiAccessControl())
			VExternalErrors::dieError(VExternalErrors::SERVICE_ACCESS_CONTROL_RESTRICTED);

		$this->forceVMCHttps = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ENFORCE_HTTPS, $partnerId);
		if( $this->forceVMCHttps ) {
			// Prevent the page fron being embeded in an iframe
			header( 'X-Frame-Options: SAMEORIGIN' );
		}
		if( $this->forceVMCHttps && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ) {
			die();
		}

		// Load the current user
		$dbUser = vuserPeer::getVuserByPartnerAndUid($partnerId, $userId);
	
		if (!$dbUser)
			VExternalErrors::dieError('INVALID_USER_ID', $userId);;

		$this->email = $dbUser->getEmail();
		$this->fname = $dbUser->getFirstName();
		$this->lname = $dbUser->getLastName();
		
		$this->parent_url = $this->clean($_GET['parent']);

		// Set page title
		switch($this->type) {
			case 'password': 
				$this->pageTitle = 'Change Password';
				break;

			case 'email':
				$this->pageTitle = 'Change Email Address';
				break;

			case 'name': 
				$this->pageTitle = 'Change Username'; 
				break;
		}

		// select which action to do
		if( isset($_POST['do']) ) {
			
			switch($_POST['do']) {
				
				case "password": 
					$this->changePassword();
					break;
					
				case "email":
					$this->changeEmail();
					break;
					
				case "name": 
					$this->changeName();
					break;
			}	
		}

		sfView::SUCCESS;
	}

	private function changePassword() 
	{
		// Checks if we have empty fields
		if( ! $this->checkRequiredFields(array('cur_password', 'new_password', 'retry_new_password')) ) {
			return ;
		}
		
		if( $_POST['new_password'] !== $_POST['retry_new_password'] ) {
			$this->setError('The passwords does not match!');
			return;
		}
		
		try {
			$this->updateLoginData($this->email, $_POST['cur_password'], null, $_POST['new_password'], null, null);
			$this->setSuccess();
						
		} catch( VidiunLoginDataException $e ){
			$this->setError($e->getMessage());
		}
	}

	private function changeEmail()
	{
		// Checks if we have empty fields
		if( ! $this->checkRequiredFields(array('email', 'password')) ) {
			return ;
		}

		try {
			$this->updateLoginData($this->email, $_POST['password'], $_POST['email'], null, null, null);
			$this->setSuccess();
						
		} catch( VidiunLoginDataException $e ){
			$this->setError($e->getMessage());
		}	
	}

	private function changeName()
	{
		// Checks if we have empty fields
		if( ! $this->checkRequiredFields(array('fname', 'lname', 'password')) ) {
			return ;
		}

		$firstName = $_POST['fname'] ;
		$lastName = $_POST['lname'] ;

		$firstName = strip_tags($firstName);
		$firstName = htmlentities($firstName);

		$lastName = strip_tags($lastName);
		$lastName = htmlentities($lastName);

		try {
			$this->updateLoginData($this->email, $_POST['password'], null, null, $firstName, $lastName);
			$this->setSuccess();

		} catch( VidiunLoginDataException $e ){
			$this->setError($e->getMessage());
		}
	}

	private function setSuccess() 
	{
		$this->success = true;
		if($this->type == 'password') {
			$this->msg = "close";
		} else {
			$this->msg = "reload";			
		}
	}

	private function setError($error) 
	{
		$error = str_replace("&lt;", "<", $error);
		$error = str_replace("&gt;", ">", $error);
		$this->error = $error;
	}

	private function clean($str) 
	{ 
		$str = str_replace("javascript:", "", $str);
		$str = str_replace("eval", "", $str);
		$str = str_replace("document", "", $str);
		$str = htmlspecialchars($str);
		$str = addslashes($str);
		
		return $str;
	}

	private function checkRequiredFields($fields) 
	{
		foreach($fields as $field) {
			if( empty($_POST[$field]) ) {
				$this->setError('You must fill all the fields.');
				return false;
			}
		}
		return true;
	}

	private function updateLoginData( $email , $password , $newEmail = "" , $newPassword = "", $newFirstName = null, $newLastName = null)
	{
		if ($newEmail != "")
		{
			if(!vString::isEmailString($newEmail))
				throw new VidiunLoginDataException ( APIErrors::INVALID_FIELD_VALUE, "newEmail" );
		}

		try {
			UserLoginDataPeer::updateLoginData ( $email , $password, $newEmail, $newPassword, $newFirstName, $newLastName);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				throw new VidiunLoginDataException(APIErrors::LOGIN_DATA_NOT_FOUND);
			}
			else if ($code == vUserException::WRONG_PASSWORD) {
				if($password == $newPassword)
					throw new VidiunLoginDataException(APIErrors::USER_WRONG_PASSWORD);
				else
					throw new VidiunLoginDataException(APIErrors::WRONG_OLD_PASSWORD);
			}
			else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$c = new Criteria(); 
				$c->add(UserLoginDataPeer::LOGIN_EMAIL, $email ); 
				$loginData = UserLoginDataPeer::doSelectOne($c);
				$invalidPasswordStructureMessage = $loginData->getInvalidPasswordStructureMessage();
				$invalidPasswordStructureMessage = str_replace('\n', "\n", $invalidPasswordStructureMessage);
				throw new VidiunLoginDataException(APIErrors::PASSWORD_STRUCTURE_INVALID,$invalidPasswordStructureMessage);
			}
			else if ($code == vUserException::PASSWORD_ALREADY_USED) {
				throw new VidiunLoginDataException(APIErrors::PASSWORD_ALREADY_USED);
			}
			else if ($code == vUserException::INVALID_EMAIL) {
				throw new VidiunLoginDataException(APIErrors::INVALID_FIELD_VALUE, 'email');
			}
			else if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				throw new VidiunLoginDataException(APIErrors::LOGIN_ID_ALREADY_USED);
			}
			throw $e;			
		}		
	}
}

class VidiunLoginDataException extends Exception 
{
	protected $code;
	
	public function __construct($errorString)
	{
		$errorArgs = func_get_args();
        array_shift( $errorArgs );
        
        $errorData = APIErrors::getErrorData( $errorString, $errorArgs );
        
        $this->code = $errorData['code'];
        $this->args = $errorData['args'];
        $this->message = @call_user_func_array('sprintf', array_merge(array($errorData['message']), $errorArgs));
	}
	
	
	public function __sleep()
	{
		return array('code', 'message');
	}
}
