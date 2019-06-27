<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage referee
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewReferee
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewReferee extends JViewLegacy
{

	/**
	 * sportsmanagementViewReferee::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        // Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
        $option = $jinput->getCmd('option');

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
		$person = sportsmanagementModelPerson::getPerson(0,$model::$cfg_which_database);

		$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		$this->config = $config;
		$this->person = $person;

		$ref = sportsmanagementModelPerson::getReferee();
		if ($ref)
		{
			$titleStr = Text::sprintf('COM_SPORTSMANAGEMENT_REFEREE_ABOUT_AS_A_REFEREE',sportsmanagementHelper::formatName(null, $ref->firstname, $ref->nickname, $ref->lastname, $this->config["name_format"]));
		}
		else
		{
			$titleStr = Text::_('COM_SPORTSMANAGEMENT_REFEREE_UNKNOWN_PROJECT');
		}

		$this->referee = $ref;
		$this->history = $model->getHistory('ASC');
		$this->title = $titleStr;

		if ($config['show_gameshistory'])
		{
			$this->games = $model->getGames();
			$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);
		}

		if ($person)
		{
			$extended = sportsmanagementHelper::getExtended($person->extended, 'referee');
			$this->extended = $extended;
		}
       
        if ( !isset($this->config['history_table_class']) )
        {
            $this->config['history_table_class'] = 'table';
        }
        if ( !isset($this->config['career_table_class']) )
        {
            $this->config['career_table_class'] = 'table';
        }
        
        $this->headertitle = $this->title;
        
		parent::display($tpl);
	}

}
?>