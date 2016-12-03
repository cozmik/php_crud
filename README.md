# php_crud
Carry out database transaction more easily and safely using static db class

The php class carry out database transactions using php pdo prepared statements for insert and update statements.


Set Up
Change require or include the DB.php file in your php document, then edit the config.php file in the config folder.

define('DSN', 'mysql:host=localhost;dbname=databaseName');
define('USER', 'db_user');
define('PASS', 'db_password');

Change the "host" to your database hostname;
Change the "databaseName" to your database name;
Change the "db_user" to your database user name;
Change the "db_password" to your database password;

Usage

****Insert statement
Once the DB.php file has been required or included, ou can use the class to carry out db transactions like so.

DB::insert("tableName", array("column1"=>"value1", "column2"=>"value2".....));
 
DB::insert("tableName", array("column1"=>$value1, "column2"=>$value2".....));
     
     returns NULL if operation is successful
     returns String error message in string if operation fails

***Select Statement
DB::select(array("table1",....), array("column1", "column2",...), "columnName = '$value'", array("columnName"), "ASC"|"DESC");


***Select all statement
DB::selectAll(array("table1",....), array("column1", "column2",...), "columnName = '$value'", array("columnName"), "ASC"|"DESC");

**Update Statement
DB::update(array("tableName"), array("column1"=>"value1", "column2"=>"value2".....));

**Count Statement
DB::count(array(table1), array(), "column1 = 'value'")


**Delete Statement
DB::delete("table1", "columName = '$value'")


Class is full documented on the usage of each method.
