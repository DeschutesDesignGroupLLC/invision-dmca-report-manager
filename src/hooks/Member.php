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
        $where[] = ['status = ?', \IPS\dmca\Reports\Report::REPORT_STATUS_APPROVED];

        return \IPS\Db::i()->select('COUNT(*)', \IPS\dmca\Reports\Report::$databaseTable, $where)->first();
    }
}
