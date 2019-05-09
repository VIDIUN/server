<?php
/**
 * Expose the schema definitions for syndication MRSS, bulk upload XML and other schema types. 
 * 
 * @service schema
 * @package api
 * @subpackage services
 */
class SchemaService extends VidiunBaseService 
{
	const CORE_SCHEMA_NAME = 'core';
	const ENUM_SCHEMA_NAME = 'enum';
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::partnerRequired()
	 */
	protected function partnerRequired($actionName)
	{
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseService::isPermitted()
	 */
	protected function isPermitted(&$allowPrivatePartnerData)
	{
		return true;
	}
	
	/**
	 * Serves the requested XSD according to the type and name. 
	 * 
	 * @action serve
	 * @param VidiunSchemaType $type  
	 * @return file 
	 */
	function serveAction($type)
	{
		$cachedXsdFilePath = self::getCachedXsdFilePath($type);
		if(file_exists($cachedXsdFilePath))
			return $this->dumpFile(realpath($cachedXsdFilePath), 'application/xml');
		
		$resultXsd = self::buildSchemaByType($type);
		vFile::safeFilePutContents($cachedXsdFilePath, $resultXsd, 0644);
		return new vRendererString($resultXsd, 'application/xml');
	}
	
	public static function getSchemaPath($type)
	{
		$cachedXsdFilePath = self::getCachedXsdFilePath($type);
		if(file_exists($cachedXsdFilePath))
			return realpath($cachedXsdFilePath);
		
		$resultXsd = self::buildSchemaByType($type);
		vFile::safeFilePutContents($cachedXsdFilePath, $resultXsd, 0644);
		return realpath($cachedXsdFilePath);
	}
	
	private static function getCachedXsdFilePath($type)
	{
		$cachedXsdFilePath = vConf::get("cache_root_path") . "/$type.xsd";
		return $cachedXsdFilePath;
	}
	
	private static function buildSchemaByType($type)
	{
		$elementsXSD = '';
		
		$baseXsdElement = new SimpleXMLElement('<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"/>');
		if($type == SchemaType::SYNDICATION)
		{
			$baseXsdElement = new SimpleXMLElement(file_get_contents(vConf::get("syndication_core_xsd_path")));
		}
		else
		{
			$plugin = vPluginableEnumsManager::getPlugin($type);
			if($plugin instanceof IVidiunSchemaDefiner)
			{
				$baseXsdElement = $plugin->getPluginSchema($type);
			}
		}
		
		if(!($baseXsdElement instanceof SimpleXMLElement))
			$baseXsdElement = new SimpleXMLElement('<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"/>');
		
		$version = '1.0';
		if($baseXsdElement['version'])
			$version = $baseXsdElement['version'];
		
		$resultXsd = "<xs:schema xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" version=\"$version\">";
		
		foreach($baseXsdElement->children('http://www.w3.org/2001/XMLSchema') as $element)
		{
			/* @var $element SimpleXMLElement */
			$xsd = $element->asXML();
			$elementsXSD .= $xsd;
			
			$resultXsd .= '
	' . $xsd;
		}
		
		$schemaContributors = VidiunPluginManager::getPluginInstances('IVidiunSchemaContributor');
		foreach($schemaContributors as $key => $schemaContributor)
		{
			/* @var $schemaContributor IVidiunSchemaContributor */
			$elements = $schemaContributor->contributeToSchema($type);
			if($elements)
			{
				$elementsXSD .= $elements;
				$resultXsd .= $elements;
			}
		}
		
		$resultXsd .= '
	<!-- Vidiun enum types -->
	';
		
		$enumClasses = array();
		$matches = null;
		if(preg_match_all('/type="(Vidiun[^"]+)"/', $elementsXSD, $matches))
			$enumClasses = $matches[1];
		
		$enumTypes = array();
		foreach($enumClasses as $class)
		{
			$classTypeReflector = VidiunTypeReflectorCacher::get($class);
			if($classTypeReflector)
				self::loadClassRecursively($classTypeReflector, $enumTypes);
		}
		
		foreach($enumTypes as $class => $classTypeReflector)
		{
			if(!is_subclass_of($class, 'VidiunEnum') && !is_subclass_of($class, 'VidiunStringEnum')) // class must be enum
				continue;
			
			$xsdType = 'int';
			if($classTypeReflector->isStringEnum())
				$xsdType = 'string';
			
			$xsd = '
	<xs:simpleType name="' . $class . '">
		<xs:annotation><xs:documentation>http://' . vConf::get('www_host') . '/api_v3/testmeDoc/index.php?object=' . $class . '</xs:documentation></xs:annotation>
		<xs:restriction base="xs:' . $xsdType . '">';
			
			$contants = $classTypeReflector->getConstants();
			foreach($contants as $contant)
			{
				$xsd .= '
			<xs:enumeration value="' . $contant->getDefaultValue() . '"><xs:annotation><xs:documentation>' . $contant->getName() . '</xs:documentation></xs:annotation></xs:enumeration>';
			}
			
			
			$xsd .= '
		</xs:restriction>
	</xs:simpleType>
			';
			
			$resultXsd .= $xsd;
		}
		
		$resultXsd .= '
</xs:schema>';
		
		return $resultXsd;
	}
	
	private static function loadClassRecursively(VidiunTypeReflector $classTypeReflector, &$enumClasses)
	{
		$class = $classTypeReflector->getType();
		if(
			$class == 'VidiunEnum'
			||
			$class == 'VidiunStringEnum'
			||
			$class == 'VidiunObject'
		)
			return;
			
		$enumClasses[$class] = $classTypeReflector;
		$parentClassTypeReflector = $classTypeReflector->getParentTypeReflector();
		if($parentClassTypeReflector)
			self::loadClassRecursively($parentClassTypeReflector, $enumClasses);
	}
}
