<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       dependsql.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'ajax.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';

jimport('joomla.form.helper');

if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	HTMLHelper::_('jquery.framework');
}

/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 *
 * @package             sportsmanagement
 * @subpackageParameter
 * @since1              .5
 */
class JFormFieldDependSQL extends FormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $type = 'dependsql';

	/**
	 * FormFieldDependSQL::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$view   = $jinput->getCmd('view');
		$option = $jinput->getCmd('option');

		$lang = Factory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);

		$attribs   = '';
		$norequest = 0;
		$required  = $this->element['required'] == "true" ? 'true' : 'false';
		$key       = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val       = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		
		$key_clubid       = ($this->element['club_id'] ? $this->element['club_id'] : 'value');
		$val_clubid       = ($this->element['club_ids'] ? $this->element['club_ids'] : $this->name);
		
		$ajaxtask  = $this->element['task'];
		$depends   = $this->element['depends'];
		$slug      = $this->element['slug'] == "true" ? 'true' : 'false';
		$query     = (string) $this->element['query'];
		$norequest = $this->element['norequest'];

		$project_id = $this->form->getValue('id');

		if ($v = $this->element['size'])
		{
			$attribs .= ' size="' . $v . '"';
		}

		if ($v = $this->element['multiple'])
		{
			$attribs .= ' multiple="' . $v . '"';
		}

		switch ($option)
		{
			case 'com_modules':
				$div = 'params';
				break;
			case 'com_sportsmanagement':
				if ($norequest)
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
      
      switch ($view)
		{
		case 'predictiongame':
          $div = '';
          break;
      }

		$value     = $this->form->getValue($val, $div);
		$key_value = $this->form->getValue($key, $div);
		
		$value_clubid     = $this->form->getValue($val_clubid, $div);
		$key_value_clubid = $this->form->getValue($key_clubid, $div);

		$cfg_which_database = $this->form->getValue('cfg_which_database', $div);

		$ctrl = $this->name;
		$id   = $this->id;

		$options = array();
		$result  = '';

		$script = array();

		$script[] = "\n";
		$script[] = "jQuery(document).ready(function ($){";
      switch ($view)
		{
		case 'predictiongame':
        $script[] = "				$('#jform" . $div . '_' . $depends . "').change(function(){";
		$script[] = "					var value = $('#jform" . $div . '_' . $depends . "').val();";  
          break;
        default:
		$script[] = "				$('#jform_" . $div . '_' . $depends . "').change(function(){";
		$script[] = "					var value = $('#jform_" . $div . '_' . $depends . "').val();";
          break;
      }
      
		$script[] = "if (window.console) console.log('json value -> ' + value);";
		
		switch ($ajaxtask)
		{
		case 'projectteamoptions':
//$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&" . $depends . "=' + value &club_id=;";								
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&club_id=".$value_clubid."&task=ajax." . $ajaxtask . "&" . $depends . "=' + value;";				
		break;
				
		default:
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&" . $depends . "=' + value;";				
		break;
		}
		

		switch ($ajaxtask)
		{
		case 'personagegroupoptions':
		$script[] = "					var valuecountry = $('#jform_country').val();";
		$script[] = "if (window.console) console.log('json valuecountry -> ' + valuecountry);";
		$script[] = " url = url + '&country=' + valuecountry;";
		break;
		}

		$script[] = "if (window.console) console.log('json url -> ' + url);";
		$script[] = "					$.ajax({";

		switch ($view)
		{
		case 'project':
		$script[] = "	url: 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&project=" . $project_id . "&" . $depends . "=' + value,";
		break;
		case 'club':
		$script[] = "	url: 'index.php?option=com_sportsmanagement&format=json&dbase=" . $cfg_which_database . "&slug=" . $slug . "&task=ajax." . $ajaxtask . "&country=" . $key_value . "&" . $depends . "=' + value,";
		break;
		default:
		$script[] = "	url: url,";
		break;
		}

		$script[] = "						dataType: 'json'";
		$script[] = "					}).done(function(r) {";
		$script[] = "						$('#" . $this->id . " option').each(function() {";
		$script[] = "								jQuery('select#" . $this->id . " option').remove();";
		$script[] = "						});";
		$script[] = "";
		$script[] = "						$.each(r.data, function (i, val) {";
		$script[] = "if (window.console) console.log('json value-> ' + val.value);";
		$script[] = "if (window.console) console.log('json text-> ' + val.text);";
		$script[] = "						});";
		$script[] = "if (r.messages)";
		$script[] = "		{";
		$script[] = "			// All the enqueued messages of the app object can simple be";
		$script[] = "			// rendered by the respective helper function of Joomla!";
		$script[] = "			// They will automatically be displayed at the messages section of the template";
		$script[] = "			Joomla.renderMessages(r.messages);";
		$script[] = "		}";
		$script[] = "						$.each(r.data, function (i, val) {";
		$script[] = "							var option = $('<option>');";
		$script[] = "							option.text(val.text).val(val.value);";
		$script[] = "							$('#" . $this->id . "').append(option);";
		$script[] = "						});";
		$script[] = "						$('#" . $this->id . "').trigger('liszt:updated');";
		$script[] = "					});";
		$script[] = "				});";
		$script[] = "});";

		/** Add the script to the document head. */
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		$ajaxtask = 'get' . $ajaxtask;
		$result   = sportsmanagementModelAjax::$ajaxtask($value, $required, $slug);

		if ($result)
		{
			$options = array_merge($options, $result);
		}

		return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $this->value, $this->id);
	}


}
