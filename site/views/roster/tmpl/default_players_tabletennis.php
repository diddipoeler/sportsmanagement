<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_player_tabletennis.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\IndividualMatchReadService;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

$app = Factory::getApplication();
$input = $app->getInput();
$debug = (bool) $app->get('debug', false);
$selector = $input->getInt(
    'cfg_which_database',
    (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
);

try {
    $database = \sportsmanagementHelper::getDBConnection(true, $selector);
} catch (\Throwable) {
    $database = null;
}

if (!$database instanceof DatabaseInterface) {
    $database = Factory::getContainer()->get(DatabaseInterface::class);
}

$individualMatchRead = new IndividualMatchReadService($database);

if ($debug) {
    echo __METHOD__ . ' ' . __LINE__ . ' rows <pre>' . print_r($this->rows, true) . '</pre>';
}
?>
<div class="container" id="roster_tabletennis">
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultplayers_tabletennis" itemscope itemtype="http://schema.org/SportsTeam">
<?php
foreach ($this->projectpositions as $position) {
    if ((int) $position->persontype !== 1) {
        continue;
    }
    ?>
    <table class="<?php echo $this->config['table_class']; ?> table-sm nowrap " id="tableplayer_tabletennis<?php echo $position->id; ?>" width="100%">
    <?php
    foreach ($this->rows as $positionId => $players) {
        if ((int) $positionId !== (int) $position->id) {
            continue;
        }

        foreach ($players as $row) {
            $playerName = sportsmanagementHelper::formatName(
                null,
                $row->firstname,
                $row->nickname,
                $row->lastname,
                $this->config['name_format']
            );
            $picture = $row->ppic ? $row->ppic : $row->picture;
            ?>
            <tr onMouseOver="this.bgColor='#CCCCFF'" onMouseOut="this.bgColor='#ffffff'" itemprop="member" itemscope itemtype="http://schema.org/Person">
                <td nowrap="nowrap">
                    <span itemprop="name" content="<?php echo $playerName; ?>"></span>
                    <span itemprop="birthDate" content="<?php echo $row->birthday; ?>"></span>
                    <span itemprop="deathDate" content="<?php echo $row->deathday; ?>"></span>
                    <span itemprop="nationality" content="<?php echo JSMCountries::getCountryName($row->country); ?>"></span>
                    <?php
                    echo sportsmanagementHelperHtml::getBootstrapModalImage(
                        'player' . $row->playerid,
                        $picture,
                        $playerName,
                        $this->config['player_picture_height'],
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $this->overallconfig['use_jquery_modal'],
                        'itemprop',
                        'image'
                    );
                    ?>
                </td>
                <td nowrap="nowrap" style="text-align:center;">
                    <?php echo JSMCountries::getCountryFlag($row->country); ?>
                </td>
                <td>
                    <?php
                    if ($this->config['link_player'] == 1) {
                        $routeparameter = [];
                        $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
                        $routeparameter['s'] = $input->get('s', '');
                        $routeparameter['p'] = $this->project->slug;
                        $routeparameter['tid'] = $this->team->slug;
                        $routeparameter['pid'] = $row->person_slug;
                        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
                        echo HTMLHelper::link($link, $playerName);
                    } else {
                        echo $playerName;
                    }
                    ?>
                </td>
                <?php if ($this->config['show_birthday'] > 0) { ?>
                <td nowrap="nowrap" style="text-align: center;">
                    <?php
                    if ($row->birthday != '0000-00-00') {
                        switch ($this->config['show_birthday']) {
                            case 1:
                                $birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
                                $birthdateStr .= '&nbsp;(' . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ')';
                                break;
                            case 2:
                                $birthdateStr = HTMLHelper::date($row->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
                                break;
                            case 3:
                                $birthdateStr = '(' . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ')';
                                break;
                            case 4:
                                $birthdateStr = HTMLHelper::date($row->birthday, 'Y');
                                break;
                            default:
                                $birthdateStr = '';
                                break;
                        }

                        $age += sportsmanagementHelper::getAge($row->birthday, $this->lastseasondate);
                        $countplayer++;
                    } else {
                        $birthdateStr = '-';
                    }

                    if ($row->deathday != '0000-00-00') {
                        $birthdateStr .= ' [&dagger; ' . HTMLHelper::date($row->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) . ']';
                    }

                    echo $birthdateStr;
                    ?>
                </td>
                <?php } ?>
                <?php
                $singleMatchesHome = $individualMatchRead->getPlayerMatches(
                    (int) $row->project_id,
                    (int) $row->projectteam_id,
                    (int) $row->season_team_person_id,
                    'SINGLE',
                    'HOME'
                );
                $singleMatchesAway = $individualMatchRead->getPlayerMatches(
                    (int) $row->project_id,
                    (int) $row->projectteam_id,
                    (int) $row->season_team_person_id,
                    'SINGLE',
                    'AWAY'
                );

                if ($debug) {
                    echo __METHOD__ . ' ' . __LINE__ . ' matches home <pre>' . print_r($singleMatchesHome, true) . '</pre>';
                    echo __METHOD__ . ' ' . __LINE__ . ' matches away <pre>' . print_r($singleMatchesAway, true) . '</pre>';
                }
                ?>
            </tr>
            <?php
        }
    }
    ?>
    </table>
    <?php
}
?>
</div>
</div>
