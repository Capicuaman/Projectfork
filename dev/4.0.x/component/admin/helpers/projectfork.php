<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// No direct access
defined('_JEXEC') or die;


class ProjectforkHelper
{
	public static $extension = 'com_projectfork';

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 * @return	void
	 */
	public static function addSubmenu($vName)
	{
        JSubMenuHelper::addEntry(
			JHtml::_('projectfork.activeproject'),
			NULL,
			false
		);

        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_DASHBOARD'),
			'index.php?option=com_projectfork&view=dashboard',
			($vName == 'dashboard')
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_PROJECTS'),
			'index.php?option=com_projectfork&view=projects',
			($vName == 'projects')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_MILESTONES'),
			'index.php?option=com_projectfork&view=milestones',
			($vName == 'milestones')
        );
        JSubMenuHelper::addEntry(
			JText::_('COM_PROJECTFORK_SUBMENU_TASKLISTS'),
			'index.php?option=com_projectfork&view=tasklists',
			($vName == 'tasklists')
        );
	}


    /**
	 * Returns all available actions?
	 *
	 * @return	object
	 */
    public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
		$asset  = 'com_projectfork';

		$actions = array(
		    'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
		);

		foreach ($actions as $action)
        {
			$result->set($action, $user->authorise($action, $asset));
		}

		return $result;
	}


    /**
	 * Returns all groups with the give access level
	 *
     * @param    int     $access      The access level id
     * @param    bool    $children    Include child groups in the result?
     *
	 * @return	 array                The groups
	 **/
    public function getGroupsByAccess($access, $children = true)
    {
        // Setup vars
        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $groups = array();

        // Get the rule of the access level
        $query->select('a.rules');
        $query->from('#__viewlevels AS a');
        $query->where('a.id = '.(int) $access);

        $db->setQuery((string) $query);
		$rules = json_decode($db->loadResult());

        if(!count($rules)) return $groups;


        // Get the associated groups data
        //$rules = implode(',', $rules);

        if(!$children) {
            $query = $db->getQuery(true);
            $rules = implode(', ', $rules);

            $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
		    $query->from('#__usergroups AS a');
            $query->where('a.id IN('.$rules.')');
            $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
            $query->group('a.id');
		    $query->order('a.lft ASC');

            $db->setQuery((string) $query);
            $groups = $db->loadObjectList();
        }
        else {
            foreach($rules AS $gid)
            {
                $gid = (int) $gid;


                // Load the group data
                $query = $db->getQuery(true);

                $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
    		    $query->from('#__usergroups AS a');
                $query->where('a.id = '.$gid);
                $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
                $query->group('a.id');
    		    $query->order('a.lft ASC');

                $db->setQuery((string) $query);
                $group = $db->loadObject();


                // Load child groups
                if(is_object($group)) {
                    $groups[] = $group;

                    $query = $db->getQuery(true);

                    $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id, a.lft, a.rgt');
        		    $query->from('#__usergroups AS a');
                    $query->leftJoin($query->qn('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
                    $query->where('a.lft > '.$group->lft.' AND a.rgt < '.$group->rgt);
                    $query->group('a.id');
        		    $query->order('a.lft ASC');

                    $db->setQuery((string) $query);
                    $subgroups = (array) $db->loadObjectList();



                    foreach($subgroups AS $subgroup)
                    {
                        $groups[] = $subgroup;
                    }
                }
            }
        }

        return $groups;
    }


    /**
	 * Returns all child access levels of a given access levels
     * The children are defined by the group hierarchy
	 *
     * @param    int     $access      The access level id
     *
	 * @return	 array                The access levels
	 **/
    public function getChildrenOfAccess($access)
    {
        // Setup vars
        static $accesslist = NULL;

        $groups   = ProjectforkHelper::getGroupsByAccess($access);
        $children = array();

        if(!count($groups)) return $children;


        // Load all access levels if not yet set
        if(is_null($accesslist)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text, a.ordering, a.rules');
            $query->from('#__viewlevels AS a');
            $query->order('a.title ASC');

            $db->setQuery((string) $query);
            $accesslist = (array) $db->loadObjectList();
        }


        // Go through each access level
        foreach($groups AS $group)
        {
            // And each access level
            foreach($accesslist AS $item)
            {
                $rules = json_decode($item->rules);
                $key   = $item->value;

                if($key == $access) continue;

                // Check if the group is listed in the access rules and add to children if so
                if(in_array($group->value, $rules) && !array_key_exists($key, $children)) {
                    $children[$key] = $item;
                }
            }
        }

        return $children;
    }
}
