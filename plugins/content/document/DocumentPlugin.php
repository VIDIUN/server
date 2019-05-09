<?php
/**
 * @package plugins.document
 */
class DocumentPlugin extends VidiunPlugin implements IVidiunPlugin, IVidiunServices, IVidiunObjectLoader, IVidiunEventConsumers, IVidiunEnumerator, IVidiunTypeExtender
{
	const PLUGIN_NAME = 'document';
	const DOCUMENT_OBJECT_CREATED_HANDLER = 'DocumentCreatedHandler';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunTypeExtender::getExtendedTypes()
	 */
	public static function getExtendedTypes($baseClass, $enumValue)
	{
		$supportedBaseClasses = array(
			assetPeer::OM_CLASS,
			assetParamsPeer::OM_CLASS,
			assetParamsOutputPeer::OM_CLASS,
		);
		
		if(in_array($baseClass, $supportedBaseClasses) && $enumValue == assetType::FLAVOR)
		{
			return array(
				DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF),
				DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::SWF),
				DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT),
				DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE),
			);
		}
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		
		// ENTRY
		if($baseClass == 'entry' && $enumValue == entryType::DOCUMENT)
		{
			return new DocumentEntry();
		}
		
		if($baseClass == 'VidiunBaseEntry' && $enumValue == entryType::DOCUMENT)
		{
			return new VidiunDocumentEntry();
		}
		
		
		// VIDIUN FLAVOR PARAMS
		
		if($baseClass == 'VidiunFlavorParams')
		{
			switch($enumValue)
			{
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF):
					return new VidiunPdfFlavorParams();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::SWF):
					return new VidiunSwfFlavorParams();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT):
					return new VidiunDocumentFlavorParams();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE);
					return new VidiunImageFlavorParams();
				
				default:
					return null;	
			}
		}
	
		if($baseClass == 'VidiunFlavorParamsOutput')
		{
			switch($enumValue)
			{
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF):
					return new VidiunPdfFlavorParamsOutput();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::SWF):
					return new VidiunSwfFlavorParamsOutput();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT):
					return new VidiunDocumentFlavorParamsOutput();
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE);
					return new VidiunImageFlavorParamsOutput();
				
				default:
					return null;	
			}
		}
		
		
		// OPERATION ENGINES
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::PDF_CREATOR)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			
			return new VOperationEnginePdfCreator($constructorArgs['params']->pdfCreatorCmd, $constructorArgs['outFilePath']);
		}

		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::PDF2SWF)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			
			return new VOperationEnginePdf2Swf($constructorArgs['params']->pdf2SwfCmd, $constructorArgs['outFilePath']);
		}
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::IMAGEMAGICK)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			
			return new VOperationEngineImageMagick($constructorArgs['params']->imageMagickCmd, $constructorArgs['outFilePath']);
		}
		
		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::PPT2IMG)
		{
			if(!isset($constructorArgs['params']) || !isset($constructorArgs['outFilePath']))
				return null;
			return new VOperationEnginePpt2Image($constructorArgs['params']->ppt2ImgCmd, $constructorArgs['outFilePath']);
		}

		if($baseClass == 'VOperationEngine' && $enumValue == VidiunConversionEngineType::THUMB_ASSETS)
		{
			return new VOperationEngineThumbAssetsGenerator(null, null);
		}

		
		// VDL ENGINES
		
		if($baseClass == 'VDLOperatorBase' && $enumValue == conversionEngineType::PDF_CREATOR)
		{
			return new VDLTranscoderPdfCreator($enumValue);
		}
				
		if($baseClass == 'VDLOperatorBase' && $enumValue == conversionEngineType::PDF2SWF)
		{
			return new VDLTranscoderPdf2Swf($enumValue);
		}
		
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(DocumentConversionEngineType::IMAGEMAGICK_ENGINE))
		{
			return new VDLTranscoderImageMagick($enumValue);
		}
		
		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(DocumentConversionEngineType::PPT2IMG_ENGINE))
		{
			return new VDLTranscoderPpt2Img($enumValue);
		}

		if($baseClass == 'VDLOperatorBase' && $enumValue == self::getApiValue(DocumentConversionEngineType::THUMB_ASSETS_ENGINE))
		{
			return new VDLTranscoderThumbAssetsGenerator($enumValue);
		}
		
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// DOCUMENT ENTRY
		if($baseClass == 'entry' && $enumValue == entryType::DOCUMENT)
		{
			return 'DocumentEntry';
		}
		
		// FLAVOR PARAMS
		if($baseClass == 'assetParams')
		{
			switch($enumValue)
			{
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF):
					return 'PdfFlavorParams';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::SWF):
					return 'SwfFlavorParams';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT):
					return 'flavorParams';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE);
					return 'ImageFlavorParams';
				
				default:
					return null;	
			}
		}
	
		if($baseClass == 'assetParamsOutput')
		{
			switch($enumValue)
			{
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::PDF):
					return 'PdfFlavorParamsOutput';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::SWF):
					return 'SwfFlavorParamsOutput';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::DOCUMENT):
					return 'flavorParamsOutput';
					
				case DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE);
					return 'ImageFlavorParamsOutput';
				
				default:
					return null;	
			}
		}
		
		return null;
	}

	/**
	 * @return array<string,string> in the form array[serviceName] = serviceClass
	 */
	public static function getServicesMap()
	{
		$map = array(
			'documents' => 'DocumentsService',
		);
		return $map;
	}

	/**
	 * @return array
	 */
	public static function getEventConsumers()
	{
		return array(
			self::DOCUMENT_OBJECT_CREATED_HANDLER,
		);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getAssetTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('assetType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DocumentAssetType','DocumentConversionEngineType');
	
		if($baseEnumName == 'assetType')
			return array('DocumentAssetType');
			
		if($baseEnumName == 'conversionEngineType')
			return array('DocumentConversionEngineType');
			
		return array();
	}
}
