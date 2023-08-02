<?php

namespace IPS\dmca\Reports;

use IPS\Email;
use IPS\forums\Topic;
use IPS\Helpers\Form\Editor;
use IPS\Helpers\Form\Select;

class _Report extends \IPS\Node\Model
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
        $form->add(new \IPS\Helpers\Form\Text('name', $this->name ? $this->name : \IPS\Member::loggedIn()->name, true));
        $form->add(new \IPS\Helpers\Form\Email('email', $this->email ? $this->email : \IPS\Member::loggedIn()->email, true));
        $form->add(new \IPS\Helpers\Form\Text('title', $this->title ?? null, true));
        $form->add(new \IPS\Helpers\Form\Text('company', $this->company ?? null, true));
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
            'maxItems' => 1
        ], null, null, null, 'topic'));
        $form->add(new \IPS\Helpers\Form\Url('url', $this->urls ? explode(',', $this->urls) : null, true, [], null, null, null, 'other'));
        $form->add(new Editor('description', $this->description ?? null, true, [
            'app' => 'dmca',
            'key' => 'ReportDescription',
            'autoSaveKey' => 'dmca_report'
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
     * @param $values
     * @return void
     */
    public function saveForm($values)
    {
        if (!$this->id) {
            $values['created_at'] = \IPS\DateTime::create()->getTimestamp();
        }

        $values['updated_at'] = \IPS\DateTime::create()->getTimestamp();

        parent::saveForm($values);
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

    public function deleteItem()
    {
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
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'approved', null, [$emailMessage]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'approved', [$this->name, $emailMessage], Email::TYPE_TRANSACTIONAL)->send($this->email);
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
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'onhold', null, [$emailMessage]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'onhold', [$this->name, $emailMessage], Email::TYPE_TRANSACTIONAL)->send($this->email);
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
            $notification = new \IPS\Notification(\IPS\Application::load('dmca'), 'denied', null, [$emailMessage]);
            $notification->recipients->attach($member);
            $notification->send();
        } else {
            \IPS\Email::buildFromTemplate('dmca', 'denied', [$this->name, $emailMessage], Email::TYPE_TRANSACTIONAL)->send($this->email);
        }

        return $this;
    }
}
