<?php
/**
 * @package api
 * @subpackage ps2
 */
class getlastversionsinfoAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "getLastVersionsInfo",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => "")
						)
					),
				"out" => array (
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType ()	{		return self::REQUIED_TICKET_REGULAR;	}

	protected function needVuserFromPuser ( )	
	{	
			return self::VUSER_DATA_NO_VUSER;
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshowId = $this->getP("vshow_id");
		$numberOfVersions = $this->getP("number_of_versions", 5);
		
		// must be int and not more than 50
		$numberOfVersions = (int)$numberOfVersions;
		$numberOfVersions = min($numberOfVersions, 50);

		$vshow = vshowPeer::retrieveByPK( $vshowId );
		
		if (!$vshow)
		{
			$this->addError(APIErrors::VSHOW_DOES_NOT_EXISTS);
			return;
		}
		
		$showEntry = $vshow->getShowEntry();
		if (!$showEntry)
		{
			$this->addError(APIErrors::ROUGHCUT_NOT_FOUND);
			return;	
		}
		
		$sync_key = $showEntry->getSyncKey ( entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA );
		$showEntryDataPath = vFileSyncUtils::getLocalFilePathForKey($sync_key);
		
		$versionsInfoFilePath 	= $showEntryDataPath.'.info';
		
		$lastVersionDoc = new VDOMDocument();
		$lastVersionDoc->loadXML(vFileSyncUtils::file_get_contents( $sync_key , true , false ));
		$lastVersion = myContentStorage::getVersion($showEntryDataPath);

		// check if we need to refresh the data in the info file
		$refreshInfoFile = true;
		if (file_exists($versionsInfoFilePath))
		{
			$versionsInfoDoc = new VDOMDocument();
			$versionsInfoDoc->load($versionsInfoFilePath);
			$lastVersionInInfoFile = vXml::getLastElementAsText($versionsInfoDoc, "ShowVersion");

			if ($lastVersionInInfoFile && $lastVersion == $lastVersionInInfoFile)
				$refreshInfoFile = false;
			else
				$refreshInfoFile = true;
		}
		else
		{
			$refreshInfoFile = true;
		}

		// refresh or create the data in the info file
		if ($refreshInfoFile)
		{
			$versionsInfoDoc = new VDOMDocument();
			$xmlElement = $versionsInfoDoc->createElement("xml");
			
			// start from the first edited version (100001) and don't use 100000
			for ($i = myContentStorage::MIN_OBFUSCATOR_VALUE + 1; $i <= $lastVersion; $i++)
			{
				$version_sync_key = $showEntry->getSyncKey ( entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA , $i );
				
				if (vFileSyncUtils::file_exists($version_sync_key,false))
				{
					$xmlContent = vFileSyncUtils::file_get_contents($version_sync_key);
//echo "[" . htmlspecialchars( $xmlContent ) . "]<br>";					
					$xmlDoc = new VDOMDocument();
					$xmlDoc->loadXML($xmlContent);
					$elementToCopy = vXml::getFirstElement($xmlDoc, "MetaData");
//echo "[$i]";				
					$elementCloned = $elementToCopy->cloneNode(true);
					
					$elementImported = $versionsInfoDoc->importNode($elementCloned, true);
					
					$xmlElement->appendChild($elementImported);
				}
			}
			$versionsInfoDoc->appendChild($xmlElement);
			vFile::setFileContent($versionsInfoFilePath, $versionsInfoDoc->saveXML()); // FileSync OK - created a temp file on DC's disk
		}
		
		$metadataNodes = $versionsInfoDoc->getElementsByTagName("MetaData");
		$count = 0;
		$versionsInfo = array();
		for($i = $metadataNodes->length - 1; $i >= 0; $i--)
		{
			$metadataNode = $metadataNodes->item($i);

			$node = vXml::getFirstElement($metadataNode, "ShowVersion");
			$showVersion = $node ? $node->nodeValue : "";

			$node = vXml::getFirstElement($metadataNode, "PuserId");
			$puserId = $node ? $node->nodeValue : "";
			
			$node = vXml::getFirstElement($metadataNode, "ScreenName");
			$screenName = $node ? $node->nodeValue : "";
			
			$node = vXml::getFirstElement($metadataNode, "UpdatedAt");
			$updatedAt = $node ? $node->nodeValue : "";

			$versionsInfo[] = array(
						"version" => $showVersion,
						"puserId" => $puserId,
						"screenName" => $screenName,
						"updatedAt" => $updatedAt,
					);
					
			$count++;

			if ($count >= $numberOfVersions)
				break;
		}
		

		$this->addMsg ( "show_versions" , $versionsInfo );
	}
}
?>