<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>" id="uefawertung">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>" method="post">
        <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?> table-responsive" id="uefawertunganzeige">
            <?php
            if ((int) ($this->config['show_sectionheader'] ?? 0) === 1) {
                echo $this->loadTemplate('sectionheader');
            }

            $picture = '/images/com_sportsmanagement/database/placeholders/uefa5jahreswertung.jpg';
            echo sportsmanagementHelperHtml::getBootstrapModalImage(
                'uefa5',
                $picture,
                'UEFA 5-Jahreswertung',
                '400',
                '',
                $this->modalwidth,
                $this->modalheight,
                $this->overallconfig['use_jquery_modal'] ?? 0
            );

            echo $this->lists['coefficientyears'];
            ?>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <?php foreach ($this->seasonnames as $season) : ?>
                            <th scope="col"><?php echo htmlspecialchars((string) $season, ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->uefapoints as $row) : ?>
                        <tr>
                            <td>
                                <?php
                                $country = substr((string) ($row->team ?? ''), 1, 3);

                                switch ($country) {
                                    case 'GER':
                                    case 'HOL':
                                    case 'POR':
                                    case 'SUI':
                                    case 'GRE':
                                    case 'CRO':
                                    case 'DEN':
                                    case 'BUL':
                                    case 'SLO':
                                    case 'KOS':
                                    case 'LAT':
                                    case 'FAR':
                                        $country = JSMCountries::getCountryalpha3fifa($country);
                                        break;
                                }

                                echo JSMCountries::getCountryFlag($country);
                                echo htmlspecialchars((string) ($row->team ?? ''), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>

                            <?php foreach ($this->seasonnames as $season) : ?>
                                <td><?php echo htmlspecialchars((string) ($row->{$season} ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php endforeach; ?>

                            <td><?php echo htmlspecialchars((string) ($row->total ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php echo $this->loadTemplate('jsminfo'); ?>
        </div>
    </form>
</div>
