<?php
/**
 * @brief		Front Navigation Extension: FileAClaim
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	DMCA Report Manager
 * @since		01 Aug 2023
 */

namespace IPS\dmca\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Front Navigation Extension: FileAClaim
 */
class _FileAClaim extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
    /**
     * Get Type Title which will display in the AdminCP Menu Manager
     *
     * @return	string
     */
    public static function typeTitle(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_dmca');
    }

    /**
     * Can this item be used at all?
     * For example, if this will link to a particular feature which has been diabled, it should
     * not be available, even if the user has permission
     *
     * @return	bool
     */
    public static function isEnabled(): bool
    {
        return true;
    }

    /**
     * Can the currently logged in user access the content this item links to?
     *
     * @return	bool
     */
    public function canAccessContent(): bool
    {
        return true;
    }

    /**
     * Get Title
     *
     * @return	string
     */
    public function title(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_dmca');
    }

    /**
     * Get Link
     *
     * @return	\IPS\Http\Url
     */
    public function link(): \IPS\Http\Url
    {
        return \IPS\Http\Url::internal("app=dmca&module=system&controller=report", 'front', 'dmca_report');
    }

    /**
     * Is Active?
     *
     * @return	bool
     */
    public function active(): bool
    {
        return \IPS\Dispatcher::i()->application->directory === 'dmca';
    }

    /**
     * Children
     *
     * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
     * @return	array
     */
    public function children($noStore = false)
    {
        return null;
    }
}
