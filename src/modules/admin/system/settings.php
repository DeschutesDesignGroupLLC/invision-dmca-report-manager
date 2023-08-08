<?php

namespace IPS\dmca\modules\admin\system;

use IPS\core\Warnings\Reason;
use IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    /**
     * @return	void
     */
    protected function manage()
    {
        $form = new Form();

        $groups = [];
        foreach (\IPS\Member\Group::groups(true, false) as $group) {
            $groups[$group->g_id] = $group->name;
        }

        $form->addTab('dmca_settings_tab_claims');
        $form->addHeader('dmca_settings');
        $form->add(new Form\Interval('dmca_automatic_deletion', \IPS\Settings::i()->dmca_automatic_deletion, false, [
            'valueAs' => Form\Interval::DAYS
        ]));
        $form->addHeader('dmca_settings_groups');
        $form->add(new Form\Select('dmca_group', explode(',', \IPS\Settings::i()->dmca_group), false, [
            'options' => $groups,
            'multiple' => true,
        ]));
        $form->addHeader('dmca_settings_warnings');
        $form->add(new Form\YesNo('dcma_warning_enable', \IPS\Settings::i()->dcma_warning_enable, true));
        $form->add(new Form\YesNo('dcma_warning_acknowledgement', \IPS\Settings::i()->dcma_warning_acknowledgement, true));
        $form->add(new Form\Interval('dcma_warning_posting_restriction', \IPS\Settings::i()->dcma_warning_posting_restriction ?? -1, false, [
            'valueAs' => Form\Interval::DAYS,
            'unlimited' => -1,
            'unlimitedLang' => 'dcma_warning_posting_restriction_none'
        ]));
        $form->add(new Form\Interval('dcma_warning_moderate_content', \IPS\Settings::i()->dcma_warning_moderate_content ?? -1, false, [
            'valueAs' => Form\Interval::DAYS,
            'unlimited' => -1,
            'unlimitedLang' => 'dcma_warning_moderate_content_none'
        ]));
        $form->add(new Form\Node('dmca_warning', \IPS\Settings::i()->dmca_warning, false, [
            'class' => Reason::class
        ]));

        $form->addTab('dmca_settings_tab_form');
        $form->addHeader('dmca_settings');
        $form->add(new Form\Editor('dmca_claim_intro', \IPS\Settings::i()->dmca_claim_intro, false, [
            'autoSaveKey' => 'dmca_claim_intro',
            'app' => 'dmca',
            'key' => 'ClaimIntro'
        ]));
        $form->add(new Form\Editor('dmca_faq', \IPS\Settings::i()->dmca_faq, true, [
            'autoSaveKey' => 'dmca_faq',
            'app' => 'dmca',
            'key' => 'Faq'
        ]));
        $form->add(new Form\Editor('dmca_final', \IPS\Settings::i()->dmca_final, true, [
            'autoSaveKey' => 'dmca_final',
            'app' => 'dmca',
            'key' => 'FinalSteps'
        ]));

        $form->addTab('dmca_settings_tab_notifications');
        $form->addHeader('dmca_settings');
        $form->add(new Form\Editor('dmca_submitted_email', \IPS\Settings::i()->dmca_submitted_email, true, [
            'autoSaveKey' => 'dmca_submitted_email',
            'app' => 'dmca',
            'key' => 'SubmittedEmail'
        ]));
        $form->add(new Form\Editor('dmca_approval_email', \IPS\Settings::i()->dmca_approval_email, true, [
            'autoSaveKey' => 'dmca_approval_email',
            'app' => 'dmca',
            'key' => 'ApprovalEmail'
        ]));
        $form->add(new Form\Editor('dmca_onhold_email', \IPS\Settings::i()->dmca_onhold_email, true, [
            'autoSaveKey' => 'dmca_onhold_email',
            'app' => 'dmca',
            'key' => 'OnHoldEmail'
        ]));
        $form->add(new Form\Editor('dmca_denied_email', \IPS\Settings::i()->dmca_denied_email, true, [
            'autoSaveKey' => 'dmca_denied_email',
            'app' => 'dmca',
            'key' => 'DeniedEmail'
        ]));
        $form->add(new Form\Editor('dmca_deleted_email', \IPS\Settings::i()->dmca_deleted_email, true, [
            'autoSaveKey' => 'dmca_deleted_email',
            'app' => 'dmca',
            'key' => 'DeletedEmail'
        ]));

        $form->addTab('dmca_settings_tab_strike_notifications');
        $form->addHeader('dmca_settings');
        $form->add(new Form\Editor('dmca_first_strike_email', \IPS\Settings::i()->dmca_first_strike_email, true, [
            'autoSaveKey' => 'dmca_first_strike_email',
            'app' => 'dmca',
            'key' => 'FirstStrikeEmail'
        ]));
        $form->add(new Form\Editor('dmca_second_strike_email', \IPS\Settings::i()->dmca_second_strike_email, true, [
            'autoSaveKey' => 'dmca_second_strike_email',
            'app' => 'dmca',
                'key' => 'SecondStrikeEmail'
        ]));
        $form->add(new Form\Editor('dmca_third_strike_email', \IPS\Settings::i()->dmca_third_strike_email, true, [
            'autoSaveKey' => 'dmca_third_strike_email',
            'app' => 'dmca',
            'key' => 'ThirdStrikeEmail'
        ]));

        if ($form->values()) {
            $form->saveAsSettings();
        }

        \IPS\Output::i()->title = 'Settings';
        \IPS\Output::i()->output = $form;
    }
}
