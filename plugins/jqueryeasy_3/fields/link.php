<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldLink extends JFormField {
		
	public $type = 'Link';

	/**
	 * Method to get the field options.
	 */
	protected function getLabel() {
		
		$html = '';
		
		$title = trim($this->element['title']);
		$image_src = $this->element['imagesrc']; // path ex: ../modules/mod_latestnews/images/icon.png
		$link = $this->element['link'];
		
		$html .= '<div style="clear: both;">';
		
		$html .= '<a href="'.$link.'" target="_blank" title="'.JText::_($title).'">';
		if ($image_src) {
			$html .= '<img src="'.$image_src.'" alt="'.JText::_($title).'">';
		} else {
			$html .= JText::_($title);
		}
		$html .= '</a>';
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Method to get the field input markup.
	 */
	protected function getInput() {
		
		$title = trim($this->element['title']);
		$text = trim($this->element['text']);
		$link = $this->element['link'];
		
		$titleintext = false;
		if ($this->element['titleintext']) {
			$titleintext = ($this->element['titleintext'] === 'true');
		}
		
		$html = '';
		
		$html .= '<div style="padding-top: 5px">';
			
		if ($titleintext) {
			$html .= '<strong>'.JText::_($title).'</strong>: ';
		}
				
		if ($text) {
			$html .= JText::sprintf($text, $link);
		}
		
		$html .= '</div>';

		return $html;
	}

}
?>