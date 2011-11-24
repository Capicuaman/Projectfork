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

jimport('joomla.application.component.controllerform');


class ProjectforkControllerProjectform extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param	  array    $config    A named array of configuration variables
	 * @return    JControllerForm
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}


    /**
	 * Cancel form function.
     * Called when the user presses the "Cancel" button on the form
	 *
	 */
    public function cancel()
    {
        // If no return URL is specified, return to the projects overview
        if(JRequest::getVar('return') == '') {
            $this->setRedirect(JRoute::_('index.php?option=com_projectfork&view=projects', false));
            return true;
        }
    }
}