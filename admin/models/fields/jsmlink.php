<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */


defined('_JEXEC') or die ;
use Joomla\CMS\Language\Text;
jimport('joomla.form.formfield');

class JFormFieldJSMLink extends FormField {
		
	public $type = 'JSMLink';

	/**
	 * Method to get the field options.
	 */
	protected function getLabel() {
		
		$html = '';
		
		$title = trim($this->element['title']);
		$image_src = $this->element['imagesrc']; // path ex: ../modules/mod_latestnews/images/icon.png
		$link = $this->element['link'];
		
		$html .= '<div style="overflow: hidden; margin: 5px 0">';
		$html .= '<label style="margin: 0">';
		
		$html .= '<a href="'.$link.'" target="_blank" title="'.Text::_($title).'">';
		if ($image_src) {
			$html .= '<img src="'.$image_src.'" alt="'.Text::_($title).'">';
		} else {
			$html .= Text::_($title);
		}
		$html .= '</a>';
		
		$html .= '</label>';
		
		return $html;
	}

	/**
	 * Method to get the field input markup.
	 */
	protected function getInput() {
		
		$title = trim($this->element['title']);
		$image_src = $this->element['imagesrc'];
		$text = trim($this->element['text']);
		$link = $this->element['link'];
		
		$titleintext = false;
		if ($this->element['titleintext']) {
			$titleintext = ($this->element['titleintext'] === 'true');
		}
		
		$html = '';
		
		if ($image_src) {
			$html .= '<div style="padding: 5px 0 0 0; overflow: inherit">';
		} else {
			$html .= '<div style="padding: 0; overflow: inherit">';
		}
			
		if ($titleintext) {
			$html .= '<strong>'.Text::_($title).'</strong>: ';
		}
				
		if ($text) {
			$html .= Text::sprintf($text, $link);
		}
		
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}
?>