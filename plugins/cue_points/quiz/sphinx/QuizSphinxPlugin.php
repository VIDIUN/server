<?php
/**
 * Enable indexing and searching answers cue point objects in sphinx
 * @package plugins.cuePoint
 */
class QuizSphinxPlugin extends VidiunPlugin implements IVidiunCriteriaFactory, IVidiunPending
{
	const PLUGIN_NAME = 'quizSphinx';
	
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
	    $cuePointDependency = new VidiunDependency(CuePointPlugin::getPluginName());
	    $quizDependency = new VidiunDependency(QuizPlugin::getPluginName());
	
	    return array($cuePointDependency , $quizDependency);
	}	
	
	/* (non-PHPdoc)
	 * @see IVidiunCriteriaFactory::getVidiunCriteria()
	 */
	public static function getVidiunCriteria($objectType)
	{
		if ($objectType == 'AnswerCuePoint')
			return new SphinxAnswerCuePointCriteria();
			
		return null;
	}
}
