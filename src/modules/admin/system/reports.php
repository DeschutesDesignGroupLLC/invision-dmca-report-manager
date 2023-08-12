<?php

namespace IPS\dmca\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Helpers\Form;

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * reports
 */
class _reports extends \IPS\Node\Controller
{
    /**
     * @brief	Has been CSRF-protected
     */
    public static $csrfProtected = true;

    /**
     * Node Class
     */
    protected $nodeClass = 'IPS\dmca\Reports\Report';

    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('reports_manage');
        parent::execute();
    }

    /**
     * @return void
     */
    public function approve()
    {
        \IPS\Request::i()->confirmedDelete('dmca_approve_title', 'dmca_approve_message', 'dmca_approve_submit');

        $form = new Form('dmca_approval_email', 'dcma_send_mail');
        $form->hiddenValues['wasConfirmed'] = 1;
        $form->add(new Form\Editor('dmca_approval_email', \IPS\Settings::i()->dmca_approval_email, true, [
            'autoSaveKey' => 'dmca_approval_email',
            'app' => 'dmca',
            'key' => 'ApprovalEmail'
        ]));

        if ($values = $form->values()) {
            try {
                $report = \IPS\dmca\Reports\Report::load(\IPS\Request::i()->id);
                $report->approve($values['dmca_approval_email']);
            } catch (\OutOfRangeException $exception) {
            }

            \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=dmca&module=system&controller=history'), 'dmca_report_approved');
        }

        \IPS\Output::i()->output = $form;
    }

    /**
     * @return void
     */
    public function onhold()
    {
        $form = new Form('dmca_onhold_email', 'dcma_send_mail');
        $form->add(new Form\Editor('dmca_onhold_email', \IPS\Settings::i()->dmca_onhold_email, true, [
            'autoSaveKey' => 'dmca_onhold_email',
            'app' => 'dmca',
            'key' => 'OnHoldEmail'
        ]));

        if ($values = $form->values()) {
            try {
                $report = \IPS\dmca\Reports\Report::load(\IPS\Request::i()->id);
                $report->onhold($values['dmca_onhold_email']);
            } catch (\OutOfRangeException $exception) {
            }

            \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=dmca&module=system&controller=history'), 'dmca_report_on_hold');
        }

        \IPS\Output::i()->output = $form;
    }

    /**
     * @return void
     */
    public function deny()
    {
        $form = new Form('dmca_denied_email', 'dcma_send_mail');
        $form->add(new Form\Editor('dmca_denied_email', \IPS\Settings::i()->dmca_denied_email, true, [
            'autoSaveKey' => 'dmca_denied_email',
            'app' => 'dmca',
            'key' => 'DeniedEmail'
        ]));

        if ($values = $form->values()) {
            try {
                $report = \IPS\dmca\Reports\Report::load(\IPS\Request::i()->id);
                $report->deny($values['dmca_denied_email']);
            } catch (\OutOfRangeException $exception) {
            }

            \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=dmca&module=system&controller=history'), 'dmca_report_denied');
        }

        \IPS\Output::i()->output = $form;

    }

    /**
     * @return void
     */
    public function delete()
    {
        \IPS\Request::i()->confirmedDelete();

        try {
            $report = \IPS\dmca\Reports\Report::load(\IPS\Request::i()->id);
            $report->delete();
        } catch (\OutOfRangeException $exception) {
        }

        \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=dmca&module=system&controller=history'), 'dmca_report_deleted');
    }
}
