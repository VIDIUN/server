<?php
/**
 * @package    Core
 * @subpackage VMC
 */

/**
 * @package    Core
 * @subpackage VMC
 */
class supportAction extends vidiunAction
{
    const SUPPORT_EMAIL_TYPE_ID = 210;
    
    public function execute ( ) 
    {
        // Prevent the page fron being embeded in an iframe
        header( 'X-Frame-Options: SAMEORIGIN' );
        
        $this->sent_request = false;

        if(isset($_GET['style']) && $_GET['style'] == 'v') {    // vmc virgo
            $this->closeFunction = 'parent.vmcCloseModal()';
            $this->bodyBgColor = 'E1E1E1';
        }
        else {
            $this->closeFunction = 'parent.vmc.utils.closeModal()';
            $this->bodyBgColor = 'F8F8F8';
        }

        /** check parameters and verify user is logged-in **/
        $this->vs = $this->getP ( "vmcvs" );
        if(!$this->vs)
        {
            // if vmcvs from cookie doesn't exist, try vs from REQUEST
            $this->vs = $this->getP('vs');
        }
        if (isset($this->vs))
        {
            $vsObj = vSessionUtils::crackVs($this->vs);
            // Set partnerId from VS
            $this->partner_id = $vsObj->partner_id;
        } else if (isset($_POST['partner_id']))
        {
            $this->partner_id = $_POST['partner_id'];
        }

        if (isset($this->partner_id))
        {
            // Check for forced HTTPS
            $force_ssl = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ENFORCE_HTTPS, $this->partner_id);
            if( $force_ssl && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') ) {
                header( "Location: " . infraRequestUtils::PROTOCOL_HTTPS . "://" . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] );
                die();
            }
        }

        if(isset($_POST['partner_id']))
        {
            // do mail
            $mail_body = array();
            foreach($_POST as $key => $val)
            {
                $mail_body[] = $key.': '.$val;
            }
            $strMailBody = implode('<BR><BR>', $mail_body);
            
            $body_params = array($strMailBody);
            $subject_params = array($_POST['subject']);
            vJobsManager::addMailJob(
                    null, 
                    0, 
                    $_POST['partner_id'],
                    self::SUPPORT_EMAIL_TYPE_ID, 
                    vMailJobData::MAIL_PRIORITY_NORMAL,
                    $_POST['email'], 
                    $_POST['your_name'].' ',
                    'vidiunsupport@vidiun.com',
                    $body_params,
                    $subject_params);

            // Send support ticket to salesforce
            $post_items = array();
            foreach ($_POST as $key => $value) {
                $post_items[] = $key . '=' . urlencode($value);
            }
            $post_string = implode ('&', $post_items);
            $ch = curl_init("https://webto.salesforce.com/servlet/servlet.WebToCase?encoding=UTF-8");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
            $this->curlResult = curl_exec($ch);
            curl_close($ch); 

            $this->sent_request = true;
        }
        sfView::SUCCESS;
    }
}
