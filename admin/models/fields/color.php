<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      color.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();

/**
 * JFormFieldColor
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldColor extends JFormFieldText
{
	protected $type = 'Color';

	/**
	 * JFormFieldColor::getInput()
	 * 
	 * @return
	 */
	public function getInput()
	{
		$document = &JFactory::getDocument();
		$document->addScript(JURI::base(). 'components/com_gcalendar/libraries/jscolor/jscolor.js' );
		return parent::getInput();
	}

	/**
	 * JFormFieldColor::setup()
	 * 
	 * @param mixed $element
	 * @param mixed $value
	 * @param mixed $group
	 * @return
	 */
	public function setup(& $element, $value, $group = null)
	{
		$return= parent::setup($element, $value, $group);
		$this->element['class'] = $this->element['class'].' color';
		return $return;
	}
}
?>