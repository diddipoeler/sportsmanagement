<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextcountries
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewjlextcountries
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjlextcountries extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewjlextcountries::init()
     * 
     * @return void
     */
    public function init ()
	{
		
        $inputappend = '';

        $starttime = microtime(); 
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }

        $this->table = JTable::getInstance('jlextcountry', 'sportsmanagementTable');
        
         //build the html options for nation
		$nation[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION'));
		if ($res = $this->get('Federation') )
        {
            $nation = array_merge($nation,$res);
            $this->federation = $res;
        

        }
		
        $lists['federation'] = JHtmlSelect::genericlist($nation,
							'filter_federation',
							$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
							'value',
							'text',
							$this->state->get('filter.federation'));

		$this->lists = $lists;

	}
	
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        // Set toolbar items for the page
		JToolbarHelper::addNew('jlextcountry.add');
		JToolbarHelper::editList('jlextcountry.edit');
		JToolbarHelper::custom('jlextcountry.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
        JToolbarHelper::custom('jlextcountries.importplz','upload','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_IMPORT_PLZ'),true);
		JToolbarHelper::archiveList('jlextcountry.export',JText::_('JTOOLBAR_EXPORT'));

        parent::addToolbar();
	}
}
?>