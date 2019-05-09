<?php

if (!$vuser)
	die;
	
$user_name = $vuser->getScreenName();
$show_vlogo = 1;
if($entry)
{
	$user_name = $entry->getSubpId() == 10003 ? "Facelift" : $vuser->getScreenName();
	$show_vlogo = ($widget_type == 3 && $entry->getPartnerId() == 18) ? 0 : 1; // dont show the vidiun logo while playing on wikieducator
}

?>
<xml>
	<?php echo baseObjectUtils::objToXml ( $entry , array ( 'id' , 'name', 'vshow_id' , 'tags', 'media_type', 'length_in_msecs', 'status' ) , 'entry' , true , 
		array ( "thumbnail_path" => $thumbnail , "user_name" => $user_name,
			"message" => $message,
			"server_time" => time(),
			"vshow_category" => $vshow_category,
			"vshow_name" => $vshow_name,
			"vshow_description" => $vshow_description,
			"vshow_tags" => $vshow_tags,
			"generic_embed_code" => $generic_embed_code, "myspace_embed_code" => $myspace_embed_code,
			"share_url" => $share_url,
			"show_vlogo" => $show_vlogo) ) ; ?>
</xml>