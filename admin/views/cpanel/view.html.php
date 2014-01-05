<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewcpanel
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewcpanel extends JView
{
	/**
	 *  view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		
        $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model	= $this->getModel();
        
        $databasetool = JModel::getInstance("databasetool", "sportsmanagementModel");
        DEFINE( 'COM_SPORTSMANAGEMENT_MODEL_ERRORLOG',$databasetool );
        
        $params = JComponentHelper::getParams( $option );
        $sporttypes = $params->get( 'cfg_sport_types' );
        $sm_quotes = $params->get( 'cfg_quotes' );
        $country = $params->get( 'cfg_country_associations' );
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country,true).'</pre>'),'Notice');
        
        if ( $sm_quotes )
        {
        // zitate
        $databasetool->checkQuotes($sm_quotes);
        }
            
        foreach ( $sporttypes as $key => $type )
        {
        $checksporttype = $model->checksporttype($type);    
        
        $checksporttype_strukt = $databasetool->checkSportTypeStructur($type);
        
        if ( $checksporttype )
        {
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS',strtoupper($type)),'');    
        
        // es können aber auch neue positionen oder ereignisse dazu kommen
        $insert_sport_type = $databasetool->insertSportType($type); 
        
        if ( $country )
        {
        foreach ( $country as $keyc => $typec )
        {    
        $insert_agegroup = $databasetool->insertAgegroup($typec,$insert_sport_type);    
        }
        }
        
        }
        else
        {
        $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR',strtoupper($type)),'Error');
        
        // es können aber auch neue positionen oder ereignisse dazu kommen
        $insert_sport_type = $databasetool->insertSportType($type); 
        
        if ( $country )
        {
        foreach ( $country as $keyc => $typec )
        {    
        $insert_agegroup = $databasetool->insertAgegroup($typec,$insert_sport_type);    
        }
        }
           
        
        }
        
        }
        
        // Get data from the model
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
        
        $checkassociations = $databasetool->checkAssociations();
        
        $checkcountry = $model->checkcountry();
        if ( $checkcountry )
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS'),'');    
        }
        else
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR'),'Error'); 
        $insert_countries = $databasetool->insertCountries();    
        }
        
		jimport('joomla.html.pane');
		$pane	= JPane::getInstance('sliders');
		$this->assignRef( 'pane'		, $pane );
        $this->assignRef( 'sporttypes'		, $sporttypes );
        $this->assign( 'version', $model->getVersion() );
        $this->assign( 'githubrequest', $model->getGithubRequests() );
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign data to the view
		$this->items = $items;
		$this->pagination = $pagination;
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		$canDo = sportsmanagementHelper::getActions();
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_MANAGER'), 'helloworld');
		
		if ($canDo->get('core.admin')) 
		{
			//JToolBarHelper::custom('cpanel.import','upload','upload',JText::_('JTOOLBAR_INSTALL'),false);
            sportsmanagementHelper::ToolbarButton('default','upload',JText::_('JTOOLBAR_INSTALL'),'githubinstall',1);
            JToolBarHelper::divider();
            sportsmanagementHelper::ToolbarButtonOnlineHelp();
			JToolBarHelper::preferences('com_sportsmanagement');
		}
	}
    
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION'));
	}
	
	public function addIcon( $image , $url , $text , $newWindow = false , $width = 0, $height = 0)
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
        $attribs = array();
        if ( $width )
        {
        $attribs['width'] = $width;
        $attribs['height'] = $height;
        }
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image , null, $attribs ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
	
}
