<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewjoomleagueimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewjoomleagueimport extends sportsmanagementView
{
	/**
	 * sportsmanagementViewjoomleagueimport::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	public function init ()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $model = $this->getModel();
        $uri = Factory::getURI();
        $this->task = $jinput->getCmd('task');
        $this->request_url	= $uri->toString();
        
        
        if ( $this->getLayout() == 'positions'  )
		{
		$this->initPositions();  
        } 
        
        
        //$count = 5;
        $count = JComponentHelper::getParams($option)->get('max_import_jl_import_steps',0);
        
        $this->step = $app->getUserState( "$option.step", '0' );
        $this->totals = $app->getUserState( "$option.totals", '0' );
        
        if ( !$this->step )
        {
            $this->step = 0;
        }
        
       
        if ( $this->step <= $this->totals )
            {
            $successTable = $model->newstructur(0, $count);    
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
            
            $this->step = $this->step + $count;
            $app->setUserState( "$option.step", $this->step);    
        
        // Load our Javascript
        $document->addStylesheet(JURI::base().'components/'.$option.'/assets/css/progressbar.css');
        JToolbarHelper::title(Text::_('Bearbeitete Steps: '.$this->step.' von: '.$this->totals),'joomleague-import');
        //$this->addToolbar();
		//parent::display($tpl);
	}
    
    /**
     * sportsmanagementViewjoomleagueimport::initPositions()
     * 
     * @return void
     */
    function initPositions()
    {
        $app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $model = $this->getModel();
        $uri = Factory::getURI();
        
        $inputappend = '';
        $which_table = Factory::getApplication()->input->getVar('filter_which_table','');
        
        $this->joomleague	= $model->getImportPositions('joomleague', $which_table);
        $this->sportsmanagement	= $model->getImportPositions('sportsmanagement');
        
        $nation[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'));
		if ($res = $model->getImportPositions('sportsmanagement'))
        {
            $nation = array_merge($nation, $res);
            }
		
        $whichtable[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TABLE'));
        $whichtable[] = HTMLHelper::_('select.option', 'project_position', Text::_('project_position'));
        $whichtable[] = HTMLHelper::_('select.option', 'person', Text::_('person'));
        
        $lists['whichtable'] = HTMLHelper::Select::genericlist(	$whichtable,
																'filter_which_table',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$which_table);
        $lists['position'] = $nation;
        
        $this->lists	= $lists;
        
        JToolbarHelper::custom('joomleagueimports.updatepositions', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_POSITION_UPDATE'), false);
        JToolbarHelper::custom('joomleagueimports.updateplayerproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_PLAYER_PRO_POSITION_UPDATE'), false);
        JToolbarHelper::custom('joomleagueimports.updatestaffproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_STAFF_PRO_POSITION_UPDATE'), false);
        
    }

}
?>
