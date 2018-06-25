<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      country.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

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
		$app = JFactory::getApplication();
        $option = $app->input->getCmd('option');
        /**
         * Initialize variables.
         */
		$options = JSMCountries::getCountryOptions();
		
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($options,true).'</pre>'),'');
        
		/**
         * Merge any additional options in the XML definition.
         */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
    
}
