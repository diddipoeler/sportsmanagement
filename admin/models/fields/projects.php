<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      projects.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.form.helper');
FormHelper::loadFieldClass('list');

/**
 * FormFieldProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldProjects extends \JFormFieldList
{

	protected $type = 'projects';

    
    /**
     * FormFieldProjects::getOptions()
     * 
     * @return
     */
    protected function getOptions() 
    {
        $options = array();
        $app = Factory::getApplication();
		//$db = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();
        // welche tabelle soll genutzt werden
        $params = ComponentHelper::getParams( 'com_sportsmanagement' );
        $database_table	= $params->get( 'cfg_which_database_table' );
        
        $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
        $value = $this->form->getValue($val,'request');
        
        if ( !$value )
        {
        $value = $this->form->getValue($val,'params');
        $div = 'params';
        }
        else
        {
        $div = 'request';
        }
       
        $cfg_which_database = $this->form->getValue('cfg_which_database',$div);
        if ( !$cfg_which_database )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,$cfg_which_database);
        }
        
		$extension = "com_sportsmanagement";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query = 'SELECT p.id, concat(p.name, \' ('.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE').': \', l.name, \')\', \' ('.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON').': \', s.name, \' )\' ) as name 
					FROM #__'.$database_table.'_project AS p 
					LEFT JOIN #__'.$database_table.'_season AS s ON s.id = p.season_id 
					LEFT JOIN #__'.$database_table.'_league AS l ON l.id = p.league_id 
					WHERE p.published=1 ORDER BY p.id DESC';
		$db->setQuery( $query );
		$projects = $db->loadObjectList();
        
		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT') );
        
        foreach ( $projects as $project ) 
        {
			$options[] = HTMLHelper::_('select.option',  $project->id, '&nbsp;&nbsp;&nbsp;'.$project->name );
		}

$options = array_merge(parent::getOptions(), $options);

		return $options;
        
	}
}
