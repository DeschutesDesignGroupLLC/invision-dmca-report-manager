<?php
/**
 * @brief		Editor Extension: SecondStrikeEmail
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	DMCA Report Manager
 * @since		04 Aug 2023
 */

namespace IPS\dmca\extensions\core\EditorLocations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Editor Extension: SecondStrikeEmail
 */
class _SecondStrikeEmail
{
    /**
     * Can we use HTML in this editor?
     *
     * @param	\IPS\Member	$member	The member
     * @return	bool|null	NULL will cause the default value (based on the member's permissions) to be used, and is recommended in most cases. A boolean value will override that.
     */
    public function canUseHtml($member)
    {
        return null;
    }

    /**
     * Can we use attachments in this editor?
     *
     * @param	\IPS\Member					$member	The member
     * @param	\IPS\Helpers\Form\Editor	$field	The editor field
     * @return	bool|null	NULL will cause the default value (based on the member's permissions) to be used, and is recommended in most cases. A boolean value will override that.
     */
    public function canAttach($member, $field)
    {
        return null;
    }

    /**
     * Permission check for attachments
     *
     * @param	\IPS\Member	$member		The member
     * @param	int|null	$id1		Primary ID
     * @param	int|null	$id2		Secondary ID
     * @param	string|null	$id3		Arbitrary data
     * @param	array		$attachment	The attachment data
     * @param	bool		$viewOnly	If true, just check if the user can see the attachment rather than download it
     * @return	bool
     */
    public function attachmentPermissionCheck($member, $id1, $id2, $id3, $attachment, $viewOnly = false): bool
    {
        /* Make sure that you add a relevant permission check to prevent attachments being accessed via ID enumeration. */
        return true;
    }

    /**
     * Attachment lookup
     *
     * @param	int|null	$id1	Primary ID
     * @param	int|null	$id2	Secondary ID
     * @param	string|null	$id3	Arbitrary data
     * @return	\IPS\Http\Url|\IPS\Content|\IPS\Node\Model
     * @throws	\LogicException
     */
    public function attachmentLookup($id1, $id2, $id3)
    {
        // return \IPS\Http\Url::internal( ... );
    }

    /**
     * Rebuild attachment images in non-content item areas
     *
     * @param	int|null	$offset	Offset to start from
     * @param	int|null	$max	Maximum to parse
     * @return	int			Number completed
     * @note	This method is optional and will only be called if it exists
     */
    //public function rebuildAttachmentImages( $offset, $max )
    //{
    //}

    /**
     * Rebuild content post-upgrade
     *
     * @param	int|null	$offset	Offset to start from
     * @param	int|null	$max	Maximum to parse
     * @return	int			Number completed
     * @note	This method is optional and will only be called if it exists
     */
    //public function rebuildContent( $offset, $max )
    //{
    //}

    /**
     * @brief	Store lazy loading status ( true = enabled )
     */
    protected $_lazyLoadStatus = null;

    /**
     * Rebuild content to add or remove lazy loading
     *
     * @param	int|null		$offset		Offset to start from
     * @param	int|null		$max		Maximum to parse
     * @param	bool			$status		Enable/Disable lazy loading
     * @return	int			Number completed
     * @note	This method is optional and will only be called if it exists
     */
    //public function rebuildLazyLoad( $offset, $max, $status=TRUE )
    //{
    //}

    /**
     * Total content count to be used in progress indicator
     *
     * @return	int			Total Count
     */
    //public function contentCount()
    //{
    //    return \IPS\Db::i()->select( 'COUNT(*) as count', '...', "..." )->setKeyField('count')->first();
    //}
}
