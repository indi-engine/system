<?php
class Year extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Year_Row';

    /**
     * Get an entry within `year` table, that represents current (or given by $year arg) year
     * as an instance of stdClass object. If such entry does not yet exists - it will be created
     *
     * @static
     * @param $year 4-digit year
     * @return stdClass
     */
    public static function o($year = null) {

        // Extract 4-digit year from a current date
        list($y) = explode('-', $year ?: date('Y'));

        // If current 4-digit year not exists within `year` table
        if (!$yearI = Indi::db()->query('SELECT `id`, `title` FROM `year` WHERE `title` = "' . $y . '"')->fetch()) {

            // Create it
            Indi::db()->query('INSERT INTO `year` SET `title` = "' . $y . '"');

            // Get it's id
            $yearO = (object) [
                'id' => Indi::db()->getPDO()->lastInsertId(),
                'title' => $y
            ];

            // Else convert got $yearA into an stdClass instance
        } else $yearO = (object) $yearI;

        // Return
        return $yearO;
    }
}