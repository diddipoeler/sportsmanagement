<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage league
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

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
       
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
      
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'league');
        $this->extended    = $extended;
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'league');      
        $this->extendeduser    = $extendeduser;
      
    }

  
    /**
     * sportsmanagementViewLeague::addToolBar()
     *
     * @return void
     */
    protected function addToolBar()
    {
        $this->jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ADD_NEW');
        $this->icon = 'league';
        parent::addToolbar();
    }
  
  
}
