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


abstract class modPFstatsTasksHelper
{
	public static function getProject()
	{
	    $item = new stdClass();

        $item->id    = ProjectforkHelper::getActiveProjectId();
        $item->title = ProjectforkHelper::getActiveProjectTitle();

		return $item;
	}


    public static function getStats($id = 0, $archived = 0, $trashed = 0)
    {
        $complete = new stdClass();
        $complete->label = JText::_('MOD_PF_STATS_TASKS_COMPLETE');
        $complete->data  = self::getData($id, array('complete = 1'));

        $pending = new stdClass();
        $pending->label = JText::_('MOD_PF_STATS_TASKS_PENDING');
        $pending->data  = self::getData($id, array('complete = 0'));

        $archived = new stdClass();
        $archived->label = JText::_('MOD_PF_STATS_TASKS_ARCHIVED');
        $archived->data  = ($archived ? self::getData($id, array('state = 2')) : 0);

        $trashed = new stdClass();
        $trashed->label = JText::_('MOD_PF_STATS_TASKS_TRASHED');
        $trashed->data  = ($trashed  ? self::getData($id, array('state = -2')) : 0);

        $data = array($complete, $pending, $archived, $trashed);

        return $data;
    }


    protected function getData($id = 0, $filters = array())
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
		$query = $db->getQuery(true);

        $query->select('COUNT(id)')
              ->from('#__pf_tasks');

        foreach($filters AS $filter)
        {
            $query->where($filter);
        }

        if($id) $query->where('project_id = '.$id);

        if(!$user->authorise('core.admin')) {
		    $groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('access IN ('.$groups.')');
		}

        $db->setQuery($query->__toString());
        $result = (int) $db->loadResult();

        return $result;
    }
}
