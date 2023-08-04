<?php
/**
 * @brief		Notification Options
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	DMCA Report Manager
 * @since		02 Aug 2023
 */

namespace IPS\dmca\extensions\core\Notifications;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Notification Options
 */
class _Claim
{
    /**
     * Get fields for configuration
     *
     * @param	\IPS\Member|null	$member		The member (to take out any notification types a given member will never see) or NULL if this is for the ACP
     * @return	array
     */
    public static function configurationOptions(\IPS\Member $member = null): array
    {
        return array(
            'membersuspension' => array(
                'type' => 'standard',
                'notificationTypes' => ['submitted', 'approved', 'onhold', 'denied', 'deleted', 'firststrike', 'secondstrike', 'thirdstrike'],
                'default' => ['email', 'inline'],
                'disabled' => ['push'],
                'description' => 'notifications__dmca_Claim_desc',
                'showTitle' => false,
                'title' => 'notifications__dmca_Claim'
            ),
        );
    }

    /**
     * Parse notification: submitted
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_submitted(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "Your copyright claim was submitted",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "Your copyright claim was submitted",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: approved
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_approved(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "Your copyright claim was approved",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "Your copyright claim was approved",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: onhold
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_onhold(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "Your copyright claim was placed on hold",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "Your copyright claim was placed on hold",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: denied
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_denied(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "Your copyright claim was denied",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "Your copyright claim was denied",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: deleted
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_deleted(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "The item in your copyright claim has been deleted",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "The item in your copyright claim has been deleted",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: firststrike
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_firststrike(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "A copyright claim has been filed against some content you posted. This is your first strike.",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "A copyright claim has been filed against some content you posted. This is your first strike.",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: secondstrike
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_secondstrike(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "A copyright claim has been filed against some content you posted. This is your second strike.",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "A copyright claim has been filed against some content you posted. This is your second strike.",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }

    /**
     * Parse notification: thirdstrike
     *
     * @param	\IPS\Notification\Inline	$notification	The notification
     * @param	bool						$htmlEscape		TRUE to escape HTML in title
     * @return	array
     */
    public function parse_thirdstrike(\IPS\Notification\Inline $notification, $htmlEscape=true): array
    {
        return array(
            'title' => "A copyright claim has been filed against some content you posted. This is your third and last strike.",
            'url' => \IPS\Http\Url::internal(''),
            'content' => "A copyright claim has been filed against some content you posted. This is your third and last strike.",
            'author' =>  \IPS\Member::loggedIn(),
        );
    }
}
