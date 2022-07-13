<?php
class Section extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Section_Row';

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = ['filter'];

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = [
        'field' => 'fraction',
        'value' => [
            'system' => 'adminSystemUi',
            'custom' => 'adminCustomUi',
            'public' => 'adminPublicUi'
        ]
    ];

    /**
     * Get left menu data cms user
     *
     * @return array
     */
    public static function menu() {

        // Append props, containing info about auto-expanding, if such props exist
        $_ = m('Section')->fields('expand') ? ', `expand`, `expandRoles`' : '';

        // Fetch temporary data about root menu items
        $tmpA = db()->query('
            SELECT `id`, `sectionId`, `title`, `alias`' . $_ . '
            FROM `section`
            WHERE `sectionId` = "0" AND `toggle` = "y"
            ORDER BY `move`
        ')->fetchAll();

        // Localize
        $tmpA = l10n($tmpA, 'title');

        // Convert that temporary data to an array, that is using items ids as items keys, and unset $tmpA array
        $rootA = []; for ($i = 0; $i < count($tmpA); $i++) $rootA[$tmpA[$i]['id']] = $tmpA[$i]; unset($tmpA);

        // Fetch menu items, that are 1st-level children for root items
        $nestedA = db()->query('
            SELECT `s`.`id`, `s`.`sectionId`, `s`.`title`, `s`.`alias`
            FROM `section` `s`, `section2action` `sa`
            WHERE 1
                AND `s`.`sectionId` IN (' . implode(',', array_keys($rootA)) . ')
                AND FIND_IN_SET("' . $_SESSION['admin']['roleId'] . '", `sa`.`roleIds`)
                AND `s`.`id` = `sa`.`sectionId`
                AND `sa`.`actionId` = "1"
                AND `s`.`toggle` = "y"
                AND `sa`.`toggle` = "y"
            ORDER BY `s`.`move`
        ')->fetchAll();

        // Localize
        $nestedA = l10n($nestedA, 'title');

        // Declare an array for function return
        $menu = [];

        // Build the menu data
        foreach ($rootA as $rootId => $rootSection) {

            // Setup a flag, containing the info about whether at least one nested menu item
            // for current root menu item was found or not
            $found = false;

            // Foreach nested item
            foreach ($nestedA as $i => $nestedI)

                // If current nested item relates to current root item
                if ($nestedI['sectionId'] == $rootId) {

                    // If it's a first time when nested menu item was found for current root item
                    if (!$found) {

                        // Append root item to the menu before appending nested item. We do that here to avoid
                        // situation when we will have root menu items without at least one nested item
                        $menu[] = $rootSection;

                        // Setup $found flag to true, to prevent further cases of appending
                        // current root item as it was already added
                        $found = true;
                    }

                    // Append nested item to the menu
                    $menu[] = $nestedI;

                    // Free the memory
                    unset($nestedA[$i]);
                }

            // Free the memory
            unset($rootA[$rootId], $rootId, $rootSection);
        }

        // Return menu data
        return $menu;
    }
}