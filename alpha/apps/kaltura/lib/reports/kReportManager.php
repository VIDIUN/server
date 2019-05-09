<?php
class vReportManager
{
	/**
	 * @var Report
	 */
	protected $_report;
	
	/**
	 * @var PDO
	 */
	protected $_pdo;
	
	public function __construct(Report $report)
	{
		$this->_report = $report;
	}
	
	public function execute($params)
	{
		$this->initPdo();
		$query = $this->_report->getQuery();
		VidiunLog::debug('Prepering statement: ' . $query);
		$pdoStatement = $this->_pdo->prepare($query);
		VidiunLog::debug('With params: ' . print_r($params, true));
		$pdoStatement->execute($params);
		$rows = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
		$columns = array();
		for($i = 0; $i < $pdoStatement->columnCount(); $i++)
		{
			$columnMeta = $pdoStatement->getColumnMeta($i);
			$columns[] = $columnMeta['name'];
		}
		
		return array($columns, $rows);
	}
	
	protected function initPdo()
	{
		if (is_null($this->_pdo))
		{
			$dbConfig = vConf::get("reports_db_config");
			$host = $dbConfig["host"];
			$port = $dbConfig["port"];
			$user = $dbConfig["user"];
			$password = $dbConfig["password"];
			$dbName = $dbConfig["db_name"];
			$db = vConf::getDB();
			$charset = isset($dbConfig["charset"]) ? $dbConfig["charset"] : null;
			
			$pdoString = "mysql:host={$host};port={$port};dbname={$dbName};";
			if($charset)
				$pdoString .= "charset={$charset};";
			
			$this->_pdo = new PDO($pdoString,$user, $password);
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	}
}