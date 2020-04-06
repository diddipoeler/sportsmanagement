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
 * @subpackage smquotes
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * sportsmanagementViewsmquotes
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewsmquotes extends sportsmanagementView
{
  
    /**
     * sportsmanagementViewsmquotes::init()
     *
     * @return void
     */
    public function init()
    {
  
        $this->table = Table::getInstance('smquote', 'sportsmanagementTable');
          
    }
  
    /**
    * Add the page title and toolbar.
    *
    * @since 1.7
    */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE');
        ToolbarHelper::addNew('smquote.add');
        ToolbarHelper::editList('smquote.edit');
        ToolbarHelper::custom('smquote.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
      
        ToolbarHelper::custom('smquotes.edittxt', 'featured.png', 'featured_f2.png', Text::_('JTOOLBAR_EDIT'), false);
      
        $bar = Toolbar::getInstance('toolbar');
        //$bar->appendButton('Link', 'info', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_sportsmanagement');
        $bar->appendButton('Link', 'info', 'Kategorie', 'index.php?option=com_categories&extension=com_sportsmanagement');
      
        ToolbarHelper::archiveList('smquote.export', Text::_('JTOOLBAR_EXPORT'));
      
      
        parent::addToolbar();
    }
}
?>
