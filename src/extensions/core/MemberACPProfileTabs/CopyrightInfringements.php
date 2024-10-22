<?php
/**
 * @brief		ACP Member Profile Tab
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	DMCA Report Manager
 * @since		04 Aug 2023
 */

namespace IPS\dmca\extensions\core\MemberACPProfileTabs;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief	ACP Member Profile Tab
 */
class _CopyrightInfringements extends \IPS\core\MemberACPProfile\MainTab
{
    /**
     * Get left-column blocks
     *
     * @return	array
     */
    public function leftColumnBlocks(): array
    {
        return [];
    }

    /**
     * Get main-column blocks
     *
     * @return	array
     */
    public function mainColumnBlocks(): array
    {
        return array(
            'IPS\dmca\extensions\core\MemberACPProfileBlocks\CopyrightInfringementChart',
        );
    }

    /**
     * Get right-column blocks
     *
     * @return	array
     */
    public function rightColumnBlocks(): array
    {
        return [];
    }
}
