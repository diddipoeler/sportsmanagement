<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Filesystem\File;

jimport('joomla.html.parameter.element.timezones');

require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'sportstypes.php';
require_once JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'leagues.php';

/**
 * sportsmanagementViewProject
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewProject extends sportsmanagementView
{

	/**
	 * sportsmanagementViewProject::init()
	 *
	 * @return
	 */
	public function init()
	{

		$tpl       = '';
		$starttime = microtime();
		$lists     = array();

		if ($this->getLayout() == 'panel' || $this->getLayout() == 'panel_3' || $this->getLayout() == 'panel_4')
		{
			if (ComponentHelper::getParams($this->option)->get('show_jsm_tips'))
		{
			$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NOTES');
			}
			$this->_displayPanel($tpl);

			return;
		}
		

if ( !$this->item->admin )
{
$this->form->setValue('admin', null, $this->user->id);
}
if ( !$this->item->editor )
{
$this->form->setValue('editor', null, $this->user->id);
}
		
		
		Factory::getApplication()->input->set('hidemainmenu', true);

		$this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
		$this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);

		$this->extended       = sportsmanagementHelper::getExtended($this->item->extended, 'project');
		$this->extendeduser       = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'project');


		$isNew = $this->item->id == 0;

		if ($isNew)
		{
			$this->form->setValue('start_date', null, date("Y-m-d"));
			$this->form->setValue('start_time', null, '18:00');
			$this->form->setValue('admin', null, $this->user->id);
			$this->form->setValue('editor', null, $this->user->id);
            //$this->form->setValue('country',null, Factory::getApplication()->getUserState("com_sportsmanagement.projectnation", ''));
		}
		else
		{
$endung = strtolower(File::getExt($this->item->picture));


if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$name = basename($this->item->picture);    
}
else
{
$name = File::getName($this->item->picture);
}
//$safefilename = File::makeSafe($this->item->picture);		
//echo ' endung <br><pre>'.print_r($endung ,true).'</pre>';
//echo ' name <br><pre>'.print_r($name ,true).'</pre>';
//echo ' safefilename <br><pre>'.print_r($safefilename ,true).'</pre>';
      if ( !$name )
      {
      $this->item->picture = 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png';  
        $this->form->setValue('picture', null, $this->item->picture);
      }			
		}

		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend',0,Factory::getApplication()->input->get('view'));

		if ($this->checkextrafields)
		{
			if (!$isNew)
			{
				$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id,'backend',0,Factory::getApplication()->input->get('view'));
			}
		}

		$this->form->setValue('fav_team', null, explode(',', $this->item->fav_team));

		$this->lists = $lists;

	}


	/**
	 * sportsmanagementViewProject::_displayPanel()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displayPanel($tpl)
	{
		$starttime = microtime();

		$this->item = $this->get('Item');

		$iProjectDivisionsCount = 0;
		$mdlProjectDivisions    = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
		$iProjectDivisionsCount = $mdlProjectDivisions->getProjectDivisionsCount($this->item->id);

		if ($this->item->project_art_id != 3)
		{
			$iProjectPositionsCount = 0;
			$mdlProjectPositions    = BaseDatabaseModel::getInstance('Projectpositions', 'sportsmanagementModel');
			/**
			 *     sind im projekt keine positionen vorhanden, dann
			 *     bitte einmal die standard positionen, torwart, abwehr,
			 *     mittelfeld und stürmer einfügen
			 */
			$iProjectPositionsCount = $mdlProjectPositions->getProjectPositionsCount($this->item->id);

			if (!$iProjectPositionsCount)
			{
				$mdlProjectPositions->insertStandardProjectPositions($this->item->id, $this->item->sports_type_id);
			}
		}

		$iProjectRefereesCount = 0;
		$mdlProjectReferees    = BaseDatabaseModel::getInstance('Projectreferees', 'sportsmanagementModel');
		$iProjectRefereesCount = $mdlProjectReferees->getProjectRefereesCount($this->item->id);

		$iProjectTeamsCount = 0;
		$mdlProjecteams     = BaseDatabaseModel::getInstance('Projectteams', 'sportsmanagementModel');
		$iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->item->id);

		$iMatchDaysCount = 0;
		$mdlRounds       = BaseDatabaseModel::getInstance("Rounds", "sportsmanagementModel");
		$iMatchDaysCount = $mdlRounds->getRoundsCount($this->item->id);

		$this->project                = $this->item;
		$this->count_projectdivisions = $iProjectDivisionsCount;
		$this->count_projectpositions = $iProjectPositionsCount;
		$this->count_projectreferees  = $iProjectRefereesCount;
		$this->count_projectteams     = $iProjectTeamsCount;
		$this->count_matchdays        = $iMatchDaysCount;

/**
 * 		Store the variable that we would like to keep for next time
 * 		function syntax is setUserState( $key, $value );
 */
		$this->app->setUserState("$this->option.pid", $this->item->id);
		$this->app->setUserState("$this->option.season_id", $this->item->season_id);
		$this->app->setUserState("$this->option.project_art_id", $this->item->project_art_id);
		$this->app->setUserState("$this->option.sports_type_id", $this->item->sports_type_id);

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		$isNew      = $this->item->id ? $this->title = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT', $this->item->name) : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW');
		$this->icon = 'project';

		$bar = Toolbar::getInstance('toolbar');

		switch (ComponentHelper::getParams($this->option)->get('which_article_component'))
		{
			case 'com_content':
				$bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&extension=com_content');
				break;
			case 'com_k2':
				$bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
				break;
		}

		parent::addToolbar();
	}


}
