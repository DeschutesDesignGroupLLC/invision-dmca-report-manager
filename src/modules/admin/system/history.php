<?php

namespace IPS\dmca\modules\admin\system;

use IPS\dmca\Reports\Report;
use IPS\Http\Url;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * history
 */
class _history extends \IPS\Dispatcher\Controller
{
    /**
     * @brief	Has been CSRF-protected
     */
    public static $csrfProtected = true;

    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('history_manage');
        parent::execute();
    }

    /**
     * Manage
     *
     * @return	void
     */
    protected function manage()
    {
        $table = new \IPS\Helpers\Table\Db(\IPS\dmca\Reports\Report::$databaseTable, \IPS\Http\Url::internal('app=dmca&module=system&controller=history'));
        $table->include = ['id', 'name', 'email', 'member_id', 'report', 'item', 'created_at', 'status', 'automation'];
        $table->langPrefix = 'dmca_reports_';
        $table->quickSearch = [['name', 'email'], 'name'];
        $table->sortBy = $table->sortBy ?: 'created_at';

        $table->filters = array(
            'dmca_report_submitted' => [ 'status=1' ],
            'dmca_report_approved' => [ 'status=2' ],
            'dmca_report_denied' => [ 'status=3' ],
            'dmca_report_pending' => [ 'status=4' ],
            'dmca_report_on_hold' => [ 'status=5' ],
        );

        $table->parsers = array(
            'member_id' => function ($val) {
                return \IPS\Member::load($val)->link();
            },
            'report' => function ($val, $row) {
                $url = Url::internal("app=dmca&module=system&controller=reports&do=form&id={$row['id']}");
                return "<a href=\"$url\">Report</a>";
            },
            'item' => function ($val, $row) {
                $report = Report::load($row['id']);

                if ($link = $report->itemLink()) {
                    return "<a href=\"$link\">Item</a>";
                }
            },
            'created_at' => function ($val, $row) {
                return \IPS\DateTime::ts($val)->html();
            },
            'status' => function ($val) {
                $text = match (true) {
                    $val === Report::REPORT_STATUS_APPROVED => 'Approved',
                    $val === Report::REPORT_STATUS_DENIED => 'Denied',
                    $val === Report::REPORT_STATUS_PENDING => 'Pending',
                    $val === Report::REPORT_STATUS_ON_HOLD => 'On Hold',
                    default => 'Submitted',
                };

                $class = match (true) {
                    $val === Report::REPORT_STATUS_APPROVED => 'ipsBadge_positive',
                    $val === Report::REPORT_STATUS_DENIED => 'ipsBadge_negative',
                    $val === Report::REPORT_STATUS_PENDING => 'ipsBadge_warning',
                    $val === Report::REPORT_STATUS_ON_HOLD => 'On ipsBadge_intermediary',
                    default => 'ipsBadge_new',
                };

                return \IPS\Theme::i()->getTemplate('history', 'dmca', 'admin')->badge($text, $class);
            },
            'automation' => function ($val, $row) {
                $report = Report::load($row['id']);

                return match (true) {
                    ($report->status === Report::REPORT_STATUS_SUBMITTED || $report->status === Report::REPORT_STATUS_APPROVED) && $report->item() => \IPS\Theme::i()->getTemplate('history', 'dmca', 'admin')->badge('Automatic Removal Pending', 'ipsBadge_warning'),
                    $report->status === Report::REPORT_STATUS_APPROVED && !$report->item() => \IPS\Theme::i()->getTemplate('history', 'dmca', 'admin')->badge('Content Deleted', 'ipsBadge_positive'),
                    $report->status === Report::REPORT_STATUS_DENIED => \IPS\Theme::i()->getTemplate('history', 'dmca', 'admin')->badge('Content Preserved', 'ipsBadge_negative'),
                    default => \IPS\Theme::i()->getTemplate('history', 'dmca', 'admin')->badge('Manual Review Required', 'ipsBadge_intermediary')
                };
            }
        );

        $table->rowButtons = function ($row) {
            try {
                $report = Report::load($row['id']);
            } catch (\OutOfRangeException $exception) {
            }

            $return = array();

            if ($report->status !== Report::REPORT_STATUS_APPROVED) {
                $return['approve'] = [
                    'icon' => 'check-circle',
                    'title' => 'dmca_approve',
                    'link' => \IPS\Http\Url::internal("app=dmca&module=system&controller=reports&do=approve&id=$report->id")->csrf(),
                    'data' => [
                        'ipsDialog' => '',
                        'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('dcma_send_mail'),
                        'ipsDialog-flashmessage' => \IPS\Member::loggedIn()->language()->addToStack('dmca_report_approved')
                    ]
                ];
            }

            if ($report->status !== Report::REPORT_STATUS_ON_HOLD) {
                $return['on_hold'] = [
                    'icon' => 'pause',
                    'title' => 'dmca_on_hold',
                    'link' => \IPS\Http\Url::internal("app=dmca&module=system&controller=reports&do=onhold&id=$report->id"),
                    'data' => [
                        'ipsDialog' => '',
                        'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('dcma_send_mail'),
                        'ipsDialog-flashmessage' => \IPS\Member::loggedIn()->language()->addToStack('dmca_report_on_hold')
                    ]
                ];
            }

            if ($report->status !== Report::REPORT_STATUS_DENIED) {
                $return['deny'] = [
                    'icon' => 'ban',
                    'title' => 'dmca_deny',
                    'link' => \IPS\Http\Url::internal("app=dmca&module=system&controller=reports&do=deny&id=$report->id"),
                    'data' => [
                        'ipsDialog' => '',
                        'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('dcma_send_mail'),
                        'ipsDialog-flashmessage' => \IPS\Member::loggedIn()->language()->addToStack('dmca_report_denied')
                    ]
                ];
            }

            $return['delete'] = [
                'icon' => 'trash',
                'title' => 'dmca_delete',
                'link' => \IPS\Http\Url::internal("app=dmca&module=system&controller=reports&do=delete&id=$report->id"),
            ];

            return $return;
        };

        \IPS\Output::i()->title = 'History';
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('global', 'core')->block('title', (string) $table);
    }
}
