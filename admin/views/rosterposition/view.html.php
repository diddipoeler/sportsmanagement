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

JHtml::_('jquery.ui');

/**
 * sportsmanagementViewrosterposition
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewrosterposition extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewrosterposition::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $document = JFactory::getDocument();
        $document->addScript('http://code.jquery.com/ui/1.10.3/jquery-ui.js');
        
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

/*
        if ( JPluginHelper::isEnabled( 'system', 'jqueryeasy' ) )
        {
            $app->enqueueMessage(JText::_('jqueryeasy ist installiert'),'Notice');
            $this->jquery = true;
        }
        else
        {
            $app->enqueueMessage(JText::_('jqueryeasy ist nicht installiert'),'Error');
            $this->jquery = false;
        }
*/        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
		$extended = sportsmanagementHelper::getExtended($item->extended, 'rosterposition');
		$this->extended	= $extended;
		
		$mdlRosterpositions = JModelLegacy::getInstance("rosterpositions", "sportsmanagementModel");
        //$bildpositionenhome = $mdlRosterpositions->getRosterHome();
//        $bildpositionenaway = $mdlRosterpositions->getRosterAway();
     
     //$app->enqueueMessage(JText::_('sportsmanagementViewrosterposition extended<br><pre>'.print_r($this->item->extended,true).'</pre>'),'Notice');
     //$app->enqueueMessage(JText::_('sportsmanagementViewrosterposition getRosterHome<br><pre>'.print_r($bildpositionenhome,true).'</pre>'),'Notice');
     //$app->enqueueMessage(JText::_('sportsmanagementViewrosterposition getRosterAway<br><pre>'.print_r($bildpositionenaway,true).'</pre>'),'Notice');
     
     // position ist vorhanden
	if ( $this->item->id )  
	{   
		$count_players = $this->item->players;
        
        // bearbeiten positionen übergeben
		$position = 1;
    //$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		//$jRegistry->loadString($this->item->extended, 'ini');
        
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
		{
			$jRegistry->loadString($this->item->extended);
		}
		
		else
		{
        $jRegistry->loadJSON($this->item->extended);
		}

	if ( !$this->item->extended )
	{
	$position = 1;
	switch ($this->item->alias)
	{
	case 'HOME_POS':
	$bildpositionenhome = $mdlRosterpositions->getRosterHome();
	
    for($a = 0; $a < $count_players; $a++)
	{
	$jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenhome[$this->item->name][$a]['heim']['oben']);
	$jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenhome[$this->item->name][$a]['heim']['links']);
	$position++;
	}
	$this->bildpositionen	= $bildpositionenhome;
	break;
	case 'AWAY_POS':
	$bildpositionenaway = $mdlRosterpositions->getRosterAway();
	for($a = 0; $a < $count_players; $a++)
	{
	$jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenaway[$this->item->name][$a]['heim']['oben']);
	$jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenaway[$this->item->name][$a]['heim']['links']);
	$position++;
	}
	$this->bildpositionen	= $bildpositionenaway;
	break;
	}
        
	}
    
    
//    for($a=$count_players; $a < 11; $a++)
//    {
//    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,'');
//    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,'');
//    $position++;
//    }
    
    //$app->enqueueMessage(JText::_('sportsmanagementViewrosterposition jRegistry<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
    
	for($a = 0; $a < $count_players; $a++)
    {
    //if ( $a < $count_players )
//    {    
	$bildpositionen[$this->item->name][$a]['heim']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
	$bildpositionen[$this->item->name][$a]['heim']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
   // }
//    else
//    {
//    $bildpositionen[$this->item->name][$a]['heim']['oben'] = '';
//    $bildpositionen[$this->item->name][$a]['heim']['links'] = '';    
//    }
	$position++;
	}
	$this->bildpositionen = $bildpositionen;  
    
    //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' jRegistry<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
    //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' bildpositionen<br><pre>'.print_r($this->bildpositionen,true).'</pre>'),'Notice');
    
	}
	else
	{
        // neuanlage
	$addposition	= $jinput->get('addposition');
	$position = 1;
	$object = new stdClass();
	$object->id = 0;
	$object->name = $addposition;
	$object->short_name = $addposition;
	$object->country = 'DEU';
	$object->picture = 'spielfeld_578x1050.png';
	$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
	$extended = JForm::getInstance('extended', $xmlfile,array('control'=> 'extended'), 
				false, '/config');
	$jRegistry = new JRegistry;
	$jRegistry->loadString('' , 'ini');
	$extended->bind($jRegistry);


    //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' addposition<br><pre>'.print_r($addposition,true).'</pre>'),'Notice');
    
    
	switch ($addposition)
	{
	case 'HOME_POS':
	for($a = 0; $a < 11; $a++)
	{
	$extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenhome[$object->name][$a]['heim']['oben']);
	$extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenhome[$object->name][$a]['heim']['links']);
	$position++;
	}
	$this->bildpositionen	= $bildpositionenhome;
	break;
	case 'AWAY_POS':
	for($a = 0; $a < 11; $a++)
	{
	$extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null, $bildpositionenaway[$object->name][$a]['heim']['oben']);
	$extended->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null, $bildpositionenaway[$object->name][$a]['heim']['links']);
	$position++;
	}
	$this->assignRef('bildpositionen',$bildpositionenaway);
	break;
	}
    $object->extended = $extended;
    
    $this->form->setValue('short_name', null, $object->short_name);
    $this->form->setValue('country', null, $object->country);
    $this->form->setValue('picture', null, $object->picture);
    $this->form->setValue('name', null,'4231');
    
	$this->item = $object;   
	}
    
    //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' bildpositionen<br><pre>'.print_r($this->bildpositionen,true).'</pre>'),'Notice');
    //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');
        

        
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
    
	$document->addScriptDeclaration( $javascript );
    
	$this->form	= $this->form;
	$this->option	= $option;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item -> <br><pre>'.print_r($this->item,true).'</pre>'),'');
        
	//$this->setLayout('edit');
	}


    
    
	/**
	 * sportsmanagementViewrosterposition::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	// Get a refrence of the page instance in joomla
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $document = JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        
		$document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
		$jinput->set('hidemainmenu', true);
        
		$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ROSTERPOSITION_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ROSTERPOSITION_NEW');
		$this->icon = 'rosterposition';
        			
    parent::addToolbar();
    }
    
    
    
    
    

}
?>
