<?php

$flavorParamsId = 0; // zero for new

$partnerId = 0;
$name = 'Flash SWF';
$tags = null;
$description = 'Flash SWF';
$readyBehavior = 2;
$isDefault = false;
$width = 0;
$height = 0;

$flashVersion = 10;
$zoom = null;
$zlib = null;
$quality = null;
$sameWindow = null;
$stop = null;
$useShapes = null;
$storeFonts = null;
$flatten = null;

/**************************************************
 * DON'T TOUCH THE FOLLOWING CODE
 ***************************************************/

chdir(dirname(__FILE__));
require_once(__DIR__ . '/../../bootstrap.php');

$flavorParams = null;

if($flavorParamsId)
{
	$flavorParams = assetParamsPeer::retrieveByPK($flavorParamsId);
	if(!($flavorParams instanceof SwfFlavorParams))
	{
		echo "Flavor params id [$flavorParamsId] is not SWF flavor params\n";
		exit;
	}
	$flavorParams->setVersion($flavorParams->getVersion() + 1);
}
else
{
	$flavorParams = new SwfFlavorParams();
	$flavorParams->setVersion(1);
	$flavorParams->setFormat(flavorParams::CONTAINER_FORMAT_SWF);
}

$swfOperator = new vOperator();
$swfOperator->id = VidiunConversionEngineType::PDF2SWF;
$pdfOperator = new vOperator();
$pdfOperator->id = VidiunConversionEngineType::PDF_CREATOR;
$operators = new vOperatorSets();
$operators->addSet(array($pdfOperator, $swfOperator));
$operators->addSet(array($swfOperator));

$flavorParams->setPartnerId($partnerId);
$flavorParams->setName($name);
$flavorParams->setTags($tags);
$flavorParams->setDescription($description);
$flavorParams->setReadyBehavior($readyBehavior);
$flavorParams->setIsDefault($isDefault);
$flavorParams->setWidth($width);
$flavorParams->setHeight($height);
$flavorParams->setOperators($operators->getSerialized());
$flavorParams->setEngineVersion(1);
$flavorParams->setVideoBitrate(1);

// specific for swf
$flavorParams->setFlashVersion($flashVersion);
$flavorParams->setZoom($zoom);
$flavorParams->setZlib($zlib);
$flavorParams->setJpegQuality($quality);
$flavorParams->setSameWindow($sameWindow);
$flavorParams->setInsertStop($stop);
$flavorParams->setUseShapes($useShapes);
$flavorParams->setStoreFonts($storeFonts);
$flavorParams->setFlatten($flatten);
$flavorParams->save();

echo "Flavor params [" . $flavorParams->getId() . "] saved\n";
