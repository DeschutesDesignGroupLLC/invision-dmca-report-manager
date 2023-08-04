<?php
/**
 * @brief		deleteCopyrightClaimContent Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	dmca
 * @since		02 Aug 2023
 */

namespace IPS\dmca\tasks;

use IPS\Patterns\ActiveRecordIterator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * deleteCopyrightClaimContent Task
 */
class _deleteCopyrightClaimContent extends \IPS\Task
{
    /**
     * Execute
     *
     * If ran successfully, should return anything worth logging. Only log something
     * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
     * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
     * Tasks should execute within the time of a normal HTTP request.
     *
     * @return	mixed	Message to log or NULL
     * @throws	\IPS\Task\Exception
     */
    public function execute()
    {
        if ($interval = \IPS\Settings::i()->dmca_automatic_deletion) {
            $where[] = ['created_at < ?', \IPS\DateTime::create()->sub(new \DateInterval('P' . $interval . 'D'))->getTimestamp()];
            $where[] = ['status = ?', \IPS\dmca\Reports\Report::REPORT_STATUS_SUBMITTED];

            foreach (new ActiveRecordIterator(\IPS\Db::i()->select('*', \IPS\dmca\Reports\Report::$databaseTable, $where), 'IPS\dmca\Reports\Report') as $report) {
                $report->approve(\IPS\Settings::i()->dmca_approval_email);
                $report->deleteItem();
            }
        }

        return null;
    }

    /**
     * Cleanup
     *
     * If your task takes longer than 15 minutes to run, this method
     * will be called before execute(). Use it to clean up anything which
     * may not have been done
     *
     * @return	void
     */
    public function cleanup()
    {

    }
}
