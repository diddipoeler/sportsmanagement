<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage cpanel
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * sportsmanagementViewcpanel
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementViewcpanel extends sportsmanagementView
{

	/**
	 *  view display method
	 *
	 * @return void
	 */
	public function init()
	{
		$document   = Factory::getDocument();
		$project_id = $this->app->getUserState("$this->option.pid", '0');
		$model      = $this->getModel();
		$my_text    = '';
        $country = array();

		$databasetool = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");
		DEFINE('COM_SPORTSMANAGEMENT_MODEL_ERRORLOG', $databasetool);

		/** Für den import die jl tabellen lesen */
		$jl_table_import = $databasetool->getJoomleagueTables();

		$params             = ComponentHelper::getParams($this->option);
		$this->sporttypes         = $params->get('cfg_sport_types');
		$sm_quotes          = $params->get('cfg_quotes');
		$country            = $params->get('cfg_country_associations');
        if( is_array($country) ){
        $country = array_merge( array_filter($country) );
        }
		$install_agegroup   = ComponentHelper::getParams($this->option)->get('install_agegroup', 0);
		$cfg_which_database = $params->get('cfg_which_database');

		if ($cfg_which_database)
		{
			$this->sporttypes       = '';
			$sm_quotes        = '';
			$country = array();
			$install_agegroup = '';
		}

		if ($model->getInstalledPlugin('jqueryeasy'))
		{
			$this->jquery = '0';

			if (!PluginHelper::isEnabled('system', 'jqueryeasy'))
			{
			}
		}
		else
		{
			$this->jquery = '1';
		}

		if ($model->getInstalledPlugin('plugin_googlemap3'))
		{
			$this->googlemap = '0';

			if (!PluginHelper::isEnabled('system', 'plugin_googlemap3'))
			{
			}
		}
		else
		{
			$this->googlemap = '0';
		}

		if ($sm_quotes)
		{
			/** Zitate */
			$result                                                                                 = $databasetool->checkQuotes($sm_quotes);
			$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_QUOTES_SUCCESS_TEXT')] = $result;
		}

		if ($this->sporttypes)
		{
			foreach ($this->sporttypes as $key => $type)
			{
				$checksporttype        = $model->checksporttype($type);
				$checksporttype_strukt = $databasetool->checkSportTypeStructur($type);

				switch ($type)
				{
				case 'soccer':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_SOCCER');
				break;
				case 'basketball':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_BASKETBALL');
				break;
				case 'handball':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_HANDBALL');
				break;
				case 'futsal':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_FUTSAL');
				break;
				case 'volleyball':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_VOLLEYBALL');
				break;
				case 'american_football':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_AMERICAN_FOOTBALL');
				break;
				case 'hockey':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_HOCKEY');
				break;
				case 'skater_hockey':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY');
				break;
				case 'icehockey':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ICEHOCKEY');
				break;
				case 'esport_cs':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ESPORT_CS');
				break;
				case 'esport_css':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ESPORT_CSS');
				break;
				case 'esport_csgo':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ESPORT_CSGO');
				break;
				case 'esport_dodc':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ESPORT_DODC');
				break;
				case 'esport_dods':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_ESPORT_DODS');
				break;
				case 'generic':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_GENERIC');
				break;
				case 'korfball':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_KORFBALL');
				break;
				case 'tennis':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_TENNIS');
				break;
				case 'tabletennis':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_TABLETENNIS');
				break;
				case 'australien_rules_football':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_AUSTRALIEN_RULES_FOOTBALL');
				break;
				case 'dart':
				$type_sport_type = Text::_('COM_SPORTSMANAGEMENT_ST_DART');
				break;
				default:
				break;
				}

				if ($checksporttype)
				{
					$my_text .= '<span style="color:' . $model->existingInDbColor . '"><strong>';
					$my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_INSTALLED') . '</strong></span><br />';

					$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_SUCCESS', strtoupper($type_sport_type)) . '<br />';

					$model->_success_text[(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_INSTALLED') . ':')] = $my_text;

					/**
					 *
					 * es können aber auch neue positionen oder ereignisse dazu kommen
					 */
					$insert_sport_type = $databasetool->insertSportType($type);

					if ($country)
					{
						foreach ($country as $keyc => $typec)
						{
							$insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);

							if (!isset($model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')]))
							{
								$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')] = '';
							}

							$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')] = $model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')] . $insert_agegroup;
						}
					}
				}
				else
				{
					$my_text .= '<span style="color:' . $model->storeFailedColor . '"><strong>';
					$my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_MISSING') . '</strong></span><br />';
					$my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_SPORT_TYPE_ERROR', strtoupper($type)) . '<br />';

					$model->_success_text[(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_INSTALLED') . ':')] = $my_text;

					/**
					 *
					 * es können aber auch neue positionen oder ereignisse dazu kommen
					 */
					$insert_sport_type = $databasetool->insertSportType($type);

					if (isset($model->_success_text[((Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_INSTALLED')) . ' (' . $type_sport_type . ')  :')]))
					{
						$model->_success_text[((Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPES_INSTALLED')) . ' (' . $type_sport_type . ')  :')] .= $databasetool->my_text;
					}

					/**
					 *
					 * nur wenn in den optionen ja eingestellt ist, werden die altersgruppen installiert
					 */
					if ($install_agegroup)
					{
						if ($country)
						{
							foreach ($country as $keyc => $typec)
							{
								$insert_agegroup = $databasetool->insertAgegroup($typec, $insert_sport_type);

								if (isset($model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')]))
								{
									$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUPS_SUCCESS_TEXT')] .= $insert_agegroup;
								}
							}
						}
					}
				}
			}
		}

		/**
		 *
		 * Get data from the model
		 */
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		/**
		 *
		 * landesverbände
		 */
		if (!$cfg_which_database)
		{
			$checkassociations = $databasetool->checkAssociations();
		}

		$checkcountry = $model->checkcountry();

		if ($checkcountry)
		{
			$my_text                                                                                   = '<span style="color:' . $model->existingInDbColor . '"><strong>';
			$my_text                                                                                   .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_SUCCESS') . '</strong></span><br />';
			$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_SUCCESS_TEXT')] = $my_text;
		}
		else
		{
			$my_text                                                                                   = '<span style="color:' . $model->storeFailedColor . '"><strong>';
			$my_text                                                                                   .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNT_COUNTRIES_ERROR') . '</strong></span><br />';
			$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_SUCCESS_TEXT')] = $my_text;
			$insert_countries                                                                          = $databasetool->insertCountries();
			$model->_success_text[Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_SUCCESS_TEXT')] .= $insert_countries;
		}

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
		}

		//$this->sporttypes = $sporttypes;
		$this->version    = $model->getVersion();

		/**
		 *
		 * diddipoeler erst mal abgeschaltet
		 */
		$this->importData  = $model->_success_text;
		$this->importData2 = $databasetool->_success_text;

		if (!$project_id)
		{
		}

		/**
		 *
		 * Check for errors.
		 */
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

		/**
		 *
		 * Assign data to the view
		 */
		$this->items      = $items;
		$this->pagination = $pagination;
		$this->params     = $params;
	}

	/**
	 * sportsmanagementViewcpanel::addIcon()
	 *
	 * @param   mixed    $image
	 * @param   mixed    $url
	 * @param   mixed    $text
	 * @param   bool     $newWindow
	 * @param   integer  $width
	 * @param   integer  $height
	 * @param   string   $maxwidth
	 *
	 * @return void
	 */
	public function addIcon($image, $url, $text, $newWindow = false, $width = 0, $height = 0, $maxwidth = '100%')
	{
		$lang      = Factory::getLanguage();
		$newWindow = ($newWindow) ? ' target="_blank"' : '';
		$attribs   = array();

		if ($width)
		{
			$attribs['width']  = $width;
			$attribs['height'] = $height;
		}
		?>
        <div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
            <div class="icon">
                <a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image, null, $attribs); ?>
                    <span><?php echo $text; ?></span></a>
            </div>
        </div>
		<?php
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$task     = $this->jinput->getCmd('task');

try
{
// Create an instance of a default JHttp object.
$http = HttpFactory::getHttp();      
// Prepare the data.
$data = array('homepage' => Uri::base(), 'notes' => '', 'homepagename' => $this->app->getCfg('sitename') , 'isadmin' => 1 );
// Invoke the POST request.
$response = $http->post('https://www.fussballineuropa.de/jsmpaket.php', $data);      

// Create an instance of a default JHttp object.
$http = HttpFactory::getHttp();      
// Prepare the data.
$data = array('homepage' => Uri::root(), 'notes' => '', 'homepagename' => $this->app->getCfg('sitename') , 'isadmin' => 0 );
// Invoke the POST request.
$response = $http->post('https://www.fussballineuropa.de/jsmpaket.php', $data);
}
catch (Exception $e)
{
//$this->app->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');	
}
		
		$this->document->addScript(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');

		if ($this->app->isClient('administrator'))
		{
			if ($task == '' && $this->option == 'com_sportsmanagement')
			{
			}
		}
		else
		{
		}

		$canDo = sportsmanagementHelper::getActions();
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'helloworld');

		if ($canDo->get('core.admin'))
		{
			$bar = Toolbar::getInstance('toolbar');
			$bar->appendButton('Link', 'upload', Text::_('COM_SPORTSMANAGEMENT_GITHUB_UPDATE'), 'index.php?option=com_sportsmanagement&&view=githubinstall');

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				JHtmlSidebar::setAction('index.php?option=com_sportsmanagement');
			}

			parent::addToolbar();
		}
	}

}
