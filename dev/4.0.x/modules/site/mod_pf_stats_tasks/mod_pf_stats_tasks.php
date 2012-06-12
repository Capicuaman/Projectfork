<?php
/**
* @package   Projectfork Task Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// no direct access
defined('_JEXEC') or die;

if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/projectfork.php')) {
    echo JText::_('MOD_PF_STATS_TASKS_PROJECTFORK_NOT_INSTALLED');
}
else {
    if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php')) {
        echo JText::_('MOD_PF_STATS_TASKS_PROJECTFORK_FILE_NOT_FOUND');
    }
    else {
        // Include the helper classes
        require_once dirname(__FILE__).'/helper.php';
        require_once JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php';

        // Load jQuery and jQuery-Visualize
        JHtml::_('projectfork.jQuery');
        JHtml::_('projectfork.jQueryVisualize');

        // Get params
        $height   = (int) $params->get('height', 240);
        $width    = (int) $params->get('width', 300);
        $show_a   = (int) $params->get('show_archived', 1);
        $show_t   = (int) $params->get('show_trashed', 1);
        $color_c  = $params->get('color_completed', '66CC66');
        $color_p  = $params->get('color_pending', 'FFCC66');
        $color_a  = $params->get('color_archived', 'FF99FF');
        $color_t  = $params->get('color_trashed', '6699CC');

        // Initialize jQueryVisualize
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration("jQuery(function(){jQuery('#mod-pf-stats-tasks').visualize({
                                            type: 'pie',
                                            height: '".$height."px',
                                            width: '".$width."px',
                                            pieMargin: 10,
                                            appendTitle: false,
                                            colors: ['#".$color_c."', '#".$color_p."', '#".$color_a."', '#".$color_t."']
                                        });
                                    });");

        // Get current project and statistics
        $project = modPFstatsTasksHelper::getProject();
        $stats   = modPFstatsTasksHelper::getStats($project->id, $show_a, $show_t);

        // Include layout
        $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
        require JModuleHelper::getLayoutPath('mod_pf_stats_tasks', $params->get('layout', 'default'));
    }
}
