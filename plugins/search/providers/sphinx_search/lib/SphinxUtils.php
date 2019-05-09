<?php
/**
 * @package plugins.sphinxSearch
 * @subpackage lib
 */
class SphinxUtils
{
	public static function escapeString($str, $escapeType = SearchIndexFieldEscapeType::DEFAULT_ESCAPE, $iterations = 2)
	{
		return VidiunCriteria::escapeString($str, $escapeType, $iterations);
	}
}
