<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       statstypelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;

FormHelper::loadFieldClass('list');


/**
 * FormFieldStatstypelist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldStatstypelist extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'statstypelist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		// $filter = (string) $this->element['filter'];
		// $exclude = (string) $this->element['exclude'];
		// $hideNone = (string) $this->element['hide_none'];
		// $hideDefault = (string) $this->element['hide_default'];

		// Get the path in which to search for file options.
		$files = Folder::files(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'statistics', 'php$');
		$options = array();

		foreach ($files as $file)
		{
			$parts = explode('.', $file);

			if ($parts[0] != 'base')
			{
				$options[] = HTMLHelper::_('select.option', $parts[0], $parts[0]);
			}
		}

			  /*
        // check for statistic in extensions
        $extensions = sportsmanagementHelper::getExtensions(0);
        foreach ($extensions as $type)
        {
         $path = JLG_PATH_SITE.DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'statistics';
         if (!file_exists($path)) {
          continue;
         }
         $files = Folder::files($path, 'php$');
         foreach ($files as $file)
         {
          $parts = explode('.', $file);
          if ($parts[0] != 'base') {
        $options[] = HTMLHelper::_('select.option', $parts[0], $parts[0]);
          }
         }
        }
        */

			  // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
