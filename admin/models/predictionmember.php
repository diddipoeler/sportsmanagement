<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionmember
 * @file       predictionmember.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'prediction.php';
require_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_sportsmanagement' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'predictionentry.php';


/**
 * sportsmanagementModelpredictionmember
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelpredictionmember extends JSMModelAdmin
{

	/**
	 * sportsmanagementModelpredictionmember::save_memberlist()
	 *
	 * @return void
	 */
	function save_memberlist()
	{
		// Reference global application object
		$app  = Factory::getApplication();
		$date = Factory::getDate();
		$user = Factory::getUser();
$fehler = 0;
		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$post          = Factory::getApplication()->input->post->getArray(array());
		$cid           = Factory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
		$prediction_id = $post['cid'];

		foreach ($post['prediction_members'] as $key => $value)
		{
			$query->clear();
			$query->select('pm.id');
			$query->from('#__sportsmanagement_prediction_member AS pm ');
			$query->where('pm.prediction_id = ' . $prediction_id);
			$query->where('pm.user_id = ' . $value);
			$db->setQuery($query);
			$result = $db->loadResult();

			if (!$result)
			{
/** Create and populate an object. */
$profile = new stdClass();
$profile->prediction_id = $prediction_id;
$profile->user_id       = $value;
$profile->registerDate  = $date->toSql();
$profile->approved      = 1;
$profile->fav_team      = '';
$profile->champ_tipp      = '';
$profile->final4_tipp      = '';
$profile->modified      = $date->toSql();
$profile->modified_by   = $user->get('id');				

try{
/** Insert the object into the user profile table. */
$result = Factory::getDbo()->insertObject('#__sportsmanagement_prediction_member', $profile);
				/**
				$table                     = 'predictionentry';
				$rowproject                = Table::getInstance($table, 'sportsmanagementTable');
				$rowproject->prediction_id = $prediction_id;
				$rowproject->user_id       = $value;
				$rowproject->registerDate  = HTMLHelper::date(time(), '%Y-%m-%d %H:%M:%S');
				$rowproject->approved      = 1;
				
				$rowproject->fav_team      = '';
				$rowproject->champ_tipp      = '';
				$rowproject->final4_tipp      = '';
				
				$rowproject->modified      = $date->toSql();
				$rowproject->modified_by   = $user->get('id');

				if (!$rowproject->store())
				{
				}
				else
				{
				}
	*/
	
				}
		catch (Exception $e)
		{
        $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
        $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
			$fehler++;
		}
			}
		}
return $fehler;
	}


	/**
	 * sportsmanagementModelpredictionmember::sendEmailtoMembers()
	 *
	 * @param   mixed  $cid
	 * @param   mixed  $prediction_id
	 *
	 * @return void
	 */
	function sendEmailtoMembers($cid, $prediction_id)
	{
		$config = Factory::getConfig();

		$language = Factory::getLanguage();
		$language->load($this->jsmoption, JPATH_SITE, $language->getTag(), true);

		sportsmanagementModelPrediction::$predictionGameID = $prediction_id;
		$configprediction                                  = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionentry');
		$overallConfig                                     = sportsmanagementModelPrediction::getPredictionOverallConfig();
		$configprediction                                  = array_merge($overallConfig, $configprediction);

		$pred_reminder_mail_text = ComponentHelper::getParams($this->jsmoption)->get('pred_reminder_mail_text', 0);

		/**
		 * add the sender Information.
		 */
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$sender = array(
				$config->get('config.mailfrom'),
				$config->get('config.fromname'));
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$sender = array(
				$config->getValue('config.mailfrom'),
				$config->getValue('config.fromname'));
		}

		$mdlPredictionProject = BaseDatabaseModel::getInstance('predictionproject', 'sportsmanagementModel');
		$mdlPredictionGame    = BaseDatabaseModel::getInstance('PredictionGame', 'sportsmanagementModel');
		$mdlPredictionGames   = BaseDatabaseModel::getInstance('PredictionGames', 'sportsmanagementModel');
		$mdlPrediction        = BaseDatabaseModel::getInstance('Prediction', 'sportsmanagementModel');

		$predictionproject    = $mdlPredictionProject->getPredictionProject($prediction_id);
		$predictiongame       = $mdlPredictionGame->getPredictionGame($prediction_id);
		$predictionprojectIDs = $mdlPredictionGame->getPredictionProjectIDs($prediction_id);

		$reminderfound = 0;
		/**
		 * schleife über die tippspieler anfang
		 */
		foreach ($cid as $key => $value)
		{
			$mailer = Factory::getMailer();
			/**
			 * als html
			 */
			$mailer->isHTML(true);
			$mailer->setSender($sender);

			$body = '';
			/**
			 * jetzt die ergebnisse
			 */
			$body           .= "<html>";
			$member_email   = sportsmanagementModelPrediction::getPredictionMemberEMailAdress($value);
			$fromdate       = '';
			$predictionlink = '';
			$projectcount   = 0;
			
			foreach ($predictionprojectIDs as $project_key => $project_value)
			{
				$totalPoints    = 0;

				$predictionProjectSettings = $mdlPrediction->getPredictionProject($project_value);
				$predictiongamematches     = $mdlPredictionGames->getPredictionGamesMatches($prediction_id, $project_value, $member_email->user_id);

				$body .= "<table class='table' width='100%' cellpadding='0' cellspacing='0'>";
				$body .= "<tr>";
				$body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DATE_TIME') . "</th>";
				$body .= "<th class='sectiontableheader' style='text-align:left;' colspan='5' >" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH') . "</th>";
				$body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_RESULT') . "</th>";
				$body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS') . "</th>";
				$body .= "<th class='sectiontableheader' style='text-align:left;'>" . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_POINTS') . "</th>";
				$body .= "</tr>";
				/**
				 * schleife über die ergebnisse in der runde
				 */
				$k = 0;
				foreach ($predictiongamematches AS $result)
				{
					$class = ($k == 0) ? 'sectiontableentry1' : 'sectiontableentry2';

					$resultHome = (isset($result->team1_result)) ? $result->team1_result : '-';

					if (isset($result->team1_result_decision))
					{
						$resultHome = $result->team1_result_decision;
					}

					$resultAway = (isset($result->team2_result)) ? $result->team2_result : '-';

					if (isset($result->team2_result_decision))
					{
						$resultAway = $result->team2_result_decision;
					}

					// --------------------------------------------------------------------------------------
					// Filter already passed matches and matches which are already tipped by the tippmember
					if ($resultHome != '-' || $resultAway != '-')
					{
						// match already ended, there is no tippreminder to send :-)
						continue;
					}
					if (  ($predictionproject->mode == '0' && (isset($result->tipp_home) || isset($result->tipp_away)))
					    ||($predictionproject->mode == '1' && isset($result->tipp))
						)
					{
						// Tippmember tipped already => skip this game
						continue;
					}
					$reminderfound++;
					// --------------------------------------------------------------------------------------

					$closingtime   = $configprediction['closing_time'];// 3600=1 hour
					$matchTimeDate = sportsmanagementHelper::getTimestamp($result->match_date, 1, $predictionProjectSettings->timezone);
					$thisTimeDate  = sportsmanagementHelper::getTimestamp(date("Y-m-d H:i:s"), 1, $predictionProjectSettings->timezone);
					$matchTimeDate = $matchTimeDate - $closingtime;
					$body          .= "<tr class='" . $class . "'>";
					$body          .= "<td class='td_c'>";
					$body          .= HTMLHelper::date($result->match_date, 'd.m.Y H:i', false);
					$body          .= " - ";
					$body          .= "</td>";
					/**
					 * clublogo oder vereinsflagge hometeam
					 */
					$body     .= "<td nowrap='nowrap' class='td_r'>";
					$body     .= $result->home_name;
					$body     .= "</td>";
					$body     .= "<td nowrap='nowrap' class='td_c'>";
					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $result->home_name);
					$body     .= JSMCountries::getCountryFlag($result->home_country);

					if (($result->home_logo_big == '') || (!file_exists($result->home_logo_big)))
					{
						$result->home_logo_big = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
					}

					$body .= HTMLHelper::_('image',Uri::root() . $result->home_logo_big, $imgTitle, array(' title' => $imgTitle, ' width' => 30));
					$body .= ' ';
					$body .= "</td>";
					$body .= "<td nowrap='nowrap' class='td_c'>";
					$body .= "<b>" . "-" . "</b>";
					$body .= "</td>";
					/**
					 * clublogo oder vereinsflagge awayteam
					 */
					$body     .= "<td nowrap='nowrap' class='td_c'>";
					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_LOGO_OF', $result->away_name);
					$body     .= ' ';
					$body     .= JSMCountries::getCountryFlag($result->away_country);

					if (($result->away_logo_big == '') || (!file_exists($result->away_logo_big)))
					{
						$result->away_logo_big = 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
					}

					$body .= HTMLHelper::_('image',Uri::root() . $result->away_logo_big, $imgTitle, array(' title' => $imgTitle, ' width' => 30));
					$body .= "</td>";
					$body .= "<td nowrap='nowrap' class='td_l'>";
					$body .= $result->away_name;
					$body .= "</td>";
					/**
					 * spielergebnisse
					 */
					$body .= "<td class='td_c'>";
					$body .= $resultHome . '-' . $resultAway;
					$body .= "</td>";
					/**
					 * tippergebnisse
					 */
					$body .= "<td class='td_c'>";
					/**
					 * Tipp in normal mode
					 */
					if ($predictionproject->mode == '0')
					{
						$body .= $result->tipp_home . $configprediction['seperator'] . $result->tipp_away;
					}

					/**
					 * Tipp in toto mode
					 */
					if ($predictionproject->mode == '1')
					{
						$body .= $result->tipp;
					}

					$body .= "</td>";

					/**
					 * punkte
					 */
					$body        .= "<td class='td_c'>";
					$points      = sportsmanagementModelPrediction::getMemberPredictionPointsForSelectedMatch($predictionproject, $result);
					$totalPoints = $totalPoints + $points;
					$body        .= $points;
					$body        .= "</td>";
					$body        .= "</tr>";
					/**
					 * tendencen im tippspiel
					 */
					if ($configprediction['show_tipp_tendence'])
					{
						$body .= "<tr class='tipp_tendence'>";
						$body .= "<td class='td_c'>";
						$body .= "&nbsp;";
						$body .= "</td>";

						$body .= "<td class='td_l' colspan='8'>";

						$totalCount = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 3);
						$homeCount  = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 1);
						$awayCount  = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 2);
						$drawCount  = sportsmanagementModelPredictionEntry::getTippCount($project_value, $result->id, 0);

						if ($totalCount > 0)
						{
							$percentageH = round(($homeCount * 100 / $totalCount), 2);
							$percentageD = round(($drawCount * 100 / $totalCount), 2);
							$percentageA = round(($awayCount * 100 / $totalCount), 2);
						}
						else
						{
							$percentageH = 0;
							$percentageD = 0;
							$percentageA = 0;
						}

						$body .= "<span style='color:" . $configprediction['color_home_win'] . "' >";
						$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_HOME_WIN', $percentageH, $homeCount) . "</span><br />";
						$body .= "<span style='color:" . $configprediction['color_draw'] . "'>";
						$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_DRAW', $percentageD, $drawCount) . "</span><br />";
						$body .= "<span style='color:" . $configprediction['color_guest_win'] . "'>";
						$body .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_PERCENT_AWAY_WIN', $percentageA, $awayCount) . "</span>";
						$body .= "</td>";

						// $body .= "<td colspan='8'>&nbsp;</td>";
						$body .= "</tr>";
					}
					else
					{
						$k = (1 - $k);
					}
				}

				if ($projectcount)
				{
					$fromdate .= ' und ';
				}

				$fromdate .= HTMLHelper::date($result->round_date_first, 'd.m.Y', false) . '-' . HTMLHelper::date($result->round_date_last, 'd.m.Y', false);

				$body .= "<tr>";
				$body .= "<td colspan='8'>&nbsp;</td>";
				$body .= "<td class='td_c'>" . Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTAL_POINTS_COUNT', $totalPoints) . "</td>";
				$body .= "</tr>";
				$body .= "<table>";

				$projectcount++;
			}

			$body .= "</html><br>";

			/**
			 * add the recipient. $recipient = $user_email;
			 */
			$mailer->addRecipient($member_email->email);
			/**
			 * add the subject
			 */
			$subject = addslashes(
				sprintf(
					Text::_("COM_SPORTSMANAGEMENT_EMAIL_PREDICTION_REMINDER_TIPS_RESULTS"),
					$predictiongame->name
				)
			);
			$mailer->setSubject($subject);
			/**
			 * add body
			 */
			$message = $pred_reminder_mail_text;

			$message = str_replace('[PREDICTIONMEMBER]', $member_email->username, $message);
			$message = str_replace('[PREDICTIONRESULTS]', $body, $message);
			$message = str_replace('[FROMDATE]', $fromdate, $message);

			$message = str_replace('[PREDICTIONENTRIES]', $predictionlink, $message);

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				// Joomla! 3.0 code here
				$message = str_replace('[PREDICTIONADMIN]', $config->get('sitename'), $message);
			}
			elseif (version_compare(JVERSION, '2.5.0', 'ge'))
			{
				// Joomla! 2.5 code here
				$message = str_replace('[PREDICTIONADMIN]', $config->getValue('sitename'), $message);
			}

            if ($reminderfound > 0) {
				$mailer->setBody($message);
                $send = $mailer->Send();

                if ($send !== true) {
                } else {
                    $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_SEND_OK', $member_email->email), 'notice');
                }
            }
		}

		/**
		 * schleife über die tippspieler ende
		 */

	}

	/**
	 * sportsmanagementModelpredictionmember::getPredictionGroups()
	 *
	 * @return
	 */
	function getPredictionGroups()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$query = 'SELECT id, name as text FROM #__sportsmanagement_prediction_groups ORDER BY name ASC ';
		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());

			return array();
		}

		return $result;
	}

	/**
	 * Method to (un)publish / (un)approve a prediction member
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  1.5.0a
	 */
	function publishpredmembers($cid = array(), $publish = 1, $predictionGameID = 0 )
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$user =& Factory::getUser();

		if (count($cid))
		{
			$cids = implode(',', $cid);

			$query = '	UPDATE #__sportsmanagement_prediction_member
							SET approved = ' . (int) $publish . '
							WHERE id IN ( ' . $cids . ' )
							AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';

			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				return false;
			}

			// Create and send mail about approving member here
			$systemAdminsMails          = sportsmanagementModelPrediction::getSystemAdminsEMailAdresses();
			$predictionGameAdminsMails  = sportsmanagementModelPrediction::getPredictionGameAdminsEMailAdresses($predictionGameID);

			foreach ($cid as $predictionMemberID)
			{
				$predictionGameMemberMail = sportsmanagementModelPrediction::getPredictionMemberEMailAdress($predictionMemberID);

				if (count($predictionGameMemberMail) > 0)
				{
					// Fetch the mail object
					$mailer = Factory::getMailer();

					// Set a sender
					$config = Factory::getConfig();
					$sender = array($config->get('mailfrom'), $config->get('fromname'));
					$mailer->setSender($sender);

					// Set Member as recipient
					$lastMailAdress = '';
					$recipient      = array();

					foreach ($predictionGameMemberMail AS $predictionGameMember_EMail)
					{
						if ($lastMailAdress != $predictionGameMember_EMail)
						{
							$recipient[]    = $predictionGameMember_EMail;
							$lastMailAdress = $predictionGameMember_EMail;
						}
					}

					$mailer->addRecipient($recipient);

					// Set system admins as BCC recipients
					$lastMailAdress  = '';
					$recipientAdmins = array();

					foreach ($systemAdminsMails AS $systemAdminMail)
					{
						if ($lastMailAdress != $systemAdminMail)
						{
							$recipientAdmins[] = $systemAdminMail;
							$lastMailAdress    = $systemAdminMail;
						}
					}

					$lastMailAdress = '';

					// Set predictiongame admins as BCC recipients
					foreach ($predictionGameAdminsMails AS $predictionGameAdminMail)
					{
						if ($lastMailAdress != $predictionGameAdminMail)
						{
							$recipientAdmins[] = $predictionGameAdminMail;
							$lastMailAdress    = $predictionGameAdminMail;
						}
					}

					$mailer->addBCC($recipientAdmins);
					unset($recipientAdmins);

					// Create the mail
					if ($publish == 1)
					{
						$mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVED'));
						$body = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REQ_APPROVED');
					}
					else
					{
						$mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_REJECTED'));
						$body = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_APPROVEMENT_REJECTED');
					}

					$mailer->setBody($body);

					// Optional file attached
					// $mailer->addAttachment(PATH_COMPONENT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'document.pdf');

					// Sending the mail
					$send =& $mailer->Send();

					if ($send !== true)
					{
						echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_ERROR_MSG') . $send->message;
					}
					else
					{
						echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBER_MODEL_MAIL_SENT');
					}

					echo '<br /><br />';
				}
				else
				{
					// Joomla_user is blocked or has set sendEmail to off
					// can't send email
					// return false;
				}
			}
		}

		return true;
	}

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionMembers()
	 *
	 * @param   mixed  $cid
	 *
	 * @return
	 */
	function deletePredictionMembers($cid = array())
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids  = implode(',', $cid);
			$query = 'DELETE FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ')';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * sportsmanagementModelpredictionmember::deletePredictionResults()
	 *
	 * @param   mixed    $cid
	 * @param   integer  $prediction_id
	 *
	 * @return
	 */
	function deletePredictionResults($cid = array(), $prediction_id = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids  = implode(',', $cid);
			$query = 'SELECT user_id FROM #__sportsmanagement_prediction_member WHERE id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;

			// Echo $query . '<br />';
			$db->setQuery($query);
			$db->execute();

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				// Joomla! 3.0 code here
				$records = $db->loadColumn();
			}
			elseif (version_compare(JVERSION, '2.5.0', 'ge'))
			{
				// Joomla! 2.5 code here
				$records = $db->loadResultArray();
			}

			if (!$result = $records)
			{
				return true;
			}

			ArrayHelper::toInteger($result);
			$cids  = implode(',', $result);
			$query = 'DELETE FROM #__sportsmanagement_prediction_result WHERE user_id IN (' . $cids . ') AND prediction_id = ' . $prediction_id;
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    The form data.
	 *
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$app  = Factory::getApplication();
		$date = Factory::getDate();
		$user = Factory::getUser();
		$post = Factory::getApplication()->input->post->getArray(array());

		// Set the values
		$data['modified']    = $date->toSql();
		$data['modified_by'] = $user->get('id');

		// Zuerst sichern, damit wir bei einer neuanlage die id haben
		if (parent::save($data))
		{
			$id         = (int) $this->getState($this->getName() . '.id');
			$isNew      = $this->getState($this->getName() . '.new');
			$data['id'] = $id;

			if ($isNew)
			{
				// Here you can do other tasks with your newly saved record...
				$app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id), '');
			}
		}

		return true;
	}

}
