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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );



/**
 * sportsmanagementViewPredictionGames
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPredictionGames extends JView
{
	/**
	 * sportsmanagementViewPredictionGames::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display( $tpl = null )
	{
		$mainframe = JFactory::getApplication();
    $model = $this->getModel();
    
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');
    $uri = JFactory::getURI();
    
    $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
    
		//$prediction_id		= (int) $mainframe->getUserState( $option . 'prediction_id' );
        //$this->prediction_id	= $mainframe->getUserState( "$option.prediction_id", '0' );
        $modalheight = JComponentHelper::getParams($option)->get('modal_popup_height', 600);
        $modalwidth = JComponentHelper::getParams($option)->get('modal_popup_width', 900);
		$this->assignRef( 'modalheight',$modalheight );
        $this->assignRef( 'modalwidth',$modalwidth );
        
        //echo '#' . $prediction_id . '#<br />';
    
    
		$lists				= array();

        
        $this->prediction_id	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier, 'prediction_id', '0' );
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPredictionGames prediction_id<br><pre>'.print_r($this->prediction_id,true).'</pre>'),'Notice');

$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        if ( !$items )
        {
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'),'Error');    
        }
        


		//build the html select list for prediction games
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'Select Prediction Game' ) . ' -', 'value', 'text' );
		if ( $res = $model->getPredictionGames() ) 
        { 
            $predictions = array_merge( $predictions, $res ); 
            }
		$lists['predictions'] = JHtml::_(	'select.genericlist',
											$predictions,
											'prediction_id',
											//'class="inputbox validate-select-required" ',
											'class="inputbox" onChange="this.form.submit();" ',
											//'class="inputbox" onChange="this.form.submit();" style="width:200px"',
											'value',
											'text',
											$this->prediction_id
										);
		unset( $res );


		$this->assign( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
        $this->assignRef( 'option',			$option );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'dPredictionID',	$this->prediction_id );
		$this->assignRef( 'pagination',		$pagination );
		
		if ( $this->prediction_id > 0 )
		{
			$this->assign( 'predictionProjects',	$this->getModel()->getChilds( $this->prediction_id ) );
		}

    
		$this->assign('request_url',$uri->toString());
    $this->addToolbar();
		parent::display( $tpl );
	}
    
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{ 
		
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
        JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE' ), 'pred-cpanel' );
        JToolBarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
      
        JToolBarHelper::editList('predictiongame.edit');
		JToolBarHelper::addNew('predictiongame.add');
		JToolBarHelper::custom('predictiongame.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('predictiongame.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('','predictiongames.delete', 'JTOOLBAR_DELETE');
        
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
        
		
	}
    
    

}
?>