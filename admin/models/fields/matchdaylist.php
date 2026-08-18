<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       matchdaylist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

/**
 * FormFieldMatchdaylist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldMatchdaylist extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Matchdaylist';

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
		$varname = (string) $this->element['varname'];
		$projectId = Factory::getApplication()->getInput()->get($varname, null, 'raw');

		if (is_array($projectId))
		{
			$projectId = reset($projectId);
		}

		$projectId = (int) $projectId;

		if ($projectId)
		{
			$options = sportsmanagementHelper::getRoundsOptions($projectId, 'ASC', true);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
