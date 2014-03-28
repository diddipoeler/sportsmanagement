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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewjoomleagueimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjoomleagueimport extends JView
{
	/**
	 * sportsmanagementViewjoomleagueimport::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();
        $model = $this->getModel();
        $uri = JFactory::getURI();
        
        $this->step = $mainframe->getUserState( "$option.step", '0' );
        
        if ( !$this->step )
        {
            $this->step = 0;
        }
        
//        $databasetool = JModel::getInstance("databasetool", "sportsmanagementModel");
        $this->assign('totals',$model->gettotals() );
        
        if ( $this->step <= $this->totals )
            {
            $successTable = $model->newstructur($this->step);    
            //$this->work_table = $this->sm_tables[$this->step];
            $this->bar_value = round( ( $this->step * 100 / $this->totals ), 0);
            }
            else
            {
            $this->step = 0;    
            $this->bar_value = 100;
            $this->work_table = '';
            }
        
     $javascript = "\n";            
$javascript .= '            jQuery(function() {' . "\n"; 
$javascript .= '    var progressbar = jQuery( "#progressbar" ),' . "\n"; 

$javascript .= '      progressLabel = jQuery( ".progress-label" );' . "\n"; 



$javascript .= '     progressbar.progressbar({' . "\n"; 
//$javascript .= '      value: false,' . "\n"; 
$javascript .= '      value: '.$this->bar_value.',' . "\n";

$javascript .= '      create: function() {' . "\n"; 
$javascript .= '        progressLabel.text( "'.$this->task.' -> " + progressbar.progressbar( "value" ) + "%" );' . "\n"; 
$javascript .= '      },' . "\n";

$javascript .= '      change: function() {' . "\n"; 
$javascript .= '        progressLabel.text( progressbar.progressbar( "value" ) + "%" );' . "\n"; 
$javascript .= '      },' . "\n"; 

$javascript .= '      complete: function() {' . "\n"; 
$javascript .= '        progressLabel.text( "Complete!" );' . "\n"; 
$javascript .= '      }' . "\n"; 

$javascript .= '    });' . "\n"; 
$javascript .= '     function progress() {' . "\n"; 
$javascript .= '      var val = progressbar.progressbar( "value" ) || 0;' . "\n"; 
$javascript .= '       progressbar.progressbar( "value", '.$this->bar_value.' );' . "\n";
$javascript .= '       if ( val < 99 ) {' . "\n"; 

$javascript .= '        setTimeout( progress, 100 );' . "\n"; 
$javascript .= '      }' . "\n"; 
$javascript .= '    }' . "\n"; 
$javascript .= '     setTimeout( progress, 3000 );' . "\n"; 
$javascript .= '  });' . "\n"; 
$document->addScriptDeclaration( $javascript );            
            
            $this->step++;
            $mainframe->setUserState( "$option.step", $this->step);    
        
        
        
//        
//        $checktables = $databasetool->checkImportTablesJlJsm($this->jl_tables);
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($checktables,true).'</pre>'),'');
//        
//        $this->assign('request_url',$uri->toString());
//        $this->assign('items',$checktables);
        
        
        
        
        // Load our Javascript
        $document->addStylesheet(JURI::base().'components/'.$option.'/assets/css/progressbar.css');
        
        //$this->addToolbar();
		parent::display($tpl);
	}
    
//    /**
//	* Add the page title and toolbar.
//	*
//	* @since	1.7
//	*/
//	protected function addToolbar()
//	{
//		// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
//        
//        // Set toolbar items for the page
//        JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT'),'joomleague-import');
//        //JToolBarHelper::custom('joomleagueimports.newstructur','upload','upload',JText::_('JMODIFY'),false);
//        JToolBarHelper::custom('joomleagueimports.checkimport','upload','upload',JText::_('JMODIFY'),false);
//        JToolBarHelper::custom('joomleagueimports.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
//        JToolBarHelper::divider();
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolBarHelper::preferences(JRequest::getCmd('option'));
//        
//    }    
    
    
    
}
?>
