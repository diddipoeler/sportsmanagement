<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      transifex.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
JLoader::import('components.com_sportsmanagement.helpers.transifex', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementControllertransifex
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllertransifex extends JSMControllerAdmin
{
    /**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
    }  
	
/**
 * sportsmanagementControllertransifex::gettransifexinfo()
 * 
 * @return void
 */
function gettransifexinfo()	
{
    
//$lang = Factory::getLanguage();
//$langtag = $lang->getTag();	
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' langtag<br><pre>'.print_r($langtag,true).'</pre>'),'');	
//$code = sportsmanagementHelperTransifex::getLangCode($langtag);
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($code,true).'</pre>'),'');		
//	
//$result = sportsmanagementHelperTransifex::getData('');
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
//$json_decode = json_decode($result['data']);
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' json_decode<br><pre>'.print_r($json_decode,true).'</pre>'),'');
//	
//$transifexlanguages = sportsmanagementHelperTransifex::getData('languages');	
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' transifexlanguages<br><pre>'.print_r($transifexlanguages,true).'</pre>'),'');	
//$json_decode = json_decode($transifexlanguages['data']);
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' json_decode<br><pre>'.print_r($json_decode,true).'</pre>'),'');
//	
//$transifexresources = sportsmanagementHelperTransifex::getData('resources');	
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' transifexresources<br><pre>'.print_r($transifexresources,true).'</pre>'),'');		
//$json_decode = json_decode($transifexresources['data']);
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' json_decode<br><pre>'.print_r($json_decode,true).'</pre>'),'');
	
	
	
}
	
	
	
}
