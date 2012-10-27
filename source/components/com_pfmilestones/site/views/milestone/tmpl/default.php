<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Create shortcuts to some parameters.
$item    = &$this->item;
$user	 = &$this->user;
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$uid	 = $user->get('id');

$asset_name = 'com_pfmilestones.milestone.'.$item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('core.edit', $asset_name));
$canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('core.edit.own', $asset_name)) && $item->created_by == $uid);
?>
<div id="projectfork" class="item-page<?php echo $this->pageclass_sfx?> view-milestone">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

	<div class="page-header">
		<h2><?php echo $this->escape($item->title); ?></h2>
	</div>

	<dl class="article-info dl-horizontal pull-right">
		<dt class="project-title">
			<?php echo JText::_('JGRID_HEADING_PROJECT');?>:
		</dt>
		<dd class="project-data">
			<a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
		</dd>
		<?php if($item->start_date != JFactory::getDBO()->getNullDate()): ?>
			<dt class="start-title">
				<?php echo JText::_('JGRID_HEADING_START_DATE');?>:
			</dt>
			<dd class="start-data">
				<?php echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
			</dd>
		<?php endif; ?>
		<?php if($item->end_date != JFactory::getDBO()->getNullDate()): ?>
			<dt class="due-title">
				<?php echo JText::_('JGRID_HEADING_DEADLINE');?>:
			</dt>
			<dd class="due-data">
				<?php echo JHtml::_('date', $item->end_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
			</dd>
		<?php endif;?>
		<dt class="owner-title">
			<?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
		</dt>
		<dd class="owner-data">
			 <?php echo $this->escape($item->author);?>
		</dd>
	</dl>

	<div class="actions btn-toolbar">
		<div class="btn-group">
			<?php if($canEdit || $canEditOwn) : ?>
			   <a class="btn" href="<?php echo JRoute::_('index.php?option=com_pfmilestones&task=form.edit&id='.intval($this->item->id).':'.$this->item->alias);?>">
			       <i class="icon-edit"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
			   </a>
			<?php endif; ?>
			<a class="btn" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($this->item->project_slug, $this->item->slug));?>"><i class="icon-th-list"></i> <?php echo $this->item->lists;?> <?php echo JText::_('JGRID_HEADING_TASKLISTS');?></a>
			<a class="btn" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($this->item->project_slug, $this->item->slug));?>"><i class="icon-ok"></i> <?php echo $this->item->tasks;?> <?php echo JText::_('JGRID_HEADING_TASKS');?></a>
            <?php echo $item->event->afterDisplayTitle;?>
		</div>
	</div>

    <?php echo $item->event->beforeDisplayContent;?>

	<div class="item-description">
		<?php echo $this->escape($item->text); ?>
	</div>
	<hr />

    <?php echo $item->event->afterDisplayContent;?>

    <!--
	<div class="items-more">
		<h3>Comments</h3>
		<div class="contact-form" class="comment-form">
			<form class="form-validate" method="post" action="#" id="contact-form">
				<fieldset>
					<legend>6 comments</legend>
					<dl>
						<dt><label title="" class="hasTip required" for="jform_contact_name" id="jform_contact_name-lbl">Name<span class="star">&nbsp;*</span></label></dt>
						<dd><input type="text" size="30" class="required" value="" id="jform_contact_name" name="jform[contact_name]" aria-required="true" required="required"></dd>
						<dt><label title="" class="hasTip required" for="jform_contact_email" id="jform_contact_email-lbl">Email<span class="star">&nbsp;*</span></label></dt>
						<dd><input type="email" size="30" value="" id="jform_contact_email" class="validate-email required" name="jform[contact_email]" aria-required="true" required="required"></dd>
						<dt><label title="" class="hasTip required" for="jform_contact_emailmsg" id="jform_contact_emailmsg-lbl">Subject<span class="star">&nbsp;*</span></label></dt>
						<dd><input type="text" size="60" class="required" value="" id="jform_contact_emailmsg" name="jform[contact_subject]" aria-required="true" required="required"></dd>
						<dt><label title="" class="hasTip required" for="jform_contact_message" id="jform_contact_message-lbl" aria-invalid="true">Comment<span class="star">&nbsp;*</span></label></dt>
						<dd><textarea class="required" rows="10" cols="50" id="jform_contact_message" name="jform[contact_message]" aria-required="true" required="required" aria-invalid="true"></textarea></dd>
						<dt><label title="" class="hasTip" for="jform_contact_email_copy" id="jform_contact_email_copy-lbl">Send notification</label></dt>
						<dd><input type="checkbox" value="" id="jform_contact_email_copy" name="jform[contact_email_copy]"></dd>
						<dt></dt>
						<dd><button type="submit" class="button validate">Post Comment</button>
							<input type="hidden" value="com_contact" name="option">
							<input type="hidden" value="contact.submit" name="task">
							<input type="hidden" value="" name="return">
							<input type="hidden" value="1:name" name="id">
							<input type="hidden" value="1" name="9dd4c34ea61fc9fb1b22f327a4c831f8">				</dd>
					</dl>
				</fieldset>
			</form>
			<div class="categories-list comments-list">
				<ul>
					<li class="first">
						<div class="cat-list-row1 comment-info">
							<span class="item-avatar">
								<a href="#" id="avatar-1">avatar</a>
							</span>
							<span class="item-title">
								<a href="#" id="comment-1">Firstname Lastname</a>
							</span>
							<span class="item-date">
								2 weeks ago
							</span>
						</div>
						<div class="category-desc comment-desc">
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
							</p>
							<ul class="actions">
								<li class="edit-icon">
									<span><a href="#" class="button">Edit</a></span>
								</li>
								<li class="reply-icon">
									<span><a href="#" class="button">Reply</a></span>
								</li>
							</ul>
						</div>
						<!-- Inline Replies --><!--
							<ul>
								<li class="first">
									<div class="cat-list-row1 comment-info">
										<span class="item-avatar">
											<a href="#" id="avatar-1">avatar</a>
										</span>
										<span class="item-title">
											<a href="#" id="comment-1">Firstname Lastname</a>
										</span>
										<span class="item-date">
											2 weeks ago
										</span>
									</div>
									<div class="category-desc comment-desc">
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
										</p>
										<ul class="actions">
											<li class="edit-icon">
												<span><a href="#" class="button">Edit</a></span>
											</li>
											<li class="reply-icon">
												<span><a href="#" class="button">Reply</a></span>
											</li>
										</ul>
									</div>
								</li>
								<li class="last">
									<div class="cat-list-row1 comment-info">
										<span class="item-avatar">
											<a href="#" id="avatar-1">avatar</a>
										</span>
										<span class="item-title">
											<a href="#" id="comment-1">Firstname Lastname</a>
										</span>
										<span class="item-date">
											2 weeks ago
										</span>
									</div>
									<div class="category-desc comment-desc">
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
										</p>
										<ul class="actions">
											<li class="edit-icon">
												<span><a href="#" class="button">Edit</a></span>
											</li>
											<li class="reply-icon">
												<span><a href="#" class="button">Reply</a></span>
											</li>
										</ul>
									</div>
								</li>
							</ul>
						<!-- Inline Replies --><!--
					</li>
					<li class="last">
						<div class="cat-list-row1 comment-info">
							<span class="item-avatar">
								<a href="#" id="avatar-1">avatar</a>
							</span>
							<span class="item-title">
								<a href="#" id="comment-1">Firstname Lastname</a>
							</span>
							<span class="item-date">
								3 weeks ago
							</span>
						</div>
						<div class="category-desc comment-desc">
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
							</p>
							<ul class="actions">
								<li class="edit-icon">
									<span><a href="#" class="button">Edit</a></span>
								</li>
								<li class="reply-icon">
									<span><a href="#" class="button">Reply</a></span>
								</li>
							</ul>
						</div>
						<!-- Inline Replies --><!--
							<ul>
								<li class="first">
									<div class="cat-list-row1 comment-info">
										<span class="item-avatar">
											<a href="#" id="avatar-1">avatar</a>
										</span>
										<span class="item-title">
											<a href="#" id="comment-1">Firstname Lastname</a>
										</span>
										<span class="item-date">
											3 weeks ago
										</span>
									</div>
									<div class="category-desc comment-desc">
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
										</p>
										<ul class="actions">
											<li class="edit-icon">
												<span><a href="#" class="button">Edit</a></span>
											</li>
											<li class="reply-icon">
												<span><a href="#" class="button">Reply</a></span>
											</li>
										</ul>
									</div>
								</li>
								<li class="last">
									<div class="cat-list-row1 comment-info">
										<span class="item-avatar">
											<a href="#" id="avatar-1">avatar</a>
										</span>
										<span class="item-title">
											<a href="#" id="comment-1">Firstname Lastname</a>
										</span>
										<span class="item-date">
											3 weeks ago
										</span>
									</div>
									<div class="category-desc comment-desc">
										<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
										</p>
										<ul class="actions">
											<li class="edit-icon">
												<span><a href="#" class="button">Edit</a></span>
											</li>
											<li class="reply-icon">
												<span><a href="#" class="button">Reply</a></span>
											</li>
										</ul>
									</div>
								</li>
							</ul>
						<!-- Inline Replies --><!--
					</li>
				</ul>
			</div>
		</div>
	</div>-->
</div>