<?php
require_once ('./config/config.php');

/**
 * Perform basic database operations insert, update, delete, select, count
 @todo Add more useful functions
 */
class DB {
    private static $dsn = DSN;
    private static $username = USER;
    private static $password = PASS;
    private static $options =  array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_EMULATE_PREPARES, false,
        );
   
    private static function connect() {
        try {
            $dbh = new PDO(self::$dsn, self::$username, self::$password, self::$options);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    private static function cleanText($text) {
        $cleanedText = substr($text, 0, -2);
        return $cleanedText;
    }

    /** 
     * Insert data into database using pdo prepared statements.
     *  
     * @param String $tableName
     *Accepts a single table name as a sting value enclosed in quotes.
     *
     * @param array $values
     * Accepts table column names and corresponding values in an associative
     * array format. e.g <code>DB::insert("tableName", array("column1"=>"value1", 
     * "column2"=>"value2".....));
     * </code>
     * <code>
     *DB::insert("tableName", array("column1"=>$value1, 
     * "column2"=>$value2".....));
     *  </code>
     @return NULL if operation is successful
     * @return String error message in string if operation fails
     * 
     *@static 
     */

    static function insert($tableName, array $values) {
        $columns = "";
        $data = array();
        $dataHold = array();
        $dataHoldText = "";


        foreach ($values as $key => $value) {
            $columns .= $key . ", ";
            array_push($dataHold, ":" . $key);
            $dataHoldText .= ":" . $key . ", ";
            array_push($data, $value);
        }

        $dataArray = array_combine($dataHold, $data);

        $dbh = self::connect();

        $ccolumns = self::cleanText($columns);
        $cdataHold = self::cleanText($dataHoldText);

        $sql = "INSERT INTO $tableName ($ccolumns) VALUES ($cdataHold)";

        $stmt = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        $stmt->execute($dataArray);
        if (!$stmt) {
            print_r($dbh->errorInfo());
        }else {
            return NULL;
        }
        $dbh = NULL;
    }

    /**
     * Select a single record from a table.
     * 
     * @param array $tables
     * the table name(s) to select from.
     * 
     * @param array $columns
     * column name(s) to select from. An empty array can also be supplied
     * if you want to select all the table columns.<br>
     * <code>
     *  array()/array("") </code> means "SELECT * tableName"  <br>
     * <code>
     * array(column1, column2,....)
     * </code>
     * 
     * @param String $where [optional]
     * condition(s) for table selection without the <b>"WHERE"</b> clause .e.g <br>
     * <code>"tableName = 'value'" or <br>
     * "tableName = '$value' </code>
     * 
     * @param array $orderBy [optional]
     * collection of columns to use in ordering the selection. If $orderBy is provided, $mode should be provided also.
     * e.g <br>
     * <code> array('column1, column2....)</code>
     * 
     * @param String $mode [optional]
     * If $orderBy is supplied, $mode should also be supplied. Accepted values are <b>"ASC"</b> or <b>"DESC"</b>
     * 
     * @return array returns an associative array containing the table name and corresponding value
     * if error occurs, return a string containing the error
     *
     *  
     * <br>
     * <b>Example </b>
     * <code>DB::select(array("table1",....), array("column1", "column2",...), "columnName = '$value'", array("columnName"), "ASC"|"DESC"); </code>
     * 
     * @static
     */
    static function select(array $tables, array $columns = NULL, $where = NULL, array $orderBy = NULL, $mode = NULL) {
        $dbh = self::connect();
        $stmt = "";
        $tabl = "";
        foreach ($tables as $tab) {
            $tabl .= $tab. ", ";
        }
        
        $table = self::cleanText($tabl);

        if ($columns == NULL || array_count_values($columns) == 0 || strlen($columns[0]) == 0) {
            $stmt .= "SELECT * FROM $table";
        } else {
            $columnText = "";
            foreach ($columns as $value) {
                $columnText .= $value . ", ";
            }
            $ccolumnText = self::cleanText($columnText);

            $stmt .= "SELECT $ccolumnText FROM $table";
        }
        
        /* @string WHERE */
        if ($where !== NULL && strlen($where) !== 0) {

            $stmt .= " WHERE " . $where;
        }

        if ($orderBy !== NULL && (array_count_values($orderBy) !== 0 || strlen($orderBy[0]) !== 0) && $mode !== NULL && strlen($mode) !== 0) {
            $conditions = "";
            foreach ($orderBy as $value) {
                $conditions .= $value . ", ";
            }
            $cconditions = self::cleanText($conditions);

            $stmt .= " ORDER BY " . $cconditions . " " . $mode;
        }
        $sth = $dbh->query($stmt);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$stmt) {
            print_r($dbh->errorInfo());
        } else {
            return $result;
        }
        $dbh = NULL;
    }
    /**
     * Count the number of record in a database
     * @param array $tables
     * Name(s) of table(s) to count from
     * @param array $columns
     * Name(s) of row(s) to count
     * @param string $where
     * Where condition for counting
     * @return number if success and error string if failed
     * 
     * <br>
     * <b>Example</b>
     * <code>
     *  DB::count(array(table1), array(), "column1 = 'value'")
     * </code>
     * 
     * @static
     */
    static function count(array $tables, array $columns = NULL, $where = NULL) {
        $dbh = self::connect();
        $stmt = "";
        $tabl = "";
        foreach ($tables as $tab) {
            $tabl .= $tab. ", ";
        }
        
        $table = self::cleanText($tabl);

        if ($columns == NULL || array_count_values($columns) == 0 || strlen($columns[0]) == 0) {
            $stmt .= "SELECT * FROM $table";
        } else {
            $columnText = "";
            foreach ($columns as $value) {
                $columnText .= $value . ", ";
            }
            $ccolumnText = self::cleanText($columnText);

            $stmt .= "SELECT $ccolumnText FROM $table";
        }
        
        /* @string WHERE */
        if ($where !== NULL && strlen($where) !== 0) {

            $stmt .= " WHERE " . $where;
        }

        $sth = $dbh->query($stmt);
        $result = $sth->rowCount();
        if (!$stmt) {
            print_r($dbh->errorInfo());
        } else {
            return $result;
        }
        $dbh = NULL;
    }
    
    /**
     * Selects more than one record from a table.
     * 
     * @param array $tables
     * the table name(s) to select from.
     * 
     * @param array $columns
     * column name(s) to select from. An empty array can also be supplied
     * if you want to select all the table columns.<br>
     * <code>
     *  array()/array("") </code> means "SELECT * tableName"  <br>
     * <code>
     * array(column1, column2,....)
     * </code>
     * 
     * @param String $where [optional]
     * condition(s) for table selection without the <b>"WHERE"</b> clause .e.g <br>
     * <code>"tableName = 'value'" or <br>
     * "tableName = '$value' </code>
     * 
     * @param array $orderBy [optional]
     * collection of columns to use in ordering the selection. If $orderBy is provided, $mode should be provided also.
     * e.g <br>
     * <code> array('column1, column2....)</code>
     * 
     * @param String $mode [optional]
     * If $orderBy is supplied, $mode should also be supplied. Accepted values are <b>"ASC"</b> or <b>"DESC"</b>
     * 
     * @param int $limit [optional]
     * The number of records to be returned.
     * 
     * @return array returns an associative array containing the table name and corresponding value
     * if error occurs, return a string contaning the error
     *
     *  
     * <br>
     * <b>Example </b>
     * <code>DB::select(array("table1",....), array("column1", "column2",...), "columnName = '$value'", array("columnName"), "ASC"|"DESC", 2); </code>
     * 
     * @static
     */
    
        static function selectAll(array $tables, array $columns = NULL, $where = NULL, array $orderBy = NULL, $mode = NULL, $limit = NULL) {
        $dbh = self::connect();
        $stmt = "";
        $tabl = "";
        foreach ($tables as $tab) {
            $tabl .= $tab. ", ";
        }
        
        $table = self::cleanText($tabl);

        if ($columns == NULL || array_count_values($columns) == 0 || strlen($columns[0]) == 0) {
            $stmt .= "SELECT * FROM $table";
        } else {
            $columnText = "";
            foreach ($columns as $value) {
                $columnText .= $value . ", ";
            }
            $ccolumnText = self::cleanText($columnText);

            $stmt .= "SELECT $ccolumnText FROM $table";
        }
        
        /* @string WHERE */
        if ($where !== NULL && strlen($where) !== 0) {

            $stmt .= " WHERE " . $where;
        }

        if ($orderBy !== NULL && (array_count_values($orderBy) !== 0 || strlen($orderBy[0]) !== 0) && $mode !== NULL && strlen($mode) !== 0) {
            $conditions = "";
            foreach ($orderBy as $value) {
                $conditions .= $value . ", ";
            }
            $cconditions = self::cleanText($conditions);

            $stmt .= " ORDER BY " . $cconditions . " " . $mode;
        }

        if ($limit !== NULL && strlen($limit) !== 0) {

            $stmt .= " LIMIT " . $limit;
        }

        $sth = $dbh->query($stmt);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (!$stmt) {
            print_r($dbh->errorInfo());
        } else {
            return $result;
        }
        $dbh = NULL;
    }
    
    /**
     * Deletes records or table from the database
     * @param string $table
     * table name to be deleted/deleted from
     * @param string $where [optional]
     * condition for choosing the record(s) to be deleted
     * @return NULL if success and error string if failed
     * 
     * <br>
     * <b>Example </b>
     * <code>DB::delete("table1")<br>
     * DB::delete("table1", "columName = '$value'")
     * </code>
     * 
     * @static
     */

    static function delete($table, $where = NULL) {
        $dbh = self::connect();
        $stmt = "DELETE FROM $table ";
        if ($where !== NULL or strlen($where) !== 0) {
            $stmt .= "WHERE " . $where;
        }

        $dbh->query($stmt);
        if (!$stmt) {
            print_r($dbh->errorInfo());
        }else {
            return NULL;
        }
        
        $dbh = NULL;
    }

    /**
     * Update data in database using pdo prepared statements.
     * 
     * @param array $table
     * Accepts table name(s) in a string array.
     * @param array $setValues
     * Accepts table column names to be updated and corresponding values in an associative
     * array format.
     * 
     * @param string $where
     * Where condition for selecting the record to be updated.
     * 
     * @param array $orderBy [optional]
     * Accepts string array of columns to used in ordering the records when more than one racord is involved
     * should be used in conjuction with $mode
     * 
     * @param string $mode
     * Accepts one of two strings "ASC" or "DESC"
     * 
     * @return NULL
     * returns NULL if successful and error string if update fails
     * 
     * <b>Example<b>
     * <code>
     * DB::insert(array("tableName"), array("column1"=>"value1", 
     * "column2"=>"value2".....));
     * </code>
     * <code>
     *DB::insert("tableName", array("column1"=>$value1, 
     * "column2"=>$value2".....), "columnName = 'value'");
     * </code>
     * 
     * @static
     */
    
    static function update(array $table, array $setValues, $where = NULL, array $orderBy = NULL, $mode = NULL, $limit = NULL) {
        $dataHold = array();
        $data = array();


        foreach ($setValues as $key => $value) {
            array_push($dataHold, ":" . $key);
            array_push($data, $value);
        }

        if (count($table) == 1) {
            $sql = self::singleTableUpdate($table, $setValues, $where);
        } elseif (count($table) > 1 && ((count($orderBy) !== 0 || count_chars($orderBy[0]) !== 0) && ($mode !== NULL || count_chars($mode) !== 0))) {
            $sql = self::multitableUpdate($table, $setValues, $where, $orderBy, $mode);
        }

        $dbh = self::connect();

        $dataArray = array_combine($dataHold, $data);
        $stmt = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        $stmt->execute($dataArray);
        if (!$stmt) {
            print_r($dbh->errorInfo());
        } else {
            return NULL;
        }
        
        $dbh = NULL;
    }

    private static function singleTableUpdate(array $table, array $setValues, $where = NULL) {
        $data = array();
        $dataHold = array();
        $dataHoldText = "";
        $stmt = "";

        foreach ($setValues as $key => $value) {
            array_push($dataHold, ":" . $key);
            $dataHoldText .= $key . " = :" . $key . ", ";
            array_push($data, $value);
        }
        $tableText = $table[0];
        $csetVals = self::cleanText($dataHoldText);
        $stmt .= "UPDATE $tableText SET $csetVals";

        if ($where !== NULL && strlen($where) !== 0) {
            $stmt .= " WHERE $where";
        }

        return $stmt;
        
    }

    private static function multitableUpdate(array $tables, array $setValues, $where = NULL, array $orderBy = NULL, $mode = NULL, $limit = NULL) {
        $rows = "";
        $data = array();
        $dataHold = array();
        $dataHoldText = "";
        foreach ($orderBy as $value) {
            $rows .= $value . ", ";
        }
        foreach ($tables as $table) {
            $dtables .= $table . ", ";
        }
        foreach ($setValues as $key => $value) {
            array_push($dataHold, ":" . $key);
            $dataHoldText .= $key . " = :" . $key . ", ";
            array_push($data, $value);
        }
        $tableText = self::cleanText($dtables);
        $csetVals = self::cleanText($dataHoldText);

        $stmt .= "UPDATE $tableText SET $csetVals";
        if ($where !== NULL && strlen($where) !== 0) {
            $stmt .= " WHERE $where";
        }
        $crows = self::cleanText($rows);

        $stmt .= " ORDER BY $crows " . $mode;
        
        if ($limit !== NULL && strlen($limit) !== 0) {

            $stmt .= " LIMIT " . $limit;
        }

        return $stmt;
    }

}
