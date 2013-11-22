<?php


defined('_JEXEC') or die('Restricted access');


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
        
		$output  = ImageSelectSM::getSelector($this->name, $this->name.'_preview', $targetfolder, $this->value, $default, $this->name, $this->id);
		$output .= '<img class="imagepreview" src="'.JURI::root(true).'/media/com_sportsmanagement/jl_images/spinner.gif" '; 
		$output .= ' name="'.$this->name.'_preview" id="'.$this->name.'_preview" border="3" alt="Preview" title="Preview" />';
		$output .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';
		return $output;
	}
}
