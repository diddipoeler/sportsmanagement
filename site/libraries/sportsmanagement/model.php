<?PHP
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       model.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * JSMModelAdmin
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class JSMModelAdmin extends AdminModel
{

	/**
	 * JSMModelAdmin::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Reference global application object
		$this->jsmapp = Factory::getApplication('site');
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
		$this->jsmview = $this->jsmjinput->getCmd('view');
		parent::__construct($config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{

	}

}

/**
 * JSMModelList
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class JSMModelList extends ListModel
{

	/**
	 * JSMModelList::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		parent::setDbo($this->jsmdb);
		$this->jsmquery = $this->jsmdb->getQuery(true);
		$this->jsmsubquery1 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery2 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery3 = $this->jsmdb->getQuery(true);

		// Reference global application object
		$this->jsmapp = Factory::getApplication('site');
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
		$this->jsmview = $this->jsmjinput->getCmd('view');

	}


}


/**
 * JSMModelLegacy
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class JSMModelLegacy extends BaseDatabaseModel
{

	/**
	 * JSMModelLegacy::__construct()
	 *
	 * @param   mixed $config
	 * @return void
	 */
	public function __construct($config = array())
	{
		/**
*
 * Reference global application object
*/
		$this->jsmapp = Factory::getApplication('site');
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
		$this->jsmview = $this->jsmjinput->getCmd('view');
		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		$this->jsmquery = $this->jsmdb->getQuery(true);

		/**
 * alle fehlermeldungen online ausgeben
 * mit der kategorie: jsmerror
 * JLog::INFO, JLog::WARNING, JLog::ERROR, JLog::ALL, JLog::EMERGENCY or JLog::CRITICAL
 */
		Log::addLogger(array('logger' => 'messagequeue'), Log::ALL, array('jsmerror'));
		/**
 * fehlermeldungen datenbankabfragen
 */
		Log::addLogger(array('logger' => 'database','db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dblog'));
		/**
 * laufzeit datenbankabfragen
 */
		Log::addLogger(array('logger' => 'database','db_table' => '#__sportsmanagement_log_entries'), Log::ALL, array('dbperformance'));

				parent::__construct($config);
	}


}
