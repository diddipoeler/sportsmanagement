<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       currentround.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldCurrentround
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldCurrentround extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Currentround';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$options = array();
		$varname = (string) $this->element['varname'];
		$project_id = $jinput->get->get('id');

		if ($project_id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id AS value');
			$query->select('CASE LENGTH(name) when 0 then CONCAT(' . $db->Quote(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')) . ', " ", id)	else concat(name, \' (\' , round_date_first , \')\') END as text ');
			$query->from('#__sportsmanagement_round ');
			$query->where('project_id = ' . $project_id);
			$query->order('roundcode,round_date_first');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
