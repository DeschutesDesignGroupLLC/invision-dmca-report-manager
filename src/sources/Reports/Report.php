<?php

namespace IPS\dmca\Reports;

use IPS\Application;
use IPS\core\Warnings\Reason;
use IPS\core\Warnings\Warning;
use IPS\DateTime;
use IPS\Db;
use IPS\Email;
use IPS\forums\Topic;
use IPS\forums\Topic\Post;
use IPS\gallery\Album;
use IPS\gallery\Image;
use IPS\Helpers\Form\Address;
use IPS\Helpers\Form\Checkbox;
use IPS\Helpers\Form\Editor;
use IPS\Helpers\Form\Stack;
use IPS\Helpers\Form\Text;
use IPS\Http\Url;
use IPS\Member;
use IPS\Notification;
use IPS\Settings;

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
        return 'Updated At: ' . DateTime::ts($this->updated_at);
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
        if (Settings::i()->dmca_claim_intro && \IPS\Dispatcher::i()->controllerLocation === 'front') {
            $form->addHtml(Settings::i()->dmca_claim_intro);
        }

        $form->add(new Text('name', $this->name ? $this->name : Member::loggedIn()->name, true));
        $form->add(new Text('copyright_name', $this->copyright_name ?? null, true, [
            'placeholder' => 'You must be the rights holder or and authorized agent thereof'
        ]));
        $form->add(new \IPS\Helpers\Form\Email('email', $this->email ? $this->email : Member::loggedIn()->email, true));
        $form->add(new Text('title', $this->title ?? null, false));
        $form->add(new Text('company', $this->company ?? null, false));
        $form->add(new Text('phone', $this->phone ?? null, true));
        $form->add(new Address('address', $this->address ? \IPS\GeoLocation::buildFromJson($this->address) : null, true));
        $form->add(new Stack('urls', $this->urls ? explode(',', $this->urls) : null, true, [
            'stackFieldType' => 'Url',
            'maxItems' => 50
        ], function ($url) {
            $urls = is_array($url) ? $url : [$url];
            foreach ($urls as $url) {
                if (!self::findContentItem($url)) {
                    throw new \DomainException('dmca_cant_find_item');
                }
            }
        }, null, null, 'other'));
        $form->add(new Editor('description_work', $this->description_work ?? null, true, [
            'app' => 'dmca',
            'key' => 'ReportDescription',
            'autoSaveKey' => 'dmca_report',
        ]));
        $form->add(new Checkbox('accept_terms', false, true));
        $form->add(new Text('signature', $this->signature ?? null, true, [
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
        $values['address']	= json_encode($values['address']);

        return $values;
    }

    /**
     * @return void
     */
    public function save()
    {
        $this->updated_at = DateTime::create()->getTimestamp();

        if ($this->_new) {
            $this->created_at = DateTime::create()->getTimestamp();

            $member = Member::load($this->email, 'email');
            if ($member && $member->member_id) {
                $notification = new Notification(Application::load('dmca'), 'submitted', null, [Settings::i()->dmca_submitted_email, $this]);
                $notification->recipients->attach($member);
                $notification->send();
            } else {
                Email::buildFromTemplate('dmca', 'submitted', [$this->name, Settings::i()->dmca_submitted_email, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
            }

            $urls = explode(',', $this->urls);

            $emailProcessed = [];
            foreach ($urls as $url) {
                $item = self::findContentItem($url);

                if (\is_object($item) && method_exists($item, 'author')) {
                    $infringingMember = $item->author();

                    if ($infringingMember && $infringingMember->member_id && !\in_array($infringingMember->member_id, $emailProcessed)) {

                        $notification = new Notification(Application::load('dmca'), 'filed', null, [Settings::i()->dmca_claim_filed_email, $this]);
                        $notification->recipients->attach($infringingMember);
                        $notification->send();

                        $emailProcessed[] = $infringingMember->member_id;
                    }
                }
            }
        }

        parent::save();
    }

    /**
     * @param $url
     * @return mixed|null
     */
    public static function findContentItem($url)
    {
        if (!$url instanceof Url) {
            $url = Url::createFromString($url);
        }

        $classes = [];
        if (isset($url->hiddenQueryString['app'])) {
            $classes = match ($url->hiddenQueryString['app']) {
                'forums' => [Topic::class, Post::class],
                'gallery' => [Image::class, Album::class],
            };
        }

        foreach ($classes as $class) {
            try {
                return $class::loadFromUrl($url);
            } catch (\OutOfRangeException|\InvalidArgumentException $exception) {
            }
        }

        return null;
    }

    /**
     * @return null
     */
    public function deleteItems()
    {
        $urls = explode(',', $this->urls);

        foreach ($urls as $url) {
            try {
                $item = self::findContentItem($url);
                if ($item) {
                    $item->delete();
                }
            } catch (\Exception $exception) {

            }
        }

        $member = Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new Notification(Application::load('dmca'), 'deleted', null, [Settings::i()->dmca_deleted_email, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            Email::buildFromTemplate('dmca', 'deleted', [$this->name, Settings::i()->dmca_deleted_email, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
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

        $member = Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new Notification(Application::load('dmca'), 'approved', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            Email::buildFromTemplate('dmca', 'approved', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        $urls = explode(',', $this->urls);

        $emailAndWarningProcessed = [];
        foreach ($urls as $url) {
            $item = self::findContentItem($url);

            if (\is_object($item) && method_exists($item, 'author')) {
                $infringingMember = $item->author();

                if ($infringingMember && $infringingMember->member_id && !\in_array($infringingMember->member_id, $emailAndWarningProcessed)) {
                    Db::i()->insert(Strike::$databaseTable, [
                        'report_id' => $this->id,
                        'member_id' => $infringingMember->member_id
                    ]);

                    if ($infringingMember->copyright_strikes == 1) {
                        $notification = new Notification(Application::load('dmca'), 'firststrike', null, [Settings::i()->dmca_first_strike_email, $this]);
                        $notification->recipients->attach($infringingMember);
                        $notification->send();
                    } elseif ($infringingMember->copyright_strikes == 2) {
                        $notification = new Notification(Application::load('dmca'), 'secondstrike', null, [Settings::i()->dmca_second_strike_email, $this]);
                        $notification->recipients->attach($infringingMember);
                        $notification->send();
                    } else {
                        $notification = new Notification(Application::load('dmca'), 'thirdstrike', null, [Settings::i()->dmca_third_strike_email, $this]);
                        $notification->recipients->attach($infringingMember);
                        $notification->send();

                        if (Settings::i()->dmca_group) {
                            $groups = explode(',', $infringingMember->mgroup_others);
                            $groupsToAdd = explode(',', Settings::i()->dmca_group);
                            $newGroups = array_filter(array_unique(array_merge($groups, $groupsToAdd)));

                            $infringingMember->mgroup_others = implode(',', $newGroups);
                            $infringingMember->save();
                        }
                    }

                    if ($reason = \IPS\Settings::i()->dmca_warning) {
                        try {
                            $reason = Reason::load($reason);
                            $this->warn($reason, $infringingMember);
                        } catch (\Exception $exception) {
                        }
                    }

                    $emailAndWarningProcessed[] = $infringingMember->member_id;
                }
            }
        }

        $this->deleteItems();

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

        $member = Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new Notification(Application::load('dmca'), 'onhold', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            Email::buildFromTemplate('dmca', 'onhold', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
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

        $member = Member::load($this->email, 'email');
        if ($member && $member->member_id) {
            $notification = new Notification(Application::load('dmca'), 'denied', null, [$emailMessage, $this]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            Email::buildFromTemplate('dmca', 'denied', [$this->name, $emailMessage, $this], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        return $this;
    }

    /**
     * @param Reason $reason
     * @param Member $infringingMember
     * @return Warning
     * @throws \Exception
     */
    public function warn(Reason $reason, Member $infringingMember)
    {
        $warning = new Warning();
        $warning->member = $infringingMember->member_id;
        $warning->moderator = Member::loggedIn()->member_id;
        $warning->date = DateTime::create()->getTimestamp();
        $warning->reason = $reason->id;
        $warning->points = $reason->points;
        $warning->note_member = $reason->notes;
        $warning->note_mods = null;
        $warning->acknowledged = !Settings::i()->dcma_warning_acknowledgement;
        $warning->expire_date = -1;
        $warning->cheev_point_reduction = 0;

        $postingRestriction = Settings::i()->dcma_warning_posting_restriction;
        if ($postingRestriction && $postingRestriction !== '-1') {
            $interval = 'P'.$postingRestriction.'D';
            $infringingMember->restrict_post = DateTime::create()->add(new \DateInterval($interval))->getTimestamp();
            $warning->rpa = $interval;
        }

        $moderateContent = Settings::i()->dcma_warning_moderate_content;
        if ($moderateContent && $moderateContent !== '-1') {
            $interval = 'P'.$moderateContent.'D';
            $infringingMember->mod_posts = DateTime::create()->add(new \DateInterval($interval))->getTimestamp();
            $warning->mq = $interval;
        }

        $infringingMember->members_bitoptions['unacknowledged_warnings'] = (bool) Settings::i()->dcma_warning_acknowledgement;
        $infringingMember->save();
        $warning->save();

        return $warning;
    }

    /**
     * @return void
     */
    public function delete()
    {
        Db::i()->delete(Strike::$databaseTable, ['report_id=?', $this->id]);

        parent::delete();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->report($this);
    }
}
