<?php
/**
 * Created by PhpStorm.
 * User: zscally
 * Date: 1/21/2016
 * Time: 3:01 PM
 */


namespace lib;

use lib\Config;
use PDO;

class DB extends PDO
{
    public $dbh; // handle of the db connexion
    private static $instance;
    private $sql;
    private $bind;
    public  $error;

    public function __construct()
    {
        // building data source name from config
        // Sample DSNs:
        // On Mac OS X: DB_DSN_XJAIL="dblib:host=svdb37;dbname=XJailLMDC"
        // On Windows: DB_DSN_XJAIL="sqlsrv:Server=svdb37;Database=XJailLMDC"

        switch( Config::read('db.driver') )
        {
            CASE 'mysql':
                $dsn = 'mysql:host=' . Config::read('db.host') .
                    ';dbname=' . Config::read('db.name') .
                    ';port=' . Config::read('db.port') .
                    ';connect_timeout=' . Config::read('db.connect_timeout')
                ;

                $options = array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );

                break;

            CASE 'mssql':
                $dsn = 'sqlsrv:server=' . Config::read('db.host') .
                    ';Database=' . Config::read('db.name')
                ;

                $options = array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );

                break;

            CASE 'odbc':
                $dsn = 'odbc:Driver=FreeTDS; Server=' . Config::read('db.host') .
                    '; Port=' . Config::read('db.port') .
                    '; Database=' . Config::read('db.name') .
                    '; UID=' . Config::read('db.user') .
                    '; PWD=' . Config::read('db.password')
                ;

                $options = array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );
                break;

            CASE 'dblib':
                $dsn = 'dblib:version=7.0;charset=UTF-8;host=' . Config::read('db.host') . ':' . Config::read('db.port') .
                    ';dbname=' . Config::read('db.name')
                ;

                $options = array();

            CASE 'sqlite':
                break;
        }

        // getting DB user from config
        $user = Config::read('db.user');
        // getting DB password from config
        $password = Config::read('db.password');
        try
        {
            $this->dbh = new PDO($dsn, $user, $password, $options);
        }
        catch (PDOException $e)
        {
            $this->error = $e->getMessage();
        }

    }


    public function getInstance() {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }


    public function delete( $table, $where, $bind='' )
    {
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $where . ';';
        $this->run( $sql, $bind );
    }


    private function filter( $table, $info )
    {
        $driver = $this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME);

        if($driver == 'sqlite')
        {
            $sql = 'PRAGMA table_info("' . $table . '");';
            $key = 'name';
        }
        elseif($driver == 'mysql')
        {
            $sql = 'DESCRIBE ' . $table . ';';
            $key = 'Field';
        }
        else
        {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
            $key = 'column_name';
        }

        if( false !== ( $list = $this->run( $sql ) ) )
        {
            $fields = array();

            foreach( $list as $record )
                $fields[] = $record[$key];

            return array_values( array_intersect( $fields, array_keys( $info ) ) );
        }

        return array();
    }


    private function cleanup( $bind )
    {
        if( ! is_array( $bind ) )
        {
            if( ! empty( $bind ) )
                $bind = array( $bind );
            else
                $bind = array();
        }
        return $bind;
    }


    public function insert( $table, $info )
    {
        $fields = $this->filter( $table, $info );
        $sql = "INSERT INTO " . $table . " (" . implode( $fields, ", " ) . ") VALUES (:" . implode( $fields, ", :" ) . ");";
        $bind = array();

        foreach( $fields as $field )
            $bind[":$field"] = $info[$field];

        return $this->run($sql, $bind);
    }


    public function run( $sql, $bind='' )
    {
        $this->sql = trim($sql);
        $this->bind = $this->cleanup($bind);
        $this->error = '';

        try
        {
            $pdostmt = $this->dbh->prepare( $this->sql );
            if( $pdostmt->execute($this->bind) !== false )
            {
                if( preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->sql ) )
                    return $pdostmt->fetchAll(PDO::FETCH_ASSOC);
                elseif( preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->sql ) )
                    return $pdostmt->rowCount();
            }
        }
        catch (PDOException $e)
        {
            $this->error = $e->getMessage();
            $this->debug();
            return false;
        }
    }


    public function select($table, $where="", $bind="", $fields="*")
    {
        $sql = "SELECT " . $fields . " FROM " . $table;
        if( ! empty( $where ) )
            $sql .= " WHERE " . $where;

        $sql .= ";";

        return $this->run( $sql, $bind );
    }


    public function update($table, $info, $where, $bind='')
    {
        $fields = $this->filter($table, $info);
        $fieldSize = sizeof($fields);

        $sql = 'UPDATE ' . $table . ' SET ';
        for( $f = 0; $f < $fieldSize; ++$f )
        {
            if($f > 0)
                $sql .= ', ';

            $sql .= $fields[$f] . ' = :update_' . $fields[$f];
        }

        $sql .= ' WHERE ' . $where . ';';

        $bind = $this->cleanup($bind);

        foreach($fields as $field)
            $bind[":update_$field"] = $info[$field];

        return $this->run($sql, $bind);
    }

    public function error()
    {
        return $this->error;
    }
}