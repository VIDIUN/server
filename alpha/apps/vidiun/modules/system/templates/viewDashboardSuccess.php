<?php $sf_context->getResponse()->setTitle("Vidiun - Dashboard")?>

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
	return "<a href='/index.php/browse?vshow_id=".$vshow_id."'><img width='50' src='".$path."' /></a>";
}

function returnEntryThumbnailLink( $vshow_id, $path, $entry_id, $media_type )
{
	if ($media_type == entry::ENTRY_MEDIA_TYPE_AUDIO)
		$path = "/images/main/ico_sound.gif";
		
	return "<a href='/index.php/browse?".$vshow_id."&entry_id=".$entry_id."'><img width='50' src='".$path."' /></a>";
}

function returnUserThumbnailLink( $path, $screenname )
{
	return "<a href='/index.php/myvidiun/viewprofile?screenname=".$screenname."'><img width='50' src='".$path."' /></a>";
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
<a href="/index.php/system/login?exit=true">logout</a><br>
<?php
echo '<h1>Vidiun System Dashboard</h1><p>Updated: '.date("D M j G:i:s T Y").'</p>'; 

echo '<h2>Summary</h2>';
echo '<TABLE border="1" cellspacing="2" cellpadding="10" bgcolor="#efefef" >';
echo '<TR bgcolor="#eee"><TD>Type</TD><TD>Total accumulated nubmer</TD><TD>Created during the last 7 days</TD><TD>Created during the last 24 hours</TD></TR>';
echo '<TR><TD><a href="#shows">Shows</a></TD><TD>'.$vshow_count.'</TD><TD>'.$vshow_count7.'</TD><TD>'.$vshow_count1.'</TD></TR>';
echo '<TR><TD><a href="#entries">Entries</a></TD><TD>'.$entry_count.'</TD><TD>'.$entry_count7.'</TD><TD>'.$entry_count1.'</TD></TR>';
echo '<TR><TD><a href="#users">Users</a></TD><TD>'.$vuser_count.'</TD><TD>'.$vuser_count7.'</TD><TD>'.$vuser_count1.'</TD></TR>';
echo '</TABLE>';

echo '<a name="shows"></a>';
$flip = 1;
echo '<h2>Recently created shows</h2>'; 
echo '<TABLE border="1" cellspacing="2" cellpadding="10">';
if( !$vshows ) echo '<h1>No shows found</h1>';
	else 
	{
	echo '<TR><TD>ID</TD><TD>Thumbnail</TD><TD>Created</TD><TD>Produer</TD><TD>Data</TD><TD >Name</TD><TD width="40%">Description</TD></TR>';
		foreach ( $vshows as $vshow )
		{
			$flip = $flip * -1;
			echo '<TR '.( $flip > 0 ? 'bgcolor="#eee"' : 'bgcolor="#ddd"').'>'.	
			'<TD>'.returnVshowLink( $vshow->getId()).'</TD>'.
			'<TD>'.returnShowThumbnailLink( $vshow->getThumbnailPath(), $vshow->getId() ).'</TD>'.
			'<TD>'.$vshow->getFormattedCreatedAt().'</TD>'.
			'<TD>'.returnUserLink( $vshow->getvuser()->getScreenName()).'</TD>'.
			'<TD>'.$vshow->getTypeText().'<br/>Views:'.$vshow->getViews().'<br/>Entries:'.( $vshow->getEntries() - 1 ) .'</TD>'.
			'<TD>'.$vshow->getName().'</TD>'.
			'<TD>'.$vshow->getDescription().'</TD>'.
			
			'</TR>';
		}
	}
echo '</TABLE>';

echo '<a name="entries"></a>';
echo '<h2>Recent Entries</h2>'; 
echo '<TABLE border="1" cellspacing="2" cellpadding="10">';
if( !$entries ) echo '<h3>No entries found</h3>';
	else 
	{
	echo '<TR><TD>ID</TD><TD>Thumbnail</TD><TD>Created</TD><TD>Contributor</TD><TD>Media Type</TD><TD >Name</TD><TD width="40%">Part of vidiun</TD></TR>';
		foreach ( $entries as $entry )
		{
			$vshow = $entry->getvshow();
			$flip = $flip * -1;
			echo '<TR '.( $flip > 0 ? 'bgcolor="#eee"' : 'bgcolor="#ddd"').'>'.	
			'<TD>'.returnEntryLink( $vshow->getId(), $entry->getId()).'</TD>'.
			'<TD>'.returnEntryThumbnailLink( $vshow->getId(),$entry->getThumbnailPath(), $entry->getId(), $entry->getMediaType() ).'</TD>'.
			'<TD>'.$entry->getFormattedCreatedAt().'</TD>'.
			'<TD>'.returnUserLink( $entry->getvuser()->getScreenName()).'</TD>'.
			'<TD>'.getEntryTypeText($entry->getMediaType()).'</TD>'.
			'<TD>'.$entry->getName().'</TD>'.
			'<TD>'.returnShowThumbnailLink( $vshow->getThumbnailPath(), $vshow->getId() ).' '.$vshow->getName().'</TD>'.
			'</TR>';
		}
	}
echo '</TABLE>';

echo '<a name="users"></a>';
echo '<h2>Recent Users</h2>'; 
echo '<TABLE border="1" cellspacing="2" cellpadding="10">';
if( !$vusers ) echo '<h3>No users found</h3>';
	else 
	{
	echo '<TR><TD>ID</TD><TD>Thumbnail</TD><TD>Created</TD><TD>Screenname</TD><TD>Demographics</TD></TR>';
		foreach ( $vusers as $vuser )
		{
			$flip = $flip * -1;
			echo '<TR '.( $flip > 0 ? 'bgcolor="#eee"' : 'bgcolor="#ddd"').'>'.	
			'<TD>'.$vuser->getId().'</TD>'.
			'<TD>'.returnUserThumbnailLink( $vuser->getPicturePath(), $vuser->getScreenName() ).'</TD>'.
			'<TD>'.$vuser->getFormattedCreatedAt().'</TD>'.
			'<TD>'.returnUserLink( $vuser->getScreenName()).'</TD>'.
			'<TD>'.($vuser->getCountry() ? image_tag('flags/'.strtolower($vuser->getCountry()).'.gif') : '').' '.$vuser->getCity().' '.$vuser->getState().'<br/>'.($vuser->getGender() == 1 ? 'Male' : ($vuser->getGender() == 2 ? 'Female' : '')).'<br/>'.$vuser->getAboutMe().'</TD>'.
			
			'</TR>';
		}
	}
echo '</TABLE>';

