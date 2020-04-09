<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       associationslist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldAssociationsList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldAssociationsList extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'AssociationsList';

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
		$option   = Factory::getApplication()->input->getCmd('option');
		$selected = 0;

		// Initialize variables.
		$options   = array();
		$vartable  = (string) $this->element['targettable'];
		$select_id = Factory::getApplication()->input->getVar('id');

		if (is_array($select_id))
		{
			$select_id = $select_id[0];
		}

		if ($select_id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('country');
			$query->from('#__sportsmanagement_' . $vartable . ' AS t');
			$query->where('t.id = ' . $select_id);
			$db->setQuery($query);
			$country = $db->loadResult();

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country = '" . $country . "'");
			$query->where('t.parent_id = 0');
			$query->order('t.name');
			$db->setQuery($query);

			$sections = $db->loadObjectList();

			$categoryparent = empty($sections) ? 0 : $sections[0]->value;

			$list = $this->JJ_categoryArray(0, $country);

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

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * FormFieldAssociationsList::JJ_categoryArray()
	 *
	 * @param   integer  $admin
	 * @param   mixed    $country
	 *
	 * @return
	 */
	function JJ_categoryArray($admin = 0, $country)
	{
		$db = sportsmanagementHelper::getDBConnection();

		// Get a list of the menu items
		$query = "SELECT * FROM #__sportsmanagement_associations where country = '" . $country . "'";

		$query .= " ORDER BY ordering, name";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Establish the hierarchy of the menu
		$children = array();

		// First pass - collect children
		foreach ($items as $v)
		{
			$pt   = $v->parent_id;
			$list = isset($children[$pt]) ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		// Second pass - get an indent list of the items
		$array = $this->fbTreeRecurse(0, '', array(), $children, 10, 0, 1);

		return $array;
	}

	/**
	 * FormFieldAssociationsList::fbTreeRecurse()
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
	 * FormFieldAssociationsList::sm_htmlspecialchars()
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
