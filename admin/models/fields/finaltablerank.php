<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       finaltablerank.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * FormFieldfinaltablerank
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldfinaltablerank extends ListField
{
	public $type = 'finaltablerank';

	/**
	 * Method to get the field options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$options = array();

		for ($rank = 1; $rank < 41; $rank++)
		{
			$options[] = HTMLHelper::_('select.option', $rank, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_FINALTABLERANK') . ' - ' . $rank);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
