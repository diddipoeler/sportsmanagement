<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextfederation
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewJlextfederation
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewJlextfederation extends sportsmanagementView
{


	/**
	 * sportsmanagementViewJlextfederation::init()
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
		}
        if ( !$this->item->founded_year )
        {
            $this->item->founded_year = 'kein';
            $this->form->setValue('founded_year','', 'kein');
        }

	}


	/**
	 * sportsmanagementViewJlextfederation::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		parent::addToolbar();
	}

}
