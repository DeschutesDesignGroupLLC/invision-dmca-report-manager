<?php

namespace IPS\dmca\Reports;

class _Strike extends \IPS\Node\Model
{
    /**
     * [ActiveRecord] Multiton Store
     *
     * @var string
     */
    protected static $multitons;

    /**
     * [ActiveRecord] Database Table
     *
     * @var string
     */
    public static $databaseTable = 'dmca_strikes';

    /**
     * [ActiveRecord] Database Prefix
     *
     * @var string
     */
    public static $databaseColumnId = 'id';

    /**
     * [Active Record] Database ID Fields
     *
     * @var  array
     *
     * @note If using this, declare a static $multitonMap = array(); in the child class to prevent duplicate loading
     *       queries
     */
    protected static $databaseIdFields = ['id', 'member_id', 'report_id'];

    /**
     * [Active Record] Multition Map
     *
     * @var array
     */
    protected static $multitonMap = [];

    /**
     * [Node] Node Title
     *
     * @var string
     */
    public static $nodeTitle = 'Strikes';
}
