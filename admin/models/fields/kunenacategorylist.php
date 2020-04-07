<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       kunenacategorylist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * FormFieldKunenaCategoryList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldKunenaCategoryList extends \JFormFieldList
{
	protected $type = 'KunenaCategoryList';

	/**
	 * FormFieldKunenaCategoryList::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		if (!class_exists('KunenaForum') || !KunenaForum::installed())
		{
			echo '<a href="index.php?option=com_kunena">PLEASE COMPLETE KUNENA INSTALLATION</a>';

			return;
		}
		else
		{
			HTMLHelper::addIncludePath(KPATH_ADMIN . '/libraries/html/html');
		}

		KunenaFactory::loadLanguage('com_kunena');

		$none = $this->element['none'];

		$size = $this->element['size'];
		$class = $this->element['class'];

		$attribs = ' ';

		if ($size)
		{
			$attribs .= 'size="' . $size . '"';
		}

		if ($class)
		{
			$attribs .= 'class="' . $class . '"';
		}
		else
		{
			$attribs .= 'class="inputbox"';
		}

		if (!empty($this->element['multiple']))
		{
			$attribs .= ' multiple="multiple"';
		}

		// Get the field options.
		$options = $this->getOptions();

		return HTMLHelper::_('kunenaforum.categorylist', $this->name, 0, $options, $this->element, $attribs, 'value', 'text', $this->value);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 * @since  11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = HTMLHelper::_('select.option', (string) $option['value'], Text::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', ((string) $option['disabled'] == 'true'));

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
