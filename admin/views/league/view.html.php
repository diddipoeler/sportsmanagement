<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage league
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewLeague
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewLeague extends sportsmanagementView
{

	/**
	 * sportsmanagementViewLeague::init()
	 *
	 * @return
	 */
	public function init()
	{

		//echo '<pre>'.print_r(Factory::getApplication()->getUserState("com_sportsmanagement.leaguenation", ''),true).'</pre>';
		
		if ($this->item->id)
		{
			/** Alles ok */
			if ($this->item->founded == '0000-00-00')
			{
				$this->item->founded = '';
				$this->form->setValue('founded',null, '');
			}

			if ($this->item->dissolved == '0000-00-00')
			{
				$this->item->dissolved = '';
				$this->form->setValue('dissolved',null, '');
			}
		}
		else
		{
			$this->form->setValue('founded',null, '');
			$this->form->setValue('dissolved',null, '');
			$this->form->setValue('country',null, Factory::getApplication()->getUserState("com_sportsmanagement.leaguenation", ''));
		}
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
		$this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);

		$this->extended           = sportsmanagementHelper::getExtended($this->item->extended, 'league');
		$this->extendeduser       = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'league');

	}

	/**
	 * sportsmanagementViewLeague::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ADD_NEW');
		$this->icon = 'league';
		parent::addToolbar();
	}

}
