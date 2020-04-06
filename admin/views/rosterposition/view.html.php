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
 * @subpackage rosterposition
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
HTMLHelper::_('jquery.ui');

/**
 * sportsmanagementViewrosterposition
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewrosterposition extends sportsmanagementView
{
    
    /**
     * sportsmanagementViewrosterposition::init()
     * 
     * @return
     */
    public function init()
    {
        $this->document->addScript('http://code.jquery.com/ui/1.10.3/jquery-ui.js');
        
        $bildpositionenhome = array();
        $bildpositionenhome['HOME_POS'][0]['heim']['oben'] = 5;
        $bildpositionenhome['HOME_POS'][0]['heim']['links'] = 233;
        $bildpositionenhome['HOME_POS'][1]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][1]['heim']['links'] = 69;
        $bildpositionenhome['HOME_POS'][2]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][2]['heim']['links'] = 179;
        $bildpositionenhome['HOME_POS'][3]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][3]['heim']['links'] = 288;
        $bildpositionenhome['HOME_POS'][4]['heim']['oben'] = 113;
        $bildpositionenhome['HOME_POS'][4]['heim']['links'] = 397;
        $bildpositionenhome['HOME_POS'][5]['heim']['oben'] = 236;
        $bildpositionenhome['HOME_POS'][5]['heim']['links'] = 179;
        $bildpositionenhome['HOME_POS'][6]['heim']['oben'] = 236;
        $bildpositionenhome['HOME_POS'][6]['heim']['links'] = 288;
        $bildpositionenhome['HOME_POS'][7]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][7]['heim']['links'] = 69;
        $bildpositionenhome['HOME_POS'][8]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][8]['heim']['links'] = 233;
        $bildpositionenhome['HOME_POS'][9]['heim']['oben'] = 318;
        $bildpositionenhome['HOME_POS'][9]['heim']['links'] = 397;
        $bildpositionenhome['HOME_POS'][10]['heim']['oben'] = 400;
        $bildpositionenhome['HOME_POS'][10]['heim']['links'] = 233;
        $bildpositionenaway = array();
        $bildpositionenaway['AWAY_POS'][0]['heim']['oben'] = 970;
        $bildpositionenaway['AWAY_POS'][0]['heim']['links'] = 233;
        $bildpositionenaway['AWAY_POS'][1]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][1]['heim']['links'] = 69;
        $bildpositionenaway['AWAY_POS'][2]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][2]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][3]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][3]['heim']['links'] = 288;
        $bildpositionenaway['AWAY_POS'][4]['heim']['oben'] = 828;
        $bildpositionenaway['AWAY_POS'][4]['heim']['links'] = 397;
        $bildpositionenaway['AWAY_POS'][5]['heim']['oben'] = 746;
        $bildpositionenaway['AWAY_POS'][5]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][6]['heim']['oben'] = 746;
        $bildpositionenaway['AWAY_POS'][6]['heim']['links'] = 288;
        $bildpositionenaway['AWAY_POS'][7]['heim']['oben'] = 664;
        $bildpositionenaway['AWAY_POS'][7]['heim']['links'] = 69;
        $bildpositionenaway['AWAY_POS'][8]['heim']['oben'] = 664;
        $bildpositionenaway['AWAY_POS'][8]['heim']['links'] = 397;
        $bildpositionenaway['AWAY_POS'][9]['heim']['oben'] = 587;
        $bildpositionenaway['AWAY_POS'][9]['heim']['links'] = 179;
        $bildpositionenaway['AWAY_POS'][10]['heim']['oben'] = 587;
        $bildpositionenaway['AWAY_POS'][10]['heim']['links'] = 288;
       
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'rosterposition');
        $this->extended    = $extended;
        
        $mdlRosterpositions = BaseDatabaseModel::getInstance("rosterpositions", "sportsmanagementModel");
    
        /**
 * position ist vorhanden
 */
        if ($this->item->id ) {   
            $count_players = $this->item->players;
        
            /**
 * bearbeiten positionen übergeben
 */
            $position = 1;
            $jRegistry = new Registry;
        
            /**
 * welche joomla version ?
 */
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                $jRegistry->loadString($this->item->extended);
            }
        
            else
            {
                  $jRegistry->loadJSON($this->item->extended);
            }

            if (!$this->item->extended ) {
                $position = 1;
                switch ($this->item->alias)
                {
                case 'HOME_POS':
                    $bildpositionenhome = $mdlRosterpositions->getRosterHome();
    
                    for($a = 0; $a < $count_players; $a++)
                    {
                        $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenhome[$this->item->name][$a]['heim']['oben']);
                        $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenhome[$this->item->name][$a]['heim']['links']);
                        $position++;
                    }
                    $this->bildpositionen    = $bildpositionenhome;
                    break;
                case 'AWAY_POS':
                    $bildpositionenaway = $mdlRosterpositions->getRosterAway();
                    for($a = 0; $a < $count_players; $a++)
                    {
                        $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenaway[$this->item->name][$a]['heim']['oben']);
                        $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenaway[$this->item->name][$a]['heim']['links']);
                        $position++;
                    }
                    $this->bildpositionen    = $bildpositionenaway;
                    break;
                }
        
            }

            for($a = 0; $a < $count_players; $a++)
              {
                $bildpositionen[$this->item->name][$a]['heim']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
                $bildpositionen[$this->item->name][$a]['heim']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
                $position++;
            }
            $this->bildpositionen = $bildpositionen;  
   
        }
        else
        {
               // neuanlage
            $addposition    = $this->jinput->get('addposition');
            $position = 1;
            $object = new stdClass();
            $object->id = 0;
            $object->name = $addposition;
            $object->short_name = $addposition;
            $object->country = 'DEU';
            $object->picture = 'spielfeld_578x1050.png';
            $xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'extended'.DIRECTORY_SEPARATOR.'rosterposition.xml';
            $extended = JForm::getInstance(
                'extended', $xmlfile, array('control'=> 'extended'), 
                false, '/config'
            );
            $jRegistry = new Registry;
            $jRegistry->loadString('', 'ini');
            $extended->bind($jRegistry);
    
            switch ($addposition)
            {
            case 'HOME_POS':
                for($a = 0; $a < 11; $a++)
                {
                    $extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenhome[$object->name][$a]['heim']['oben']);
                    $extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenhome[$object->name][$a]['heim']['links']);
                    $position++;
                }
                   $this->bildpositionen    = $bildpositionenhome;
                break;
            case 'AWAY_POS':
                for($a = 0; $a < 11; $a++)
                {
                    $extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenaway[$object->name][$a]['heim']['oben']);
                    $extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenaway[$object->name][$a]['heim']['links']);
                    $position++;
                }
                $this->assignRef('bildpositionen', $bildpositionenaway);
                break;
            }
              $object->extended = $extended;
    
              $this->form->setValue('short_name', null, $object->short_name);
              $this->form->setValue('country', null, $object->country);
              $this->form->setValue('picture', null, $object->picture);
              $this->form->setValue('name', null, '4231');
    
            $this->item = $object;   
        }
        
        $javascript = "\n";
        $javascript .= 'jQuery(document).ready(function() {' . "\n";
        $start = 1;
        $ende = 11;
        for ($a = $start; $a <= $ende; $a++ )
        {
            $javascript .= '    jQuery("#draggable_'.$a.'").draggable({stop: function(event, ui) {
    	// Show dropped position.
    	var Stoppos = jQuery(this).position();
    	jQuery("div#stop").text("STOP: \nLeft: "+ Stoppos.left + "\nTop: " + Stoppos.top);
    	jQuery("#extended_COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$a.'_TOP").val(Stoppos.top);
      jQuery("#extended_COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$a.'_LEFT").val(Stoppos.left);
    }});' . "\n";    
        }
    
        $javascript .= '  });' . "\n";
        $javascript .= "\n";
    
        $this->document->addScriptDeclaration($javascript);
    
        $this->form    = $this->form;

    }
    
    /**
     * sportsmanagementViewrosterposition::addToolBar()
     * 
     * @return void
     */
    protected function addToolBar() 
    {
       
        $this->document->addScript(Uri::base().'components/'.$this->option.'/assets/js/sm_functions.js');
        $this->jinput->set('hidemainmenu', true);
        
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITION_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITION_NEW');
        $this->icon = 'rosterposition';
                    
          parent::addToolbar();
    }
    
    
    
    
    

}
?>
