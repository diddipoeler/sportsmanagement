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
 * @subpackage joomleagueimport
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewjoomleagueimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjoomleagueimport extends sportsmanagementView
{
    /**
     * sportsmanagementViewjoomleagueimport::display()
     * 
     * @param  mixed $tpl
     * @return void
     */
    public function init()
    {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $model = $this->getModel();
        $uri = Factory::getURI();
        $this->task = $jinput->getCmd('task');
        $this->request_url    = $uri->toString();
        
        
        if ($this->getLayout() == 'positions'  ) {
            $this->initPositions();  
        } 
        
        
        //$count = 5;
        $count = ComponentHelper::getParams($option)->get('max_import_jl_import_steps', 0);
        
        $this->step = $app->getUserState("$option.step", '0');
        $this->totals = $app->getUserState("$option.totals", '0');
        
        if (!$this->step ) {
            $this->step = 0;
        }
        
       
        if ($this->step <= $this->totals ) {
            $successTable = $model->newstructur(0, $count);    
            //$this->work_table = $this->sm_tables[$this->step];
            $this->bar_value = round(( $this->step * 100 / $this->totals ), 0);
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
        $document->addScriptDeclaration($javascript);            
            
            $this->step = $this->step + $count;
            $app->setUserState("$option.step", $this->step);    
        
        // Load our Javascript
        $document->addStylesheet(Uri::base().'components/'.$option.'/assets/css/progressbar.css');
        ToolbarHelper::title(Text::_('Bearbeitete Steps: '.$this->step.' von: '.$this->totals), 'joomleague-import');
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
        $which_table = Factory::getApplication()->input->getVar('filter_which_table', '');
        
        $this->joomleague    = $model->getImportPositions('joomleague', $which_table);
        $this->sportsmanagement    = $model->getImportPositions('sportsmanagement');
        
        $nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'));
        if ($res = $model->getImportPositions('sportsmanagement')) {
            $nation = array_merge($nation, $res);
        }
        
        $whichtable[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TABLE'));
        $whichtable[] = HTMLHelper::_('select.option', 'project_position', Text::_('project_position'));
        $whichtable[] = HTMLHelper::_('select.option', 'person', Text::_('person'));
        
        $lists['whichtable'] = HTMLHelper::Select::genericlist(
            $whichtable,
            'filter_which_table',
            $inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
            'value',
            'text',
            $which_table
        );
        $lists['position'] = $nation;
        
        $this->lists    = $lists;
        
        ToolbarHelper::custom('joomleagueimports.updatepositions', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_POSITION_UPDATE'), false);
        ToolbarHelper::custom('joomleagueimports.updateplayerproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_PLAYER_PRO_POSITION_UPDATE'), false);
        ToolbarHelper::custom('joomleagueimports.updatestaffproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_STAFF_PRO_POSITION_UPDATE'), false);
        
    }

}
?>
