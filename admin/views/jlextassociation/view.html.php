<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextassociastion
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewJlextassociation
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewJlextassociation extends sportsmanagementView
{

	/**
	 * sportsmanagementViewJlextassociation::init()
	 *
	 * @return
	 */
	public function init()
	{
	   
       if ($this->item->id)
		{
			/** Alles ok */
			if ($this->item->founded == '0000-00-00')
			{
				$this->item->founded = '';
				$this->form->setValue('founded', '');
			}

			if ($this->item->dissolved == '0000-00-00')
			{
				$this->item->dissolved = '';
				$this->form->setValue('dissolved', '');
			}
		}
		else
		{
			$this->form->setValue('founded', '');
			$this->form->setValue('dissolved', '');
		}
        if ( !$this->item->founded_year )
        {
            $this->item->founded_year = 'kein';
            $this->form->setValue('founded_year','', 'kein');
        }

	}


	/**
	 * sportsmanagementViewJlextassociation::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		parent::addToolbar();
	}


}
