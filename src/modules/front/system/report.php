<?php

namespace IPS\dmca\modules\front\system;

use IPS\Helpers\Form;
use IPS\Helpers\Wizard;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * report
 */
class _report extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        parent::execute();
    }

    /**
     * @return	void
     */
    protected function manage()
    {
        $wizard = new Wizard([
            'dmca_wizard_faq' => function ($data) {
                $form = new Form('dmca_wizard_faq', 'continue');
                $form->class = 'ipsForm_fullWidth';
                $form->addHtml(\IPS\Settings::i()->dmca_faq ?? 'Please enter some Frequently Asked Questions.');
                $form->addHtml('<hr class="ipsSpacer_top" style="border-top-color: rgb( var(--theme-area_background_light) )">');
                $form->add(new Form\Checkbox('dmca_wizard_faq_read', false, true));

                if ($values = $form->values()) {
                    if ($values['dmca_wizard_faq_read'] === false) {
                        $form->error = \IPS\Member::loggedIn()->language()->addToStack('dmca_wizard_faq_error');
                    } else {
                        return $values;
                    }
                }

                return \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->faq($form);
            },
            'dmca_wizard_report' => function ($data) {
                $report = new \IPS\dmca\Reports\Report();
                $form = new Form('dmca_wizard_report', 'dmca_wizard_report_submit');
                $form->class = 'ipsForm_fullWidth';
                $report->form($form);

                if ($values = $form->values()) {
                    $report->saveForm($report->formatFormValues($values));

                    return $values;
                }

                return \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->form($form);
            },
            'dmca_wizard_final' => function ($data) {
                return \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->final(\IPS\Settings::i()->dmca_final ?? 'Please enter some Final Steps.');
            }
        ], \IPS\Http\Url::internal('app=dmca&module=system&controller=report', 'front', 'dmca_report'));

        \IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
        \IPS\Output::i()->sidebar['enabled'] = true;
        \IPS\Output::i()->title = 'Report';
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('report', 'dmca', 'front')->wizard($wizard);
    }
}
