<?php

class myThumbUtils
{
	public static function validateImageContent($thumbPath)
	{
		if(!$thumbPath)
		{
			return true;
		}

		$fileType = vFileUtils::getMimeType($thumbPath);
		if($fileType == 'image/svg+xml')
		{
			$xmlContent = file_get_contents($thumbPath);
			if($xmlContent)
			{
				$dom = new VDOMDocument();
				$dom->loadXML($xmlContent);
				$element = $dom->getElementsByTagName('script')->item(0);
				if($element)
				{
					return false;
				}
			}
		}

		return true;
	}
}