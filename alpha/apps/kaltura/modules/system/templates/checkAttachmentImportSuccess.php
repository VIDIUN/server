<?php
use_helper('Javascript');
echo javascript_tag('contributeurl = "'. url_for( '/contribute' ).'";');
?>

<script type="text/javascript" src='/sf/prototype/js/prototype.js'></script>

<script type="text/javascript">

function mobileInsertEntry( vshowid, usermobile, mediatype, fileforupload, filethumbnail )
{

	if (mediatype == "video")
	{ mediatypecode = 1; }
	else if ( mediatype = "image" )
	{ mediatypecode = 2 }
	else mediatypecode = 0; // unknown type
	

	new Ajax.Request( contributeurl + '/insertEntry' +
		'?vshow_id=' + vshowid +
		'&entry_id=0' +
		'&mobile_upload=1' +
		'&mobile_id=' + usermobile +
		'&entry_name=Mobile Entry' +
		'&entry_description=Entry from mobile phone' +
		'&entry_media_type=' + mediatypecode +
		'&entry_tags=' +
		'&entry_data='+ fileforupload + 
		'&entry_thumbnail=' + filethumbnail
	, {asynchronous:true, evalScripts:false, onComplete:function(request){onMobileInsertComplete(request.responseText)}});
}

function onMobileInsertComplete( responseText )
{
 	alert ("mobile insert complete: " + responseText );
}

</script>
<h1>Recent MMS uploads</h1>

<?php

$host="mail.vidiun.com"; //  imap host
$login="mobile@vidiun.com"; //imap  login
$password="passme"; //imap password

$importer=new myMailAttachmentImporter(); // Creating instance of class####

$importedMessageDataArray = $importer->getdata($host,$login,$password,myContentStorage::getFSUploadsPath());

if ( $importedMessageDataArray != null )
{
	echo 'Number of messages in mailbox '. $login.': '.count( $importedMessageDataArray);
	echo '<hr>';
	
	foreach(  $importedMessageDataArray as $importedMessageData  )
	{
		echo 'subject:'.$importedMessageData["subject"].'<BR>';
		echo 'from:'.$importedMessageData["fromaddress"].'<BR>';
		echo 'date:'.$importedMessageData["date"].'<BR>';
		echo 'body:'.$importedMessageData["body"].'<BR>';
	
		if( $importedMessageData["attachment"] )
		{
			echo 'type = '.$importedMessageData["attachment"]["type"].'/'.$importedMessageData["attachment"]["subtype"].'<BR>';
				
				if( $importedMessageData["attachment"]["type"] == 'image' ) echo '<img width="100" width="100" src="/content/uploads/'.$importedMessageData["attachment"]["filename"].'"><BR>';
				else
				{
					echo '<img width="100" width="100" src="/content/uploads/'.$importedMessageData["attachment"]["thumbnail"].'">';
					echo '<a href="/content/uploads/'.$importedMessageData["attachment"]["filename"].'">download file</a>';
				}
				
				$vshowinsertid = $importedMessageData["subject"];
				if ( $vshowinsertid == "" ) $vshowinsertid = 1;
				$insertypte = $importedMessageData["attachment"]["type"];
				$insertfilename = $importedMessageData["attachment"]["filename"];
				$thumbnailfilename = $importedMessageData["attachment"]["thumbnail"];
				
				$pieces = explode( '@', $importedMessageData["fromaddress"] );
				$vusermobileid = $pieces[0];
				
				$functionname = "mobileInsertEntry('".$vshowinsertid."','".$vusermobileid."','".$insertypte."','".$insertfilename."','".$thumbnailfilename."')";
				echo '<input type="button" value="submit mobile entry" onclick="'.$functionname.'">';
			
		}
		else echo 'no attachment';
		
		echo '<hr>';
	}
}
else echo "can't connect: " . imap_last_error();
		

?>