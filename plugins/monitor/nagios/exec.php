<?php
define('NAGIOS_CODE_OK', 0);
define('NAGIOS_CODE_WARNING', 1);
define('NAGIOS_CODE_CRITICAL', 2);
define('NAGIOS_CODE_UNKNOWN', 3);


$vidiunRootPath = realpath(__DIR__ . '/../../../');

require_once "$vidiunRootPath/tests/monitoring/VidiunMonitorResult.php";
if($argc == 1)
{
	echo "usage...";
}

$systemConfig = parse_ini_file("$vidiunRootPath/configurations/system.ini");

$errorThresholdMax = null;
$errorThresholdMin = null;
$warningThresholdMax = null;
$warningThresholdMin = null;

$options = getopt('', array(
	'script:',
	'error-threshold:',
	'warning-threshold:',
));
$matches = null;
if(isset($options['error-threshold']))
{
	if(preg_match('/^([\d]*)-([\d]*)$/', trim($options['error-threshold']), $matches))
	{
		if(is_numeric($matches[1]))
			$errorThresholdMin = $matches[1];
		if(is_numeric($matches[2]))
			$errorThresholdMax = $matches[2];
	}
	elseif(is_numeric($options['error-threshold']))
	{
		$errorThresholdMax = intval($options['error-threshold']);
	}
}
if(isset($options['warning-threshold']))
{
	if(preg_match('/^([\d]*)-([\d]*)$/', trim($options['warning-threshold']), $matches))
	{
		if(is_numeric($matches[1]))
			$warningThresholdMin = $matches[1];
		if(is_numeric($matches[2]))
			$warningThresholdMax = $matches[2];
	}
	elseif(is_numeric($options['warning-threshold']))
	{
		$warningThresholdMax = intval($options['warning-threshold']);
	}
}
if(!isset($options['script']))
{
	echo "Script argument not supplied";
	exit(NAGIOS_CODE_UNKNOWN);
}

$testScriptCmd = $options['script'];

$outputLines = null;
$returnedValue = null;
$output = exec($systemConfig['PHP_BIN'] . ' ' . $testScriptCmd, $outputLines, $returnedValue);
if($returnedValue !== 0)
{
	echo $output;
	exit(NAGIOS_CODE_UNKNOWN);
}

$xml = implode("\n", $outputLines);
$monitorResult = VidiunMonitorResult::fromXml($xml);

if($monitorResult->errors)
{
	$exitCode = NAGIOS_CODE_OK;
	$descriptions = array();
	
	foreach($monitorResult->errors as $error)
	{
		$descriptions[] = $error->description;
		
		switch($error->level)
		{
		    case VidiunMonitorError::EMERG:
		    case VidiunMonitorError::ALERT:
		    case VidiunMonitorError::CRIT:
		    	$exitCode = NAGIOS_CODE_CRITICAL;
		    	break;
		    	
		    case VidiunMonitorError::WARN:
		    	$exitCode = max($exitCode, NAGIOS_CODE_WARNING);
		    	break;
		    	
		    case VidiunMonitorError::NOTICE:
		    case VidiunMonitorError::INFO:
		    case VidiunMonitorError::DEBUG:
		    	break;
		    	
		    case VidiunMonitorError::ERR:
		    default:
		    	$exitCode = max($exitCode, NAGIOS_CODE_UNKNOWN);
		    	break;
		}
	}
	
	if($exitCode != NAGIOS_CODE_OK)
	{
		echo implode('; ', $descriptions);
		exit($exitCode);
	}
}

if(!is_null($errorThresholdMax) && $monitorResult->value > $errorThresholdMax)
{
	echo "CRITICAL: Threshold crossed - $monitorResult->description";
	exit(NAGIOS_CODE_CRITICAL);
}

if(!is_null($warningThresholdMax) && $monitorResult->value > $warningThresholdMax)
{
    echo "WARNING: Threshold crossed - $monitorResult->description";
	exit(NAGIOS_CODE_WARNING);
}

if(!is_null($errorThresholdMin) && $monitorResult->value < $errorThresholdMin)
{
	echo "CRITICAL: Threshold crossed - $monitorResult->description";
	exit(NAGIOS_CODE_CRITICAL);
}

if(!is_null($warningThresholdMin) && $monitorResult->value < $warningThresholdMin)
{
        echo "WARNING: Threshold crossed - $monitorResult->description";
	exit(NAGIOS_CODE_WARNING);
}

echo $monitorResult->description;
exit(NAGIOS_CODE_OK);


