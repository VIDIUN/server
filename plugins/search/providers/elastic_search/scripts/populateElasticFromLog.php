<?php
set_time_limit(0);
chdir(dirname(__FILE__));

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../../../../'));
require_once(ROOT_DIR . '/infra/VAutoloader.php');
require_once(ROOT_DIR . '/alpha/config/vConf.php');

// ------------------------------------------------------
class OldLogRecordsFilter {
    private $logId;

    function __construct($logId) {
        $this->logId = $logId;
    }

    function filter($i) {
        return $i > $this->logId;
    }
}

VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "vendor", "propel", "*"));
VAutoloader::addClassPath(VAutoloader::buildPath(VIDIUN_ROOT_PATH, "plugins", "*"));
VAutoloader::setClassMapFilePath(vConf::get("cache_root_path") . '/elastic/' . basename(__FILE__) . '.cache');
VAutoloader::register();

$skipExecutedUpdates = false;
error_reporting(E_ALL);
VidiunLog::setLogger(new VidiunStdoutLogger());

$hostname = (isset($_SERVER["HOSTNAME"]) ? $_SERVER["HOSTNAME"] : gethostname());
$configFile = ROOT_DIR . "/configurations/elastic/populate/$hostname.ini";
if(!file_exists($configFile))
{
    VidiunLog::err("Configuration file [$configFile] not found.");
    exit(-1);
}
$config = parse_ini_file($configFile);
$elasticCluster = $config['elasticCluster'];
$elasticServer = $config['elasticServer'];
$elasticPort = (isset($config['elasticPort']) ? $config['elasticPort'] : 9200);
$processScriptUpdates = (isset($config['processScriptUpdates']) ? $config['processScriptUpdates'] : false);
$systemSettings = vConf::getMap('system');
if(!$systemSettings || !$systemSettings['LOG_DIR'])
{
    VidiunLog::err("LOG_DIR not found in system configuration.");
    exit(-1);
}
$pid = $systemSettings['LOG_DIR'] . '/populate_elastic.pid';
if(file_exists($pid))
{
    VidiunLog::err("Scheduler already running - pid[" . file_get_contents($pid) . "]");
    exit(1);
}
file_put_contents($pid, getmypid());

$dbConf = vConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();

$limit = 1000;
$gap = 500;

$sphinxLogReadConn = myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_SPHINX_LOG_READ);

$serverLastLogs = SphinxLogServerPeer::retrieveByServer($elasticCluster, $sphinxLogReadConn);


$lastLogs = array();
$handledRecords = array();

foreach($serverLastLogs as $serverLastLog) {
    $lastLogs[$serverLastLog->getDc()] = $serverLastLog;
    $handledRecords[$serverLastLog->getDc()] = array();
}

$elasticClient = new elasticClient($elasticServer, $elasticPort); //take the server and port from config - $elasticServer , $elasticPort

while(true)
{

    if(!elasticSearchUtils::isMaster($elasticClient, $hostname))
    {
        VidiunLog::log('elastic server ['.$hostname.'] is not the master , sleeping for 30 seconds');
        sleep(30);
        //update the last log ids
        $serverLastLogs = SphinxLogServerPeer::retrieveByServer($elasticCluster, $sphinxLogReadConn);
        foreach($serverLastLogs as $serverLastLog)
        {
            $lastLogs[$serverLastLog->getDc()] = $serverLastLog;
            $handledRecords[$serverLastLog->getDc()] = array();
        }
        SphinxLogServerPeer::clearInstancePool();
        continue;
    }

    $elasticLogs = SphinxLogPeer::retrieveByLastId($lastLogs, $gap, $limit, $handledRecords, $sphinxLogReadConn, SphinxLogType::ELASTIC);

    while(!count($elasticLogs))
    {
        sleep(1);
        $elasticLogs = SphinxLogPeer::retrieveByLastId($lastLogs, $gap, $limit, $handledRecords, $sphinxLogReadConn, SphinxLogType::ELASTIC);
    }

    $ping = $elasticClient->ping();

    if(!$ping)
    {
        VidiunLog::err('cannot connect to elastic cluster with client['.print_r($elasticClient, true).']');
        sleep(5);
        continue;
    }

    foreach($elasticLogs as $elasticLog)
    {
        /* @var $elasticLog SphinxLog */
        $dc = $elasticLog->getDc();
        $executedServerId = $elasticLog->getExecutedServerId();
        $elasticLogId = $elasticLog->getId();

        $serverLastLog = null;

        if(isset($lastLogs[$dc])) {
            $serverLastLog = $lastLogs[$dc];
        } else {
            $serverLastLog = new SphinxLogServer();
            $serverLastLog->setServer($elasticCluster);
            $serverLastLog->setDc($dc);

            $lastLogs[$dc] = $serverLastLog;
        }

        $handledRecords[$dc][] = $elasticLogId;
        VidiunLog::log("Elastic log id $elasticLogId dc [$dc] executed server id [$executedServerId] Memory: [" . memory_get_usage() . "]");

        try
        {
            if ($skipExecutedUpdates && $executedServerId == $serverLastLog->getId())
            {
                VidiunLog::log ("Elastic server is initiated and the command already ran synchronously on this machine. Skipping");
            }
            else
            {
                //we save the elastic command as serialized object in the sql field
                $command = $elasticLog->getSql();
                $command = unserialize($command);
                $index = $command['index'];
                $action = $command['action'];

                if ($action && ($processScriptUpdates || !($index == ElasticIndexMap::ELASTIC_ENTRY_INDEX && $action == ElasticMethodType::UPDATE)))
                {
                    $response = $elasticClient->$action($command);
                }

            }

            // If the record is an historical record, don't take back the last log id
            if($serverLastLog->getLastLogId() < $elasticLogId) {
                $serverLastLog->setLastLogId($elasticLogId);

                // Clear $handledRecords from before last - gap.
                foreach($serverLastLogs as $serverLastLog) {
                    $dc = $serverLastLog->getDc();
                    $threshold = $serverLastLog->getLastLogId() - $gap;
                    $handledRecords[$dc] = array_filter($handledRecords[$dc], array(new OldLogRecordsFilter($threshold), 'filter'));
                }
            }
        }
        catch(Exception $e)
        {
            VidiunLog::err($e->getMessage());
        }
    }

    foreach ($lastLogs as $serverLastLog)
    {
        $serverLastLog->save(myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_SPHINX_LOG));
    }
    
    SphinxLogPeer::clearInstancePool();
    vMemoryManager::clearMemory();
}

VidiunLog::log('Done');
