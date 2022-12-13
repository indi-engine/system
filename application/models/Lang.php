<?php
class Lang extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Lang_Row';

    /**
     * Array of json-templates for each l10n-fraction
     *
     * @var array
     */
    public static $_jtpl = [];

    /**
     * Array of [id => alias] pairs fetched from `lang` table
     *
     * @var array
     */
    protected static $_aliasA = null;

    /**
     * @param null $langId
     * @return mixed
     */
    public static function alias($langId = null) {

        // If self::$_aliasA is null - fetch key-value pairs
        self::$_aliasA = self::$_aliasA ?? db()->query("
            SELECT `id`, `alias` FROM `lang`
        ")->pairs();

        // If $langId arg is given - return `alias` of corresponding `lang` entry
        return $langId ? self::$_aliasA[$langId] : self::$_aliasA;
    }
}