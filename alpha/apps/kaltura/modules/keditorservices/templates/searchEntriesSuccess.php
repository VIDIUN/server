<?php ?>

<assets total_count="<?php echo $number_of_results ?>" pages="<?php echo $number_of_pages ?>">
<?php
vAssetUtils::createAssets ( $entry_results , "search" );
?>
</assets>

