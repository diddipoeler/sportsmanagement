<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage transifex
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
JLoader::import('components.com_sportsmanagement.helpers.transifex', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementViewtransifex
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementViewtransifex extends sportsmanagementView
{

	
	/**
	 * sportsmanagementViewtransifex::init()
	 * 
	 * @return void
	 */
	public function init ()
	{

$lang = Factory::getLanguage();
$langtag = $lang->getTag();	
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' langtag<br><pre>'.print_r($langtag,true).'</pre>'),'');	
$code = sportsmanagementHelperTransifex::getLangCode($langtag);
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($code,true).'</pre>'),'');		
	
$result = sportsmanagementHelperTransifex::getData('');
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
$json_decode = json_decode($result['data']);
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' json_decode<br><pre>'.print_r($json_decode,true).'</pre>'),'');
	
$transifexlanguages = sportsmanagementHelperTransifex::getData('languages');	
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' transifexlanguages<br><pre>'.print_r($transifexlanguages,true).'</pre>'),'');	
$json_decode = json_decode($transifexlanguages['data']);
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' json_decode<br><pre>'.print_r($json_decode,true).'</pre>'),'');
	
$transifexresources = sportsmanagementHelperTransifex::getData('resources');	
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' transifexresources<br><pre>'.print_r($transifexresources,true).'</pre>'),'');		
$this->transifexresources = json_decode($transifexresources['data']);
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' transifexresources<br><pre>'.print_r($this->transifexresources,true).'</pre>'),'');        


foreach ( $this->transifexresources as $key => $value )
{
$resourceData = sportsmanagementHelperTransifex::getData('resource/' . $value->slug . '/stats');    
$this->app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' resource data<br><pre>'.print_r(json_decode($resourceData['data'])  ,true).'</pre>'),'');    
  
}

        

	}
    
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
        $this->jinput->set('hidemainmenu', true);
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TRANSIFEX');
        $this->icon = 'transifex';
        parent::addToolbar();
	}
    

    		

}
?>
