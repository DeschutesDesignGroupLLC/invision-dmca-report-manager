<?php
/**
 * @brief		ACP Member Profile Block
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	DMCA Report Manager
 * @since		04 Aug 2023
 */

namespace IPS\dmca\extensions\core\MemberACPProfileBlocks;

use IPS\dmca\Reports\Strike;
use IPS\dmca\Reports\Report;
use IPS\Helpers\Chart\Database;
use IPS\Http\Url;
use IPS\Member;
use IPS\Request;
use IPS\Theme;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief	ACP Member Profile Block
 */
class _CopyrightInfringementChart extends \IPS\core\MemberACPProfile\Block
{
    /**
     * Get output
     *
     * @return	string
     */
    public function output(): string
    {
        $chart = new Database(Url::internal('app=core&module=members&controller=members&do=view')->setQueryString('id', Request::i()->id)->setQueryString('tab', 'dmca_CopyrightInfringements'), Strike::$databaseTable, 'created_at', '', array(
            'isStacked' => false,
            'backgroundColor' => '#ffffff',
            'hAxis' => array('gridlines' => array('color' => '#f5f5f5')),
            'lineWidth' => 1,
            'areaOpacity' => 0.4
        ));

        $chart->joins[] = ['dmca_reports', 'dmca_reports.id=dmca_strikes.report_id'];
        $chart->where[] = ['member_id = ?', Request::i()->id];
        $chart->where[] = ['status = ?', Report::REPORT_STATUS_APPROVED];

        $chart->addSeries(Member::loggedIn()->language()->addToStack('dcma_chart_infringements'), 'number', 'COUNT(*)');
        $chart->title = Member::loggedIn()->language()->addToStack('dcma_chart_infringements_title');
        $chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );

        return Theme::i()->getTemplate('chart', 'dmca', 'admin')->chart($chart);
    }
}
