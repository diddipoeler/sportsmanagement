<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');
jimport('joomla.version');

class JFormFieldMessage extends JFormField {
		
	public $type = 'Message';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
				
		$html = '';
		
		$message_type = trim($this->element['style']);
		
		if ($message_type == 'example') {			
			$html .= '<label style="visibility: hidden; margin: 0px; font-size: 1px;">Example</label>';
		} else {
			$html .= '<div style="clear: both;"></div>';
		}		
		
		return $html;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		
		$html = '';
			
		$version = new JVersion();
		$jversion = explode('.', $version->getShortVersion());
		
		$message = trim($this->element['text']);
		$message_type = trim($this->element['style']);
		
		if ($message_type == 'example') {
			if (intval($jversion[0]) > 2) {
				$html .= '<span class="muted" style="font-size: 0.8em;">';
			} else {
				$html .= '<span style="float: left; color: #999999; margin-bottom: 10px">';
			}
			if ($message) {
				$html .= JText::_($message);
			}
			$html .= '</span>';
		} else {
			$style = '';
			switch ($message_type) {
				case 'warning':
					if (intval($jversion[0]) > 2) {
						$style = 'alert-warning';
					} else {
						$style = 'border: 1px solid #FBEED5; background-color: #FCF8E3; color: #C09853;';
					}
					break;
				case 'error':
					if (intval($jversion[0]) > 2) {
						$style = 'alert-error';
					} else {
						$style = 'border: 1px solid #EED3D7; background-color: #F2DEDE; color: #B94A48;';
					}
					break;
				case 'info':
					if (intval($jversion[0]) > 2) {
						$style = 'alert-info';
					} else {
						$style = 'border: 1px solid #BCE8F1; background-color: #D9EDF7; color: #3A87AD';
					}
					break;
				default: /* message/success */
					if (intval($jversion[0]) > 2) {
						$style = 'alert-success';
					} else {
						$style = 'border: 1px solid #D6E9C6; background-color: #DFF0D8; color: #468847;';
					}
					break;
			}
			
			if (intval($jversion[0]) > 2) {
				$html .= '<div class="alert '.$style.'">';
			} else {
				$html .= '<div style="margin: 5px 0; padding: 8px 35px 8px 14px; border-radius: 4px; '.$style.'">';
			}
			$html .= '<span>';
			if ($message) {
				$html .= JText::_($message);
			}
			$html .= '</span>';
			$html .= '</div>';
		}
		
		return $html;
	}

}
?>