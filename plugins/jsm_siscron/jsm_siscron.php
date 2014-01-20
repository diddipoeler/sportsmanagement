<?php

/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class PlgSystemjsm_siscron extends JPlugin
{


	public function PlgSystemjsm_siscron(&$subject, $params)
	{
		parent::__construct($subject, $params);
		// load language file for frontend
		JPlugin::loadLanguage( 'plg_jsm_siscron', JPATH_ADMINISTRATOR );
	}
    
    
	public function onBeforeRender()
	{
		
        $app = JFactory::getApplication();

//		if (!$app->isSite())
//		{
//			return;
//		}
        
        $projectid = JRequest::getInt('p',0);
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterRender()
	{
		
        $app = JFactory::getApplication();

//		if (!$app->isSite())
//		{
//			return;
//		}
        
        $projectid = JRequest::getInt('p',0);
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterRoute()
	{
		
        $app = JFactory::getApplication();

//		if (!$app->isSite())
//		{
//			return;
//		}
        
        $projectid = JRequest::getInt('p',0);
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterDispatch()
	{
		
        $app = JFactory::getApplication();

//		if (!$app->isSite())
//		{
//			return;
//		}
        
        $projectid = JRequest::getInt('p',0);
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterInitialise()
	{
		
        $app = JFactory::getApplication();

//		if (!$app->isSite())
//		{
//			return;
//		}
        
        $projectid = JRequest::getInt('p',0);
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    
    
    
    

}    

?>