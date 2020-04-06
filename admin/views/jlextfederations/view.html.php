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
 * @subpackage jlextfederations
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewjlextfederations
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjlextfederations extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewjlextfederations::init()
     * 
     * @return void
     */
    public function init()
    {
        
        $this->table = Table::getInstance('jlextfederation', 'sportsmanagementTable');
        
        //build the html options for nation
        $nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
            $this->search_nation = $res;
        }
        
        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist(
            $nation,
            'filter_search_nation',
            'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
            'value',
            'text',
            $this->state->get('filter.search_nation')
        );

        $this->lists = $lists;
        
        
        
       
    }
    
    /**
    * Add the page title and toolbar.
    *
    * @since 1.7
    */
    protected function addToolbar()
    {
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE');
        $this->icon = 'federations';

        ToolbarHelper::addNew('jlextfederation.add');
        ToolbarHelper::editList('jlextfederation.edit');
        ToolbarHelper::custom('jlextfederation.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('jlextfederation.export', Text::_('JTOOLBAR_EXPORT'));
        
        parent::addToolbar();
    }
    
    

}
?>
