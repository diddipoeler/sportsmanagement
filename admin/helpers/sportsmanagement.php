<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * toolbar
 * https://issues.joomla.org/tracker/joomla-cms/19670
 */
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\Button\PopupButton;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\CMS\Form\Form;
//BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

HTMLHelper::_('behavior.keepalive');

if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	jimport('joomla.html.toolbar');
}

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);

if (version_compare($baseVersion, '4.0', 'ge'))
{
	// Joomla! 4.0 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}


if (version_compare($baseVersion, '3.0', 'ge'))
{
	// Joomla! 3.0 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}


if (version_compare($baseVersion, '2.5', 'ge'))
{
	// Joomla! 2.5 code here
	defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}

/**
 * sportsmanagementHelper
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
abstract class sportsmanagementHelper
{
	static $latitude = '';
	static $longitude = '';
	static $_jsm_db = '';
	static $_success_text = array();
    
    /** @var    array    An array of tips */
	static $_tips = array();
	/** @var    array    An array of warnings */
	static $_warnings = array();
    /** @var    array    An array of notes */
	static $_notes = array();
    
    /**
     * sportsmanagementHelper::getTips()
     * 
     * @return
     */
    public static function getTips()
	{
		return self::$_tips;
	}
    
    /**
     * sportsmanagementHelper::getWarnings()
     * 
     * @return
     */
    public static function getWarnings()
	{
		return self::$_warnings;
	}
    
    /**
     * sportsmanagementHelper::getNotes()
     * 
     * @return
     */
    public static function getNotes()
	{
		return self::$_notes;
	}
    
    /**
     * sportsmanagementHelper::setTip()
     * 
     * @param mixed $tip
     * @return void
     */
    public static function setTip($tip)
	{
		self::$_tips[] = $tip;
	}
    
    /**
     * sportsmanagementHelper::setWarning()
     * 
     * @param mixed $warning
     * @return void
     */
    public static function setWarning($warning)
	{
		self::$_warnings[] = $warning;
	}
    /**
     * sportsmanagementHelper::setNote()
     * 
     * @param mixed $note
     * @return void
     */
    public static function setNote($note)
	{
		self::$_notes[] = $note;
	}

	/**
	 * Record transaction details in log record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $tran_id  The transaction id just created or updated
	 * @param   int     $id  Passed id reference from the form to identify if new record
	 * @return  boolean	True
	 */
    public static function recordActionLog($user = null, $tran = null, $id = 0)
	{
	// get the component details such as the id
	//$extension =  MycomponentHelper::getExtensionDetails('com_sportsmanagement');
	$extension = 'com_sportsmanagement';
	// get the transaction details for use in the log for easy reference
        //$tran = MycomponentHelper::getTransaction($tran_id);
        $con_type = Factory::getApplication()->input->getCmd('view', 'cpanel');
        if ($id === 0) { $type = Text::_('JTOOLBAR_NEW'); } else { $type = Text::_('JLIB_INSTALLER_UPDATE'); }

		$message = array();
		$message['action'] = $con_type;
	    switch ( $con_type )
	    {
		    case 'player':
		$message['type'] = $type .' '. $tran['firstname'].' '. $tran['lastname'];	    
			    break;
		    default:
		$message['type'] = $type .' '. $tran['name'];	    
			    break;
	    }
		$message['id'] = $tran['id'];
		$message['title'] = $extension;
		$message['extension_name'] = $extension;
		$message['itemlink'] = "index.php?option=com_sportsmanagement&task=".$con_type.".edit&id=".$tran['id'];
		$message['userid'] = $user->id;
		$message['username'] = $user->username;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);
		
		$messageLanguageKey = Text::_('COM_SPORTSMANAGEMENT_TRANSACTION_LINK');
		$context = $extension.'.'.$con_type;
		
/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
/** @var ActionlogModel $model */
		$fmodel = new ActionlogModel;	
}
	    elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	        /** @var ActionlogsModelActionlog $model **/
		    BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');
		$fmodel = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
	    }
		//$model->addLog($messages, strtoupper($messageLanguageKey), $context, $userId);
	    try{
		$fmodel->addLog($messages, $messageLanguageKey, $context, $user->id);
	    }
		    catch (Exception $e)
			{
		    }

		return true;
	}

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name. Optional. Default to my own for safety.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 */
	public function getForeignModel($name = 'Transaction', $prefix = 'MycomponentModel', $config = array('ignore_request' => true))
	{
		\Joomla\CMS\MVC\Model\ItemModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModelActionlog');
		$fmodel = \Joomla\CMS\MVC\Model\ItemModel::getInstance($name, $prefix, $config);

		return $fmodel;
	}
    
    
	/**
	 * sportsmanagementHelper::formatselect2output()
	 * 
	 * @param mixed $daten
	 * @param string $placeholder
	 * @param string $class
	 * @return
	 */
	function formatselect2output($daten=array(),$placeholder='',$class='' )
	{
?>
<script type="text/javascript">
   // (function () {
        // altered decision fields management
        //toggle_altdecision();
//	jQuery('#jform_alt_decision0').change(toggle_altdecision);
//    jQuery('#jform_alt_decision1').change(toggle_altdecision);
   // });
var <?php echo $placeholder; ?> = new Array;
			<?php
			foreach ($daten as $key => $value)
			{
				if (!$value->itempicture)
				{
					$value->itempicture = sportsmanagementHelper::getDefaultPlaceholder($placeholder);
				}

				echo $placeholder.'[' . ($key) . ']=\'' . $value->itempicture . "';\n";
			}
			?>
</script>
<?php		
	// String $opt - second parameter of formbehavior2::select2
	// for details http://ivaynberg.github.io/select2/
	$opt = ' allowClear: true,
   width: "100%",
   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = '.$placeholder.'[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },
 
   escapeMarkup: function(m) { return m; }
';
	
	return $opt;
	}
	
	

	/**
	 * sportsmanagementHelper::getBootstrapModalImage()
	 * 
	 * @param string $target
	 * @param string $picture
	 * @param string $text
	 * @param string $picturewidth
	 * @param string $url
	 * @param string $width
	 * @param string $height
	 * @param string $extrabutton
	 * @param string $modalWidth
	 * @return
	 */
	public static function getBootstrapModalImage($target = '', $picture = '', $text = '', $picturewidth = '20', $url = '', $width = '100', $height = '200', $extrabutton = '',$modalWidth = '80')
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		
		
		
		
		if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
		$modaltext = '<a href="#' . $target . '" title="' . $text . '" data-bs-toggle="modal" ' .'data-bs-target="#' . $target . '">';	
		$footer = '<button type="button" class="btn btn-default" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button> '.$extrabutton;
		}
		else
		{
		$modaltext = '<a href="#' . $target . '" title="' . $text . '" data-toggle="modal" >';
		$footer = '<button type="button" class="btn btn-default" data-dismiss="modal">' . Text::_('JCANCEL') . '</button> '.$extrabutton;
		}

		if ($picture)
		{
			//$modaltext .= '<img src="' . $picture . '" alt="' . $text . '" width="' . $picturewidth . '" class"sportsmanagement-img-preview" />';
			//$modaltext .= '<img src="' . $picture . '" alt="' . $text . '" class"sportsmanagement-img-preview" />';
			$modaltext .= '<img src="' . $picture . '" alt="' . $text . '" style="width: auto;height: ' . $picturewidth . 'px" />';
		}
		else
		{
			$modaltext .= '<button type="button" class="btn btn-primary">' . $text . '</button>';
		}

		$modaltext .= '</a>';

		if (!$url)
		{
			$url = $picture;
		}

		$modaltext .= HTMLHelper::_(
			'bootstrap.renderModal', $target, array(
				'title'  => $text,
				'url'    => $url,
				'height' => $height,
				'width'  => $width,
                'bodyHeight'  => '60',
				'modalWidth'  => $modalWidth,
				'footer' => $footer
			)
		);

		return $modaltext;
	}

	/**
	 * sportsmanagementHelper::jsmsernum()
	 * generiert eine seriennummer
	 * das template kann angepasst werden
	 *
	 * @return
	 */
	public static function jsmsernum()
	{
		$template = 'XX99-XX99-99XX-99XX-XXXX-99XX';
		$k        = strlen($template);
		$sernum   = '';

		for ($i = 0; $i < $k; $i++)
		{
			switch ($template[$i])
			{
				case 'X':
					$sernum .= chr(rand(65, 90));
					break;
				case '9':
					$sernum .= rand(0, 9);
					break;
				case '-':
					$sernum .= '-';
					break;
			}
		}

		return $sernum;
	}

	/**
	 * sportsmanagementHelper::existPicture()
	 *
	 * @param   string  $picture
	 * @param   string  $standard
	 *
	 * @return void
	 */
	public static function existPicture($picture = '', $standard = '')
	{
		$app        = Factory::getApplication();
		$imageArray = '';

		if (!File::exists($picture))
		{
			return false;
		}
		else
		{
			return true;
		}

	}

	/**
	 * sportsmanagementHelper::setDebugInfoText()
	 *
	 * @param   mixed  $methode
	 * @param   mixed  $funktion
	 * @param   mixed  $klasse
	 * @param   mixed  $zeile
	 * @param   mixed  $text
	 *
	 * @return void
	 */
	public static function setDebugInfoText($methode, $funktion, $klasse, $zeile, $text)
	{
		$app = Factory::getApplication();

		// Create an object for the record we are going to update.
		$object = new stdClass;

		// Must be a valid primary key value.
		$object->methode  = $methode;
		$object->function = $funktion;
		$object->class    = $klasse;
		$object->line     = $zeile;
		$object->text     = $text;

		if (!array_key_exists($klasse, self::$_success_text))
		{
			self::$_success_text[$klasse] = array();
		}

		$export[]                     = $object;
		self::$_success_text[$klasse] = array_merge(self::$_success_text[$klasse], $export);
	}

	/**
	 * sportsmanagementHelper::getTimezone()
	 *
	 * @param   mixed  $project
	 * @param   mixed  $overallconfig
	 *
	 * @return
	 */
	public static function getTimezone($project, $overallconfig)
	{
		if ($project)
		{
			return $project->timezone;
		}
		else
		{
			return $overallconfig['time_zone'];
		}
	}

	/**
	 * sportsmanagementHelper::getMatchContent()
	 *
	 * @param   mixed  $match_id
	 *
	 * @return void
	 */
	public static function getMatchContent($content_id)
	{
		$app = Factory::getApplication();

		// Create a new query object.
		$db    = self::getDBConnection();
		$query = $db->getQuery(true);

		// Select some fields

		$query->select('introtext');

		// From the table
		$query->from('#__content');
		$query->where('id = ' . $content_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * sportsmanagementHelper::getDBConnection()
	 *
	 * @return
	 */
	public static function getDBConnection($request = false, $value = false)
	{
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_sportsmanagement');
		$config = Factory::getConfig();

		// Echo '<pre>'.print_r($params,true).'</pre>';
		// Log::add(Text::_($params->get('cfg_which_database')), Log::ERROR, 'jsmerror');
		if ($params->get('cfg_which_database'))
		{
			$options             = array(); // Prevent problems
			$options['driver']   = $params->get('jsm_dbtype');            // Database driver name
			$options['host']     = $params->get('jsm_host');    // Database host name
			$options['user']     = $params->get('jsm_user');       // User for database authentication
			$options['password'] = $params->get('jsm_password');   // Password for database authentication
			$options['database'] = $params->get('jsm_db');      // Database name
			$options['prefix']   = $params->get('jsm_dbprefix');             // Database prefix (may be empty)

			// Log::add(Text::_('options <pre>'.print_r($options,true).'</pre>'), Log::ERROR, 'jsmerror');

			try
			{
				// Zuerst noch überprüfen, ob der user
				// überhaupt den zugriff auf die datenbank hat.
				if (version_compare(JSM_JVERSION, '4', 'eq'))
				{
					self::$_jsm_db = JDatabaseDriver::getInstance($options);
				}
				else
				{
					self::$_jsm_db = JDatabase::getInstance($options);
				}

				$user_id = $params->get('jsm_server_user');
			}
			catch (Exception $e)
			{
				// Catch any database errors.
				//   $db->transactionRollback();
				Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
				Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');

				// JErrorPage::render($e);
			}

			// Log::add(Text::_('user_id '.$user_id), Log::WARNING, 'jsmerror');
			if ($user_id)
			{
				// Load the profile data from the database.
				$db    = self::$_jsm_db;
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('up.profile_key, up.profile_value');
				$query->from('#__user_profiles as up');
				$query->where('up.user_id = ' . $user_id);
				$query->where('up.profile_key LIKE ' . $db->Quote('' . 'jsmprofile.%' . ''));

try
		{
				$db->setQuery($query);
				$row = $db->loadAssocList('profile_key');
 }
		catch (Exception $e)
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			return Factory::getDbo();
		}
				// Log::add(Text::_('row <pre>'.print_r($row,true).'</pre>'), Log::INFO, 'jsmerror');

				if ($row['jsmprofile.databaseaccess']['profile_value'])
				{
					Log::add(Text::_('Sie haben Zugriff.'), Log::INFO, 'jsmerror');

					if ($row['jsmprofile.serialnumber']['profile_value'] == $params->get('jsm_user_serialnumber'))
					{
						Log::add(Text::_('Die Seriennummer stimmt.'), Log::INFO, 'jsmerror');

						if ($row['jsmprofile.access_from']['profile_value'] && $row['jsmprofile.access_to']['profile_value'])
						{
							$timestampfrom    = self::getTimestamp($row['jsmprofile.access_from']['profile_value']);
							$timestampto      = self::getTimestamp($row['jsmprofile.access_to']['profile_value']);
							$timestampaktuell = self::getTimestamp();

							$varaccess = filter_var(
								$timestampaktuell,
								FILTER_VALIDATE_INT,
								array(
									'options' => array(
										'min_range' => $timestampfrom,
										'max_range' => $timestampto
									)
								)
							);

							// Log::add(Text::_('access '.$varaccess), Log::INFO, 'jsmerror');

							if ($varaccess)
							{
								Log::add(Text::_('Der Zeitraum ist freigeschaltet.'), Log::INFO, 'jsmerror');

								return self::$_jsm_db;
							}
							else
							{
								Log::add(Text::_('Der Zeitraum ist nicht freigeschaltet.'), Log::ERROR, 'jsmerror');
							}

							// Log::add(Text::_('timestamp von '.$timestampfrom), Log::INFO, 'jsmerror');
							// Log::add(Text::_('timestamp bis '.$timestampto), Log::INFO, 'jsmerror');
							// Log::add(Text::_('timestamp aktuell '.$timestampaktuell), Log::INFO, 'jsmerror');
						}
						else
						{
							Log::add(Text::_('Der Zeitraum ist nicht freigeschaltet.'), Log::ERROR, 'jsmerror');
						}
					}
					else
					{
						Log::add(Text::_('Die Seriennummer stimmt nicht.'), Log::ERROR, 'jsmerror');
					}
				}
			}

			/*
	if ( !$db ) {
		header('HTTP/1.1 500 Internal Server Error');
		jexit('Database Error: ' . $db->toString());
	} else {

	}
	*/
			// $db->debug($debug);
			// return $db;
		}

		return Factory::getDbo();

		// Return self::$_jsm_db;
	}

	/**
	 * sportsmanagementHelper::getTimestamp()
	 *
	 * @param   mixed    $date
	 * @param   integer  $use_offset
	 * @param   mixed    $offset
	 *
	 * @return
	 */
	public static function getTimestamp($date = null, $use_offset = 0, $offset = null)
	{
		$date = $date ? $date : 'now';
		$app  = Factory::getApplication();

		try
		{
			$res = Factory::getDate(strtotime($date));

			if ($use_offset)
			{
				if ($offset)
				{
					$serveroffset = explode(':', $offset);

					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						$res->setTimezone(new DateTimeZone($serveroffset[0]));
					}
					else
					{
						$res->setOffset($serveroffset[0]);
					}
				}
				else
				{
					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						$res->setTimezone(new DateTimeZone($app->getCfg('offset')));
					}
					else
					{
						$res->setOffset($app->getCfg('offset'));
					}
				}
			}

			return $res->toUnix('true');
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

			return false;
		}
	}

	/**
	 * sportsmanagementHelper::getMatchDate()
	 *
	 * @param   mixed   $match
	 * @param   string  $format
	 *
	 * @return
	 */
	public static function getMatchDate($match, $format = 'Y-m-d')
	{
		$app = Factory::getApplication();

		try
		{
			return $match->match_date ? $match->match_date->format($format, true) : "xxxx-xx-xx";
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');

			return $match->match_date;
		}
	}

	/**
	 * sportsmanagementHelper::getMatchTime()
	 *
	 * @param   mixed   $match
	 * @param   string  $format
	 *
	 * @return
	 */
	public static function getMatchTime($match, $format = 'H:i')
	{
		$app = Factory::getApplication();

		try
		{
			return $match->match_date ? $match->match_date->format($format, true) : "xx:xx";
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');

			return $match->match_date;
		}
	}

	/**
	 * sportsmanagementHelper::getMatchEndTimestamp()
	 *
	 * @param   mixed   $match
	 * @param   mixed   $totalMatchDuration
	 * @param   string  $format
	 *
	 * @return
	 */
	public static function getMatchEndTimestamp($match, $totalMatchDuration, $format = 'Y-m-d H:i')
	{
		$app          = Factory::getApplication();
		$endTimestamp = "xxxx-xx-xx xx:xx";

		try
		{
			if ($match->match_date)
			{
				$start        = new DateTime(self::getMatchStartTimestamp($match));
				$end          = $start->add(new DateInterval('PT' . $totalMatchDuration . 'M'));
				$endTimestamp = $end->format($format);
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
		}

		return $endTimestamp;
	}

	/**
	 * sportsmanagementHelper::getMatchStartTimestamp()
	 *
	 * @param   mixed   $match
	 * @param   string  $format
	 *
	 * @return
	 */
	public static function getMatchStartTimestamp($match, $format = 'Y-m-d H:i')
	{
		$app = Factory::getApplication();

		try
		{
			if ($match->match_date)
			{
				//return $match->match_date ? $match->match_date->format($format, true) : "xxxx-xx-xx xx:xx";
				return $match->match_date ? date_format($match->match_date, $format) : "xxxx-xx-xx xx:xx";
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			return $match->match_date;
		}
	}

	/**
	 * sportsmanagementHelper::getMatchTimezone()
	 *
	 * @param   mixed  $match
	 *
	 * @return
	 */
	public static function getMatchTimezone($match)
	{
		return $match->timezone;
	}

	/**
	 * Convert the UTC timestamp of a match (stored as UTC in the database) to:
	 * - the timezone of the Joomla user if that is set
	 * - to the project timezone as set in the project otherwise (so also for guest users,
	 *   aka visitors that have not logged in).
	 *
	 * @param   match  $match  Typically obtained from a DB-query and contains the match_date and timezone (of the project)
	 */
	public static function convertMatchDateToTimezone(&$match)
	{
		$app = Factory::getApplication();

		// Get some system objects.
		$config = Factory::getConfig();
		$user   = Factory::getUser();

		try
		{
			$res = Factory::getDate(strtotime($match->match_date));

			if ($match->match_date > 0)
			{
				$app = Factory::getApplication();

				if ($app->isClient('administrator'))
				{
					// In case we are editing match(es) always use the project timezone
					$timezone = $match->timezone;
				}
				else
				{
					// Otherwise use user timezone for display, and if not set use the project timezone
					$timezone = $user->getParam('timezone', $match->timezone);
				}

				$matchDate = new JDate($match->match_date);

				if ($timezone)
				{
					$matchDate->setTimezone(new DateTimeZone($timezone));
				}
				else
				{
					$timezone = $config->get('offset');
					$matchDate->setTimezone(new DateTimeZone($config->get('offset')));
				}

				$match->match_date = $matchDate;
				$match->timezone   = $timezone;
			}
			else
			{
				$match->match_date = null;
			}
		}
		catch (Exception $e)
		{
			// $app->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_EDITMATCH_MATCHDATE'), $match->value),'Notice');
		}
	}

	/**
	 * sportsmanagementHelper::isJoomlaVersion()
	 *
	 * @param   mixed  $version
	 *
	 * @return
	 */
	public static function isJoomlaVersion($version = '2.5')
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$j = new JVersion;

		if (!defined('COM_SPORTSMANAGEMENT_JOOMLAVERSION'))
		{
			DEFINE('COM_SPORTSMANAGEMENT_JOOMLAVERSION', substr($j->RELEASE, 0, strlen($version)));
		}

		return substr($j->RELEASE, 0, strlen($version)) == $version;
	}

	/**
	 * returns titleInfo
	 *
	 * @param   prefix Text that must be placed at the start of the title.
	 */
	public static function createTitleInfo($prefix)
	{
		return (object) array(
			"prefix"         => $prefix,
			"clubName"       => null,
			"team1Name"      => null,
			"team2Name"      => null,
			"roundName"      => null,
			"personName"     => null,
			"playgroundName" => null,
			"projectName"    => null,
			"divisionName"   => null,
			"leagueName"     => null,
			"seasonName"     => null
		);
	}

	/**
	 * returns formatName
	 *
	 * @param   titleInfo (info on prefix, teams (optional), project, division (optional), league and season)
	 * @param   format
	 */
	public static function formatTitle($titleInfo, $format)
	{
		$name = array();

		if (!empty($titleInfo->personName))
		{
			$name[] = $titleInfo->personName;
		}

		if (!empty($titleInfo->playgroundName))
		{
			$name[] = $titleInfo->playgroundName;
		}

		if (!empty($titleInfo->team1Name))
		{
			if (!empty($titleInfo->team2Name))
			{
				$name[] = $titleInfo->team1Name . " - " . $titleInfo->team2Name;
			}
			else
			{
				$name[] = $titleInfo->team1Name;
			}
		}

		if (!empty($titleInfo->clubName))
		{
			$name[] = $titleInfo->clubName;
		}

		if (!empty($titleInfo->roundName))
		{
			$name[] = $titleInfo->roundName;
		}

		$projectDivisionName = !empty($titleInfo->projectName) ? $titleInfo->projectName : "";

		if (!empty($titleInfo->divisionName))
		{
			$projectDivisionName .= " - " . $titleInfo->divisionName;
		}

		switch ($format)
		{
			case 0: // Projectname
				if (!empty($projectDivisionName))
				{
					$name[] = $projectDivisionName;
				}
				break;

			case 1: // Project and league name
				if (!empty($projectDivisionName))
				{
					$name[] = $projectDivisionName;
				}

				if (!empty($titleInfo->leagueName))
				{
					$name[] = $titleInfo->leagueName;
				}
				break;

			case 2: // Project, league and season name
				if (!empty($projectDivisionName))
				{
					$name[] = $projectDivisionName;
				}

				if (!empty($titleInfo->leagueName))
				{
					$name[] = $titleInfo->leagueName;
				}

				if (!empty($titleInfo->seasonName))
				{
					$name[] = $titleInfo->seasonName;
				}
				break;

			case 3: // Project and season name
				if (!empty($projectDivisionName))
				{
					$name[] = $projectDivisionName;
				}

				if (!empty($titleInfo->seasonName))
				{
					$name[] = $titleInfo->seasonName;
				}
				break;

			case 4: // League name
				if (!empty($titleInfo->leagueName))
				{
					$name[] = $titleInfo->leagueName;
				}
				break;

			case 5: // League and season name
				if (!empty($titleInfo->leagueName))
				{
					$name[] = $titleInfo->leagueName;
				}

				if (!empty($titleInfo->seasonName))
				{
					$name[] = $titleInfo->seasonName;
				}
				break;

			case 6: // Season name
				if (!empty($titleInfo->seasonName))
				{
					$name[] = $titleInfo->seasonName;
				}
				break;

			case 7: // None
				break;
		}

		return $titleInfo->prefix . ": " . implode(" | ", $name);
	}

	/**
	 * sportsmanagementHelper::addSubmenu()
	 *
	 * @param   mixed  $submenu
	 *
	 * @return void
	 */
	public static function addSubmenu($submenu)
	{
		$app             = Factory::getApplication();
		$jinput          = $app->input;
		$option          = $jinput->getCmd('option');
		$document        = Factory::getDocument();
		$project_id      = $app->getUserState("$option.pid", '0');
		$project_team_id = $app->getUserState("$option.project_team_id", '0');
		$team_id         = $app->getUserState("$option.team_id", '0');
		$club_id         = $app->getUserState("$option.club_id", '0');

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_MENU'), 'index.php?option=com_sportsmanagement', $submenu == 'cpanel'
			);

			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS'), 'index.php?option=com_sportsmanagement&view=projects', $submenu == 'projects'
			);

			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS'), 'index.php?option=com_sportsmanagement&view=predictions', $submenu == 'predictions'
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS'), 'index.php?option=com_sportsmanagement&view=currentseasons', $submenu == 'currentseasons'
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR'), 'index.php?option=com_sportsmanagement&view=jsmgcalendars', $submenu == 'googlecalendar'
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=extensions', $submenu == 'extensions'
			);
			JHtmlSidebar::addEntry(
				Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=specialextensions', $submenu == 'specialextensions'
			);
		}
		else
		{
			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_MENU'), 'index.php?option=com_sportsmanagement', $submenu == 'cpanel');

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=extensions', $submenu == 'extensions');

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS'), 'index.php?option=com_sportsmanagement&view=projects', $submenu == 'projects');

			if ($project_id != 0)
			{
				JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS_DETAILS'), 'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $project_id, $submenu == 'project');
			}
			else
			{
			}

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS'), 'index.php?option=com_sportsmanagement&view=predictions', $submenu == 'predictions');

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS'), 'index.php?option=com_sportsmanagement&view=currentseasons', $submenu == 'currentseasons');

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR'), 'index.php?option=com_sportsmanagement&view=jsmgcalendars', $submenu == 'googlecalendar');

			JSubMenuHelper::addEntry(Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=specialextensions', $submenu == 'specialextensions');

			// Set some global property
			$document = Factory::getDocument();
			$document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_sportsmanagement/images/tux-48x48.png);}');

			if ($submenu == 'extensions')
			{
				$document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMINISTRATION_EXTENSIONS'));
			}
		}
	}

	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user   = Factory::getUser();
		$result = new JObject;

		if (empty($messageId))
		{
			$assetName = 'com_sportsmanagement';
		}
		else
		{
			$assetName = 'com_sportsmanagement.message.' . (int) $messageId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 *
	 * @param   string  $data
	 * @param   string  $file
	 *
	 * @return object
	 */
	static function getExtendedStatistic($data = '', $file, $format = 'ini')
	{
		$app          = Factory::getApplication();
		$templatepath = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'statistics';
		$xmlfile      = $templatepath . DIRECTORY_SEPARATOR . $file . '.xml';
		$extended     = Form::getInstance('params', $xmlfile, array('control' => 'params'), false, '/config');
		$extended->bind($data);

		return $extended;
	}

	/**
	 * support for extensions which can overload extended data
	 *
	 * @param   string  $data
	 * @param   string  $file
	 *
	 * @return object
	 */
	static function getExtended($data = '', $file, $format = 'ini', $frontend = false)
	{
		$app     = Factory::getApplication();
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'extended' . DIRECTORY_SEPARATOR . $file . '.xml';
		/**
		 * extended data
		 */
		if (File::exists($xmlfile))
		{
			try
			{
				$jRegistry = new Registry;

				if ($data)
				{
					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						$jRegistry->loadString($data);
					}
					else
					{
						$jRegistry->loadJSON($data);
					}
				}

				$extended = Form::getInstance('extended', $xmlfile, array('control' => 'extended'), false, '/config');
				$extended->bind($jRegistry);

				if ($frontend)
				{
					return $jRegistry;
				}
				else
				{
					return $extended;
				}
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * sportsmanagementHelper::getExtendedUser()
	 *
	 * @param   string  $data
	 * @param   mixed   $file
	 * @param   string  $format
	 *
	 * @return
	 */
	static function getExtendedUser($data = '', $file, $format = 'ini')
	{
		$app     = Factory::getApplication();
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'extendeduser' . DIRECTORY_SEPARATOR . $file . '.xml';

		/*
								 * Extended data
         */

		if (File::exists($xmlfile))
		{
			try
			{
				$jRegistry = new Registry;

				if ($data)
				{
					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						$jRegistry->loadString($data);
					}
					else
					{
						$jRegistry->loadJSON($data);
					}
				}

				$extended = Form::getInstance('extendeduser', $xmlfile, array('control' => 'extendeduser'), false, '/config');
				$extended->bind($jRegistry);

				return $extended;
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to return a project array (id,name)
	 *
	 * @access public
	 * @return array project
	 * @since  1.5
	 */
	public static function getProjects()
	{
		$db = self::getDBConnection();

		$query = '	SELECT	id,
							name

					FROM #__sportsmanagement_project
					ORDER BY ordering, name ASC';

		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to return a SportsType name
	 *
	 * @access public
	 * @return array project
	 * @since  1.5
	 */
	public static function getSportsTypeName($sportsType)
	{
		$db    = self::getDBConnection();
		$query = 'SELECT name FROM #__sportsmanagement_sports_type WHERE id=' . (int) $sportsType;
		$db->setQuery($query);

		if (!$result = $db->loadResult())
		{
			// $this->setError($db->getErrorMsg());
			return false;
		}

		return Text::_($result);
	}

	/**
	 * Method to return a SportsType name
	 *
	 * @access public
	 * @return array project
	 * @since  1.5
	 */
	public static function getPosPersonTypeName($personType)
	{
		switch ($personType)
		{
			case 2 :
				$result = Text::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF');
				break;
			case 3 :
				$result = Text::_('COM_SPORTSMANAGEMENT_F_REFEREES');
				break;
			case 4 :
				$result = Text::_('COM_SPORTSMANAGEMENT_F_CLUB_STAFF');
				break;
			default :
			case 1 :
				$result = Text::_('COM_SPORTSMANAGEMENT_F_PLAYERS');
				break;
		}

		return $result;
	}

	/**
	 * sportsmanagementHelper::getExtensionsOverlay()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	public static function getExtensionsOverlay($project_id)
	{
		$option           = 'com_sportsmanagement';
		$arrExtensions    = array();
		$excludeExtension = array();

		if ($project_id)
		{
			$db    = self::getDBConnection();
			$query = 'SELECT extension FROM #__sportsmanagement_project WHERE id=' . $db->Quote((int) $project_id);

			$db->setQuery($query);
			$res = $db->loadObject();

			if (!empty($res))
			{
				$excludeExtension = explode(",", $res->extension);
			}
		}

		if (Folder::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'extensions-overlay'))
		{
			$folderExtensions = Folder::folders(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'extensions-overlay', '.', false, false, $excludeExtension);

			if ($folderExtensions !== false)
			{
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
	}

	/**
	 * returns number of years between 2 dates
	 *
	 * @param   string  $birthday      date in YYYY-mm-dd format
	 * @param   string  $current_date  date in YYYY-mm-dd format,default to today
	 *
	 * @return integer age
	 */
	public static function getAge($date, $seconddate)
	{

		if (($date != "0000-00-00")
			&& (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $date, $regs))
			&& ($seconddate == "0000-00-00")
		)
		{
			$intAge = date('Y') - $regs[1];

			if ($regs[2] > date('m'))
			{
				$intAge--;
			}
			else
			{
				if ($regs[2] == date('m'))
				{
					if ($regs[3] > date('d'))
					{
						$intAge--;
					}
				}
			}

			return $intAge;
		}

		if (($date != "0000-00-00")
			&& (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $date, $regs))
			&& ($seconddate != "0000-00-00")
			&& (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $seconddate, $regs2))
		)
		{
			$intAge = $regs2[1] - $regs[1];

			if ($regs[2] > $regs2[2])
			{
				$intAge--;
			}
			else
			{
				if ($regs[2] == $regs2[2])
				{
					if ($regs[3] > $regs2[3])
					{
						$intAge--;
					}
				}
			}

			return $intAge;
		}

		return '-';
	}

	/**
	 * returns the default placeholder
	 *
	 * @param   string  $type  ,default is player
	 *
	 * @return string placeholder (path)
	 */
	public static function getDefaultPlaceholder($type = "player")
	{
		$params                = ComponentHelper::getParams('com_sportsmanagement');
		$ph_player             = $params->get('ph_player', 0);
		$ph_logo_big           = $params->get('ph_logo_big', 0);
		$ph_logo_medium        = $params->get('ph_logo_medium', 0);
		$ph_logo_small         = $params->get('ph_logo_small', 0);
		$ph_icon               = $params->get('ph_icon', 'images/com_sportsmanagement/database/placeholders/placeholder_21.png');
		$ph_team               = $params->get('ph_team', 0);
		$ph_stadium            = $params->get('ph_stadium', 0);
		$ph_trikot             = $params->get('ph_trikot', 0);
		$ph_project            = $params->get('ph_project', 0);
		$ph_player_men_large   = $params->get('ph_player_men_large', 0);
		$ph_player_men_small   = $params->get('ph_player_men_small', 0);
		$ph_player_woman_large = $params->get('ph_player_woman_large', 0);
		$ph_player_woman_small = $params->get('ph_player_woman_small', 0);

		/**
		 * setup the different placeholders
		 */
		switch ($type)
		{
			case "trikot_home": //
			case "trikot_away": //
			case "clubs_trikot_home": //
			case "clubs_trikot_away": //
				return $ph_trikot;
				break;
			case "projects":
				return $ph_project;
				break;
			case "projectteams/trikot_home":
				return $ph_logo_small;
				break;
			case "projectteams/trikot_away":
				return $ph_logo_small;
				break;

			case "player": // Player
			case "persons":
            case "teamplayers":
				return $ph_player;
				break;

			case "stadium": //
			case "playgrounds": //
				return $ph_stadium;
				break;

			case "menlarge": //
				return $ph_player_men_large;
				break;

			case "mensmall": //
				return $ph_player_men_small;
				break;

			case "womanlarge": //
				return $ph_player_woman_large;
				break;

			case "womansmall": //
				return $ph_player_woman_small;
				break;

			case "clublogobig": // Club logo big
			case "logo_big":
			case "clubs_large":
			case "projects":
			case "league":
			case "leagues":
				return $ph_logo_big;
				break;

			case "clublogomedium": // Club logo medium
			case "logo_middle":
			case "clubs_medium":
				return $ph_logo_medium;
				break;

			case "clublogosmall": // Club logo small
			case "logo_small":
			case "clubs_small":
				return $ph_logo_small;
				break;

			case "icon": // Icon
				return $ph_icon;
				break;

			case "team": // Team picture
			case "team_picture":
			case "teams":
			case "projectteams":
			case "projectteam_picture":
				return $ph_team;
				break;
			default:
				$picture = null;
				break;
		}
	}

	/**
	 * static method which extends template path for given view names
	 * Can be used by views to search for extensions that implement parts of common views
	 * and add their path to the template search path.
	 * (e.g. 'projectheading', 'backbutton', 'footer')
	 *
	 * @param   array(string) $viewnames, names of views for which templates need to be loaded,
	 *                          so that extensions are used when available
	 * @param   JLGView  $view  to which the template paths should be added
	 */
	public static function addTemplatePaths($templatesToLoad, &$view)
	{
		$jinput     = Factory::getApplication()->input;
		$extensions = self::getExtensions($jinput->getInt('p'));

		foreach ($templatesToLoad as $template)
		{
			$view->addTemplatePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . 'tmpl');

			if (is_array($extensions) && count($extensions) > 0)
			{
				foreach ($extensions as $e => $extension)
				{
					$extension_views = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $extension . DIRECTORY_SEPARATOR . 'views';
					$tmpl_path       = $extension_views . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . 'tmpl';

					if (Folder::exists($tmpl_path))
					{
						$view->addTemplatePath($tmpl_path);
					}
				}
			}
		}
	}

	/**
	 * sportsmanagementHelper::getExtensions()
	 *
	 * @return
	 */
	public static function getExtensions()
	{
		$app              = Factory::getApplication();
		$jinput           = $app->input;
		$option           = 'com_sportsmanagement';
		$view             = $jinput->get('view');
		$arrExtensions    = array();
		$excludeExtension = array();

		if (Folder::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'extensions'))
		{
			$folderExtensions = Folder::folders(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'extensions', '.', false, false, $excludeExtension);

			if ($folderExtensions !== false)
			{
				foreach ($folderExtensions as $ext)
				{
					if ($ext == $view)
					{
						$arrExtensions[] = $ext;
					}
				}
			}
		}

		return $arrExtensions;
	}

	/**
	 * Method to convert a date from 0000-00-00 to 00-00-0000 or back
	 * return a date string
	 * $direction == 1 means from convert from 0000-00-00 to 00-00-0000
	 * $direction != 1 means from convert from 00-00-0000 to 0000-00-00
	 * call by sportsmanagementHelper::convertDate($date) inside the script
	 *
	 * When no "-" are given in $date two short date formats (DDMMYYYY and DDMMYY) are supported
	 * for example "31122011" or "311211" for 31 december 2011
	 *
	 * @access public
	 * @return array
	 */
	static function convertDate($DummyDate, $direction = 1)
	{
		$result = '';

		if (!strpos($DummyDate, "-") !== false)
		{
			// For example 31122011 is used for 31 december 2011
			if (strlen($DummyDate) == 8)
			{
				$result = substr($DummyDate, 4, 4);
				$result .= '-';
				$result .= substr($DummyDate, 2, 2);
				$result .= '-';
				$result .= substr($DummyDate, 0, 2);
			}
			// For example 311211 is used for 31 december 2011
			elseif (strlen($DummyDate) == 6)
			{
				$result = substr(date("Y"), 0, 2);
				$result .= substr($DummyDate, 4, 4);
				$result .= '-';
				$result .= substr($DummyDate, 2, 2);
				$result .= '-';
				$result .= substr($DummyDate, 0, 2);
			}
		}
		else
		{
			if ($direction == 1)
			{
				$result = substr($DummyDate, 8);
				$result .= '-';
				$result .= substr($DummyDate, 5, 2);
				$result .= '-';
				$result .= substr($DummyDate, 0, 4);
			}
			else
			{
				$result = substr($DummyDate, 6, 4);
				$result .= '-';
				$result .= substr($DummyDate, 3, 2);
				$result .= '-';
				$result .= substr($DummyDate, 0, 2);
			}
		}

		return $result;
	}

	/**
	 * sportsmanagementHelper::formatTeamName()
	 *
	 * @param   mixed    $team
	 * @param   mixed    $containerprefix
	 * @param   mixed    $config
	 * @param   integer  $isfav
	 * @param   mixed    $link
	 *
	 * @return
	 */
	public static function formatTeamName($team, $containerprefix, &$config, $isfav = 0, $link = null, $cfg_which_database = 0)
	{
		$app = Factory::getApplication();

		$output = '';
		$desc   = '';

		if ((isset($config['results_below'])) && ($config['results_below']) && ($config['show_logo_small']))
		{
			$js_func      = 'visibleMenu';
			$style_append = 'visibility:hidden';
			$container    = 'span';
		}
		else
		{
			$js_func      = 'switchMenu';
			$style_append = 'display:none';
			$container    = 'div';
		}

		$showIcons   = (
				($config['show_info_link'] == 2) && ($isfav)
			) ||
			(
				($config['show_info_link'] == 1) &&
				(
					$config['show_club_link'] ||
					$config['show_team_link'] ||
					$config['show_curve_link'] ||
					$config['show_plan_link'] ||
					$config['show_teaminfo_link'] ||
					$config['show_teamstats_link'] ||
					$config['show_clubplan_link'] ||
					$config['show_rivals_link']
				)
			);
		$containerId = $containerprefix . 't' . $team->id . 'p' . $team->project_id;

		if ($showIcons)
		{
			$onclick = $js_func . '(\'' . $containerId . '\');return false;';
			$params  = array('onclick' => $onclick);
		}

		$style = 'padding:2px;';

		if ($config['highlight_fav'] && $isfav)
		{
			$favs  = self::getProjectFavTeams($team->project_id);
			$style .= ($favs->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
			$style .= (trim($favs->fav_team_text_color) != '') ? 'color:' . trim($favs->fav_team_text_color) . ';' : '';
			$style .= (trim($favs->fav_team_color) != '') ? 'background-color:' . trim($favs->fav_team_color) . ';' : '';
		}

		$desc .= '<span style="' . $style . '">';

		$formattedTeamName = "";

		if ($config['team_name_format'] == 0)
		{
			$formattedTeamName = $team->short_name;
		}
		elseif ($config['team_name_format'] == 1)
		{
			$formattedTeamName = $team->middle_name;
		}

		if (empty($formattedTeamName))
		{
			$formattedTeamName = $team->name;
		}

		if (($config['team_name_format'] == 0) && (!empty($team->short_name)))
		{
			$desc .= '<acronym title="' . $team->name . '">' . $team->short_name . '</acronym>';
		}
		else
		{
			$desc .= $formattedTeamName;
		}

		$desc .= '</span>';

		if ($showIcons)
		{
			$output .= HTMLHelper::link('javascript:void(0);', $desc, $params);
			$output .= '<' . $container . ' id="' . $containerId . '" style="' . $style_append . ';" class="rankingteam jsmeventsshowhide">';
			$output .= self::showTeamIcons($team, $config, $cfg_which_database);
			$output .= '</' . $container . '>';
		}
		else
		{
			$output = $desc;
		}

		if ($link != null)
		{
			$output = HTMLHelper::link($link, $output);
		}

		return $output;
	}

	/**
	 * sportsmanagementHelper::getProjectFavTeams()
	 *
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	public static function getProjectFavTeams($project_id)
	{
$app    = Factory::getApplication();
$jinput = $app->input;

		
		if ($project_id)
		{
		$db    = sportsmanagementHelper::getDBConnection(true, $jinput->get('cfg_which_database', 0, '') );	
		$query = $db->getQuery(true);
		$query->select('fav_team');
		$query->from('#__sportsmanagement_project');
		$db->setQuery($query);
		$row = $db->loadObject();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			
			//$row = Table::getInstance('project', 'sportsmanagementTable');
			//$row->load($project_id);

			return $row;
		}
		else
		{
			return false;
		}
	}

	
	/**
	 * sportsmanagementHelper::showTeamIcons()
	 * 
	 * @param mixed $team
	 * @param mixed $config
	 * @param integer $cfg_which_database
	 * @param integer $s
	 * @return
	 */
	public static function showTeamIcons(&$team, &$config, $cfg_which_database = 0, $s = 0)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        $default_width = 20;

		if (!isset($team->projectteamid))
		{
			return "";
		}

		$projectteamid = $team->projectteam_slug;
		$teamname      = $team->name;
		$teamid        = $team->team_id;
		$teamSlug      = (isset($team->team_slug) ? $team->team_slug : $teamid);
		$clubSlug      = (isset($team->club_slug) ? $team->club_slug : $team->club_id);
		$division_slug = (isset($team->division_slug) ? $team->division_slug : $team->division_id);
		$projectSlug   = (isset($team->project_slug) ? $team->project_slug : $team->project_id);
		$output        = '<ul class="list-inline">';

		if ($config['show_team_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;
			$routeparameter['ptid']               = $projectteamid;
			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/team_icon.png';
            $picture = 'media/com_sportsmanagement/jl_images/user_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}
        
        if ($config['show_alltime_team_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;
			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('rosteralltime', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_ALLTIME_ROSTER_LINK') . '&nbsp;' . $teamname;
			$picture = 'media/com_sportsmanagement/jl_images/team_icon.png';
			$desc    = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if (((!isset($team_plan)) || ($teamid != $team_plan->id)) && ($config['show_plan_link']))
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;
			$routeparameter['division']           = $division_slug;
			$routeparameter['mode']               = 0;
			$routeparameter['ptid']               = $projectteamid;
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
			$title                                = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMPLAN_LINK') . '&nbsp;' . $teamname;
			//$picture                              = 'media/com_sportsmanagement/jl_images/calendar_icon.gif';
            $picture                              = 'media/com_sportsmanagement/jl_images/cal_32x32.png';
			$desc                                 = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output                               .= '<li class="list-inline-item">';
			$output                               .= HTMLHelper::link($link, $desc);
			$output                               .= '</li>';
		}

		if ($config['show_curve_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid1']               = $teamSlug;
			$routeparameter['tid2']               = 0;
			$routeparameter['division']           = $division_slug;
			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('curve', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/curve_icon.gif';
            $picture = 'media/com_sportsmanagement/jl_images/line_graph_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if ($config['show_teaminfo_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;
			$routeparameter['ptid']               = $projectteamid;

			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/teaminfo_icon.png';
            $picture = 'media/com_sportsmanagement/jl_images/workflow_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if ($config['show_club_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['cid']                = $clubSlug;
			$routeparameter['task']               = null;

			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('clubinfo', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/mail.gif';
            $picture = 'media/com_sportsmanagement/jl_images/mail_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if ($config['show_teamstats_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;

			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/teamstats_icon.png';
            $picture = 'media/com_sportsmanagement/jl_images/line_chart_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if ($config['show_clubplan_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['cid']                = $clubSlug;
			$routeparameter['task']               = null;

			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('clubplan', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/clubplan_icon.png';
            $picture = 'media/com_sportsmanagement/jl_images/clock_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		if ($config['show_rivals_link'])
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = $projectSlug;
			$routeparameter['tid']                = $teamSlug;

			$link    = sportsmanagementHelperRoute::getSportsmanagementRoute('rivals', $routeparameter);
			$title   = Text::_('COM_SPORTSMANAGEMENT_TEAMICONS_RIVALS_LINK') . '&nbsp;' . $teamname;
			//$picture = 'media/com_sportsmanagement/jl_images/rivals.png';
            $picture = 'media/com_sportsmanagement/jl_images/calculator_32x32.png';
			$desc    = self::getPictureThumb($picture, $title, $default_width, 0, 4);
			$output  .= '<li class="list-inline-item">';
			$output  .= HTMLHelper::link($link, $desc);
			$output  .= '</li>';
		}

		$output .= '</ul>';

		return $output;
	}

	/**
	 * static method which return a <img> tag with the given picture
	 *
	 * @param   string  $picture
	 * @param   string  $alttext
	 * @param   int     $width   =40,  if set to 0 the original picture width will be used
	 * @param   int     $height  =40, if set to 0 the original picture height will be used
	 * @param   int     $type    =0,    0=player, 1=club logo big, 2=club logo medium, 3=club logo small
	 *
	 * @return string
	 */
	public static function getPictureThumb($picture, $alttext, $width = 40, $height = 40, $type = 0)
	{
		$ret            = "";
		$picturepath    = JPath::clean(JPATH_SITE . DIRECTORY_SEPARATOR . str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, '', $picture));
		$params         = ComponentHelper::getParams('com_sportsmanagement');
		$ph_player      = $params->get('ph_player', 0);
		$ph_logo_big    = $params->get('ph_logo_big', 0);
		$ph_logo_medium = $params->get('ph_logo_medium', 0);
		$ph_logo_small  = $params->get('ph_logo_small', 0);
		$ph_icon        = $params->get('ph_icon', 0);
		$ph_team        = $params->get('ph_team', 0);

		if (!file_exists($picturepath) || $picturepath == JPATH_SITE . DIRECTORY_SEPARATOR)
		{
			// Setup the different placeholders
			switch ($type)
			{
				case 0: // Player
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_player;
					break;

				case 1: // Club logo big
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_logo_big;
					break;

				case 2: // Club logo medium
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_logo_medium;
					break;

				case 3: // Club logo small
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_logo_small;
					break;

				case 4: // Icon
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_icon;
					break;

				case 5: // Team picture
					$picture = JPATH_SITE . DIRECTORY_SEPARATOR . $ph_team;
					break;
				default:
					$picture = null;
					break;
			}
		}

		if (!empty($picture))
		{
			$params       = ComponentHelper::getParams('com_sportsmanagement');
			$format       = "JPG"; // PNG is not working in IE8
			$format       = $params->get('thumbformat', 'PNG');
			$bUseThumbLib = $params->get('usethumblib', false);

			if ($bUseThumbLib)
			{
				if (file_exists($picturepath))
				{
					$picture = $picturepath;
				}

				$thumb = PhpThumbFactory::create($picture);
				$thumb->setFormat($format);

				// Height and width set, resize it with the thumblib
				if ($height > 0 && $width > 0)
				{
					$thumb->setMaxHeight($height);
					$thumb->adaptiveResizeQuadrant($width, $height, $quadrant = 'C');
					$pic = $thumb->getImageAsString();
					$ret .= '<img src="data:image/' . $format . ';base64,' . base64_encode($pic);
					$ret .= '" alt="' . $alttext . '" title="' . $alttext . '"/>';
				}

				// Height==0 and width set, let the browser resize it
				if ($height == 0 && $width > 0)
				{
					$thumb->setMaxWidth($width);
					$pic = $thumb->getImageAsString();
					$ret .= '<img src="data:image/' . $format . ';base64,' . base64_encode($pic);
					$ret .= '" width="' . $width . '" alt="' . $alttext . '" title="' . $alttext . '"/>';
				}

				// Width==0 and height set, let the browser resize it
				if ($height > 0 && $width == 0)
				{
					$thumb->setMaxHeight($height);
					$pic = $thumb->getImageAsString();
					$ret .= '<img src="data:image/' . $format . ';base64,' . base64_encode($pic);
					$ret .= '" height="' . $height . '" alt="' . $alttext . '" title="' . $alttext . '"/>';
				}

				// Width==0 and height==0, use original picture size
				if ($height == 0 && $width == 0)
				{
					$thumb->setMaxHeight($height);
					$pic = $thumb->getImageAsString();
					$ret .= '<img src="data:image/' . $format . ';base64,' . base64_encode($pic);
					$ret .= '" alt="' . $alttext . '" title="' . $alttext . '"/>';
				}
			}
			else
			{
				$picture = Uri::root(true) . '/' . str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, "", $picture);
				$title   = $alttext;

				// Height and width set, let the browser resize it
				$bUseHighslide = $params->get('use_highslide', false);

				if ($bUseHighslide && $type != 4)
				{
					$title .= ' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLICK_TO_ENLARGE') . ')';
					$ret   .= '<a href="' . $picture . '" class="highslide">';
				}

				$ret .= '<img ';
				$ret .= ' ';

				if ($height > 0 && $width > 0)
				{
					$ret .= ' src="' . $picture;
					$ret .= '" width="' . $width . '" height="' . $height . '"
							alt="' . $alttext . '" title="' . $title . '"';
				}

				// Height==0 and width set, let the browser resize it
				if ($height == 0 && $width > 0)
				{
					$ret .= ' src="' . $picture;
					$ret .= '" width="' . $width . '" alt="' . $alttext . '" title="' . $title . '"';
				}

				// Width==0 and height set, let the browser resize it
				if ($height > 0 && $width == 0)
				{
					$ret .= ' src="' . $picture;
					$ret .= '" height="' . $height . '" alt="' . $alttext . '" title="' . $title . '"';
				}

				// Width==0 and height==0, use original picture size
				if ($height == 0 && $width == 0)
				{
					$ret .= ' src="' . $picture;
					$ret .= '" alt="' . $alttext . '" title="' . $title . '"';
				}

				$ret .= '/>';

				if ($bUseHighslide)
				{
					$ret .= '</a>';
				}
			}
		}

		return $ret;
	}

	/**
	 * sportsmanagementHelper::showClubIcon()
	 *
	 * @param   mixed    $team
	 * @param   integer  $type
	 * @param   integer  $with_space
	 *
	 * @return void
	 */
	public static function showClubIcon(&$team, $type = 1, $with_space = 0)
	{
		if (($type == 1) && (isset($team->country)))
		{
			if ($team->logo_small != '')
			{
				echo HTMLHelper::_('image',$team->logo_small, '', array(' title' => '', ' width' => 20));

				if ($with_space == 1)
				{
					echo ' style="padding:1px;"';
				}
			}
			else
			{
				echo '&nbsp;';
			}
		}
		elseif (($type == 3) && (isset($team->country)))
		{
			if ($team->logo_middle != '')
			{
				echo HTMLHelper::_('image',$team->logo_middle, '', array(' title' => '', ' width' => 20));

				if ($with_space == 1)
				{
					echo ' style="padding:1px;"';
				}
			}
			else
			{
				echo '&nbsp;';
			}
		}
		elseif (($type == 4) && (isset($team->country)))
		{
			if ($team->logo_big != '')
			{
				echo HTMLHelper::_('image',$team->logo_big, '', array(' title' => '', ' width' => 20));

				if ($with_space == 1)
				{
					echo ' style="padding:1px;"';
				}
			}
			else
			{
				echo '&nbsp;';
			}
		}
		elseif (($type == 2) && (isset($team->country)))
		{
			echo JSMCountries::getCountryFlag($team->country);
		}
	}

	/**
	 * sportsmanagementHelper::showColorsLegend()
	 *
	 * @param   mixed  $colors
	 * @param   mixed  $divisions
	 *
	 * @return void
	 */
	public static function showColorsLegend($colors, $divisions = null)
	{
		$jinput  = Factory::getApplication()->input;
		$favshow = $jinput->getString('func', '');

		if (($favshow != 'showCurve') && (sportsmanagementModelProject::$_project->fav_team))
		{
			$fav = array('color' => sportsmanagementModelProject::$_project->fav_team_color, 'description' => Text::_('COM_SPORTSMANAGEMENT_RANKING_FAVTEAM'));
			array_push($colors, $fav);
		}

		if (!$divisions)
		{
			foreach ($colors as $color)
			{
				if (trim($color['description']) != '')
				{
					echo '<td align="center" style="background-color:' . $color['color'] . ';"><b>' . $color['description'] . '</b>&nbsp;</td>';
				}
			}
		}

		foreach ($divisions as $division)
		{
			echo '<tr>';
			echo '<td align="center" style=""><b>' . $division->name . '</b>&nbsp;</td>';
			$jRegistry = new Registry;

			if ( version_compare(JVERSION, '3.0.0', 'ge') )
			{
				$jRegistry->loadString($division->rankingparams);
			}
			else
			{
				$jRegistry->loadJSON($division->rankingparams);
			}

			$configvalues = $jRegistry->toArray();
			$colors       = array();

			if (isset($configvalues['rankingparams']))
			{
				for ($a = 1; $a <= sizeof($configvalues['rankingparams']); $a++)
				{
					$colors[] = implode(",", $configvalues['rankingparams'][$a]);
				}
			}

			$configvalues = implode(";", $colors);
			$colors       = sportsmanagementModelProject::getColors($configvalues, sportsmanagementModelProject::$cfg_which_database);

			foreach ($colors as $color)
			{
				if (trim($color['description']) != '')
				{
					// Echo '<tr>';
					echo '<td align="center" style="background-color:' . $color['color'] . ';"><b>' . $color['description'] . '</b>&nbsp;</td>';

					// Echo '</tr>';
				}
			}

			echo '</tr>';
		}
	}

	/**
	 * returns formatName
	 *
	 * @param   prefix
	 * @param   firstName
	 * @param   nickName
	 * @param   lastName
	 * @param   format
	 */
	static function formatName($prefix, $firstName, $nickName, $lastName, $format)
	{
		$name = array();

		if ($prefix)
		{
			$name[] = $prefix;
		}

		switch ($format)
		{
			case 0: // Firstname 'Nickname' Lastname
				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "'";
				}

				if ($lastName != "")
				{
					$name[] = $lastName;
				}
				break;

			case 1: // Lastname, 'Nickname' Firstname
				if ($lastName != "")
				{
					$name[] = $lastName . ",";
				}

				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "'";
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;

			case 2: // Lastname, Firstname 'Nickname'
				if ($lastName != "")
				{
					$name[] = $lastName . ",";
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "'";
				}
				break;

			case 3: // Firstname Lastname
				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($lastName != "")
				{
					$name[] = $lastName;
				}
				break;

			case 4: // Lastname, Firstname
				if ($lastName != "")
				{
					$name[] = $lastName . ",";
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;

			case 5: // 'Nickname' - Firstname Lastname
				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "' - ";
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($lastName != "")
				{
					$name[] = $lastName;
				}
				break;

			case 6: // 'Nickname' - Lastname, Firstname
				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "' - ";
				}

				if ($lastName != "")
				{
					$name[] = $lastName . ",";
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;

			case 7: // Firstname Lastname (Nickname)
				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($lastName != "")
				{
					$name[] = $lastName;
				}

				if ($nickName != "")
				{
					$name[] = "(" . $nickName . ")";
				}
				break;

			case 8: // F. Lastname
				if ($firstName != "")
				{
					$name[] = $firstName[0] . ".";
				}

				if ($lastName != "")
				{
					$name[] = $lastName;
				}
				break;

			case 9: // Lastname, F.
				if ($lastName != "")
				{
					$name[] = $lastName . ",";
				}

				if ($firstName != "")
				{
					$name[] = $firstName[0] . ".";
				}
				break;

			case 10: // Lastname
				if ($lastName != "")
				{
					$name[] = $lastName;
				}
				break;

			case 11: // Firstname 'Nickname' L.
				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($nickName != "")
				{
					$name[] = "'" . $nickName . "'";
				}

				if ($lastName != "")
				{
					$name[] = $lastName[0] . ".";
				}
				break;

			case 12: // Nickname
				if ($nickName != "")
				{
					$name[] = $nickName;
				}
				break;

			case 13: // Firstname L.
				if ($firstName != "")
				{
					$name[] = $firstName;
				}

				if ($lastName != "")
				{
					$name[] = $lastName[0] . ".";
				}
				break;

			case 14: // Lastname Firstname
				if ($lastName != "")
				{
					$name[] = $lastName;
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;

			case 15: // Lastname newline Firstname
				if ($lastName != "")
				{
					$name[] = $lastName;
					$name[] = '<br \>';
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;

			case 16: // Firstname newline Lastname
				if ($lastName != "")
				{
					$name[] = $lastName;
					$name[] = '<br \>';
				}

				if ($firstName != "")
				{
					$name[] = $firstName;
				}
				break;
		}

		return implode(" ", $name);
	}

	/**
	 * Creates the print button
	 *
	 * @param   string  $print_link
	 * @param   array   $config
	 *
	 * @since 1.5.2
	 */
	public static function printbutton($print_link, &$config)
	{
		$jinput = Factory::getApplication()->input;
		$app    = Factory::getApplication();

		if ($config['show_print_button'] == 1)
		{
			//HTMLHelper::_('behavior.tooltip');
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no';

			// Checks template image directory for image, if non found default are loaded
			if ($config['show_icons'] == 1)
			{
				$image = HTMLHelper::_('image','media/com_sportsmanagement/jl_images/printButton.png', Text::_('Print'));
			}
			else
			{
				$image = Text::_('Print');
			}

			if ($jinput->getInt('pop'))
			{
				// Button in popup
				$output = '<a href="javascript: void(0)" onclick="window.print();return false;">' . $image . '</a>';
			}
			else
			{
				// Button in view
				$overlib = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PRINT_TIP');
				$text    = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PRINT');

				/**
				 * welche joomla version
				 * und ist seo eingestellt
				 */
				if (version_compare(JVERSION, '3.0.0', 'ge'))
				{
					$sef = Factory::getConfig()->get('sef', false);
				}
				else
				{
					$sef = Factory::getConfig()->getValue('config.sef', false);
				}

				$print_urlparams = ($sef ? "?tmpl=component&print=1" : "&tmpl=component&print=1");

				if (is_null($print_link))
				{
					$output = '<a href="javascript: void(0)" class="editlinktip hasTip" onclick="window.open(window.location.href + \'' . $print_urlparams . '\',\'win2\',\'' . $status . '\'); return false;" rel="nofollow" title="' . $text . '::' . $overlib . '">' . $image . '</a>';
				}
				else
				{
					$output = '<a href="' . Route::_($print_link) . '" class="editlinktip hasTip" onclick="window.open(window.location.href + \'' . $print_urlparams . '\',\'win2\',\'' . $status . '\'); return false;" rel="nofollow" title="' . $text . '::' . $overlib . '">' . $image . '</a>';
				}
			}

			return $output;
		}

		return;
	}

	/**
	 * sportsmanagementHelper::ToolbarButton()
	 *
	 * @param   mixed    $layout
	 * @param   string   $icon_image
	 * @param   string   $alt_text
	 * @param   string   $view
	 * @param   integer  $type
	 *
	 * @return void
	 */
	static function ToolbarButton($layout = null, $icon_image = 'upload', $alt_text = 'My Label', $view = '', $type = 0, $issueview = null, $issuelayout = null)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$zusatz     = '';
		$project_id = $jinput->get('pid');

		if ($project_id)
		{
			$zusatz .= '&pid=' . $project_id;
		}

		if (!$view)
		{
			$view = $jinput->get('view');
		}

		switch ($layout)
		{
			case 'assignpersons':
				$zusatz .= '&team_id=' . $jinput->get('team_id');
				$zusatz .= '&persontype=' . $jinput->get('persontype');
				$zusatz .= '&season_id=' . $app->getUserState("$option.season_id", '0');
                $zusatz .= '&whichview='.$jinput->get('view');
				break;
		}

		$modal_popup_width  = ComponentHelper::getParams($option)->get('modal_popup_width', 0);
		$modal_popup_height = ComponentHelper::getParams($option)->get('modal_popup_height', 0);
		$bar                = Toolbar::getInstance('toolbar');

		$page_url = OutputFilter::ampReplace('index.php?option=com_sportsmanagement&view=' . $view . '&tmpl=component&layout=' . $layout . '&type=' . $type . '&issueview=' . $issueview . '&issuelayout=' . $issuelayout . $zusatz);

		$bar->appendButton('Popup', $icon_image, $alt_text, $page_url, $modal_popup_width, $modal_popup_height);
	}

	/**
	 * sportsmanagementHelper::ToolbarButtonOnlineHelp()
	 *
	 * @return void
	 */
	static function ToolbarButtonOnlineHelp()
	{
		$app      = Factory::getApplication();
		$jinput   = $app->input;
		$option   = $jinput->getCmd('option');
		$document = Factory::getDocument();
		$view     = $jinput->get('view');
		$layout   = $jinput->get('layout');
		$view     = ucfirst(strtolower($view));
		$layout   = ucfirst(strtolower($layout));
		$document->addScript(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');
		$window_width  = '<script>alert($(window).width()); </script>';
		$window_height = '<script>alert(window.screen.height); </script>';

		switch ($view)
		{
			case 'Template':
			case 'Predictiontemplate':
				$template_help = $app->getUserState($option . 'template_help');
				$view          = $view . '_' . $template_help;
				break;
			default:
				break;
		}

		$cfg_help_server    = ComponentHelper::getParams($option)->get('cfg_help_server', '');
		$modal_popup_width  = ComponentHelper::getParams($option)->get('modal_popup_width', 0);
		$modal_popup_height = ComponentHelper::getParams($option)->get('modal_popup_height', 0);
		$bar                = ToolBar::getInstance('toolbar');

		if ($layout)
		{
			$send = '<button class="btn btn-small modal" rel="help" href="#" onclick="Joomla.popupWindow(\'' . $cfg_help_server . 'SM-Backend:' . $view . '-' . $layout . '\', \'Help\', ' . $modal_popup_width . ', ' . $modal_popup_height . ', 1)"><i class="icon-question-sign"></i>' . Text::_('Onlinehilfe') . '</button>';
		}
		else
		{
			$send = '<button class="btn btn-small modal" rel="help" href="#" onclick="Joomla.popupWindow(\'' . $cfg_help_server . 'SM-Backend:' . $view . '\', \'Help\', ' . $modal_popup_width . ', ' . $modal_popup_height . ', 1)"><i class="icon-question-sign"></i>' . Text::_('Onlinehilfe') . '</button>';
		}

		// Add a help button.
		$bar->appendButton('Custom', $send);
	}

	/**
	 * return project rounds as array of objects(roundid as value, name as text)
	 *
	 * @param   string  $ordering
	 *
	 * @return array
	 */
	public static function getRoundsOptions($project_id, $ordering = 'ASC', $required = false, $round_ids = null, $cfg_which_database = 0)
	{
		$app   = Factory::getApplication();
		$db    = self::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		if ($app->isClient('administrator'))
		{
			$query->select('id AS value');
		}
		else
		{
			$query->select('CONCAT_WS( \':\', id, alias ) AS value');
		}

		$query->select('name AS text');
		$query->select('id, name, round_date_first, round_date_last, roundcode');

		$query->from('#__sportsmanagement_round');

		$query->where('project_id = ' . $project_id);
		$query->where('published = 1');

		if ($round_ids)
		{
			$query->where('id IN (' . implode(',', $round_ids) . ')');
		}

		$query->order('roundcode ' . $ordering);

		try
		{
			$db->setQuery($query);

			if (!$required)
			{
				$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

				return array_merge($mitems, $db->loadObjectList());
			}
			else
			{
				return $db->loadObjectList();
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_($e->getMessage()), 'error');

			return false;
		}

	}

	/**
	 * returns -1/0/1 if the team lost/drew/won in specified game, or false if not played/cancelled
	 *
	 * @param   object  $game  date from match table
	 * @param   int     $ptid  project team id
	 *
	 * @return false|integer
	 */
	public static function getTeamMatchResult($game, $ptid)
	{
		if (!isset($game->team1_result))
		{
			return false;
		}

		if ($game->cancel)
		{
			return false;
		}

		if (!$game->alt_decision)
		{
			$result1 = $game->team1_result;
			$result2 = $game->team2_result;
		}
		else
		{
			$result1 = $game->team1_result_decision;
			$result2 = $game->team2_result_decision;
		}

		if ($result1 == $result2)
		{
			return 0;
		}

		if ($ptid == $game->projectteam1_id)
		{
			return ($result1 > $result2) ? 1 : -1;
		}
		else
		{
			return ($result1 > $result2) ? -1 : 1;
		}
	}

	/**
	 * sportsmanagementHelper::getExtraSelectOptions()
	 *
	 * @param   string   $view
	 * @param   string   $field
	 * @param   bool     $template
	 * @param   integer  $fieldtyp
	 *
	 * @return
	 */
	public static function getExtraSelectOptions($view = '', $field = '', $template = false, $fieldtyp = 0)
	{
		$app            = Factory::getApplication();
		$jinput         = $app->input;
		$option         = $jinput->getCmd('option');
		$select_columns = array();
		$select_values  = array();
		$select_options = array();

		// Create a new query object.
		$db    = self::getDBConnection();
		$query = $db->getQuery(true);

		// Select some fields
		if ($template)
		{
			$query->select('select_columns,select_values');

			// From the table
			$query->from('#__sportsmanagement_user_extra_fields');
			$query->where('template_backend LIKE ' . $db->Quote('' . $view . ''));
			$query->where('name LIKE ' . $db->Quote('' . $field . ''));
		}
		else
		{
			$query->select('select_columns,select_values');

			// From the table
			$query->from('#__sportsmanagement_user_extra_fields');
			$query->where('views_backend LIKE ' . $db->Quote('' . $view . ''));
			$query->where('views_backend_field LIKE ' . $db->Quote('' . $field . ''));
		}

		$query->where('fieldtyp = ' . $fieldtyp);
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			$select_columns = explode(",", $result->select_columns);

			if ($result->select_values)
			{
				$select_values = explode(",", $result->select_values);
			}
			else
			{
				$select_values = explode(",", $result->select_columns);
			}

			foreach ($select_columns as $key => $value)
			{
				$temp             = new stdClass;
				$temp->value      = $value;
				$temp->text       = $select_values[$key];
				$select_options[] = $temp;
			}

			return $select_options;
		}
		else
		{
			return false;
		}
	}

	/**
	 * sportsmanagementHelper::checkUserExtraFields()
	 *
	 * @param   string  $template
	 *
	 * @return
	 */
	static function checkUserExtraFields($template = 'backend', $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = self::getDBConnection();
		$query  = $db->getQuery(true);

		$query->select('ef.id');
		$query->from('#__sportsmanagement_user_extra_fields as ef ');
		$query->where('ef.template_' . $template . ' LIKE ' . $db->Quote('' . $jinput->get('view') . ''));

		try
		{
			$db->setQuery($query);

			if ($db->loadResult())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

			return false;
		}
	}

	/**
	 * sportsmanagementHelper::getUserExtraFields()
	 *
	 * @param   mixed   $jlid
	 * @param   string  $template
	 *
	 * @return
	 */
	static function getUserExtraFields($jlid, $template = 'backend', $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$db     = self::getDBConnection();
		$query  = $db->getQuery(true);

		if ($jlid)
		{
			$query->select('ef.*,ev.fieldvalue as fvalue,ev.id as value_id ');
			$query->from('#__sportsmanagement_user_extra_fields as ef ');
			$query->join('LEFT', '#__sportsmanagement_user_extra_fields_values as ev ON ( ef.id = ev.field_id AND ev.jl_id = ' . $jlid . ')');
			$query->where('ef.template_' . $template . ' LIKE ' . $db->Quote('' . $jinput->get('view') . ''));
			$query->order('ef.ordering');

			try
			{
				$db->setQuery($query);
				$result = $db->loadObjectList();

				return $result;
			}
			catch (Exception $e)
			{
				$msg  = $e->getMessage(); // Returns "Normally you would have other code...
				$code = $e->getCode(); // Returns
				Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * sportsmanagementHelper::saveExtraFields()
	 *
	 * @param   mixed  $post
	 * @param   mixed  $pid
	 *
	 * @return void
	 */
	public static function saveExtraFields($post, $pid)
	{
		$app           = Factory::getApplication();
		$address_parts = array();
		$db            = self::getDBConnection();

		// -------extra fields-----------//
		if (isset($post['extraf']) && count($post['extraf']))
		{
			for ($p = 0; $p < count($post['extraf']);
			     $p++)
			{
				// Create a new query object.
				$query = $db->getQuery(true);

				// Delete all
				$conditions = array(
					$db->quoteName('field_id') . '=' . $post['extra_id'][$p],
					$db->quoteName('jl_id') . '=' . $pid
				);

				$query->delete($db->quoteName('#__sportsmanagement_user_extra_fields_values'));
				$query->where($conditions);

				// $db->setQuery($query);

				try
				{
					$db->setQuery($query);
					$result = $db->execute();
				}
				catch (Exception $e)
				{
				}

				// Create a new query object.
				$query = $db->getQuery(true);

				// Insert columns.
				$columns = array('field_id', 'jl_id', 'fieldvalue');

				// Insert values.
				$values = array($post['extra_id'][$p], $pid, '\'' . $post['extraf'][$p] . '\'');

				// Prepare the insert query.
				$query
					->insert($db->quoteName('#__sportsmanagement_user_extra_fields_values'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));

				try
				{
					$db->setQuery($query);
					$result = $db->execute();
				}
				catch (Exception $e)
				{
				}
			}
		}
	}

	/**
	 * sportsmanagementHelper::resolveLocation()
	 *
	 * @param   mixed  $address
	 *
	 * @return
	 */
	public static function resolveLocation($address)
	{
		$app    = Factory::getApplication();
		$coords = array();
		$data   = self::getAddressData($address);

		if ($data)
		{
			if ($data->status == 'OK')
			{
				self::$latitude      = $data->results[0]->geometry->location->lat;
				$coords['latitude']  = $data->results[0]->geometry->location->lat;
				self::$longitude     = $data->results[0]->geometry->location->lng;
				$coords['longitude'] = $data->results[0]->geometry->location->lng;

				for ($a = 0; $a < sizeof($data->results[0]->address_components); $a++)
				{
					switch ($data->results[0]->address_components[$a]->types[0])
					{
						case 'administrative_area_level_1':
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME']  = $data->results[0]->address_components[$a]->long_name;
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
							break;

						case 'administrative_area_level_2':
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME']  = $data->results[0]->address_components[$a]->long_name;
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
							break;

						case 'administrative_area_level_3':
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME']  = $data->results[0]->address_components[$a]->long_name;
							$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
							break;

						case 'locality':
							$coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
							break;

						case 'sublocality':
							$coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
							break;
					}
				}

				return $coords;
			}

			//            else
			//            {
			//                $osm = self::getOSMGeoCoords($address);
			//            }
		}
	}

	/**
	 * Fetch google map data refere to
	 * http://code.google.com/apis/maps/documentation/geocoding/#Geocoding
	 */
	public static function getAddressData($address)
	{
		$app     = Factory::getApplication();
		$url     = 'http://maps.google.com/maps/api/geocode/json?' . 'address=' . urlencode($address) . '&sensor=false&language=de';
		$content = self::getContent($url);

		$status = null;

		if (!empty($content))
		{
			$json   = new JSMServices_JSON;
			$status = $json->decode($content);
		}

		return $status;
	}

	/**
	 * sportsmanagementHelper::getContent()
	 * Return content of the given url
	 *
	 * @param   mixed  $url
	 * @param   bool   $raw
	 * @param   bool   $headerOnly
	 *
	 * @return
	 */
	static public function getContent($url, $raw = false, $headerOnly = false)
	{
		if (!$url)
		{
			return false;
		}

		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, true);

			if ($raw)
			{
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			}

			$response = curl_exec($ch);

			$curl_errno = curl_errno($ch);
			$curl_error = curl_error($ch);

			if ($curl_errno != 0)
			{
				$app = Factory::getApplication();
				$err = 'CURL error : ' . $curl_errno . ' ' . $curl_error;
				$app->enqueueMessage($err, 'error');
			}

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			// For redirects, we need to handle this properly instead of using CURLOPT_FOLLOWLOCATION
			// as it doesn't work with safe_mode or openbase_dir set.
			if ($code == 301 || $code == 302)
			{
				list($headers, $body) = explode("\r\n\r\n", $response, 2);

				preg_match("/(Location:|URI:)(.*?)\n/", $headers, $matches);

				if (!empty($matches) && isset($matches[2]))
				{
					$url = JString::trim($matches[2]);
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, true);
					$response = curl_exec($ch);
				}
			}

			if (!$raw)
			{
				list($headers, $body) = explode("\r\n\r\n", $response, 2);
			}

			$ret = $raw ? $response : $body;
			$ret = $headerOnly ? $headers : $ret;

			curl_close($ch);

			return $ret;
		}

		// CURL unavailable on this install
		return false;
	}

	/**
	 * sportsmanagementHelper::getPictureClub()
	 *
	 * @param   mixed  $id
	 *
	 * @return
	 */
	static function getPictureClub($id)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = self::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select(array('logo_big'))
			->from('#__sportsmanagement_club')
			->where('id = ' . $id);
		$db->setQuery($query);
		$picture = $db->loadResult();

		if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $picture))
		{
			// Alles ok
		}
		else
		{
			$picture = ComponentHelper::getParams($option)->get('ph_logo_big', '');
		}

		return $picture;
	}

	/**
	 * sportsmanagementHelper::getPicturePlayground()
	 *
	 * @param   mixed  $id
	 *
	 * @return
	 */
	static function getPicturePlayground($id)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = self::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select(array('picture'))
			->from('#__sportsmanagement_playground')
			->where('id = ' . $id);
		$db->setQuery($query);
		$picture = $db->loadResult();

		if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $picture))
		{
			// Alles ok
		}
		else
		{
			$picture = ComponentHelper::getParams($option)->get('ph_team', '');
		}

		return $picture;
	}

	/**
	 * sportsmanagementHelper::getArticleList()
	 *
	 * @param   mixed  $project_category_id
	 *
	 * @return
	 */
	public static function getArticleList($project_category_id)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Wenn der user die k2 komponente
		// in der konfiguration ausgewählt hat,
		// kommt es zu einem fehler, wenn wir darüber selektieren
		// ist k2 installiert ?
		if (ComponentHelper::getParams($option)->get('which_article_component') == 'com_k2')
		{
			$k2 = ComponentHelper::getComponent('com_k2');

			if (!$k2->option)
			{
				$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COM_K2_NOT_AVAILABLE'), 'Error');

				return false;
			}
		}

		// Create a new query object.
		$query = Factory::getDBO()->getQuery(true);

		$query->select('c.id as value,c.title as text');

		switch (ComponentHelper::getParams($option)->get('which_article_component'))
		{
			case 'com_content':
				$query->from('#__content as c');
				break;
			case 'com_k2':
				$query->from('#__k2_items as c');
				break;
			default:
				$query->from('#__content as c');
				break;
		}

		$query->where('catid =' . $project_category_id);
		Factory::getDBO()->setQuery($query);
		$result = Factory::getDBO()->loadObjectList();

		return $result;
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array  $array  The array to convert to JavaScript object notation
	 *
	 * @return string  JavaScript object notation representation of the array
	 *
	 * @since 3.0
	 */
	public static function getJSObject(array $array = array())
	{
		$elements = array();

		foreach ($array as $k => $v)
		{
			// Don't encode either of these types
			if (is_null($v) || is_resource($v))
			{
				continue;
			}

			// Safely encode as a Javascript string
			$key = json_encode((string) $k);

			if (is_bool($v))
			{
				$elements[] = $key . ': ' . ($v ? 'true' : 'false');
			}
			elseif (is_numeric($v))
			{
				$elements[] = $key . ': ' . ($v + 0);
			}
			elseif (is_string($v))
			{
				if (strpos($v, '\\') === 0)
				{
					// Items such as functions and JSON objects are prefixed with \, strip the prefix and don't encode them
					$elements[] = $key . ': ' . substr($v, 1);
				}
				else
				{
					// The safest way to insert a string
					$elements[] = $key . ': ' . json_encode((string) $v);
				}
			}
			else
			{
				$elements[] = $key . ': ' . self::getJSObject(is_object($v) ? get_object_vars($v) : $v);
			}
		}

		return '{' . implode(',', $elements) . '}';
	}

	/**
	 * JSMInfo
	 */
	public static function jsminfo()
	{

		$aktversion = self::checkUpdateVersion();
		$version    = self::getVersion();

		if (!$aktversion)
		{
			$status_text = Text::_('COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_UP_TO_DATE');
			$status      = 'update-ok';
		}
		else
		{
			$status_text = Text::_('COM_SPORTSMANAGEMENT_ADMIN_COMPONENT_UPDATE');
			$status      = 'update';
		}

		echo '<div class="d-flex align-self-center jsm-logo-box">
            <img src="components/com_sportsmanagement/assets/icons/boxklein.png" />
        </div>
            <hr>
        <div class="d-flex flex-wrap justify-content-center mb-1 ' . $status . '">
            <div class="p-1">' . Text::_('COM_SPORTSMANAGEMENT_VERSION') . ': ' . $version . '</div>
            <div class="p-1">' . $status_text . ' </div>
        </div>
        <div class="d-flex flex-column flex-wrap">
             <div class="p-1">' . Text::_('COM_SPORTSMANAGEMENT_DEVELOPERS') . ': </div>   
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/ploeger_dieter.jpg" alt="diddipoeler" height="80px">
                <span>diddipoeler</span><br>
            </div>
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/prochnow_hauke.jpg" alt="svdoldie" height="80px">
                <span>svdoldie</span><br>
            </div>
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/galun-siegfried02.png" alt="stony" height="80px">
                <span>stony</span><br>
            </div>
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/llambion.jpg" alt="llambion" height="80px">
                <span>llambion</span><br>
            </div>
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/Andreas_Haunold.jpg" alt="ortwinn20000" height="80px">
                <span>ortwin20000</span><br>
            </div>
	    
	    <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/joerg-stelter.jpg" alt="jst" height="80px">
                <span>jst</span><br>
            </div>
	    
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/appu-konrad.jpg" alt="appukonrad" height="80px">
                <span>appukonrad</span><br>
            </div>
            
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/dittmann-timo.png" alt="tdittmann" height="80px">
                <span>tdittmann</span><br>
            </div>
            <div class="jsm-info-team p-1">
                <img src="components/com_sportsmanagement/assets/icons/keller-jens.jpg" alt="donclumsy" height="80px">
                <span>donclumsy</span><br>
            </div>

            <div class="p-1 mb-2">' . Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') . ': <a href="http://www.fussballineuropa.de" target="_blank">fussballineuropa</a></div>

            <div class="p-1 mb-2">' . Text::_('COM_SPORTSMANAGEMENT_COPYRIGHT') . ': &copy; 2014 fussballineuropa, All rights reserved.</div>

            <div class="p-1 mb-2">' . Text::_('COM_SPORTSMANAGEMENT_LICENSE') . ': GNU General Public License</div>          
        </div>';
	}

	/**
	 * sportsmanagementModelcpanel::checkUpdateVersion()
	 *
	 * @return
	 */
	public static function checkUpdateVersion()
	{
		$return  = 0;
		$version = self::getVersion();

		$temp = explode(".", $version);

		// Laden
		$datei = "https://raw.githubusercontent.com/diddipoeler/sportsmanagement/master/sportsmanagement.xml";

		if (function_exists('curl_version'))
		{
			$curl = curl_init();

			// Define header array for cURL requestes
			$header = array('Contect-Type:application/xml');
			curl_setopt($curl, CURLOPT_URL, $datei);
			curl_setopt($curl, CURLOPT_VERBOSE, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

			// Curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

			if (curl_errno($curl))
			{
				// Moving to display page to display curl errors
			}
			else
			{
				$content = curl_exec($curl);
				curl_close($curl);
			}
		}
		elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
		{
			$content = file_get_contents($datei);
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
		}

		if ($content)
		{
			$doc = new DOMDocument;
			$doc->loadXML($content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
			$doc->save(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement.xml');
		}

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$xml = simplexml_load_file(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement.xml');
		}
		else
		{
			$xml = Factory::getXML(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sportsmanagement.xml');
		}

		$github_version = (string) $xml->version;

		if (version_compare($github_version, $version, 'gt'))
		{
			$return = false;
		}
		else
		{
			$return = true;
		}
	}

	/**
	 * sportsmanagementHelper::getVersion()
	 *
	 * @return
	 */
	public static function getVersion()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$query  = Factory::getDbo()->getQuery(true);

		// Select some fields
		$query->select('manifest_cache');

		// From the table
		$query->from('#__extensions');
		$query->where('name LIKE ' . Factory::getDbo()->Quote('' . 'com_sportsmanagement' . ''));
		Factory::getDbo()->setQuery($query);
		$manifest_cache = json_decode(Factory::getDbo()->loadResult(), true);

		return $manifest_cache['version'];
	}

	/**
	 * sportsmanagementHelper::date_diff()
	 *
	 * @param   mixed  $d1
	 * @param   mixed  $d2
	 *
	 * @return
	 */
	function date_diff($d1, $d2)
	{
		/*
         This is correctly working time differentiating function. It's resistant to problems with
          leap year and different days of month. Inputs are two timestamps and function returns array
          with differences in year, month, day, hour, minute a nd second.

          Compares two timestamps and returns array with differencies (year, month, day, hour, minute, second)

          More infos at http://de2.php.net/manual/de/function.date-diff.php#93915

          beispiele
          define(BIRTHDAY, strtotime('1980-08-26 15:25:00')); // geburtsdatum
          $now  = time(); // aktuelles datum

          // geburtsdatum dieses jahr:
          $birthdayThisYear = mktime(date('H',BIRTHDAY),date('i',BIRTHDAY),date('s',BIRTHDAY),date('n',BIRTHDAY),date('j',BIRTHDAY),date('Y',$now));

          // geburtsdatum nächstes jahr:
          $birthdayNextYear = mktime(date('H',BIRTHDAY),date('i',BIRTHDAY),date('s',BIRTHDAY),date('n',BIRTHDAY),date('j',BIRTHDAY),date('Y',$now)+1);

          // geburtsdatum letztes jahr:
          $birthdayLastYear = mktime(date('H',BIRTHDAY),date('i',BIRTHDAY),date('s',BIRTHDAY),date('n',BIRTHDAY),date('j',BIRTHDAY),date('Y',$now)-1);

          // der nächste geburtstag:
          $nextBirthday     = ($now<$birthdayThisYear)?$birthdayThisYear:$birthdayNextYear;

          // der letzte geburtstag:
          $lastBirthday     = ($now<=$birthdayThisYear)?$birthdayLastYear:$birthdayThisYear;

          // wie viel prozent des jahres bis zum nächsten geburtstag sind schon um?
          $percent          = ($now-$lastBirthday) / ($nextBirthday-$lastBirthday) * 100;

          // wie viele tage sind es noch bis zum nächsten geburtstag?
          $days2Birthday    = floor(($nextBirthday - $now) / 86400);

          // alter als array  (jahre, monate, tage, stunden, minuten, sekunden - schaltjahre werde berücksichtigt!)
          $age              = date_diff(BIRTHDAY, $now);

         */
		// check higher timestamp and switch if neccessary
		if ($d1 < $d2)
		{
			$temp = $d2;
			$d2   = $d1;
			$d1   = $temp;
		}
		else
		{
			$temp = $d1; // Temp can be used for day count if required
		}

		$d1 = date_parse(date("Y-m-d H:i:s", $d1));
		$d2 = date_parse(date("Y-m-d H:i:s", $d2));

		// Seconds
		if ($d1['second'] >= $d2['second'])
		{
			$diff['second'] = $d1['second'] - $d2['second'];
		}
		else
		{
			$d1['minute']--;
			$diff['second'] = 60 - $d2['second'] + $d1['second'];
		}

		// Minutes
		if ($d1['minute'] >= $d2['minute'])
		{
			$diff['minute'] = $d1['minute'] - $d2['minute'];
		}
		else
		{
			$d1['hour']--;
			$diff['minute'] = 60 - $d2['minute'] + $d1['minute'];
		}

		// Hours
		if ($d1['hour'] >= $d2['hour'])
		{
			$diff['hour'] = $d1['hour'] - $d2['hour'];
		}
		else
		{
			$d1['day']--;
			$diff['hour'] = 24 - $d2['hour'] + $d1['hour'];
		}

		// Days
		if ($d1['day'] >= $d2['day'])
		{
			$diff['day'] = $d1['day'] - $d2['day'];
		}
		else
		{
			$d1['month']--;
			$diff['day'] = date("t", $temp) - $d2['day'] + $d1['day'];
		}

		// Months
		if ($d1['month'] >= $d2['month'])
		{
			$diff['month'] = $d1['month'] - $d2['month'];
		}
		else
		{
			$d1['year']--;
			$diff['month'] = 12 - $d2['month'] + $d1['month'];
		}

		// Years
		$diff['year'] = $d1['year'] - $d2['year'];

		return $diff;
	}

	/**
	 * sportsmanagementHelper::get_IP_address()
	 *
	 * @return
	 */
	function get_IP_address()
	{
		$app = Factory::getApplication();

		foreach (array('HTTP_CLIENT_IP',
			         'HTTP_X_FORWARDED_FOR',
			         'HTTP_X_FORWARDED',
			         'HTTP_X_CLUSTER_CLIENT_IP',
			         'HTTP_FORWARDED_FOR',
			         'HTTP_FORWARDED',
			         'REMOTE_ADDR') as $key)
		{
			if (array_key_exists($key, $_SERVER) === true)
			{
				foreach (explode(',', $_SERVER[$key]) as $IPaddress)
				{
					$IPaddress = trim($IPaddress); // Just to be safe

					if (filter_var($IPaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
					{
						return $IPaddress;
					}
				}
			}
		}
	}

	/**
	 * Add data to the xml
	 *
	 * @param   array  $data  data what we want to add in the xml
	 *
	 * @access private
	 * @return void
	 * @since  1.5.0a
	 *
	 */
	function _addToXml($data)
	{
		if (is_array($data) && count($data) > 0)
		{
			$object = $data[0]['object'];
			$output = '';

			foreach ($data as $name => $value)
			{
				$output .= "<record object=\"" . self::stripInvalidXml($object) . "\">\n";

				foreach ($value as $key => $data)
				{
					if (!is_null($data) && !(substr($key, 0, 1) == "_") && $key != "object")
					{
						$output .= "  <$key><![CDATA[" . self::stripInvalidXml(trim($data)) . "]]></$key>\n";
					}
				}

				$output .= "</record>\n";
			}

			return $output;
		}

		return false;
	}

	/**
	 * Removes invalid XML
	 *
	 * @access public
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	public function stripInvalidXml($value)
	{
		$ret     = '';
		$current = '';

		if (is_null($value))
		{
			return $ret;
		}

		$length = strlen($value);

		for ($i = 0; $i < $length; $i++)
		{
			$current = ord($value[$i]);

			if (($current == 0x9)
				|| ($current == 0xA)
				|| ($current == 0xD)
				|| (($current >= 0x20) && ($current <= 0xD7FF))
				|| (($current >= 0xE000) && ($current <= 0xFFFD))
				|| (($current >= 0x10000) && ($current <= 0x10FFFF))
			)
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= ' ';
			}
		}

		return $ret;
	}

	/**
	 * sportsmanagementHelper::_setSportsManagementVersion()
	 *
	 * @return
	 */
	function _setSportsManagementVersion()
	{
		$exportRoutine              = '2010-09-23 15:00:00';
		$result[0]['exportRoutine'] = $exportRoutine;
		$result[0]['exportDate']    = date('Y-m-d');
		$result[0]['exportTime']    = date('H:i:s');

		// Welche joomla version ?
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$result[0]['exportSystem'] = Factory::getConfig()->get('sitename');
		}
		else
		{
			$result[0]['exportSystem'] = Factory::getConfig()->getValue('sitename');
		}

		$result[0]['object'] = 'SportsManagementVersion';

		return $result;
	}

	/**
	 * sportsmanagementHelper::_setLeagueData()
	 *
	 * @param   mixed  $league
	 *
	 * @return
	 */
	function _setLeagueData($league)
	{

		if ($league)
		{
			$result[]            = ArrayHelper::fromObject($league);
			$result[0]['object'] = 'League';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementHelper::_setProjectData()
	 *
	 * @param   mixed  $project
	 *
	 * @return
	 */
	function _setProjectData($project)
	{
		if ($project)
		{
			$result[]            = ArrayHelper::fromObject($project);
			$result[0]['object'] = 'SportsManagement';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementHelper::_setSeasonData()
	 *
	 * @param   mixed  $season
	 *
	 * @return
	 */
	function _setSeasonData($season)
	{
		if ($season)
		{
			$result[]            = ArrayHelper::fromObject($season);
			$result[0]['object'] = 'Season';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementHelper::_setSportsType()
	 *
	 * @param   mixed  $sportstype
	 *
	 * @return
	 */
	function _setSportsType($sportstype)
	{

		if ($sportstype)
		{
			$result[]            = ArrayHelper::fromObject($sportstype);
			$result[0]['object'] = 'SportsType';

			return $result;
		}

		return false;
	}

	/**
	 * sportsmanagementHelper::_setXMLData()
	 *
	 * @param   mixed  $data
	 * @param   mixed  $object
	 *
	 * @return
	 */
	function _setXMLData($data, $object)
	{
		if ($data)
		{
			foreach ($data as $row)
			{
				$result[] = ArrayHelper::fromObject($row);
			}

			$result[0]['object'] = $object;

			return $result;
		}

		return false;
	}

	/**
	 * Method to return the project
	 *
	 * @access public
	 * @return array project
	 * @since  1.5
	 */
	function getTeamplayerProject($projectteam_id)
	{
		$db    = self::getDBConnection();
		$query = 'SELECT project_id FROM #__sportsmanagement_project_team WHERE id=' . (int) $projectteam_id;
		$db->setQuery($query);

		if (!$result = $db->loadResult())
		{
			// $this->setError($db->getErrorMsg());
			return false;
		}

		return $result;
	}

	/**
	 * Method to return a sportsTypees array (id,name)
	 *
	 * @access public
	 * @return array seasons
	 * @since  1.5.0a
	 */
	function getSportsTypes()
	{
		$db    = self::getDBConnection();
		$query = 'SELECT id, name FROM #__sportsmanagement_sports_type ORDER BY name ASC ';
		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		foreach ($result as $sportstype)
		{
			$sportstype->name = Text::_($sportstype->name);
		}

		return $result;
	}

	/**
	 * return name of extension assigned to current project.
	 *
	 * @param   int project_id
	 *
	 * @return string or false
	 */
	function getExtension($project_id = 0)
	{
		$option = 'com_sportsmanagement';

		if (!$project_id)
		{
			$app        = Factory::getApplication();
			$project_id = $app->getUserState($option . 'project', 0);
		}

		if (!$project_id)
		{
			return false;
		}

		$db    = self::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('extension');
		$query->from('#__sportsmanagement_project');
		$query->where('id =' . $db->Quote((int) $project_id));
		$db->setQuery($query);
		$res = $db->loadResult();

		return (!empty($res) ? $res : false);
	}

	/**
	 * return true if mootools upgrade is enabled
	 *
	 * @return boolean
	 */
	function isMootools12()
	{
		$version = new JVersion;

		if ($version->RELEASE == '1.5' && $version->DEV_LEVEL >= 19 && JPluginHelper::isEnabled('system', 'mtupgrade'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @function     getOSMGeoCoords
	 *
	 * @param        $address  : string
	 *
	 * @returns      -
	 * @description  Gets GeoCoords by calling the OpenStreetMap geoencoding API
	 */
	public function getOSMGeoCoords($address)
	{
		$app    = Factory::getApplication();
		$coords = array();

		// Call OSM geoencoding api
		// limit to one result (limit=1) without address details (addressdetails=0)
		// output in JSON
		$geoCodeURL = "http://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=" . urlencode($address);
		$result     = json_decode(file_get_contents($geoCodeURL), true);

		if (isset($result[0]))
		{
			$coords['latitude']                                                   = $result[0]["lat"];
			$coords['longitude']                                                  = $result[0]["lon"];
			$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $result[0]["address"]["state"];

			$coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME']                    = $result[0]["address"]["city"];
			$coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME']                 = $result[0]["address"]["residential"];
			$coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $result[0]["address"]["county"];
		}

		return $coords;
	}

	/**
	 * sportsmanagementHelper::time_to_sec()
	 *
	 * @param   mixed  $time
	 *
	 * @return
	 */
	function time_to_sec($time)
	{
		$hours   = substr($time, 0, -6);
		$minutes = substr($time, -5, 2);
		$seconds = substr($time, -2);

		return $hours * 3600 + $minutes * 60 + $seconds;
	}

	/**
	 * sportsmanagementHelper::sec_to_time()
	 *
	 * @param   mixed  $seconds
	 *
	 * @return
	 */
	function sec_to_time($seconds)
	{
		$hours   = floor($seconds / 3600);
		$minutes = floor($seconds % 3600 / 60);
		$seconds = $seconds % 60;

		return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
	}

}
