<?php

namespace IPS\dmca\modules\admin\system;

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

        $form->addTab('dmca_settings_tab_claims');
        $form->addHeader('dmca_settings');
        $form->add(new Form\Interval('dmca_automatic_deletion', \IPS\Settings::i()->dmca_automatic_deletion, false, [
            'valueAs' => \IPS\Helpers\Form\Interval::DAYS
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

        if ($form->values()) {
            $form->saveAsSettings();
        }

        \IPS\Output::i()->title = 'Settings';
        \IPS\Output::i()->output = $form;
    }
}
