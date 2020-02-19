<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      dependsql.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'ajax.php');
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php');  

jimport('joomla.form.helper');

/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 * @package sportsmanagement
 * @subpackageParameter
 * @since1.5
 */
class JFormFieldDependSQL extends FormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';
    
    /**
     * FormFieldDependSQL::getInput()
     * 
     * @return
     */
    protected function getInput()
	{
	   // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
       $view = $jinput->getCmd('view');
       $option = $jinput->getCmd('option');
       
       $lang = Factory::getLanguage();
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
            $div = 'request';
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

		$ctrl = $this->name;
		$id = $this->id;
        
        $options = array();
        $result = '';
        
     // Build the script.
    $script = array();

$script[] = "\n";       
$script[] = "jQuery(document).ready(function ($){";
/*
$script[] = "					var value = $('#jform_".$div."_".$depends."').val();";
//$script[] = "					var dbparam = $('#jform_".$div."_cfg_which_database').prop('checked');";

//$script[] = " alert('cfg_which_database ' + dbparam);";

$script[] = "					$.ajax({";
switch ($view)
{
    case 'project':
    $script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&dbase=".$cfg_which_database."&slug=false&task=ajax.".$ajaxtask."&project=".$project_id."&".$depends."=' + value,";
    break;
    default:
    $script[] = "						url: 'index.php?option=com_sportsmanagement&format=json&dbase=".$cfg_which_database."&slug=false&task=ajax.".$ajaxtask."&".$depends."=' + value,";
    break;
}

$script[] = "						dataType: 'json'";
$script[] = "					}).done(function(data) {";
$script[] = "						$('#".$this->id." option').each(function() {";
//$script[] = "								jQuery('select#".$this->id." option').remove();";
$script[] = "						});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";
$script[] = "							$('#".$this->id."').append(option);";
$script[] = "						});";

$script[] = "						$('#".$this->id."').trigger('liszt:updated');";
$script[] = "					});";
*/

$script[] = "				$('#jform_".$div.'_'.$depends."').change(function(){";
$script[] = "					var value = $('#jform_".$div.'_'.$depends."').val();";
//$script[] = "					var dbparam = $('#jform_params_cfg_which_database').val();";
//$script[] = "					var dbparam = $('#jform_home').prop('checked');";
//$script[] = "					var dbparam = $('input:radio[name=jform_home]:checked').val();";

//$script[] = " alert('value -> ' + value);";

$script[] = "if (window.console) console.log('json value -> ' + value);";

//$script[] = " alert('task -> ' + ".$ajaxtask.");";
//$script[] = " alert('depends -> ' + ".$depends.");";

$script[] = "					var	url = 'index.php?option=com_sportsmanagement&format=json&dbase=".$cfg_which_database."&slug=".$slug."&task=ajax.".$ajaxtask."&".$depends."=' + value;";

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
    Factory::getDocument()->addScriptDeclaration(implode("\n", $script));
        
        $ajaxtask = 'get'.$ajaxtask;    
        $result = sportsmanagementModelAjax::$ajaxtask($value,$required,$slug);

     if ( $result )
        {
     $options = array_merge($options, $result);
     }

    return HTMLHelper::_('select.genericlist',  $options, $ctrl, $attribs, 'value', 'text', $this->value, $this->id);
    }    


}
