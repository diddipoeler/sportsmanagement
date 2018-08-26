<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      googleapikey.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

//JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);
FormHelper::loadFieldClass('list');


/**
 * FormFieldGoogleApiKey
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class FormFieldGoogleApiKey extends FormField
{
    
	protected $type = 'GoogleApiKey';


	/**
	 * FormFieldGoogleApiKey::getOptions()
	 * 
	 * @return
	 */
	protected function getOptions()
    {
	$google_api_key = JComponentHelper::getParams('com_sportsmanagement')->get('google_api_developerkey','');	
		return $google_api_key;
	}
    
}