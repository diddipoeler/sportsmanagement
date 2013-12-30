<?php
/**
 * @copyright	Copyright (C) 2006-2011 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementViewrosterposition extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $document = JFactory::getDocument();
        
        if ( JPluginHelper::isEnabled( 'system', 'jqueryeasy' ) )
        {
            $mainframe->enqueueMessage(JText::_('jqueryeasy ist installiert'),'Notice');
            $this->jquery = true;
        }
        else
        {
            $mainframe->enqueueMessage(JText::_('jqueryeasy ist nicht installiert'),'Error');
            $this->jquery = false;
        }
        
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
		$this->assignRef( 'extended', $extended );
        
        $mdlRosterpositions = JModel::getInstance("rosterpositions", "sportsmanagementModel");
        $bildpositionenhome = $mdlRosterpositions->getRosterHome();
        $bildpositionenaway = $mdlRosterpositions->getRosterAway();
     
     //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition extended<br><pre>'.print_r($this->item->extended,true).'</pre>'),'Notice');
     //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition getRosterHome<br><pre>'.print_r($bildpositionenhome,true).'</pre>'),'Notice');
     //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition getRosterAway<br><pre>'.print_r($bildpositionenaway,true).'</pre>'),'Notice');
     
     if ( $this->item->id )   
     {   
        // bearbeiten positionen �bergeben
    $position = 1;
    //$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($this->item->extended, 'ini');
    
    if ( !$this->item->extended )
    {
    $position = 1;
    switch ($this->item->alias)
    {
    case 'HOME_POS':
    for($a=0; $a < 11; $a++)
    {
    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenhome[$this->item->name][$a]['heim']['oben']);
    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenhome[$this->item->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenhome);
    break;
    case 'AWAY_POS':
    for($a=0; $a < 11; $a++)
    {
    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenaway[$this->item->name][$a]['heim']['oben']);
    $jRegistry->setValue('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenaway[$this->item->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenaway);
    break;
    }
        
    }
    
    //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition jRegistry<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$this->item->name][$a]['heim']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$this->item->name][$a]['heim']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionen);  
    }
    
        /*
        if ( !$this->item->extended )
        {
            $position = 1;
        switch ($this->item->alias)
    {
    case 'HOME_POS':
    for($a=0; $a < 11; $a++)
    {
    $this->extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenhome[$this->item->name][$a]['heim']['oben']);
    $this->extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenhome[$this->item->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenhome);
    break;
    case 'AWAY_POS':
    for($a=0; $a < 11; $a++)
    {
    $this->extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenaway[$this->item->name][$a]['heim']['oben']);
    $this->extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenaway[$this->item->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenaway);
    break;
    }    
        }
        */
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition extended<br><pre>'.print_r($this->item->extended,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewrosterposition extended<br><pre>'.print_r($this->extended,true).'</pre>'),'');
        
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
    
        $this->assignRef('form', $this->form);
        $this->assignRef('option', $option);
        
        // Set the toolbar
		$this->addToolBar();
		parent::display($tpl);
        // Set the document
		$this->setDocument();
	}

	function _displayForm($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe =& JFactory::getApplication();
    $document = JFactory::getDocument();
		$db =& JFactory::getDBO();
		$uri =& JFactory::getURI();
		$user =& JFactory::getUser();
		$model =& $this->getModel();
        
    $edit	= JRequest::getVar('edit',true);
    $addposition	= JRequest::getVar('addposition');
    $this->assignRef('edit',$edit);
		$lists=array();
		//get the project
		$object =& $this->get('data');
		$isNew=($object->id < 1);

$bildpositionenhome = array();
$bildpositionenhome[HOME_POS][0][heim][oben] = 5;
$bildpositionenhome[HOME_POS][0][heim][links] = 233;
$bildpositionenhome[HOME_POS][1][heim][oben] = 113;
$bildpositionenhome[HOME_POS][1][heim][links] = 69;
$bildpositionenhome[HOME_POS][2][heim][oben] = 113;
$bildpositionenhome[HOME_POS][2][heim][links] = 179;
$bildpositionenhome[HOME_POS][3][heim][oben] = 113;
$bildpositionenhome[HOME_POS][3][heim][links] = 288;
$bildpositionenhome[HOME_POS][4][heim][oben] = 113;
$bildpositionenhome[HOME_POS][4][heim][links] = 397;
$bildpositionenhome[HOME_POS][5][heim][oben] = 236;
$bildpositionenhome[HOME_POS][5][heim][links] = 179;
$bildpositionenhome[HOME_POS][6][heim][oben] = 236;
$bildpositionenhome[HOME_POS][6][heim][links] = 288;
$bildpositionenhome[HOME_POS][7][heim][oben] = 318;
$bildpositionenhome[HOME_POS][7][heim][links] = 69;
$bildpositionenhome[HOME_POS][8][heim][oben] = 318;
$bildpositionenhome[HOME_POS][8][heim][links] = 233;
$bildpositionenhome[HOME_POS][9][heim][oben] = 318;
$bildpositionenhome[HOME_POS][9][heim][links] = 397;
$bildpositionenhome[HOME_POS][10][heim][oben] = 400;
$bildpositionenhome[HOME_POS][10][heim][links] = 233;
$bildpositionenaway = array();
$bildpositionenaway[AWAY_POS][0][heim][oben] = 970;
$bildpositionenaway[AWAY_POS][0][heim][links] = 233;
$bildpositionenaway[AWAY_POS][1][heim][oben] = 828;
$bildpositionenaway[AWAY_POS][1][heim][links] = 69;
$bildpositionenaway[AWAY_POS][2][heim][oben] = 828;
$bildpositionenaway[AWAY_POS][2][heim][links] = 179;
$bildpositionenaway[AWAY_POS][3][heim][oben] = 828;
$bildpositionenaway[AWAY_POS][3][heim][links] = 288;
$bildpositionenaway[AWAY_POS][4][heim][oben] = 828;
$bildpositionenaway[AWAY_POS][4][heim][links] = 397;
$bildpositionenaway[AWAY_POS][5][heim][oben] = 746;
$bildpositionenaway[AWAY_POS][5][heim][links] = 179;
$bildpositionenaway[AWAY_POS][6][heim][oben] = 746;
$bildpositionenaway[AWAY_POS][6][heim][links] = 288;
$bildpositionenaway[AWAY_POS][7][heim][oben] = 664;
$bildpositionenaway[AWAY_POS][7][heim][links] = 69;
$bildpositionenaway[AWAY_POS][8][heim][oben] = 664;
$bildpositionenaway[AWAY_POS][8][heim][links] = 397;
$bildpositionenaway[AWAY_POS][9][heim][oben] = 587;
$bildpositionenaway[AWAY_POS][9][heim][links] = 179;
$bildpositionenaway[AWAY_POS][10][heim][oben] = 587;
$bildpositionenaway[AWAY_POS][10][heim][links] = 288;

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id')))
		{
			$msg=JText::sprintf('DESCBEINGEDITTED',JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITION'),$object->name);
			$mainframe->redirect('index.php?option='.$option,$msg);
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout($user->get('id'));
		}
		else
		{
			// initialise new record
			$object->order=0;
		}

		//build the html select list for countries
		$countries[]=JHtml::_('select.option','',JText::_('COM_JOOMLEAGUE_ADMIN_ROSTERPOSITIONS_SELECT_COUNTRY'));
		if ($res =& Countries::getCountryOptions()){$countries=array_merge($countries,$res);}
		$lists['countries']=JHtml::_('select.genericlist',$countries,'country','class="inputbox" size="1"','value','text',$object->country);
		unset($countries);

		// build the html select list for ordering
		$query='SELECT ordering AS value,name AS text FROM	#__joomleague_rosterposition ORDER BY ordering ';
		$lists['ordering']=JHtml::_('list.specificordering',$object,$object->id,$query,1);

    
		
//     $document->addScript( JURI::base(true).'/components/com_joomleague/assets/js/dragpull.js');
    
    /*
    * extended data
    */
//     echo JPATH_COMPONENT.'<br>';
//     echo JPATH_COMPONENT_SITE.'<br>';
    //$paramsdata=$object->extended;
    
//    $paramsdefs=JPATH_COMPONENT.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
//     echo $paramsdefs.'<br>';
//    $extended=new JLGExtraParams($paramsdata,$paramsdefs);
    $this->assignRef('form'      	, $this->get('form'));
    $extended = $this->getExtended($object->extended, 'rosterposition');
    $this->assignRef('extended',$extended);

    $this->assign('show_debug_info', JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0) );
    $this->assign('jquery_version', JComponentHelper::getParams('com_joomleague')->get('jqueryversionfrontend',0) );
    $this->assign('jquery_sub_version', JComponentHelper::getParams('com_joomleague')->get('jquerysubversionfrontend',0) );
    $this->assign('jquery_ui_version', JComponentHelper::getParams('com_joomleague')->get('jqueryuiversionfrontend',0) );
    $this->assign('jquery_ui_sub_version', JComponentHelper::getParams('com_joomleague')->get('jqueryuisubversionfrontend',0) );
		
    if (!$this->edit)
		{
    // neu
    $position = 1;
    $object->name = $addposition;
    $object->short_name = $addposition;
    $xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
    $extended = JForm::getInstance('extended', $xmlfile,array('control'=> 'extended'),
				false, '/config');
    $jRegistry = new JRegistry;
$jRegistry->loadString('' , 'ini');
$extended->bind($jRegistry);


    
    
    
    switch ($addposition)
    {
    case 'HOME_POS':
    for($a=0; $a < 11; $a++)
    {
    $extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenhome[$object->name][$a]['heim']['oben']);
    $extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenhome[$object->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenhome);
    break;
    case 'AWAY_POS':
    for($a=0; $a < 11; $a++)
    {
    $extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_TOP', null,$bildpositionenaway[$object->name][$a]['heim']['oben']);
    $extended->setValue('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_LEFT', null,$bildpositionenaway[$object->name][$a]['heim']['links']);
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionenaway);
    break;
    }
    $object->extended = $extended;
    }
    else
    {
    // bearbeiten positionen �bergeben
    $position = 1;
    //$xmlfile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		$jRegistry->loadString($object->extended, 'ini');
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$object->name][$a]['heim']['oben'] = $jRegistry->get('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$object->name][$a]['heim']['links'] = $jRegistry->get('COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
    $this->assignRef('bildpositionen',$bildpositionen);
    }
    
    $project_type=array (	JHtmlSelect::option('HOME_POS',JText::_('HOME_POS'),'id','name'),
								JHtmlSelect::option('AWAY_POS',JText::_('AWAY_POS'),'id','name')
							);
		$lists['project_type']=JHtmlSelect::genericlist($project_type,'short_name','class="inputbox" size="1"','id','name',$object->short_name);
		unset($project_type);
    
    // Add Script
//$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/'.$this->jquery_version.'/jquery.min.js');
//$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/'.$this->jquery_ui_version.'.'.$this->jquery_ui_sub_version.'/jquery-ui.min.js');



//$javascript .= "\n".'var $JoLe2 = jQuery.noConflict();' . "\n";
$javascript .= "\n";
$javascript .= 'jQuery(document).ready(function() {' . "\n";

$start = 1;
$ende = 11;
for ($a = $start; $a <= $ende; $a++ )
{
$javascript .= '    jQuery("#draggable_'.$a.'").draggable({stop: function(event, ui) {
    	// Show dropped position.
    	var Stoppos = jQuery(this).position();
    	jQuery("div#stop").text("STOP: \nLeft: "+ Stoppos.left + "\nTop: " + Stoppos.top);
    	jQuery("#extended_COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$a.'_TOP").val(Stoppos.top);
      jQuery("#extended_COM_JOOMLEAGUE_EXT_ROSTERPOSITIONS_'.$a.'_LEFT").val(Stoppos.left);
    }});' . "\n";    
}


    
$javascript .= '  });' . "\n";

$javascript .= "\n";


    
    $document->addScriptDeclaration( $javascript );
    $this->assignRef('form'      	, $this->get('form'));
    $this->assignRef('lists',$lists);
		$this->assignRef('object',$object);

		parent::display($tpl);
	}
    
    /**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_ROSTERPOSITION_NEW') : JText::_('COM_SPORTSMANAGEMENT_ROSTERPOSITION_EDIT'), 'rosterposition');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('rosterposition.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('rosterposition.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('rosterposition.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('rosterposition.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
		          
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('rosterposition.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('rosterposition.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('rosterposition.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('rosterposition.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('rosterposition.cancel', 'JTOOLBAR_CLOSE');
		}
	}
    
    
    
    /**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    

}
?>
