<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage smquotetxt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


/**
 * sportsmanagementViewsmquotetxt
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewsmquotetxt extends sportsmanagementView
{
    /**
     * sportsmanagementViewsmquotetxt::init()
     * 
     * @return void
     */
    public function init()
    {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $model = $this->getModel();
        $this->file_name = $jinput->getString('file_name');
        
        // Initialise variables.
        $this->form        = $this->get('Form');
        $this->source    = $this->get('Source');
      
        $this->option = $option;
        
    }
    
   
    /**
     * sportsmanagementViewsmquotetxt::addToolbar()
     * 
     * @return void
     */
    protected function addToolbar()
    {
        $jinput = Factory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_ADD_NEW');
        $this->icon = 'quote';

        parent::addToolbar();
    }    
    
    
    
}
?>
