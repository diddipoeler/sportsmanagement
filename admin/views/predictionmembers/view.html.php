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



class sportsmanagementViewPredictionMembers extends JView
{

  function display( $tpl = null )
	{


    if ( $this->getLayout() == 'default')
		{
			$this->_display( $tpl );
			return;
		}
		
		if ( $this->getLayout() == 'editlist')
		{
			$this->_editlist( $tpl );
			return;
		}
    
    parent::display($tpl);
		
	}

  function _editlist( $tpl = null )
	{
		$mainframe			=& JFactory::getApplication();
    $db					=& JFactory::getDBO();
		$uri				=& JFactory::getURI();
		//$document =& JFactory::getDocument();
		//$model				=& $this->getModel();
		// Get a refrence of the page instance in joomla
		$document	=& JFactory::getDocument();
    $option = JRequest::getCmd('option');
    $optiontext = strtoupper(JRequest::getCmd('option').'_');
    $this->assignRef( 'optiontext',			$optiontext );
    
//     $baseurl    = JURI::root();
// 		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.js');
// 		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.Request.js');
// 		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Observer.js');
// 		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/quickaddteam.js');
// 		$document->addStyleSheet($baseurl.'administrator/components/com_joomleague/assets/css/Autocompleter.css');
		

    		
		$prediction_id		= (int) $mainframe->getUserState( $option . 'prediction_id' );
		$prediction_name =& $this->getModel()->getPredictionProjectName($prediction_id);
		$this->assignRef( 'prediction_name',			$prediction_name );
		
    $res_prediction_members =& $this->getModel()->getPredictionMembers($prediction_id);
    
    if ( $res_prediction_members )
    {
    $lists['prediction_members']=JHtml::_(	'select.genericlist',
										$res_prediction_members,
										'prediction_members[]',
										'class="inputbox" multiple="true" onchange="" size="15"',
										'value',
										'text');
    }
    else
    {
    $lists['prediction_members'] = '<select name="prediction_members[]" id="prediction_members" style="" class="inputbox" multiple="true" size="15"></select>';
    }
    
    $res_joomla_members =& $this->getModel()->getJLUsers($prediction_id);
    if ( $res_joomla_members )
    {
    $lists['members']=JHtml::_(	'select.genericlist',
										$res_joomla_members,
										'members[]',
										'class="inputbox" multiple="true" onchange="" size="15"',
										'value',
										'text');
    }
                    																
    $this->assignRef( 'prediction_id',			$prediction_id );
    $this->assignRef( 'lists',			$lists );
    $this->assignRef('request_url',$uri->toString());
    
		parent::display( $tpl );
	}	

	
    
    
    function _display( $tpl = null )
	{
$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$option = JRequest::getCmd('option');
    $model	= $this->getModel();
    	$uri = JFactory::getURI();
        
	$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmb_filter_state',		'filter_state',		'',				'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmb_filter_order',		'filter_order',		'u.username',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmb_filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier. 'tmb_search',				'search',			'',				'string' );
		$search				= JString::strtolower( $search );	
    
    $items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        // state filter
		$lists['state']		= JHtml::_( 'grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search'] = $search; 
        
        //build the html select list for prediction games
        $mdlPredGames = JModel::getInstance("PredictionGames", "sportsmanagementModel");
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ) . ' -', 'value', 'text' );
		if ( $res = $mdlPredGames->getPredictionGames() ) 
        { 
            $predictions = array_merge( $predictions, $res ); 
            }
		$lists['predictions'] = JHtml::_(	'select.genericlist',
											$predictions,
											'prediction_id',
											'class="inputbox" onChange="this.form.submit();" ',
											'value',
											'text',
											$prediction_id
										);
		unset( $res );
        
        
        
        
           
    
    /*
		$prediction_id		= (int) $mainframe->getUserState( $option . 'prediction_id' );
//echo '#' . $prediction_id . '#<br />';
		$lists				= array();
		$db					=& JFactory::getDBO();
		$uri				=& JFactory::getURI();
		$items				=& $this->get( 'Data' );
		$total				=& $this->get( 'Total' );
		$pagination			=& $this->get( 'Pagination' );
		//$model				=& $this->getModel();
		

    $baseurl    = JURI::root();
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Autocompleter.Request.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/Observer.js');
		$document->addScript($baseurl.'administrator/components/com_joomleague/assets/js/autocompleter/1_4/quickaddteam.js');
		$document->addStyleSheet($baseurl.'administrator/components/com_joomleague/assets/css/Autocompleter.css');
		
		// state filter
		$lists['state']		= JHtml::_( 'grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search'] = $search;

		//build the html select list for prediction games
		$predictions[] = JHtml::_( 'select.option', '0', '- ' . JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME' ) . ' -', 'value', 'text' );
		if ( $res =& $this->getModel()->getPredictionGames() ) { $predictions = array_merge( $predictions, $res ); }
		$lists['predictions'] = JHtml::_(	'select.genericlist',
											$predictions,
											'prediction_id',
											'class="inputbox" onChange="this.form.submit();" ',
											'value',
											'text',
											$prediction_id
										);
		unset( $res );

		// Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_joomleague/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
    $document->addCustomTag($stylelink);
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE' ), 'pred-cpanel' );

		JToolBarHelper::custom( 'predictionmember.reminder', 'send.png', 'send_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER' ), true );
		JToolBarHelper::divider();
		
		if ( $prediction_id )
		{
		  JLToolBarHelper::editList('predictionmember.edit');
    JToolBarHelper::custom('predictionmember.editlist','upload.png','upload_f2.png',JText::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_BUTTON_ASSIGN'),false);
 		JToolBarHelper::divider();
 		}
		JToolBarHelper::publishList( 'predictionmember.publish', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVE' ) );
		JToolBarHelper::unpublishList( 'predictionmember.unpublish', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REJECT' ) );
		JToolBarHelper::divider();

		//JToolBarHelper::addNewX();
		//JToolBarHelper::divider();

		//JToolBarHelper::editListX();
		JToolBarHelper::deleteList( '', 'predictionmember.remove' );
		JToolBarHelper::divider();

		JLToolBarHelper::onlinehelp();

		$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
		
		if ( $prediction_id )
		{
		$this->assignRef( 'items',			$items );
		}
		*/
		
        
        $this->assign('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
        $this->assignRef( 'pagination',		$pagination );
        $this->assignRef( 'items',			$items );
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
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		// Set toolbar items for the page
		
        JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE' ), 'predmembers' );
		
     /*   
		JToolBarHelper::addNew('predictiongroup.add');
		JToolBarHelper::editList('predictiongroup.edit');
		JToolBarHelper::custom('predictiongroup.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('predictiongroup.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('', 'predictiongroups.delete', 'JTOOLBAR_DELETE');
        */
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
        
		
	}
    
    
    

}
?>
