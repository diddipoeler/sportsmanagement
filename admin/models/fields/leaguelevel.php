<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      leaguelevel.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}


jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldLeague_Level
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JFormFieldLeagueLevel extends FormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'leaguelevel';

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
		for($a=1; $a < 21; $a++ )
        {
            $options[] = JHtml::_('select.option', $a, JText::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LEVEL').' - '.$a);   
        }
	
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($options,true).'</pre>'),'');
        
		/**
         * Merge any additional options in the XML definition.
         */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
    
}
