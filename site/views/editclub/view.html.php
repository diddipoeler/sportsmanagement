<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editclub
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewEditClub
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewEditClub extends sportsmanagementView
{

	/**
	 * sportsmanagementViewEditClub::init()
	 *
	 * @return void
	 */
	function init()
	{

		$this->item = $this->model->getData();
		$lists      = array();
		$this->form = $this->get('Form');

		if ($this->item->id)
		{
			// Alles ok
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

		$this->item->merge_teams = explode(",", $this->item->merge_teams);

		$extended       = sportsmanagementHelper::getExtended($this->item->extended, 'club');
		$this->extended = $extended;
		$this->lists    = $lists;

		$this->cfg_which_media_tool = ComponentHelper::getParams($this->option)->get('cfg_which_media_tool', 0);

	}


}
