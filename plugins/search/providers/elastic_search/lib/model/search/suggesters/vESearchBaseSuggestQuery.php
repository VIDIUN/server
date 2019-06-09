<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
abstract class vESearchBaseSuggestQuery
{
	const SUGGEST_KEY = 'suggest';

	abstract public function getFinalQuery();

}
