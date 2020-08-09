<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmopenligadb
 * @file       jsmopenligadb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

$maxImportTime = 480;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = '350M';

if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	@ini_set('memory_limit', $maxImportMemory);
}

/**
 * sportsmanagementModeljsmopenligadb
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeljsmopenligadb extends BaseDatabaseModel
{
	static public $success_text = '';
	var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
	var $success_text_teams = '';
	var $success_text_results = '';
    
    /**
     * sportsmanagementModeljsmopenligadb::getMatchLink()
     * 
     * @param mixed $projectid
     * @return
     */
    function getMatchLink($projectid)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$post   = Factory::getApplication()->input->post->getArray(array());

		if ($app->isClient('administrator'))
		{
			$view = Factory::getApplication()->input->getVar('view');
		}
		else
		{
			$view = 'jsmopenligadb';
		}

		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
try{
		$query->select('ev.fieldvalue');
		$query->from('#__sportsmanagement_user_extra_fields_values as ev ');
		$query->join('INNER', '#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
		$query->where('ev.jl_id = ' . $projectid);
		$query->where('ef.name LIKE ' . $db->Quote('' . $view . ''));
		$query->where('ef.template_backend LIKE ' . $db->Quote('' . 'project' . ''));
		$query->where('ef.field_type LIKE ' . $db->Quote('' . 'link' . ''));
		$db->setQuery($query);
		$derlink = $db->loadResult();
        }
catch (Exception $e)
{
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');	
}

		return $derlink;
	}
    
    
    /**
     * sportsmanagementModeljsmopenligadb::getdata()
     * 
     * @param mixed $projectlink
     * @return void
     */
    function getdata($projectlink)
	{
$http = JHttpFactory::getHttp(null, array('curl', 'stream'));
$result  = $http->get($projectlink );
$matches = json_decode($result->body, true);





return $matches;
	}
    
    
}
    
?>