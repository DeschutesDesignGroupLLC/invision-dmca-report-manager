//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

/**
 * @mixin \IPS\Member
 */
class dmca_hook_Member extends _HOOK_CLASS_
{
    public function get_copyright_strikes()
    {
        $where[] = ['member_id = ?', $this->member_id];
        $where[] = ['dmca_reports.status = ?', \IPS\dmca\Reports\Report::REPORT_STATUS_APPROVED];

        $select = \IPS\Db::i()->select('COUNT(*)', \IPS\dmca\Reports\Strike::$databaseTable, $where);
        $select->join(\IPS\dmca\Reports\Report::$databaseTable, 'dmca_reports.id=dmca_strikes.report_id');

        return $select->first();
    }
}
