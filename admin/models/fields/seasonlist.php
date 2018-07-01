<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      seasonlist.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

if ( !class_exists('sportsmanagementHelper')) 
{
    require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
}

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldseasonlist
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
//class JFormFieldseasonlist extends JFormFieldList
class JFormFieldseasonlist extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'seasonlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	//protected function getOptions()
    protected function getInput()
	{
		// Initialize variables.
		$options = array();
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
       $view = $jinput->getCmd('view');
       $option = $jinput->getCmd('option');
       $lang = JFactory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR); 
        
        
    $attribs = '';
    $ctrl = $this->name;
    $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
//    $value = $this->form->getValue($val,'request');
//    if ( !$value )
//        {
//        $value = $this->form->getValue($val,'params');
//        $div = 'params';
//        }
//        else
//        {
//        $div = 'request';
//        }
        
        switch ($option)
        {
            case 'com_modules':
            $div = 'params';
            break;
            default:
            $div = 'request';
            break;
        }
        
        if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
        
        $cfg_which_database = $this->form->getValue('cfg_which_database',$div);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' name -> <br><pre>'.print_r($this->name,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value_field -> <br><pre>'.print_r($this->element['value_field'],true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value -> <br><pre>'.print_r($value,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' div -> <br><pre>'.print_r($div,true).'</pre>'),'Notice'); 
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database -> <br><pre>'.print_r($this->form->getValue('cfg_which_database',$div),true).'</pre>'),'Notice');
        
    $db = sportsmanagementHelper::getDBConnection(TRUE,$cfg_which_database);
			$query = $db->getQuery(true);
			
			$query->select('id AS value, name AS text');
			$query->from('#__sportsmanagement_season');
			$query->order('name DESC');
			$db->setQuery($query);
			$result = $db->loadObjectList();
    
		//// Merge any additional options in the XML definition.
//		$options = array_merge(parent::getOptions(), $options);
//		return $options;
$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), 'value','text' ));
     if ( $result )
        {
     $options = array_merge($options, $result);
     }
//     // Merge any additional options in the XML definition.
//		$options = array_merge(parent::getOptions(), $options);
//
//		return $options;   
    //return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, $key, $val, $this->value, $this->id);
    return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, 'value', 'text', $this->value, $this->id);
    
	}
}
