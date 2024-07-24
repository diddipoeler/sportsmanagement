<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       userfields.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * FormFieldactseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFielduserfields extends \JFormFieldList
{
	protected $type = 'userfields';

	/**
	 * FormFieldactseason::getOptions()
	 *
	 * @return
	 */
	protected function getOptions()
	{
		$options = array();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

$view = Factory::getApplication()->input->getVar('view');

		
		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_user_extra_fields as s');
		$query->order('s.name');

switch ( $view )
	{
		case 'projects':
		case 'project':
$query->where('template_backend LIKE '.$db->Quote(''.'project'.''));
		break;
		case 'teams':
		case 'team':
$query->where('template_backend LIKE '.$db->Quote(''.'team'.''));
		break;

	}

		
		$db->setQuery($query);
		$options = $db->loadObjectList();

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


}
