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
}
?>