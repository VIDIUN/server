<?php use_helper('Validation') ?>
<?php use_helper('Javascript') ?>

<?php 

function retrieveSubject( $type, $id )
{
	switch( $type )
	{
		case flag::SUBJECT_TYPE_ENTRY: { $entry = entryPeer::retrieveByPK( $id ); return 'entry id:'.$id.'<br/>vshow:'.returnVshowLink($entry->getVshowId()).'<br/>Name:'.$entry->getName(); }
		case flag::SUBJECT_TYPE_USER: { $user = vuserPeer::retrieveByPK( $id ); return returnUserLink( $user->getScreenName()); }
		case flag::SUBJECT_TYPE_COMMENT: { $comment = commentPeer::retrieveByPK( $id ); return 'comment id:'.$id.'<br/>Commnet:'.$comment->getComment(); }
		default: return 'Unknown';
	}
}

function returnUserLink( $username )
{
	return "<a href='/index.php/myvidiun/viewprofile?screenname=".$username."'>".$username."</a>";
}

function returnEntryLink( $vshow_id, $entry_id )
{
	return "<a href='/index.php/browse?vshow_id=".$vshow_id."&entry_id=".$entry_id."'>".$entry_id."</a>";
}

function returnVshowLink( $vshow_id )
{
	return "<a href='/index.php/browse?vshow_id=".$vshow_id."'>".$vshow_id."</a>";
}

function returnShowThumbnailLink( $path, $vshow_id )
{
	return "<a href='/index.php/browse?vshow_id=".$vshow_id."'><img src='".$path."' alt='' /></a>";
}

function returnEntryThumbnailLink( $vshow_id, $path, $entry_id, $media_type )
{
	if ($media_type == entry::ENTRY_MEDIA_TYPE_AUDIO)
		$path = "/images/main/ico_sound.gif";
		
	return "<a href='/index.php/browse?".$vshow_id."&entry_id=".$entry_id."'><img src='".$path."' alt='' /></a>";
}

function returnUserThumbnailLink( $path, $screenname )
{
	return "<a href='/index.php/myvidiun/viewprofile?screenname=".$screenname."'><img src='".$path."' alt='' /></a>";
}

function getEntryTypeText( $type )
{
	switch ( $type )
	{
		case entry::ENTRY_MEDIA_TYPE_ANY: return 'Any'; 
		case entry::ENTRY_MEDIA_TYPE_VIDEO: return 'Video'; 
		case entry::ENTRY_MEDIA_TYPE_IMAGE: return 'Image'; 
		case entry::ENTRY_MEDIA_TYPE_TEXT: return 'Text'; 
		case entry::ENTRY_MEDIA_TYPE_HTML: return 'Html'; 
		case entry::ENTRY_MEDIA_TYPE_AUDIO: return 'Audio'; 
		case entry::ENTRY_MEDIA_TYPE_SHOW: return 'Show'; 
		default: return 'unknown type';
	}
}

?>

<?php

$now = date("D M j G:i:s T Y");

$options = dashboardUtils::partnerOptions ( $partner_id );

$bands_only_str =  " (Partner: " .
'<select onchange="partnerSelect(this)" id="partner_id" style="">' .	$options .'</select>' .
")";

//( $bands_only ? " (Bands)" : "(No Bands)" );



	
echo <<<EOT
<div id="header" class="clearfix">
	<div class="top">
		<a href="/index.php/system/login?exit=true">logout</a>
	</div>
	<h1>Vidiun System Dashboard $bands_only_str</h1>
	<span>Updated: $now</span>
</div>

<h2>Summary</h2>
<table border="0" cellspacing="0" cellpadding="10">
	<thead>
		<tr>
			<td>Type</td>
			<td>Total accumulated nubmer</td>
			<td>Created during the last 7 days</td>
			<td>Created during the last 24 hours</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><a href="#shows">Shows</a></td>
			<td>$vshow_count</td><td>$vshow_count7</td>
			<td>$vshow_count1</td>
		</tr>
		<tr class="even">
			<td><a href="#entries">Entries</a></td>
			<td>$entry_count</td><td>$entry_count7</td><td>$entry_count1</td>
		</tr>
		<tr>
			<td><a href="#users">Users</a></td>
			<td>$vuser_count</td><td>$vuser_count7</td>
			<td>$vuser_count1</td>
		</tr>
	</tbody>
</table>
EOT;

echo '<a name="shows"></a>';
$flip = 1;
echo '<h2>Recently created shows</h2>'; 
echo '<table border="0" cellspacing="0" cellpadding="10">';
if( !$vshows ) echo '<h1>No shows found</h1>';
	else 
	{
	echo '<thead><tr><td>ID</td><td>Thumbnail</td><td>Created</td><td>Produer</td><td>Data</td><td >Name</td><td width="40%">Description</td></tr></thead>';
		foreach ( $vshows as $vshow )
		{
			if ( $modified_only ) 
			{
				$has_new_entries = key_exists( $vshow->getId() , $vshows_with_new_entries ) ;
				$new_entries =  $has_new_entries ? " (" . $vshows_with_new_entries[$vshow->getId() ] . ")" : "";
			}
			else
			{
				$has_new_entries = false ;
				$new_entries =  "";
			}
			
			$flip = $flip * -1;
			echo '<tbody>'.
			 ( $has_new_entries ? 
			 	( '<tr '. ( $flip > 0 ? 'class="even2"' : 'class="odd2"') .'>' ) : 
			 	( '<tr '. ( $flip > 0 ? 'class="even"' : '' ) .'>' )
			 ).				
	
			'<td>'.returnVshowLink( $vshow->getId()).'</td>'.
			'<td class="image">'.returnShowThumbnailLink( $vshow->getThumbnailPath(), $vshow->getId() ).'</td>'.
			'<td>'.$vshow->getFormattedCreatedAt().'</td>'.
			'<td>'.returnUserLink( $vshow->getvuser()->getScreenName()).'</td>'.
			'<td>'.$vshow->getTypeText().'<br/>Views:'.$vshow->getViews().'<br/>Roughcuts:' . $vshow->getRoughcutCount(). 
				'<br/>Entries:'.( $vshow->getEntries() - 1 ) . $new_entries .'<br/>Comments:' . $vshow->getComments() . '</td>'.
			'<td>'.$vshow->getName().'</td>'.
			'<td>'.$vshow->getdescription().'</td>'.
			'</tr>'.
			'</tbody>';
		}
	}
echo '</table>';

echo '<a name="entries"></a>';
echo '<h2>Recent Entries</h2>'; 
echo '<table border="0" cellspacing="0" cellpadding="10">';
if( !$entries ) echo '<h3>No entries found</h3>';
	else 
	{
	echo '<thead><tr><td>ID</td><td>Thumbnail</td><td>Created</td><td>Contributor</td><td>Media Type</td><td >Name</td><td width="40%">Part of vidiun</td></tr></thead>';
		foreach ( $entries as $entry )
		{
			$vshow = $entry->getvshow();
			$flip = $flip * -1;
			echo '<tbody>'.
			'<tr '.( $flip > 0 ? 'class="even"' : '').'>'.		
			'<td>'.returnEntryLink( $vshow->getId(), $entry->getId()).'</td>'.
			'<td class="image">'.returnEntryThumbnailLink( $vshow->getId(),$entry->getThumbnailPath(), $entry->getId(), $entry->getMediaType() ).'</td>'.
			'<td>'.$entry->getFormattedCreatedAt().'</td>'.
			'<td>'.returnUserLink( $entry->getvuser()->getScreenName()).'</td>'.
			'<td>'.getEntryTypeText($entry->getMediaType()).'</td>'.
			'<td>'.$entry->getName().'</td>'.
			'<td class="image">'.returnShowThumbnailLink( $vshow->getThumbnailPath(), $vshow->getId() ).' '.$vshow->getName().'</td>'.
			'</tr>'.
			'</tbody>';
		}
	}
echo '</table>';

echo '<a name="users"></a>';
echo '<h2>Recent Users</h2>'; 
echo '<table border="0" cellspacing="0" cellpadding="10">';
if( !$vusers ) echo '<h3>No users found</h3>';
	else 
	{
	echo '<thead><tr><td>ID</td><td>Thumbnail</td><td>Created</td><td>Screenname</td><td>Data</td><td>Demographics</td></tr></thead>';
		foreach ( $vusers as $vuser )
		{
			$flip = $flip * -1;
			echo '<tbody>'.
			'<tr '.( $flip > 0 ? 'class="even"' : '').'>'.	
			'<td>'.$vuser->getId().'</td>'.
			'<td class="image">'.returnUserThumbnailLink( $vuser->getPicturePath(), $vuser->getScreenName() ).'</td>'.
			'<td>'.$vuser->getFormattedCreatedAt().'</td>'.
			'<td>'.returnUserLink( $vuser->getScreenName()).'</td>'.
			'<td>Vidiuns:' .$vuser->getProducedVshows() . "<br/>Roughcuts:" . $vuser->getRoughcutCount() .'</td>'.
			'<td class="country">'.($vuser->getCountry() ? image_tag('flags/'.strtolower($vuser->getCountry()).'.gif') : '').' '.$vuser->getCity().' '.$vuser->getState().'<br/>'.($vuser->getGender() == 1 ? 'Male' : ($vuser->getGender() == 2 ? 'Female' : '')).'<br/>'.$vuser->getAboutMe().'</td>'.
			'</tr>'.
			'</tbody>';
		}
	}
echo '</table>';

