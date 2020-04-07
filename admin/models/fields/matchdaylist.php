<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       matchdaylist.php
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

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldMatchdaylist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldMatchdaylist extends \JFormFieldList
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
		// Initialize variables.
		$options = array();

			  $varname = (string) $this->element['varname'];
		$project_id = Factory::getApplication()->input->getVar($varname);

		if (is_array($project_id))
		{
			$project_id = $project_id[0];
		}

		if ($project_id)
		{
			$options = & sportsmanagementHelper::getRoundsOptions($project_id, 'ASC', true);
		}

			  // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

					  return $options;
	}
}
