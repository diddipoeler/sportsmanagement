<?php
/**
 * @author Wolfgang Pinitsch <andone@aon.at>
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * 
 * creates a selector to upload, select, reset, clear an image path
 * @author And_One
 * @see administrator/components/com_sportsmanagement/helpers/imageselect.php
 *
 */
class JFormFieldImageSelect extends JFormField
{
	protected $type = 'imageselect';

	function getInput() 
    {
        $mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        
		$default = $this->value;
		$arrPathes = explode('/', $default);
		$filename = array_pop($arrPathes);
		//$targetfolder = array_pop($arrPathes);
		$targetfolder = $this->element['targetfolder'];
        
        //$mainframe->enqueueMessage(JText::_('JFormFieldImageSelect targetfolder<br><pre>'.print_r($targetfolder,true).'</pre>'),'Notice');
        
		
// 		echo 'this->value -> '.$this->value.'<br>';
// 		echo 'this->name -> '.$this->name.'<br>';
// 		echo 'filename -> '.$filename.'<br>';
// 		echo 'targetfolder -> '.$targetfolder.'<br>';
		
		$output  = ImageSelectSM::getSelector($this->name, $this->name.'_preview', $targetfolder, $this->value, $default, $this->name, $this->id);
		$output .= '<img class="imagepreview" src="'.JURI::root(true).'/media/com_sportsmanagement/jl_images/spinner.gif" '; 
		$output .= ' name="'.$this->name.'_preview" id="'.$this->name.'_preview" border="3" alt="Preview" title="Preview" />';
		$output .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';
		return $output;
	}
}
