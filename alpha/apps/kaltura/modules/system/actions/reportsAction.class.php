<?php
/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
require_once ( __DIR__ . "/vidiunSystemAction.class.php" );

/**
 * @package    Core
 * @subpackage system
 * @deprecated
 */
class reportsAction extends vidiunSystemAction
{
	
	/**
	 * Gives a system applicative snapsot
	 */
	public function execute()
	{
		$this->forceSystemAuthentication();
		
		$chart = $this->getRequestParameter( "chart" , null);
		if ( ! $chart )
			return ; // draw the default page which is the PHP/SWF chart viewer
		
		if ( $chart == "hour" )
		{
			$period_in_seconds = 3600;
			$date_format = "%d/%m\n%H:%M:%S" ;
			$limit = 24;
		}
		else // assume day
		{
			$period_in_seconds = 86400;
			$date_format = "%A\n%d/%m" ;
			$limit = 31;
		}
			
		
		$vuser_stats = $this->getVusersInPeriod( $period_in_seconds , "" , $limit );
		$vshow_stats = $this->getVshowsInPeriod( $period_in_seconds , "" , $limit );
		$entry_stats = $this->getEntriesInPeriod ( $period_in_seconds , "" , $limit );

//		print_r ( $vuser_stats ); 
		
//		echo "<br><br>";
		
		$chart = array();
		
		$chart_x = array(""); // first one empty !!
		$vuser_values = array ("Users");
		$vshow_values = array ("Shows");
		$entry_values = array ("Entries");
		
		$count = count ( $vuser_stats );
		for ( $i=$count - 1  ; $i >= 0 ; $i-- )
		{
			$vuser_row = $vuser_stats[$i];
			$vshow_row = @$vshow_stats[$i];
			$entry_row = @$entry_stats[$i];
			
			//$chart_x[] = strftime( "%d/%m" , ( $vuser_row[2] * $period_in_seconds  ) ) ; // period 
			$chart_x[] = strftime( $date_format , ( $vuser_row[2] * $period_in_seconds ) ) ; // period
			//$chart_x[] = $vuser_row[1];
			$vuser_values[] = $vuser_row[3];
			$vshow_values[] = $vshow_row ? $vshow_row[3] : 0;
			$entry_values[] = $entry_row ? $entry_row[3] : 0;
		}			
/*		
		foreach ( $stats as $row )
		{
			$chart_x[] = strftime( "%d/%m" , ( $row[2] * $period_in_seconds ) ) ; // period 
			$vuser_values[] = $row[3];
			$vshow_values[] = $row[3] /3;
			$entry_values[] = $row[3] /2;
		}
	*/	
		$chart [ 'axis_category' ] = array (   'skip'         =>  0,
                                       'font'         =>  "Arial", 
                                       'bold'         =>  true, 
                                       'size'         =>  9, 
                                       'color'        =>  "88FF00", 
                                       'alpha'        =>  75,
                                       'orientation'  =>  "diagonal_up"
                                   ); 
		$chart [ 'axis_value' ] = array (   'min'           =>  10,  
                                    'font'          =>  "Arial", 
                                    'bold'          =>  true, 
                                    'size'          =>  10, 
                                    'color'         =>  "88FF00", 
                                   );
                                   
	$chart [ 'chart_value' ] = array (  'prefix'         =>  "", 
                                    'position'       =>  "outside",
                                    'hide_zero'      =>  false, 
                                    'as_percentage'  =>  false, 
                                    'font'           =>  "Arial", 
                                    'bold'           =>  true, 
                                    'size'           =>  10, 
                                    'color'          =>  "FF0000", 
                                    'alpha'          =>  90
                                  ); 
                                   
                                   
                                 
		$chart [ 'chart_data' ] = array ( $chart_x ,
                                  		  $vuser_values  ,
                                  		  $vshow_values	, 
                                  		  $entry_values );
		
		//$chart [ 'chart_type' ] = "bar";
		
		return $this->renderText( charts::SendChartData ( $chart ) );
	}
	
	
	
	// TODO - this code has many duplicates - generalize !!
	
	
	// select  id, created_at,floor(UNIX_TIMESTAMP(created_at)/600) as '10-minutes',count(1) as 'vuser count' from vuser 
	// where partner_id!=5 group by floor(UNIX_TIMESTAMP(created_at)/600) order by id desc limit 30;
	private function getVusersInPeriod ( $period_in_seconds = 3600, $period_text = "1 hour" , $limit = 30 , $last_id = null )
	{
		return self::getObjectsInPeriod( new vuserPeer() , $period_in_seconds , $period_text , $limit  , $last_id );
	}
	
	// select  id, created_at,floor(UNIX_TIMESTAMP(created_at)/600) as '10-minutes',count(1) as 'vuser count' from vuser 
	// where partner_id!=5 group by floor(UNIX_TIMESTAMP(created_at)/600) order by id desc limit 30;
	private function getVshowsInPeriod ( $period_in_seconds = 3600, $period_text = "1 hour" , $limit = 30 , $last_id = null )
	{
		return self::getObjectsInPeriod( new vshowPeer() , $period_in_seconds , $period_text , $limit  , $last_id );
	}	
	
	// select  id, created_at,floor(UNIX_TIMESTAMP(created_at)/600) as '10-minutes',count(1) as 'vuser count' from vuser 
	// where partner_id!=5 group by floor(UNIX_TIMESTAMP(created_at)/600) order by id desc limit 30;
	private function getEntriesInPeriod ( $period_in_seconds = 3600, $period_text = "1 hour" , $limit = 30 , $last_id = null )
	{
		return self::getObjectsInPeriod( new entryPeer() , $period_in_seconds , $period_text , $limit  , $last_id );
	}	

	private static function getObjectsInPeriod ( /*BasePeer*/ $object_peer , $period_in_seconds = 3600, $period_text = "1 hour" , $limit = 30 , $last_id = null )
	{
		if ( $period_in_seconds < 1) $period_in_seconds = 1;
		
		$period_clause = "ceil(UNIX_TIMESTAMP(created_at)/$period_in_seconds)";
		$c = new Criteria();
		$c->addSelectColumn( self::get ( $object_peer, "id" ) ) ;
		$c->addSelectColumn( self::get ( $object_peer, "created_at" ) );
		$c->addAsColumn( "'$period_text'" , $period_clause );
		$c->addAsColumn( "'obj count'" , "count(1)" );
		$c->add ( self::get ( $object_peer, "partner_id" ) , 5  ,Criteria::NOT_EQUAL );
		$c->addGroupByColumn( $period_clause );
		$c->addDescendingOrderByColumn( self::get ( $object_peer, "created_at" ) );  // TODO - change to ID 
		$c->setLimit( $limit );
		$rs = $object_peer->doSelectStmt( $c );

		$stats= Array();

		$res = $rs->fetchAll();
		foreach($res as $record) 
		{
			$row = array ( $record[0] , $record[1] , $record[2] , $record[3]);
			$stats[] = $row;
		}
		
//		// old code from doSelectRs
//		while($rs->next())
//		{
//			$row = array ( $rs->getString(1) , $rs->getTimestamp(2) , $rs->getInt(3) , $rs->getInt(4));
//			$stats[] = $row;
//		}

		$rs->close();		
		return $stats;	
	}	
	
	private static function get ( /*BasePeer*/ $peer , $field_name )
	{
		return $peer->translateFieldName ( $field_name , BasePeer::TYPE_FIELDNAME , BasePeer::TYPE_COLNAME  );
	}
}
?>