<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); // Check to ensure this file is included in Joomla!

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'ajax.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'sportsmanagement.php');  

jimport('joomla.form.helper');
//JFormHelper::loadFieldClass('list');

// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}

/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 * @package Joomleague
 * @subpackageParameter
 * @since1.5
 */
class JFormFieldDependSQL extends JFormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';
    
    /**
     * JFormFieldDependSQL::getInput()
     * 
     * @return
     */
    protected function getInput()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
       $view = $jinput->getCmd('view');
       $option = $jinput->getCmd('option');
       
       $lang = JFactory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);
        
       $attribs = '';
       $norequest = 0;
		$required = $this->element['required'] == "true" ? 'true' : 'false';
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$ajaxtask = $this->element['task'];
		$depends = $this->element['depends'];
        $slug = $this->element['slug'] == "true" ? 'true' : 'false';
        $query = (string)$this->element['query'];
        $norequest = $this->element['norequest'];

		
        $project_id = $this->form->getValue('id');
        
        if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
        if ($v = $this->element['multiple'])
		{
			$attribs .= ' multiple="'.$v.'"';
		}
        
//        if ( !$value )
//        {
//        $value = $this->form->getValue($val,'params');
//        $div = 'params';
//        }
//        else
//        {
        
        switch ($option)
        {
            case 'com_modules':
            $div = 'params';
            break;
            case 'com_sportsmanagement':
            if ( $norequest )
            {
            $div = '';
            }
            else
            {
            //$div = 'request';
            $div = 'params';
            }
            break;
            default:
            $div = 'request';
            break;
        }
        
        $value = $this->form->getValue($val,$div);
        $key_value = $this->form->getValue($key,$div);
        
        //$div = 'request';
//        }
        
        $cfg_which_database = $this->form->getValue('cfg_which_database',$div);
        
//        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view -> '.$view.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' option -> '.$option.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' norequest -> '.$norequest.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' fieldset<br><pre>'.print_r($this->fieldset,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jform<br><pre>'.print_r($this->jform,true).'</pre>'),'Notice');
        
//        
//        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_which_database -> '.$this->form->getValue('cfg_which_database',$div).' name -> '.$this->name),'Notice');
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' div -> '.$div.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value -> '.$value.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' this->value -> '.$this->value.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' id -> '.$this->id.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view -> '.$view.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id -> '.$project_id.''),'Notice');

//        }

		$ctrl = $this->name;
		$id = $this->id;
        
        $options = array();
        $result = '';
        
     // Build the script.
    $script = array();

$script[] = "\n";       
$script[] = "jQuery(document).ready(function ($){";

//$script[] = "				$('#jform_".$div.'_'.$depends."').change(function(){";
//$script[] = "					var value = $('#jform_".$div.'_'.$depends."').val();";
$script[] = "				$('#".$div.'_'.$depends."').change(function(){";
$script[] = "					var value = $('#".$div.'_'.$depends."').val();";

$script[] = "if (window.console) console.log('json value -> ' + value);";

$script[] = "					var	url = 'index.php?option=com_sportsmanagement&format=json&task=ajax.".$ajaxtask."&".$depends."=' + value;";

switch ($ajaxtask)
{
case 'personagegroupoptions':
$script[] = "					var valuecountry = $('#jform_country').val();";
$script[] = "if (window.console) console.log('json valuecountry -> ' + valuecountry);";
$script[] = " url = url + '&country=' + valuecountry;";
break;
}

//$script[] = " alert('url -> ' + url);";

$script[] = "if (window.console) console.log('json url -> ' + url);";

$script[] = "					$.ajax({";
switch ($view)
{
    case 'project':
    $script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&dbase=".$cfg_which_database."&slug=".$slug."&task=ajax.".$ajaxtask."&project=".$project_id."&".$depends."=' + value,";
    break;
    case 'club':
    $script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&dbase=".$cfg_which_database."&slug=".$slug."&task=ajax.".$ajaxtask."&country=".$key_value."&".$depends."=' + value,";
    break;
    default:
    $script[] = "						url: url,";
    break;
}



$script[] = "						dataType: 'json'";
$script[] = "					}).done(function(r) {";
$script[] = "						$('#".$this->id." option').each(function() {";
$script[] = "								jQuery('select#".$this->id." option').remove();";
$script[] = "						});";
$script[] = "";

//$script[] = " alert('r data -> ' + r.data);";
//$script[] = "if (window.console) console.log('json data -> ' + r.data);";

$script[] = "						$.each(r.data, function (i, val) {";
$script[] = "if (window.console) console.log('json value-> ' + val.value);";
$script[] = "if (window.console) console.log('json text-> ' + val.text);";
$script[] = "						});";

$script[] = "if (r.messages)";
$script[] = "		{";
//$script[] = " alert('r messages -> ' + r.messages);";
$script[] = "			// All the enqueued messages of the app object can simple be";
$script[] = "			// rendered by the respective helper function of Joomla!";
$script[] = "			// They will automatically be displayed at the messages section of the template";
$script[] = "			Joomla.renderMessages(r.messages);";
$script[] = "		}";
$script[] = "						$.each(r.data, function (i, val) {";
//$script[] = " alert('r data -> ' + r.data);";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";

//$script[] = " alert('value -> ' + val.text);";

$script[] = "							$('#".$this->id."').append(option);";
$script[] = "						});";

$script[] = "						$('#".$this->id."').trigger('liszt:updated');";
$script[] = "					});";

$script[] = "				});";

$script[] = "});";       
       
       // Add the script to the document head.
    JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        
        //if ( $ajaxtask && $value )
        //{
        $ajaxtask = 'get'.$ajaxtask;    
        $result = sportsmanagementModelAjax::$ajaxtask($value,$required,$slug);
        //}

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' script<br><pre>'.print_r($script,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ajaxtask<br><pre>'.print_r($ajaxtask,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
     //$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), $key, JText::_($val)));
     //$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), 'value','text' ));
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
