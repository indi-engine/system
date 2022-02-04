<?php
class Indi_Cache {
    /**
     * Create/update cache file, that contains contents of database table, that model $model is related to
     *
     * @static
     * @param string $table
     */
    public static function update($table){

        // Get the model name
        $model = ucfirst($table);

        // Get the columns array
        $columnA = m($model)->fields(null, 'columns');

        // Prepend columns array with 'id' item
        array_unshift($columnA, 'id');

        // Fetch all data from database table
		$dataA = m($model)->all()->toArray();

        // Start building data section of cache file
        $php = "<?php \$GLOBALS['cache']['" . $table . "']['myd'] = Array";
        $php .= "\n(\n";

        // Declare array for database table columns values data representation
        $cacheColumnA = [];

        // For each column within $columnA array
		foreach ($columnA as $columnI) {

            // Start building certain column and it's values data representation
            $cacheColumnI = "    '" . $columnI . "'=>Array(";

            // Declare/Reset array for certain column values data representation
            $cacheColumnDataA = [];

            // For each item within fetched data
			foreach ($dataA as $dataI)
                $cacheColumnDataA[] = preg_match('/^[0-9]+\.?[0-9]*$/', $dataI[$columnI])
                    ? $dataI[$columnI] : "'" . preg_replace("/'/", "\'", $dataI[$columnI]) . "'";

            // Append imploded values and enclosing bracket
            $cacheColumnI .= implode(',', $cacheColumnDataA) . ')';

            // Append whole column values data representation to columns representation array
            $cacheColumnA[] = $cacheColumnI;
		}

        // Append imploded columns values representation to contents
        $php .= implode(",\n", $cacheColumnA) . "\n);\n";

        // Start building indexes/usage section of cache file
        $php .= "\n\$GLOBALS['cache']['" . $table . "']['myi'] = Array";
        $php .= "\n(\n";

        // Reset array for database table columns values representation
        $cacheColumnA = [];

        // For each column within $columnA array
        foreach ($columnA as $columnI) {

            // Start building certain column and it's values usage representation
            $cacheColumnI = "    '" . $columnI . "'=>Array(";

            // Declare/Reset array for certain column values usage representation
            $cacheColumnValueUsageA = [];

            // For each item within fetched data - get the usage
            foreach ($dataA as $i => $dataI)
                $cacheColumnValueUsageA[preg_match('/^[0-9]+\.?[0-9]*$/', $dataI[$columnI])
                    ? $dataI[$columnI] : "'" . preg_replace("/'/", "\'", $dataI[$columnI]) . "'"][] = $i;


            // For each item within column value usage - convert usage info format from array to string,
            // for it to be recognizable by php interpreter
            foreach ($cacheColumnValueUsageA as $value => $indexA)
                $cacheColumnValueUsageA[$value] = $value . '=>' .
                    (count($indexA) > 1
                        ? 'Array(' . implode(',', array_unique($indexA)) . ')'
                        : $indexA[0]);

            // Append imploded values and enclosing bracket
            $cacheColumnI .= implode(',', $cacheColumnValueUsageA) . ')';

            // Append whole column values representation to columns representation array
            $cacheColumnA[] = $cacheColumnI;
        }

        // Append imploded columns values representation to contents
        $php .= implode(",\n", $cacheColumnA) . "\n);\n";

        // Write contents to cache file
		file_put_contents(self::file($table), $php);
    }

    /**
     * Remove cache file for certain database table

     * @static
     * @param $table
     */
    public static function remove($table) {
        unlink(self::file($table));
    }

    /**
     * Get filename of cache file for certain database table
     *
     * @static
     * @param $table
     * @return string
     */
    public static function file($table) {
		return DOC . STD . '/www/application/cache/' . $table . '.php';
	}


    /**
     * Create  a new Indi_Cache_Fetcher object, pass query params to, and return it
     *
     * @param $params
     * @return Indi_Cache_Fetcher
     */
    public function fetcher($params) {
		return new Indi_Cache_Fetcher($params);
	}

    /**
     * Load all existing cache files, if cache usage it switched on
     *
     * @static
     */
    public static function load() {
        if (ini()->db->cache)
            foreach (glob(DOC . STD . '/www/application/cache/*.php') as $cacheI)
                require_once($cacheI);
    }
}
