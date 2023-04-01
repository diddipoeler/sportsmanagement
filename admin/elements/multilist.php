<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       multilist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * JFormFieldMultiList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldMultiList extends JFormField
{
	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $type = 'MultiList';

	/**
	 * JFormFieldMultiList::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		// Base name of the HTML control.
		$ctrl = $control_name . '[' . $name . ']';

		// Construct an array of the HTML OPTION statements.
		$options = array();

		foreach ($node->children() as $option)
		{
			$val       = $option->attributes('value');
			$text      = $option->data();
			$options[] = HTMLHelper::_('select.option', $val, Text::_($text));
		}

		// Construct the various argument calls that are supported.
		$attribs = ' ';

		if ($v = $node->attributes('size'))
		{
			$attribs .= 'size="' . $v . '"';
		}

		if ($v = $node->attributes('class'))
		{
			$attribs .= 'class="' . $v . '"';
		}
		else
		{
			$attribs .= 'class="inputbox"';
		}

		if ($m = $node->attributes('multiple'))
		{
			$attribs .= ' multiple="multiple"';
			$ctrl    .= '[]';
		}

		// Render the HTML SELECT list.
		// Return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
		return HTMLHelper::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', array_map('trim', explode(',', $value)), $control_name . $name);
	}
}
