<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewjlextlmoimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewjlextlmoimports extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
    $lang = JFactory::getLanguage();
    $document	= JFactory::getDocument();
    
	
    $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/'.$option.'/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
    $document->addCustomTag($stylelink);
    
    // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_LMO_IMPORT_TITLE_1'),'lmo-cpanel');
		//JLToolBarHelper::onlinehelp();

		$uri = JFactory::getURI();
		$config = JComponentHelper::getParams('com_media');
		$post=JRequest::get('post');
		$files=JRequest::get('files');

		$this->assign('request_url',$uri->toString());
		$this->assignRef('config',$config);
		$teile = explode("-",$lang->getTag());
    $country = Countries::convertIso2to3($teile[1]);
    $this->assignRef('country',$country);
		$countries = Countries::getCountryOptions();
		$lists['countries']=JHTML::_('select.genericlist',$countries,'country','class="inputbox" size="1"','value','text',$country);
		$this->assignRef('countries',$lists['countries']);
    

    //$this->assignRef('form',  $this->get('form'));
    
		parent::display($tpl);
	}



  
}
?>
