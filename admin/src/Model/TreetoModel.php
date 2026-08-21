<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TreetoTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for tournament trees. */
final class TreetoModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.treeto',
            'treeto',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'Treeto', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Treeto') === 0) {
            return new TreetoTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getTreeToData($treeto_id)
    {
        $id = (int) $treeto_id;

        if ($id <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_treeto'))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: false;
    }

    /** Generate the complete node set for one tournament tree. */
    public function setGenerateNode(): bool
    {
        $input = Factory::getApplication()->getInput();
        $treetoId = $input->post->getInt('id');
        $formData = new Registry($input->post->get('jform', [], 'array'));
        $treeDepth = (int) $formData->get('tree_i', 0);

        if ($treetoId <= 0 || $treeDepth <= 0) {
            return false;
        }

        $globalBestOf = $input->post->getInt('global_bestof');
        $globalMatchday = $input->post->getInt('global_matchday');
        $globalKnown = $input->post->getInt('global_known');
        $globalFake = $input->post->getInt('global_fake');
        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            $tree = (object) [
                'id' => $treetoId,
                'global_bestof' => $globalBestOf,
                'global_matchday' => $globalMatchday,
                'global_known' => $globalKnown,
                'global_fake' => $globalFake,
                'leafed' => 2,
                'tree_i' => $treeDepth,
            ];
            $db->updateObject('#__sportsmanagement_treeto', $tree, 'id');

            $nodeCount = (2 ** ($treeDepth + 1)) - 1;

            for ($node = 1; $node <= $nodeCount; ++$node) {
                $i = $treeDepth;
                $x = $node;
                $base = 2 ** $i;
                $row = $base;

                while ($x > 1) {
                    if ($x >= (2 ** $i)) {
                        $delta = $base * (1 / (2 ** $i));
                        $row += ($x % 2 === 1) ? $delta : -$delta;
                        --$i;
                        $x = (int) floor($x / 2);
                    } else {
                        --$i;
                    }
                }

                $record = (object) [
                    'treeto_id' => $treetoId,
                    'node' => $node,
                    'row' => $row,
                    'bestof' => $globalBestOf,
                ];
                $db->insertObject('#__sportsmanagement_treeto_node', $record);
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }
}
