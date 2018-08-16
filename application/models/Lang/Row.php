<?php
class Lang_Row extends Indi_Db_Table_Row {

    /**
     * @return array|mixed
     */
    public function validate() {

        // Check
        $this->vcheck(array(
            'title' => array(
                'req' => true
            ),
            'alias' => array(
                'req' => true,
                'rex' => '/^[a-zA-Z0-9_]+$/',
                'unq' => true
            )
        ));

        // Call parent
        return $this->callParent();
    }

    /**
     * Update language alias within json-encoded translations
     */
    public function onUpdate() {

        // If `alias` prop was not affected - return
        if (!$prev = $this->affected('alias', true)) return;

        // Get info about what entities have what localized fields
        $fieldA = Indi::db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append new item in $fieldA
        if ($fieldIdA_enumset = Indi::db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= array(
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                );

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (Indi::db()->query('
            SELECT `id`, `:p` FROM `:p` WHERE :p
        ', $info['field'], $info['table'], $info['where'])->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $json) {

            // Convert json to array
            $dataWas = json_decode($json, true);

            // Create same array but use updated key
            foreach ($dataWas as $lang => $l10n) {

                // If initial alias was faced as a key - use new alias instead
                if ($lang == $this->affected('alias', true)) $lang = $this->alias;

                // Append translations in the same order
                $dataNow[$lang] = $l10n;
            }

            // Convert array back to json
            $json = json_encode($dataNow);

            // Update
            Indi::db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /**
     * Append translation to the localized fields' values
     */
    public function onInsert() {

        // Get info about what entities have what localized fields
        $fieldA = Indi::db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append new item in $fieldA
        if ($fieldIdA_enumset = Indi::db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= array(
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                );

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (Indi::db()->query('
            SELECT `id`, `:p` FROM `:p` WHERE :p
        ', $info['field'], $info['table'], $info['where'])->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $json) {

            // Convert json to array
            $data = json_decode($json, true);

            // Append new translation, equal to current translation.
            // This is a temporary solution, Google Translate API will be used instead.
            $data[$this->alias] = $data[Indi::ini('lang')->admin];

            // Convert array back to json
            $json = json_encode($data);

            // Update
            Indi::db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /*
     * Remove translation from localized fields' values
     */
    public function onDelete() {

        // Get info about what entities have what localized fields
        $fieldA = Indi::db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append new item in $fieldA
        if ($fieldIdA_enumset = Indi::db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= array(
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                );

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (Indi::db()->query('
            SELECT `id`, `:p` FROM `:p` WHERE :p
        ', $info['field'], $info['table'], $info['where'])->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $json) {

            // Convert json to array
            $data = json_decode($json, true);

            // Remove current translation.
            unset($data[$this->alias]);

            // Convert array back to json
            $json = json_encode($data);

            // Update
            Indi::db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /**
     * Prevent the last remaining (or currently used) `lang` entry from being deleted
     */
    public function onBeforeDelete() {

        // If current entry is the last remaining `lang` entry - flush error
        if (Indi::db()->query('SELECT COUNT(*) FROM `lang`')->fetchColumn() == 1)
            jflush(false, sprintf(I_LANG_LAST, Indi::model('Lang')->title()));

        // If current entry is a translation, that is currently used - flush error
        if ($this->alias == Indi::ini('lang')->admin) jflush(false, I_LANG_CURR);
    }
}