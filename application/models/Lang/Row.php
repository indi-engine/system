<?php
class Lang_Row extends Indi_Db_Table_Row {

    /**
     * @return array|mixed
     */
    public function validate() {

        // Check
        $this->vcheck([
            'title' => [
                'req' => true
            ],
            'alias' => [
                'req' => true,
                'rex' => '/^[a-zA-Z0-9_\-]+$/',
                'unq' => true
            ]
        ]);

        // Call parent
        return $this->callParent();
    }

    /**
     * Update `state` prop, as it will be used to provide
     * entries having at least something turned On to be
     * at the top of the grid
     */
    public function onBeforeSave() {

        // Initial state
        $this->state = 'noth';

        // If something is non-'n', update state
        foreach ($this->model()->fields()->select(6, 'relation')->column('alias') as $alias)
            if ($alias != 'state' && $this->$alias != 'n')
                if ($this->state = 'smth') // Assignment, just for avoiding brackets
                    break;
    }

    /**
     * Update language alias within json-encoded translations
     */
    public function onUpdate() {

        // If `alias` prop was not affected - return
        if (!$prev = $this->affected('alias', true)) return;

        // Get info about what entities have what localized fields
        $fieldA = db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append new item in $fieldA
        if ($fieldIdA_enumset = db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= [
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                ];

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (db()->query('
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
            db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /**
     * Append translation to the localized fields' values
     */
    public function onInsert() {

        // If `lang` entry is turned Off - do no translations for now
        if ($this->toggle == 'n') return;

        // Get info about what entities have what localized fields
        $fieldA = db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append them in $fieldA
        if ($fieldIdA_enumset = db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= [
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                ];

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (db()->query('
            SELECT `id`, `:p` FROM `:p` WHERE :p
        ', $info['field'], $info['table'], $info['where'])->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $json) {

            // Convert json to array
            $data = json_decode($json, true);

            // Append new translation, equal to current translation.
            // This is a temporary solution, Google Translate API will be used instead.
            $data[$this->alias] = $data[ini('lang')->admin];

            // Convert array back to json
            $json = json_encode($data);

            // Update
            db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /*
     * Remove translation from localized fields' values
     */
    public function onDelete() {

        // If `lang` entry is turned Off - do no translations for now
        if ($this->toggle == 'n') return;

        // Get info about what entities have what localized fields
        $fieldA = db()->query('
            SELECT `e`.`table`, `f`.`alias` AS `field`, "1" AS `where`
            FROM `entity` `e`, `field` `f`
            WHERE 1
              AND `e`.`id` = `f`.`entityId`
              AND `f`.`l10n` = "y"
              AND `f`.`relation` = "0"
        ')->fetchAll();

        // If localized enumset-fields found, append new item in $fieldA
        if ($fieldIdA_enumset = db()->query('
            SELECT `id` FROM `field` WHERE `l10n` = "y" AND `relation` = "6"
        ')->fetchAll(PDO::FETCH_COLUMN))
            foreach(ar('title') as $field)
                $fieldA []= [
                    'table' => 'enumset',
                    'field' => $field,
                    'where' => '`fieldId` IN (' . im($fieldIdA_enumset) . ')'
                ];

        // Foreach table-field pair - fetch rows containing `id` and current value of localized prop
        foreach ($fieldA as $info) foreach (db()->query('
            SELECT `id`, `:p` FROM `:p` WHERE :p
        ', $info['field'], $info['table'], $info['where'])->fetchAll(PDO::FETCH_KEY_PAIR) as $id => $json) {

            // Convert json to array
            $data = json_decode($json, true);

            // Remove current translation.
            unset($data[$this->alias]);

            // Convert array back to json
            $json = json_encode($data);

            // Update
            db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $info['table'], $info['field'], $json, $id);
        }
    }

    /**
     * Prevent the last remaining (or currently used) `lang` entry from being deleted
     */
    public function onBeforeDelete() {

        // If current entry is the last remaining `lang` entry - flush error
        if (db()->query('SELECT COUNT(*) FROM `lang`')->fetchColumn() == 1)
            jflush(false, sprintf(I_LANG_LAST, m('Lang')->title()));

        // If current entry is a translation, that is currently used - flush error
        if ($this->alias == ini('lang')->admin) jflush(false, I_LANG_CURR);
    }
}