<?php
//$vshow_id = $vshow ? $vshow->getId() : "";
$band_id = $vshow ? $vshow->getIndexedCustomData1() : "";
$delete_text = $should_delete ? "Deleted " : "Will delete ";

$vshow_count = count ( $other_vshows_by_producer );

echo $error . "<br>";

$url = url_for ( "/system") . "/deleteVshow?vshow_id="; 
if ( $vshow_count > 1 )
{
	$str = "";
	foreach ( $other_vshows_by_producer as $other_vshow )
	{
		$str .= "<a href='$url" . $other_vshow->getId() ."'>" .$other_vshow->getId() . "</a> "; 
	}
	
	echo $str;
}

if ( $vuser_count > 1 )
{
	echo "There are [$vuser_count] results for [$vuser_name]. Bellow is displayed the first one.<br>You may want to better specify the screen name." ; 
}
?>
 
<form id="form1" method=get>
	vshow id: <input name="vshow_id" value="<?php echo $vshow_id ?>"> band id: <input name="band_id" value="<?php echo $band_id ?>">
	User name: <input name="vuser_name" value="<?php echo $vuser_name ?>">
	<input type="hidden" id="deleteme" name="deleteme" value="false">
	<input type="submit"  name="find" value="find">
</form>

<?php if ( !$vshow ) 
{
	if ( $vshow_id )
	{
		echo "Vshow id [" . $vshow_id . "] does not exist in the DB";
	}	
	return ;
}

?>

<?php if ( $vuser && $vshow_count < 2 ) 
{
	echo $delete_text . "vuser '" . $vuser->getScreenName() . "' [" . $vuser->getId()
	. "] which was created at " . $vuser->getCreatedAt() . " (" .  $vuser->getFormattedCreatedAt() . ")" ; 
} ?>
<br> 

<?php echo $delete_text . "'" . $vshow->getName() ."' [" . $vshow->getId() ."] with band id . " . $vshow->getIndexedCustomData1() . ":" ?>
<br>
<table>
<?php 
echo investigate::printVshowHeader();
echo investigate::printVshow( $vshow );
?>
</table>
<br>
and entries:<br>
<table>
<?php
echo investigate::printEntryHeader();
foreach ( $entries as $entry )
{
	echo investigate::printEntry( $entry );	
}
?>
</table>

<br>
<input type="button" name="Delete" value="Delete" onclick="deleteme()">

<script>
function deleteme()
{
<?php if ( $vshow_count ) { ?> 
	text = "vuser '<?php echo $vuser->getScreenName()?>' will not be deleted becuase he/she has (<?php echo $vshow_count ?>) vshows.'\n" + 
		"One of the vshows: vshow '<?php echo $vshow->getName() ?>' with all (<?php echo count ( $entries ) ?>) entries\n" +
			"????\n\n" +
			"Remember - this action is NOT reversible!!" ;
	
<?php } else { ?>
	text = "Do you really want to delete poor vuser '<?php echo $vuser->getScreenName()?>'\n" + 
		"and it's vshow '<?php echo $vshow->getName() ?>' with all (<?php echo count ( $entries ) ?>) entries\n" +
			"????\n\n" +
			"Remember - this action is NOT reversible!!" ;
<?php } ?>
	if ( confirm ( text ) )
	{
		text2 = "I'll ask again...\n\n" + text + "\n\n\n";
		if (  confirm ( text2) ) 
		{
			deleteImpl();
		}
	}
}

function deleteImpl()
{
	e = jQuery ( "#deleteme" );
	e.attr ("value", "true" ); 
	
	jQuery ( "#form1")[0].submit()
}
</script>
	