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
 * @subpackage clubname
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewclubname
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewclubname extends sportsmanagementView
{

    /**
     * sportsmanagementViewclubname::init()
     *
     * @return
     */
    public function init()
    {
    
    }

  
    /**
     * sportsmanagementViewagegroup::addToolBar()
     *
     * @return void
     */
    protected function addToolBar()
    {
        $this->jinput->setVar('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_NEW');
        $this->icon = 'clubname';
        parent::addToolbar();
    }
  
}
