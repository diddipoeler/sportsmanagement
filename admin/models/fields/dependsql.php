<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); // Check to ensure this file is included in Joomla!

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'ajax.php');

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
    
    protected function getInput()
	{
	   $mainframe = JFactory::getApplication();
       
       $attribs = '';
		$required = $this->element['required'] == "true" ? 'true' : 'false';
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$ajaxtask = $this->element['task'];
		$depends = $this->element['depends'];
        $query = (string)$this->element['query'];
        $value = $this->form->getValue($val,'request');
        
		if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
        
        if ( !$value )
        {
        $value = $this->form->getValue($val,'params');
        $div = 'params';
        }
        else
        {
            $div = 'request';
        }
        

//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'Notice');

		$ctrl = $this->name;
		$id = $this->id;
        
        $options = array();
        $result = '';
        
     // Build the script.
    $script = array();

$script[] = "\n";       
$script[] = "jQuery(document).ready(function ($){";

//$script[] = "					$.ajax({";
//$script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&task=ajax.".$ajaxtask."&".$depends."=' + value,";
//$script[] = "						dataType: 'json'";
//$script[] = "					}).done(function(data) {";
//$script[] = "						$('#".$this->id." option').each(function() {";
////$script[] = "							if ($(this).val() != '1') {";
////$script[] = "								$(this).remove();";
//$script[] = "								jQuery('select#".$this->id." option').remove();";
////$script[] = "							}";
//$script[] = "						});";
//$script[] = "";
//$script[] = "						$.each(data, function (i, val) {";
//$script[] = "							var option = $('<option>');";
//$script[] = "							option.text(val.text).val(val.value);";
//
////$script[] = " alert(val.text);";
//
//$script[] = "							$('#".$this->id."').append(option);";
//$script[] = "						});";

$script[] = "				$('#jform_".$div."_".$depends."').change(function(){";
//$script[] = "					var value = $(this).val();";
$script[] = "					var value = $('#jform_".$div."_".$depends."').val();";

//$script[] = " alert(value);";

$script[] = "					$.ajax({";
$script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&task=ajax.".$ajaxtask."&".$depends."=' + value,";
$script[] = "						dataType: 'json'";
$script[] = "					}).done(function(data) {";
$script[] = "						$('#".$this->id." option').each(function() {";
//$script[] = "							if ($(this).val() != '1') {";
//$script[] = "								$(this).remove();";
$script[] = "								jQuery('select#".$this->id." option').remove();";
//$script[] = "							}";
$script[] = "						});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";

//$script[] = " alert(val.text);";

$script[] = "							$('#".$this->id."').append(option);";
$script[] = "						});";

$script[] = "						$('#".$this->id."').trigger('liszt:updated');";
$script[] = "					});";
$script[] = "				});";
$script[] = "});";       
       
       // Add the script to the document head.
    JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
    
       
        
//        $mdlAjax = JModelLegacy::getInstance('Ajax', 'sportsmanagementModel');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mdlAjax<br><pre>'.print_r($mdlAjax,true).'</pre>'),'Notice');
        
        if ( $ajaxtask && $value )
        {
        $ajaxtask = 'get'.$ajaxtask;    
        //$result = sportsmanagementModelAjax::$query($value,$required);
        $result = sportsmanagementModelAjax::$ajaxtask($value,$required);
        }

//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ajaxtask<br><pre>'.print_r($ajaxtask,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
     //$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), $key, JText::_($val)));
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

//	/**
//	 * JFormFieldDependSQL::getInput()
//	 * 
//	 * @return
//	 */
//	function getInput()
//	{
//		$required = $this->element['required'] == "true" ? 'true' : 'false';
//		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
//		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
//		$task = $this->element['task'];
//		$depends = $this->element['depends'];
//
//		$ctrl = $this->name;
//		$id = $this->id;
//        
//        
//		// Construct the various argument calls that are supported.
//		$attribs	 = ' task="'.$task.'"';
//        // muss ausgesternt werden, da es zu einem fehler in der
//        // menü- oder modul erstellung kommt.
//		//$attribs	.= ' required="'.$required.'"';
//		if ($v = $this->element['size'])
//		{
//			$attribs .= ' size="'.$v.'"';
//		}
//		if ($depends)
//		{
//			$attribs	.= ' depends="'.$depends.'"';
//		}
//		$attribs	.= ' class="inputbox';
//		// Optionally add "depend" to the class attribute
//		if ($depends)
//		{
//			$attribs	.= ' depend"';
//		}
//		else
//		{
//			$attribs	.= '"';
//		}
//		$attribs	.= ' current="'.$this->value.'"';
//		
//		$lang = JFactory::getLanguage();
//		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);
//		if ($required) {
//			$options = array();
//		}
//		else {
//			$options = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'), $key, JText::_($val)));
//		}
//
//		$query = $this->element['query'];
//		if ($query!='')
//		{
//			$db = JFactory::getDBO();
//			$db->setQuery($query);
//			$options = array_merge($options, $db->loadObjectList());
//		}
//		
//		if ($depends)
//		{
//			$doc = JFactory::getDocument();
//            if(version_compare(JVERSION,'3.0.0','ge')) 
//            {
//            $doc->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/depend_3.js' );
//            }
//            else
//            {
//			$doc->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/depend.js' );
//            }
//		}
//
//		return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, $key, $val, $this->value, $this->id);
//	}
}
