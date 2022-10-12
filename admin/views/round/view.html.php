<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage round
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewRound
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewRound extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRound::init()
	 *
	 * @return
	 */
	public function init()
	{

		$this->project_id     = $this->app->getUserState("$this->option.pid", '0');
		$this->project_art_id = $this->app->getUserState("$this->option.project_art_id", '0');

		$mdlProject    = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$project       = $mdlProject->getProject($this->project_id);
		$this->project = $this->project_id;
        
        $isNew = $this->item->id == 0;
        if ($isNew)
		{
		$this->form->setValue('round_date_first',null, date("Y-m-d"));
			$this->form->setValue('round_date_last', null, date("Y-m-d"));
			$this->form->setValue('project_id', null, $this->project_id);  
          }
          else
          {
          if ($this->item->round_date_first == '0000-00-00')
			{
				$this->form->setValue('round_date_first',null, date("Y-m-d"));
			}  
            if ($this->item->round_date_last == '0000-00-00')
			{
				$this->form->setValue('round_date_last', null, date("Y-m-d"));
			}
            
          }

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$this->jinput->set('pid', $this->project_id);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUND_NEW');
		$this->icon = 'round';
		parent::addToolbar();
	}


}
