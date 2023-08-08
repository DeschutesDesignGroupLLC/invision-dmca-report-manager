<?php

namespace IPS\dmca\Reports;

use IPS\core\Warnings\Reason;
use IPS\core\Warnings\Warning;
use IPS\Email;
use IPS\forums\Topic;
use IPS\Helpers\Form\Checkbox;
use IPS\Helpers\Form\Editor;
use IPS\Helpers\Form\Select;

class _Report extends \IPS\Node\Model implements \Stringable
{
    /**
     * Status Constants
     */
    public const REPORT_STATUS_SUBMITTED = 1;
    public const REPORT_STATUS_APPROVED = 2;
    public const REPORT_STATUS_DENIED = 3;
    public const REPORT_STATUS_PENDING = 4;
    public const REPORT_STATUS_ON_HOLD = 5;


    /**
     * [ActiveRecord] Multiton Store
     *
     * @var string
     */
    protected static $multitons;

    /**
     * [ActiveRecord] Database Table
     *
     * @var string
     */
    public static $databaseTable = 'dmca_reports';

    /**
     * [ActiveRecord] Database Prefix
     *
     * @var string
     */
    public static $databaseColumnId = 'id';

    /**
     * [Active Record] Database ID Fields
     *
     * @var  array
     *
     * @note If using this, declare a static $multitonMap = array(); in the child class to prevent duplicate loading
     *       queries
     */
    protected static $databaseIdFields = ['id'];

    /**
     * [Active Record] Multition Map
     *
     * @var array
     */
    protected static $multitonMap = [];

    /**
     * [Node] Node Title
     *
     * @var string
     */
    public static $nodeTitle = 'Reports';

    //    /**
    //     * @var string
    //     */
    //    public static $databaseColumnOrder = 'updated_at DESC';

    /**
     * @return \IPS\Patterns\Bitwise|mixed|string|null
     */
    public function get__title()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    protected function get__description()
    {
        return 'Updated At: ' . \IPS\DateTime::ts($this->updated_at);
    }

    /**
     * @return string[]
     */
    protected function get__badge()
    {
        return array(
            0	=> 'ipsBadge ipsBadge_neutral ipsPos_right',
            2	=> 'Submitted',
        );
    }

    /**
     * [Node] Add/Edit Form
     *
     * @param	\IPS\Helpers\Form	$form	The form
     * @return	void
     */
    public function form(&$form)
    {
        if (\IPS\Settings::i()->dmca_claim_intro && \IPS\Dispatcher::i()->controllerLocation === 'front') {
            $form->addHtml(\IPS\Settings::i()->dmca_claim_intro);
        }

        $form->add(new \IPS\Helpers\Form\Text('name', $this->name ? $this->name : \IPS\Member::loggedIn()->name, true));
        $form->add(new \IPS\Helpers\Form\Text('copyright_name', $this->copyright_name ?? null, true, [
            'placeholder' => 'You must be the rights holder or and authorized agent thereof'
        ]));
        $form->add(new \IPS\Helpers\Form\Email('email', $this->email ? $this->email : \IPS\Member::loggedIn()->email, true));
        $form->add(new \IPS\Helpers\Form\Text('title', $this->title ?? null, false));
        $form->add(new \IPS\Helpers\Form\Text('company', $this->company ?? null, false));
        $form->add(new \IPS\Helpers\Form\Text('phone', $this->phone ?? null, true));
        $form->add(new \IPS\Helpers\Form\Address('address', $this->address ? \IPS\GeoLocation::buildFromJson($this->address) : null, true));
        $form->add(new \IPS\Helpers\Form\Member('member_id', $this->member_id ? \IPS\Member::load($this->member_id) : null, true));
        $form->add(new Select('type', $this->type ?? null, true, [
            'options' => [
                Topic::class => 'Forum Topic',
                'other' => 'Other'
            ],
            'toggles' => [
                Topic::class => ['topic'],
                'other' => ['other']
            ]
        ]));
        $form->add(new \IPS\Helpers\Form\Item('item', $this->item ?? null, true, [
            'class' => '\IPS\forums\Topic',
            'maxItems' => 50
        ], null, null, null, 'topic'));
        $form->add(new \IPS\Helpers\Form\Stack('url', $this->url ? explode(',', $this->url) : null, true, [
            'stackFieldType' => 'Url',
            'maxItems' => 50
        ], null, null, null, 'other'));
        $form->add(new Editor('description', $this->description ?? null, true, [
            'app' => 'dmca',
            'key' => 'ReportDescription',
            'autoSaveKey' => 'dmca_report',
        ]));
        $form->add(new Checkbox('accept_terms', false, true));
        $form->add(new \IPS\Helpers\Form\Text('signature', $this->signature ?? null, true, [
            'placeholder' => 'Enter your name to sign this submission'
        ]));
    }

    /**
     * [Node] Format form values from add/edit form for save
     *
     * @param	array	$values	Values from the form
     * @return	array
     */
    public function formatFormValues($values)
    {
        $values['member_id'] = $values['member_id']?->member_id;
        $values['address']	= json_encode($values['address']);

        if ($values['item'] && is_array($values['item'])) {
            $item = reset($values['item']);

            if (is_object($item)) {
                $class = get_class($item);
                $idColumn = $class::$databaseColumnId;

                $values['item'] = $item->$idColumn;
            }
        }

        return $values;
    }

    /**
     * @return void
     */
    public function save()
    {
        $this->updated_at = \IPS\DateTime::create()->getTimestamp();

        if ($this->_new) {
            $this->created_at = \IPS\DateTime::create()->getTimestamp();

            $member = \IPS\Member::load($this->email, 'email');
            if ($member && $member->member_id) {
                $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'submitted', null, [\IPS\Settings::i()->dmca_submitted_email, $this]);
                $notification->recipients->attach($member);
                $notification->send();
            } else {
                \IPS\Email::buildFromTemplate('dmca', 'submitted', [$this->name, \IPS\Settings::i()->dmca_submitted_email, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
            }
        }

        parent::save();
    }

    /**
     * @return Topic|null
     */
    public function item()
    {
        try {
            return match (true) {
                $this->type === Topic::class => Topic::load($this->item),
                default => null
            };
        } catch (\OutOfRangeException $exception) {
            return null;
        }

    }

    /**
     * @return \IPS\Http\Url|null
     */
    public function itemLink()
    {
        return match (true) {
            $this->item() instanceof Topic => $this->item()->url(),
            $this->type === 'other' => $this->url,
            default => null
        };
    }

    /**
     * @return null
     */
    public function deleteItem()
    {
        match (true) {
            $this->item() instanceof Topic => $this->item()->delete(),
            default => null
        };

        $member = \IPS\Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'deleted', null, [\IPS\Settings::i()->dmca_deleted_email, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'deleted', [$this->name, \IPS\Settings::i()->dmca_deleted_email, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        return null;
    }

    /**
     * @param $emailMessage
     * @return $this
     */
    public function approve($emailMessage)
    {
        $this->status = \IPS\dmca\Reports\Report::REPORT_STATUS_APPROVED;
        $this->save();

        $member = \IPS\Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'approved', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'approved', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        $infringingMember = \IPS\Member::load($this->member_id);
        if ($infringingMember && $infringingMember->member_id) {
            if ($infringingMember->copyright_strikes == 1) {
                $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'firststrike', null, [\IPS\Settings::i()->dmca_first_strike_email]);
                $notification->recipients->attach($infringingMember);
                $notification->send();
            } elseif ($infringingMember->copyright_strikes == 2) {
                $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'secondstrike', null, [\IPS\Settings::i()->dmca_second_strike_email]);
                $notification->recipients->attach($infringingMember);
                $notification->send();
            } else {
                $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'thirdstrike', null, [\IPS\Settings::i()->dmca_third_strike_email]);
                $notification->recipients->attach($infringingMember);
                $notification->send();

                if (\IPS\Settings::i()->dmca_group) {
                    $groups = explode(',', $infringingMember->mgroup_others);
                    $groupsToAdd = explode(',', \IPS\Settings::i()->dmca_group);
                    $newGroups = array_filter(array_unique(array_merge($groups, $groupsToAdd)));

                    $infringingMember->mgroup_others = implode(',', $newGroups);
                    $infringingMember->save();
                }
            }

            if (\IPS\Settings::i()->dmca_warning_points && \IPS\Settings::i()->dmca_warning_points > 0) {
                $infringingMember->warn_level += \IPS\Settings::i()->dmca_warning_points;
                $infringingMember->save();
            }
        }

        return $this;
    }

    /**
     * @param $emailMessage
     * @return $this
     */
    public function onhold($emailMessage)
    {
        $this->status = \IPS\dmca\Reports\Report::REPORT_STATUS_ON_HOLD;
        $this->save();

        $member = \IPS\Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'onhold', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'onhold', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        return $this;
    }

    /**
     * @param $emailMessage
     * @return $this
     */
    public function deny($emailMessage)
    {
        $this->status = \IPS\dmca\Reports\Report::REPORT_STATUS_DENIED;
        $this->save();

        $member = \IPS\Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'denied', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'denied', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        return $this;
    }

    /**
     * @param Reason|null $reason
     * @return Warning
     */
    public function warn(Reason $reason)
    {
        $infringingMember = \IPS\Member::load($this->member_id);

        $warning = new Warning();
        $warning->member = $infringingMember->member_id;
        $warning->moderator = \IPS\Member::loggedIn()->member_id;
        $warning->date = \IPS\DateTime::create()->getTimestamp();
        $warning->reason = $reason->id;
        $warning->points = $reason->points;
        $warning->note_member = $reason->notes;
        $warning->note_mods = null;
        $warning->acknowledged = !\IPS\Settings::i()->dcma_warning_acknowledgement;
        $warning->expire_date = -1;
        $warning->cheev_point_reduction = 0;

        $postingRestriction = \IPS\Settings::i()->dcma_warning_posting_restriction;
        if ($postingRestriction && $postingRestriction !== '-1') {
            $interval = 'P'.$postingRestriction.'D';
            $infringingMember->restrict_post = \IPS\DateTime::create()->add(new \DateInterval($interval))->getTimestamp();
            $warning->rpa = $interval;
        }

        $moderateContent = \IPS\Settings::i()->dcma_warning_moderate_content;
        if ($moderateContent && $moderateContent !== '-1') {
            $interval = 'P'.$moderateContent.'D';
            $infringingMember->mod_posts = \IPS\DateTime::create()->add(new \DateInterval($interval))->getTimestamp();
            $warning->mq = $interval;
        }

        $infringingMember->members_bitoptions['unacknowledged_warnings'] = (bool) \IPS\Settings::i()->dcma_warning_acknowledgement;
        $infringingMember->save();
        $warning->save();

        return $warning;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->report($this);
    }
}
