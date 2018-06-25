<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_ranking.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');

/**
 * es wird als erstes die farblegende der divisionen/gruppen gelesen 
 */
foreach ($this->currentRanking as $division => $cu_rk) {
    if ($division) {

        $jRegistry = new JRegistry;
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $jRegistry->loadString($this->divisions[$division]->rankingparams);
        } else {
            $jRegistry->loadJSON($this->divisions[$division]->rankingparams);
        }
        $configvalues = $jRegistry->toArray();
        $colors = array();
        if ( isset($configvalues['rankingparams']) )
        {
        for ($a = 1; $a <= sizeof($configvalues['rankingparams']); $a++) {
            $colors[] = implode(",", $configvalues['rankingparams'][$a]);
        }
    }
        $configvalues = implode(";", $colors);

        $this->colors = sportsmanagementModelProject::getColors($configvalues, sportsmanagementModelProject::$cfg_which_database);
        ?>
        <table class="<?PHP echo $this->config['table_class']; ?>">
            <tr>
                <td class="contentheading">
        <?php
        //get the division name from the first team of the division 
        foreach ($cu_rk as $ptid => $team) {
            echo $this->divisions[$division]->name;
            break;
        }
        ?>
                </td>
            </tr>
        </table>
        <div class="table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
                    <?php
                    foreach ($cu_rk as $ptid => $team) {
                        echo $this->loadTemplate('rankingheading');
                        break;
                    }
                    $this->division = $division;
                    $this->current = &$cu_rk;
                    $this->teamrow = 'tr';
                    echo $this->loadTemplate('rankingrows');
                    ?>
            </table>
        </div>
                <?php
            } else {
                ?>
        <div class="table-responsive">
            <table class="<?PHP echo $this->config['table_class']; ?>">
                <?php
                echo $this->loadTemplate('rankingheading');
                $this->division = $division;
                $this->current = &$cu_rk;
                $this->teamrow = 'tr';
                echo $this->loadTemplate('rankingrows');
                ?>
            </table>
        </div>
        <br />
        <?php
    }
}
?>
<!-- ranking END -->
