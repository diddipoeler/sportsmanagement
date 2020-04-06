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
 * @subpackage statistic
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewstatistic
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewstatistic extends sportsmanagementView
{
  
  
    /**
     * sportsmanagementViewstatistic::init()
     *
     * @return
     */
    public function init()
    {
        $app = Factory::getApplication();
        $this->description = '';
        // get the Data
        $form = $this->get('Form');
        $item = $this->get('Item');
        $script = $this->get('Script');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors));
            return false;
        }
        // Assign the Data
        $this->form = $form;
        $this->item = $item;
        $this->script = $script;
      
      
        $isNew = $this->item->id == 0;
      
        if ($isNew ) {
            $item->class = 'basic';  
        }
      
        if ($this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' ) {
            //$this->setLayout('edit');
        }

        $formparams = sportsmanagementHelper::getExtendedStatistic($item->params, $item->class);
        $this->formparams = $formparams;
      
    }

  
    /**
     * sportsmanagementViewstatistic::addToolBar()
     *
     * @return void
     */
    protected function addToolBar()
    {
  
        Factory::getApplication()->input->set('hidemainmenu', true);
      
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_NEW');
        $this->icon = 'statistic';
      
        parent::addToolbar();
    }

}
