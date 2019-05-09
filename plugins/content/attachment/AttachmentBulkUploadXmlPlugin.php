<?php
/**
 * Enable entry attachment asset ingestion from XML bulk upload
 * @package plugins.attachment
 */
class AttachmentBulkUploadXmlPlugin extends VidiunPlugin implements IVidiunPending, IVidiunSchemaContributor, IVidiunBulkUploadXmlHandler, IVidiunConfigurator
{
	const PLUGIN_NAME = 'attachmentBulkUploadXml';
	const BULK_UPLOAD_XML_PLUGIN_NAME = 'bulkUploadXml';
	
	/**
	 * @var array
	 */
	protected $currentAttachmentAssets = null;
	
	/**
	 * @var BulkUploadEngineXml
	 */
	private $xmlBulkUploadEngine = null;
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$bulkUploadXmlDependency = new VidiunDependency(self::BULK_UPLOAD_XML_PLUGIN_NAME);
		$attachmentDependency = new VidiunDependency(AttachmentPlugin::getPluginName());
		
		return array($bulkUploadXmlDependency, $attachmentDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{		
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if(
			$coreType != BulkUploadXmlPlugin::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_XML)
			&&
			$coreType != BulkUploadXmlPlugin::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_RESULT_XML)
		)
			return null;
	
		$xsd = '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_attachments">
		<xs:sequence>
			<xs:element name="action" minOccurs="0" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>
						The action to apply:<br/>
						Update - Update existing attachment<br/>
					</xs:documentation>
				</xs:annotation>
				<xs:simpleType>
					<xs:restriction base="xs:string">
						<xs:enumeration value="update" />
					</xs:restriction>
				</xs:simpleType>
			</xs:element>
			<xs:element ref="attachment" maxOccurs="unbounded" minOccurs="0">
				<xs:annotation>
					<xs:documentation>All attachment elements</xs:documentation>
				</xs:annotation>
			</xs:element>
		</xs:sequence>
	</xs:complexType>
	
	<xs:complexType name="T_attachment">
		<xs:sequence>
			<xs:element name="tags" minOccurs="0" maxOccurs="1" type="T_tags">
				<xs:annotation>
					<xs:documentation>Specifies specific tags you want to set for the flavor asset</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:choice minOccurs="1" maxOccurs="1">
				<xs:element ref="serverFileContentResource" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Specifies that content ingestion location is on a Vidiun hosted server</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="urlContentResource" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Specifies that content file location is a URL (http,ftp)</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="remoteStorageContentResource" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Specifies that content file location is a path within a Vidiun defined remote storage</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="remoteStorageContentResources" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Set of content files within several Vidiun defined remote storages</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="entryContentResource" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Specifies that content is a Vidiun entry</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="assetContentResource" minOccurs="1" maxOccurs="1">
					<xs:annotation>
						<xs:documentation>Specifies that content is a Vidiun asset</xs:documentation>
					</xs:annotation>
				</xs:element>
				<xs:element ref="contentResource-extension" minOccurs="1" maxOccurs="1" />
			</xs:choice>
			<xs:element name="filename" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Attachment asset file name</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="title" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Attachment asset title</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="description" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Attachment asset free text description</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element ref="attachment-extension" minOccurs="0" maxOccurs="unbounded" />
		</xs:sequence>
		
		<xs:attribute name="attachmentAssetId" type="xs:string" use="optional">
			<xs:annotation>
				<xs:documentation>The asset id to be updated with this resource used only for update</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="format" type="VidiunAttachmentType" use="optional">
			<xs:annotation>
				<xs:documentation>Attachment asset file format</xs:documentation>
			</xs:annotation>
		</xs:attribute>
						
	</xs:complexType>
	
	<xs:element name="attachment-extension" />
		<xs:element name="attachments" type="T_attachments" substitutionGroup="item-extension">
		<xs:annotation>
			<xs:documentation>All attachments elements</xs:documentation>
			<xs:appinfo>
				<example>
					<attachments>
						<action>update</action>
						<attachment>...</attachment>
						<attachment>...</attachment>
						<attachment>...</attachment>
					</attachments>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
	<xs:element name="attachment" type="T_attachment" substitutionGroup="item-extension">
		<xs:annotation>
			<xs:documentation>Attachment asset element</xs:documentation>
			<xs:appinfo>
				<example>
					<attachment format="1" attachmentAssetId="{asset id}">
						<tags>
							<tag>example</tag>
							<tag>my_tag</tag>
						</tags>
						<urlContentResource url="http://my.domain/path/file.txt"/>
						<filename>my_file_name.txt</filename>
						<title>my attachment asset title</title>
						<description>my attachment asset free text description</description>
					</attachment>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
		';
		
		return $xsd;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::configureBulkUploadXmlHandler()
	 */
	public function configureBulkUploadXmlHandler(BulkUploadEngineXml $xmlBulkUploadEngine)
	{
		$this->xmlBulkUploadEngine = $xmlBulkUploadEngine;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemAdded()
	*/
	public function handleItemAdded(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!($object instanceof VidiunBaseEntry))
			return;
		
		if(!isset($item->attachments))
			return;
		
		if(empty($item->attachments->attachment))
			return;
		
		VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
				
		$pluginsErrorResults = array();
		foreach($item->attachments->attachment as $attachment)
		{
			try {
				$this->handleAttachmentAsset($object->id, $attachment);
			}
			catch (Exception $e)
			{
				VidiunLog::err($this->getContainerName() . ' failed: ' . $e->getMessage());
				$pluginsErrorResults[] = $e->getMessage();
			}
		}
		
		if(count($pluginsErrorResults))
			throw new Exception(implode(', ', $pluginsErrorResults));
		
		VBatchBase::unimpersonate();		
	}

	private function handleAttachmentAsset($entryId, SimpleXMLElement $attachment)
	{
		$attachmentPlugin = VidiunAttachmentClientPlugin::get(VBatchBase::$vClient);
		
		$attachmentAsset = new VidiunAttachmentAsset();
		$attachmentAsset->tags = $this->xmlBulkUploadEngine->implodeChildElements($attachment->tags);
		
		if(isset($attachment->fileExt))
			$attachmentAsset->fileExt = $attachment->fileExt;
		
		if(isset($attachment->description))
			$attachmentAsset->partnerDescription = $attachment->description;
		
		if(isset($attachment->filename))
			$attachmentAsset->filename = $attachment->filename;

		if(isset($attachment->title))
			$attachmentAsset->title = $attachment->title;
			
		if(isset($attachment['format']))
			$attachmentAsset->format = $attachment['format'];
		 
		$attachmentAssetId = null;
		if(isset($attachment['attachmentAssetId']))
			$attachmentAssetId = $attachment['attachmentAssetId'];
		
		if($attachmentAssetId)
		{
			$attachmentPlugin->attachmentAsset->update($attachmentAssetId, $attachmentAsset);
		}else
		{
			$attachmentAsset = $attachmentPlugin->attachmentAsset->add($entryId, $attachmentAsset);
			$attachmentAssetId = $attachmentAsset->id;
		}
		
		$attachmentAssetResource = $this->xmlBulkUploadEngine->getResource($attachment, 0);
		if($attachmentAssetResource)
			$attachmentPlugin->attachmentAsset->setContent($attachmentAssetId, $attachmentAssetResource);
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemUpdated()
	*/
	public function handleItemUpdated(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!$item->attachments)
			return;
			
		if(empty($item->attachments->attachment))
			return;
		
		$action = VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE];
		if(isset($item->attachments->action))
			$action = strtolower($item->attachments->action);
			
		switch ($action)
		{
			case VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE]:
				$this->handleItemAdded($object, $item);
				break;
			default:
				throw new VidiunBatchException("attachments->action: $action is not supported", VidiunBatchJobAppErrors::BULK_ACTION_NOT_SUPPORTED);
		}
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemDeleted()
	*/
	public function handleItemDeleted(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		// No handling required
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getContainerName()
	*/
	public function getContainerName()
	{
		return 'attachments';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getConfig()
	*/
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/generator.ini');

		return null;
	}
}
