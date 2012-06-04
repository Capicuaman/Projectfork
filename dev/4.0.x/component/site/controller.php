<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

defined( '_JEXEC' ) or die( 'Restricted access' );


jimport('joomla.application.component.controller');

class ProjectforkController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)
	{
	    jimport( 'joomla.application.component.helper' );

        JHtml::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/html');

        $params = JComponentHelper::getParams('com_projectfork');
		$doc    = JFactory::getDocument();
        $uri    = JFactory::getURI();


        if($doc->getType() == 'html') {
            // Load bootstrap if enabled
            if($params->get('bootstrap', '1') == '1') {
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap.min.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap-responsive.min.css');

                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/js/jquery/jquery.min.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/js/jquery/jquery.noconflict.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/bootstrap/js/bootstrap.min.js');
            }

            // Load Projectfork CSS if enabled
            if($params->get('css', '1') == '1') {
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/icons.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/layout.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/theme.css');
            }

            // Load Projectfork JS
            $doc->addScript($uri->base(true).'/components/com_projectfork/assets/projectfork/js/projectfork.js');


            JHTML::_('behavior.tooltip');
        }


        $cachable = true;
        $safeurlparams = array('id' => 'INT',
                               'cid' => 'ARRAY',
                               'limit' => 'INT',
                               'limitstart' => 'INT',
			                   'showall' => 'INT',
                               'return' => 'BASE64',
                               'filter' => 'STRING',
                               'filter_order' => 'CMD',
                               'filter_order_Dir' => 'CMD',
                               'filter_search' => 'STRING',
                               'filter_published' => 'CMD',
                               'filter_project' => 'CMD',
                               'filter_milestone' => 'CMD',
                               'filter_tasklist' => 'CMD',
                               'filter_priority' => 'CMD'
                              );


		parent::display($cachable, $safeurlparams);
		return $this;
	}
}