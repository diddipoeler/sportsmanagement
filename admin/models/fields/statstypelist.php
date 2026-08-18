<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       statstypelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Folder;

/**
 * FormFieldStatstypelist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldStatstypelist extends ListField
{
	public $type = 'statstypelist';

	/**
	 * Method to get the field options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$options = array();
		$files = Folder::files(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'statistics', 'php$');

		foreach ($files as $file)
		{
			$parts = explode('.', $file);

			if ($parts[0] !== 'base')
			{
				$options[] = HTMLHelper::_('select.option', $parts[0], $parts[0]);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
