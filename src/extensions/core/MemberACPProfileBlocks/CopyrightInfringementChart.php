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

use IPS\dmca\Reports\Report;
use IPS\Http\Url;

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
        $chart = new \IPS\Helpers\Chart\Database(Url::internal('app=core&module=members&controller=members&do=view')->setQueryString('id', \IPS\Request::i()->id)->setQueryString('tab', 'dmca_CopyrightInfringements'), Report::$databaseTable, 'created_at', '', array(
            'isStacked' => false,
            'backgroundColor' => '#ffffff',
            'hAxis' => array('gridlines' => array('color' => '#f5f5f5')),
            'lineWidth' => 1,
            'areaOpacity' => 0.4
        ));

        $chart->where[] = ['member_id = ?', \IPS\Request::i()->id];
        $chart->where[] = ['status = ?', \IPS\dmca\Reports\Report::REPORT_STATUS_APPROVED];

        $chart->addSeries(\IPS\Member::loggedIn()->language()->addToStack('dcma_chart_infringements'), 'number', 'COUNT(*)');
        $chart->title = \IPS\Member::loggedIn()->language()->addToStack('dcma_chart_infringements_title');
        $chart->availableTypes = array( 'AreaChart', 'ColumnChart', 'BarChart' );

        return \IPS\Theme::i()->getTemplate('chart', 'dmca', 'admin')->chart($chart);
    }
}
