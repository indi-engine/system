<?php
class Indi_Trail_Admin {

    /**
     * Array of Indi_Trail_Admin_Item items
     *
     * @var array
     */
    public static $items = array();

    /**
     * Array of actions that are toggled on
     *
     * @var array
     */
    public static $toggledActionIdA = array();

    /**
     * Indi_Controller_Admin object, by reference
     *
     * @var Indi_Controller_Admin
     */
    public static $controller = null;

    /**
     * Constructor
     *
     * @param array $routeA Array of section ids, starting from current section and up to the top
     * @param Indi_Controller_Admin $controller
     */
    public function __construct($routeA, Indi_Controller_Admin &$controller) {

        // Setup controller
        self::$controller = &$controller;

        // Reset items
        self::$items = array();

        // Get all sections, starting from current and up to the most top
        $sectionRs = Indi::model('Section')->fetchAll(
            '`id` IN (' . $route = implode(',', $routeA) . ')',
            'FIND_IN_SET(`id`, "' . implode(',', $routeA) . '")'
        )->foreign('parentSectionConnector,defaultSortField');

        // Get the id of most top section (menu group)
        $top = $routeA[count($routeA) - 1];

        // Get array of actions that are toggled on
        self::$toggledActionIdA = Indi::db()
            ->query('SELECT `id` FROM `action` WHERE `toggle` = "y"')
            ->fetchAll(PDO::FETCH_COLUMN);

        // Setup accessible actions
        $sectionRs->nested('section2action', array(
            'where' => array(
                '`sectionId` != "' . $top . '"',
                '`toggle` = "y"',
                'FIND_IN_SET("' . $_SESSION['admin']['profileId'] . '", `profileIds`)',
                'FIND_IN_SET(`actionId`, "' . implode(',', self::$toggledActionIdA) . '")'
            ),
            'order' => 'move',
            'foreign' => 'actionId'
        ));

        // Get the array of accessible sections ids
        $accessibleSectionIdA = array();
        foreach ($sectionRs->nested('section2action') as $sectionId => $section2actionRs)
            foreach ($section2actionRs as $section2actionR)
                if ($section2actionR->actionId == 1)
                    $accessibleSectionIdA[] = $sectionId;

        // Get accessible nested sections for each section within the trail
        $sectionRs->nested('section', array(
            'where' => array(
                '`sectionId` IN ("' . implode('","', $accessibleSectionIdA) . '")',
                '`toggle` != "n"'
            ),
            'order' => 'move'
        ));

        // Get filters
        $searchWHERE = array('`sectionId` = "' . $routeA[0] . '"', '`toggle` = "y"');
        if (Indi::model('Search')->fields('access') && Indi::model('Search')->fields('profileIds')) {
            $searchWHERE[] = '(' . im(array(
                '`access` = "all"',
                '(`access` = "only" AND FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
                '(`access` = "except" AND NOT FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
            ), ' OR ') . ')';
        }

        // Setup filters
        $sectionRs->nested('search', array(
            'where' => $searchWHERE,
            'order' => 'move'
        ));

        // Grid columns WHERE clause
        $gridWHERE = array('`sectionId` = "' . $routeA[0] . '"', '`toggle` != "n"');
        if (Indi::model('Grid')->fields('access') && Indi::model('Grid')->fields('profileIds')) {
            $gridWHERE[] = '(' . im(array(
                '`access` = "all"',
                '(`access` = "only" AND FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
                '(`access` = "except" AND NOT FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
            ), ' OR ') . ')';
        }

        // Setup grid columns
        $sectionRs->nested('grid', array(
            'where' => $gridWHERE,
            'order' => '`group` = "locked" DESC, `move`'
        ));

        // Altered field WHERE clause
        $alteredFieldsWHERE = array('`sectionId` = "' . $routeA[0] . '"');
        $alteredFieldsWHERE[] = '(' . im(array(
            '`impact` = "all"',
            '(`impact` = "only" AND FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
            '(`impact` = "except" AND NOT FIND_IN_SET("' . Indi::admin()->profileId . '", `profileIds`))',
        ), ' OR ') . ')';

        // Setup disabled fields
        $sectionRs->nested(entity('alteredField') ? 'alteredField' : 'disabledField', array(
            'where' => $alteredFieldsWHERE
        ));

        // Setup initial set of properties
        foreach ($sectionRs as $sectionR)
            self::$items[] = new Indi_Trail_Admin_Item($sectionR, $sectionRs->count() - ++$i);

        // If currently we are at at least 2-level section, assuming that
        // 0-level sections are the most top sections, e.g left menu groups,
        // 1-level sections are sections, that are nested to menu groups
        // For example, if we have the following structure:
        //
        // Geography   (0-level, menu group)
        //   Countries (1-level)
        //     Cities  (2-level)
        //
        // - example assumes, that we are viewing list of cities within sme certain country,
        // and url is like /cities/index/id/123/, where 123 - is the id of country.
        // So, in such situation we need to remember '123', because if user would like to add
        // a new city within that certain country, he will be at url /cities/form/, and it does not
        // contain any definition of country, that city should be added under. So, this solution
        // allow to get the id of country

        if (Indi::uri('section') != 'index' && Indi::uri('action') == 'index' && Indi::uri('id')) {

            // If there is no info about nesting yet, we create an array, where it will be stored
            if (!is_array($_SESSION['indi']['admin']['trail']['parentId']))
                $_SESSION['indi']['admin']['trail']['parentId'] = array();

            // Save id
            $_SESSION['indi']['admin']['trail']['parentId'][self::$items[0]->section->sectionId] = Indi::uri('id');

        // If we're trying to jump to creation-form in a section, having parent section - setup parent id
        } else if (Indi::uri('action') == 'form' && Indi::uri('jump') && ($parent = Indi::uri('parent')))
            $_SESSION['indi']['admin']['trail']['parentId'][self::$items[0]->section->sectionId] = Indi::uri('parent');

        // Reverse items
        self::$items = array_reverse(self::$items);
    }

    /**
     * Performs the last set auth checks, or, if no errors met - setup a row for each item within trail
     */
    public function authLevel3() {

        // If user is trying to create row, despite on it's restricted - raise up an error
        if (((Indi::uri('action') == 'form' && !(Indi::uri('combo') || Indi::uri('filter')))
            || Indi::uri('action') == 'save') && !Indi::uri('id') && !Indi::uri('aix')
            && !Indi::uri('check') && $this->item()->section->disableAdd == 1) {
            $error = I_ACCESS_ERROR_ROW_ADDING_DISABLED;

        // Else if 'id' param is mentioned in uri, but it's value either not specified,
        // or does not match allowed format - setup an error
        } else if (array_key_exists('id', (array) Indi::uri()) && !preg_match('/^[0-9]+$/', Indi::uri()->id))
            $error = I_URI_ERROR_ID_FORMAT;

        // Setup row for each trail item, or setup an access error
        else
            for ($i = 0; $i < count(self::$items) - 1; $i++)
                if ($error = Indi::trail($i)->row($i))
                    break;

        // Flush an error in json format, if error was met
        if ($error) jflush(false, $error);

        // Setup blank scope object for each trail item
        for ($i = 0; $i < count(self::$items) - 1; $i++)
            if (Indi::trail($i)->section->sectionId) {
                if (Indi::trail($i)->row) Indi::trail($i)->row->compileDefaults($level = 'trail');
                Indi::trail($i)->scope = new Indi_Trail_Admin_Item_Scope($i);
                Indi::trail($i)->filtersSharedRow($i);
            }

        // Adjust disabled fields
        self::$controller->adjustDisabledFields();
    }

    /**
     * Get trail item
     *
     * @param int $stepsUp
     * @return mixed
     */
    public function item($stepsUp = 0) {
        return self::$items[count(self::$items) - 1 - (int) $stepsUp];
    }

    /**
     * Get trail items count
     *
     * @return int
     */
    public function count() {
        return count(self::$items);
    }

    /**
     * Build and return a string representation of trail, e.g bread crumbs
     * Currently used in excel export
     *
     * @param bool $imploded
     * @return array|string
     */
    public function toString($imploded = true) {

         // Declare crumbs array and push the first item - section group
        $crumbA = array(self::$items[0]->section->title());

        // For each remaining trail items
        for ($i = 1; $i < count(self::$items); $i++) {

            // Define a shortcut for current trail item
            $item = self::$items[$i];

            // Append a current item section title
            $crumbA[] = $item->section->title();

            // If current trail item has a row
            if ($item->row) {

                // If that row has an id
                if ($item->row->id) {

                    // At first, we strip newline characters, html '<br>' tags
                    $title = preg_replace('<br(|\/)>', '', preg_replace('/[\n\r]/' , '', $item->row->title()));

                    // Detect color
                    preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $title, $color);

                    // Strip the html tags from title, and extract first 50 characters
                    $title = mb_substr(strip_tags($title), 0, 50, 'utf-8');

                    // Append current trail item row title, with color definition
                    $crumbA[] = '<i' . ($color ? ' style="color: ' . $color[1] . ';"' : '') . '>' . $title . '</i>';

                    // If current trail item is a last item, append current trail item action title
                    if ($i == count(self::$items) - 1) $crumbA[] = $item->action->title();

                // Else if current trail item row does not have and id, and current action alias is 'form'
                } else if ($item->action->alias == 'form') {

                    // We append 'form' action title, but it' version for case then new row is going to be
                    // created, hovewer, got from localization object, instead of actual action title
                    $crumbA[] = I_CREATE;
                }
            }
        }

        // Return bread crumbs as ' » '-separated string, or array, depending on $imploded argument
        return $imploded ? implode(' » ', $crumbA) : $crumbA;
    }

    /**
     * Get an array version of trail. Method is used to pass trail data to javascript as json
     *
     * @uses Indi_Trail_Item::toArray()
     * @return array
     */
    public function toArray() {
        $array = array();
        foreach (self::$items as $item) {
            $array[] = $item->toArray();
        }
        end(self::$items);
        return $array;
    }

    /**
     * Get the array of uris, that represent the navigation steps,
     * as if user navigated to current location by step-by-step
     *
     * @return array
     */
    public function nav() {

        // Declare $nav array
        $nav = array();

        // Build $nav array
        for ($i = 1; $i < count(self::$items); $i++)
            $nav[] = '/' . self::$items[$i]->section->alias . '/index/'
                . ($i == 1 ? '' : 'id/' . self::$items[$i-1]->row->id . '/')
                . 'single/' . (self::$items[$i]->row->id ?: 0) . '/';

        // Append non-index action, as additional navigation step
        if ($this->item()->action->alias != 'index')
            $nav[] = '/' . $this->item()->section->alias . '/' . $this->item()->action->alias . '/id/' . $this->item()->row->id . '/';

        // Return
        return $nav;
    }
}