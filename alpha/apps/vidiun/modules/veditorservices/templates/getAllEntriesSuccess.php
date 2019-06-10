<?php
// create xml for each entry is entry_list
if ( $debug ) { echo "Result<br><textarea cols=100 rows=50>"; }
?>

<assets>
<?php
vAssetUtils::createAssets ( $vshow_entry_list , "show" );
vAssetUtils::createAssets ( $vuser_entry_list , "user" );
?>
</assets>

<?php if ( $debug ) { echo "</textarea>" ; } ?>