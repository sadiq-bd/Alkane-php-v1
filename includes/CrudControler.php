<?php

namespace Alkane\CrudControler;

use \Alkane\AlkaneAPI\AlkaneAPI;
use Alkane\Database\Database;
use Alkane\SqlQuery\SqlQuery;


class CrudControler extends AlkaneAPI {

    private $columnNames;

    private $columnData;

    private $columnDataAssoc;

    private $tableName;

    private $tablePrimaryKey;

    private $dbInstanceConfigName;

    private $dbInstance;

    public $errorInfo;

    public function __construct() {

        $this->columnNames = array();

        $this->columnData = array();

        $this->columnDataAssoc = array();

        $this->tableName = '';

        $this->tablePrimaryKey = 'ID';

        $this->dbInstance = null;

        $this->dbInstanceConfigName = '';

        $this->errorInfo = '';

    }

    public function setColumnNames(array $columns) {
        $this->columnNames = $columns;
    }

    public function setColumnData(array $data) {
        $this->columnData = $data;
    }

    public function addColumnDataAssoc(string $key, $value) {
        $this->columnDataAssoc[$key] = $value;
    }

    public function setColumnDataAssoc(array $data) {
        foreach ($data as $key => $value) {
            $this->columnDataAssoc[$key] = $value;
        }
    }

    public function setTableName(string $tbl) {
        $this->tableName = trim($tbl);
    } 

    public function setTablePrimaryKey(string $key) {
        $this->tablePrimaryKey = trim($key);
    }

    public function setDbInstanceConfigName(string $name) {
        $this->dbInstanceConfigName = trim($name);
    }

    private function initDbInstance() {
        if ($this->dbInstance === null) {
            if ($this->dbInstanceConfigName === '') {
                $this->dbInstance = Database::getInstance(); 
                return;
            }
            $this->dbInstance = Database::getInstance($this->dbInstanceConfigName);
        }
    }

    private function initColumnData() {
        foreach ($this->columnDataAssoc as $key => $value) {
            $i = count($this->columnNames) - 1;
            $this->columnNames[$i] = $key;
            $this->columnData[$i] = $value;
        }
        
        $this->columnDataAssoc = array();   // empty array
        
        foreach ($this->setColumnNames as $key => $value) {
            $this->columnDataAssoc[$value] = $this->setColumnData[$key];
        }
    }

    private function initDefault() {
        // init DB instance
        $this->initDbInstance();

        // init Column
        $this->initColumnData();

    }

    public function countRows() {

        $this->initDefault();
        
        $sql = new SqlQuery($this->dbInstance);

        $count = $sql->select(
            [
                $this->tablePrimaryKey
            ], 
            'count'
        )->from(
            $this->tableName
        )->exec();
        
        return $count->fetchColumn();
    }

    public function create() {

        $this->initDefault();
        
        $sql = new SqlQuery($this->dbInstance);

        $create = $sql->insert(
            $this->tableName, 
            $this->columnNames
        )->values(
            $this->columnData
        )->exec();
        
        if (!$create) {
            $this->errorInfo .= $sql->getErrorInfo();
            return false;
        } else {
            return true;
        }
    }

    public function update($identity) {
        
        $this->initDefault();
        
        $sql = new SqlQuery($this->dbInstance);

        $update = $sql->update(
            $this->tableName
        )->set(
            $this->columnDataAssoc
        )->where(
            $this->tablePrimaryKey . ' = :identity',
            [
                $identity
            ]
        )->exec();
        
        if (!$update) {
            $this->errorInfo .= $sql->getErrorInfo();
        } else {
            return true;
        }
    }


    public function read($identity) {
        
        $this->initDefault();

        $sql = new SqlQuery($this->dbInstance);

        $select = $sql-> select()->from(
            $this->tableName
        )->where(
            $this->tablePrimaryKey . ' = :identity',
            [
                $identity
            ]
        )->exec();

        if ($select) {
            return $select->fetch(SqlQuery::FETCH_ASSOC);
        } else {
            $this->errorInfo .= $sql->getErrorInfo();
            return false;
        }
    }


    public function readAll(int $sort = 0, int $max = 10, int $start = 0) {
        
        $this->initDefault();
        
        if ($sort === 0) {
            $sort = SqlQuery::SORT_ASC;
        } else {
            $sort = SqlQuery::SORT_DESC;
        }

        $sql = new SqlQuery($this->dbInstance);

        $select = $sql->select()->from(
            $this->tableName
        )->orderby(
            $this->tablePrimaryKey, 
            $sort
        )->limit(
            $max, 
            $start
        )->exec();

        if ($select) {
            return $select->fetchAll(SqlQuery::FETCH_ASSOC);
        } else {
            $this->errorInfo .= $sql->getErrorInfo();
            return false;
        }
    }


    public function search(array $search, string $operator = 'AND') {

        $this->initDefault();

        $operator = strtolower($operator);
        switch ($operator) {
            case 'and':
            case '&&':
            case '&':
                $operator = 'AND';
                break;
            case 'or':
            case '||':
            case '|':
                $operator = 'OR';
                break;
            default:
                $operator = 'AND';
                break;
        }
        
        $sql = new SqlQuery($this->dbInstance);

        $sql-> select()->from(
            $this->tableName
        );
        $sql->where('',[]);
        
        $i = 0;
        $values = array();
        foreach ($search as $key => $val) {
            if ($i + 1 === count($search)) {
                $sql->appendQuery("{$key} LIKE :{$key} ");
            } else {
                $sql->appendQuery("{$key} LIKE :{$key} {$operator} ");
            }
            $values[$key] = '%' . $val . '%';

            $i++;
        }
        
        $sql->orderby(
            $this->tablePrimaryKey, 
            SqlQuery::SORT_DESC
        )->limit(10);

        $select = $sql->exec($values);

        if ($select) {
            return $sql->fetchAll(SqlQuery::FETCH_ASSOC);
        } else {
            $this->errorInfo .= $sql->getErrorInfo();
            return false;
        }
    }

    
    public function delete($identity) {

        $this->initDefault();
        
        $sql = new SqlQuery($this->dbInstance);

        $delete = $sql->delete(
            $this->tableName
        )->where(
            $this->tablePrimaryKey . ' = :identity',
            [
                $identity
            ]
        )->exec();
        
        if (!$delete) {
            $this->errorInfo .= $sql->getErrorInfo();
        } else {
            return true;
        }
    }


}

