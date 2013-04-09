<?php
/**
 * @author Wolfgang Pinitsch <andone@aon.at>
 * @copyright	Copyright (C) 2007-2013 JoomLeague.net. All rights reserved.
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
 * @see administrator/components/com_joomleague/helpers/imageselect.php
 *
 */
class JFormFieldImageSelect extends JFormField
{
	protected $type = 'imageselect';

	function getInput() {
		$default = $this->value;
		$arrPathes = explode('/', $default);
		$filename = array_pop($arrPathes);
		$targetfolder = array_pop($arrPathes);
		$output  = ImageSelect::getSelector($this->name, $this->name.'_preview', $targetfolder, $this->value, $default, $this->name, $this->id);
		$output .= '<img class="imagepreview" src="'.JURI::root(true).'/media/com_joomleague/jl_images/spinner.gif" '; 
		$output .= ' name="'.$this->name.'_preview" id="'.$this->name.'_preview" border="3" alt="Preview" title="Preview" />';
		$output .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';
		return $output;
	}
}
