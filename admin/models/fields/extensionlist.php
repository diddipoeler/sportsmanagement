<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       extensionlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Folder;

/**
 * FormFieldExtensionlist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldExtensionlist extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Extensionlist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$options = array();

		$filter      = (string) $this->element['filter'];
		$exclude     = (string) $this->element['exclude'];
		$hideNone    = (string) $this->element['hide_none'];
		$hideDefault = (string) $this->element['hide_default'];

		$path = JPATH_ROOT . '/components/com_sportsmanagement/extensions';

		if (!is_dir($path))
		{
			return parent::getOptions();
		}

		$folders = Folder::folders($path, $filter);

		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{
				if ($exclude && preg_match(chr(1) . $exclude . chr(1), $folder))
				{
					continue;
				}

				$options[] = HTMLHelper::_('select.option', $folder, $folder);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
