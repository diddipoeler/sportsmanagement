<?php
/**
 * @copyright	Copyright (C) 2005-2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'countries.php');
jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldCountry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class JFormFieldCountry extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'Country';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialize variables.
		$options = Countries::getCountryOptions();
		
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($options,true).'</pre>'),'');
        
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
