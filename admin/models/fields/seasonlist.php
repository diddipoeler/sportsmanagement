<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       seasonlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

if (!class_exists('sportsmanagementHelper'))
{
	include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
}

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldseasonlist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldseasonlist extends FormField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'seasonlist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getInput()
	{
		$options = array();
		$app = Factory::getApplication();
		$jinput = $app->input;
		$view   = $jinput->getCmd('view');
		$option = $jinput->getCmd('option');
		$lang   = Factory::getLanguage();
		$lang->load("com_sportsmanagement", JPATH_ADMINISTRATOR);


		$attribs = '';
		$ctrl    = $this->name;
		$val     = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
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

		$attribs   .= ' class="form-select select2-container"';
        if ($v = $this->element['size'])
		{
			$attribs .= ' size="' . $v . '"';
		}
        
switch ( Factory::getApplication()->input->getCmd('view', '') )
{
case 'clubs':
case 'projects':
$attribs .= 'onchange="this.form.submit();"';
break;
case 'project':
$attribs .= 'onchange="javascript:setseasonname();"';
break;
case 'club':
$attribs .= ' multiple="true"';
break;
case 'league':
$attribs .= ' multiple="true"';
break;	
case 'playground':
$attribs .= ' multiple="true"';
break;		
default:
$attribs .= '';
break;
}

		$cfg_which_database = $this->form->getValue('cfg_which_database', $div);

		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_season');
		$query->order('name DESC');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		//// Merge any additional options in the XML definition.
		//		$options = array_merge(parent::getOptions(), $options);
		//		return $options;
		$options = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'value', 'text'));
		if ($result)
		{
			$options = array_merge($options, $result);
		}
		//     // Merge any additional options in the XML definition.
		//		$options = array_merge(parent::getOptions(), $options);
		//
		//		return $options;
		//return HTMLHelper::_('select.genericlist',  $options, $ctrl, $attribs, $key, $val, $this->value, $this->id);
		return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $this->value, $this->id);

	}
}
