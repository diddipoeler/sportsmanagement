<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       multidependsql.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Welche version
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}

/**
 * Renders a Dynamic SQL element
 *
 * in the xml element, the following elements must be defined:
 * - depends: list of elements name this element depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends element names as parameters for query (=> 'index.php?option=com_sportsmanagement&controller=ajax&task=<task>&p=1&tid=34')
 *
 * @package             Joomleague
 * @subpackageParameter
 * @since1              .5
 */
class JFormFieldMultiDependSQL extends JFormField
{
	/**
	 * Element name
	 *
	 * @accessprotected
	 * @varstring
	 */
	protected $type = 'multidependsql';

	/**
	 * JFormFieldMultiDependSQL::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		// TODO: for the moment always require a selection, because when it is set to 0, the multiselection
		// will also select the empty line, next to the real selected ones. This will lead to a longer link
		// (all selected ids (e.g. events or stats) will be included in the link address), so this should
		// be fixed later, so that when nothing is selected, only id=0 will be in the link address.
		// $required = (int) $node->attributes('required');
		$required = 1;
		$key      = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val      = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task     = $this->element['task'];
		$depends  = $this->element['depends'];
		$query    = $this->element['query'];

		$ctrl = $this->name;

		// Construct the various argument calls that are supported.
		$attribs = ' task="' . $task . '"';
		$attribs .= ' isrequired="' . $required . '"';

		if ($v = $this->element['size'])
		{
			$attribs .= 'size="' . $v . '"';
		}

		if ($depends)
		{
			$attribs .= ' depends="' . $depends . '"';
		}

		$attribs .= ' class="mdepend inputbox';

		// Optionally add "depend" to the class attribute
		if ($depends)
		{
			$attribs .= ' depend"';
		}
		else
		{
			$attribs .= '"';
		}

		$value   = is_array($this->value) ? $this->value[0] : $this->value;
		$attribs .= ' current="' . $value . '"';
		$attribs .= ' multiple="multiple"';

		$selected = explode("|", $value);

		if ($required)
		{
			$options = array();
		}
		else
		{
			$options = array(HTMLHelper::_('select.option', '', Text::_('Select'), $key, $val));
		}

		if ($query != '')
		{
			$db = sportsmanagementHelper::getDBConnection();
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}

		if ($depends)
		{
			$doc = Factory::getDocument();
			$doc->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/depend.js');
		}

		// Render the HTML SELECT list.
		$text = HTMLHelper::_('select.genericlist', $options, 'l' . $ctrl, $attribs, $key, $val, $selected);
		$text .= '<input type="hidden" name="' . $ctrl . '" id="' . $this->id . '" value="' . $value . '"/>';

		return $text;
	}
}
