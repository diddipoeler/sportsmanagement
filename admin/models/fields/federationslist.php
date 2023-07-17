<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       federationslist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * FormFieldFederationsList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldFederationsList extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'FederationsList';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$app      = Factory::getApplication();
        $view   = Factory::getApplication()->input->getCmd('view');
        $db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$selected = 0;
		$options   = array();
		$vartable  = (string) $this->element['targettable'];
		$select_id = $app->input->getVar('id');

		if (is_array($select_id))
		{
			$select_id = $select_id;
		}



switch ($view)
{
    case 'leagues':
    $query->select('t.id,t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_federations AS t');
			$query->where('t.parent_id = 0');
			$query->order('t.name');
		try{
			$db->setQuery($query);
			$options = $db->loadObjectList();
		 }
		catch (Exception $e)
		{
	Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
    break;
    default:
		if ($select_id)
		{
			$query->select('t.id,t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_federations AS t');
			$query->where('t.parent_id = 0');
			$query->order('t.name');
			try{
			$db->setQuery($query);
			$sections = $db->loadObjectList();
			$list     = $this->JJ_categoryArray(0);
 }
		catch (Exception $e)
		{
	Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
			$preoptions = array();
			$name       = 'parent_id';

			foreach ($list as $item)
			{
				if (!$preoptions && !$selected && ($sections || !$item->section))
				{
					$selected = $item->id;
				}

				$options [] = HTMLHelper::_('select.option', $item->id, $item->treename, 'value', 'text', !$sections && $item->section);
			}
		}
        break;
        }

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * FormFieldFederationsList::JJ_categoryArray()
	 *
	 * @param   integer  $admin
	 *
	 * @return
	 */
	function JJ_categoryArray($admin = 0)
	{
		$db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        $query->select('*');
		$query->from('#__sportsmanagement_federations');
		$query->order('ordering, name');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$children = array();

		/** First pass - collect children */
		foreach ($items as $v)
		{
			$pt   = $v->parent_id;
			$list = isset($children[$pt]) ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		/** Second pass - get an indent list of the items */
		$array = $this->fbTreeRecurse(0, '', array(), $children, 10, 0, 1);

		return $array;
	}

	/**
	 * FormFieldFederationsList::fbTreeRecurse()
	 *
	 * @param   mixed    $id
	 * @param   mixed    $indent
	 * @param   mixed    $list
	 * @param   mixed    $children
	 * @param   integer  $maxlevel
	 * @param   integer  $level
	 * @param   integer  $type
	 *
	 * @return
	 */
	function fbTreeRecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{

		if (isset($children[$id]) && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type)
				{
					$pre    = '&nbsp;';
					$spacer = '...';
				}
				else
				{
					$pre    = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $this->sm_htmlspecialchars($v->name);
				}
				else
				{
					$txt = $pre . $this->sm_htmlspecialchars($v->name);
				}

				$pt                  = $v->parent_id;
				$list[$id]           = $v;
				$list[$id]->treename = $indent . $txt;
				$list[$id]->children = !empty($children[$id]) ? count($children[$id]) : 0;
				$list[$id]->section  = ($v->parent_id == 0);

				$list = $this->fbTreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}

		return $list;
	}

	/**
	 * FormFieldFederationsList::sm_htmlspecialchars()
	 *
	 * @param   mixed   $string
	 * @param   mixed   $quote_style
	 * @param   string  $charset
	 *
	 * @return
	 */
	function sm_htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'UTF-8')
	{
		return htmlspecialchars($string, $quote_style, $charset);
	}

}
