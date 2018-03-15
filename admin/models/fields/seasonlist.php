<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
