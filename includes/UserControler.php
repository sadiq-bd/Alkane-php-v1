<?php

namespace Alkane\UserControler;

use \Alkane\AlkaneAPI\AlkaneAPI;
use Alkane\Database\Database;
use Alkane\SqlQuery\SqlQuery;
use Alkane\SessionStorage\SessionStorage;

/**
 * UserControler Class
 *
 * @category  User Handler
 * @package   User
 * @author    Sadiq <sadiq.com.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   1.0.0
 * @package   Alkane\User
 */

class UserControler extends AlkaneAPI {
    
    /**
     * @var $id
     */
    private $userId = 0;

    /**
     * @var $email
     */
    private $userEmail = '';

    /**
     * @var $password
     */
    private $userPassword = '';

    /**
     * @var $extraOption
     */
    private $userExtra = array();

    /**
     * @var $dbConn
     */
    private $dbInstance = null;

    /**
     * @var $tableName
     */
    private $tableName = 'app_users';

    /**
     * @var $errInfo
     */
    private $errInfo = '';



    public $auth_by = 'user_email';

    public $email_hosts = array();

    /**
     * class constructor
     */
    public function __construct(int $id = 0) {
        if ($id !== 0) 
            $this->userId = $id;

        // create dbInstance
        $this->dbInstance = Database::getInstance();
        
        // set extra option (default)
        $this->set_default_user_extra();
    }

    public function set_id(int $id) {
        $this->userId = $id;
    }

    public function set_email(string $email, bool $validate = true) {
        if ($validate === true) {
            if ($this->is_valid_email($email))  $this->userEmail = $email;
        } else {
            $this->userEmail = $email;
        }
    }

    public function set_password(string $password) {
        $this->userPassword = $password;
    }

    public function set_option(string $opt, string $val) {
        $opt = 'user_' . str_replace(' ', '_', trim($opt));
        if (isset($this->userExtra[$opt])) {
            $this->userExtra[$opt] = $val;
            return true;
        }
    }

    public function add_user_extra_option(string $key, $val = '') {
        $key = 'user_' . str_replace(' ', '_', trim($key));
        $this->userExtra[$key] = $val;
    }

    public function remove_user_extra_option(string $key) {
        $key = 'user_' . str_replace(' ', '_', trim($key));
        unset($this->userExtra[$key]);
    }

    private function set_default_user_extra() {
        $userExtra = array(
            'username' => '',
            'first_name' => '',
            'last_name' => '',
            'updated_at' => '0000-00-00 00:00:00',
            'created_at' => date('Y-m-d H:i:s')
        );

        foreach ($userExtra as $key => $val) {
            $this->add_user_extra_option($key, $val);
        }
    }

    public function session_start($id = 0) {
        if (!$this->is_session_exist(['user'])) {
            if ($id == 0) $id = $this->userId;
            SessionStorage::set('user', ['id' => $id]);
        }
    }

    public function session_abort() {
        SessionStorage::unset('user');
    }

    public function is_session_exist() {
        if (SessionStorage::is_exist('user')) {
            if (!empty(SessionStorage::get('user')['id'])) {
                return true;
            }
        }
    }


    public function authenticate(string $email = '', string $password = '', bool $start_session = false) {
        $email = ($email == '') ? $this->userEmail : $email;
        $password = ($password == '') ? $this->userPassword : $password;

        if ($email == '' || $password == '') {
            $this->append_error('email-password-empty\n');
            return false;
        }

        if (!$this->is_valid_email($email, $this->email_hosts) && $this->auth_by == 'user_email') {
            $this->append_error('email-invalid\n');
            return false;
        }

        $user = $this->get_info($email, $this->auth_by);
        if (!$user) {
            $this->append_error('email-not-found\n');
            return false;
        }

        if ($this->verify_hash($password, $user['user_password']) == false) {
            $this->append_error('password-incorrect\n');
            return false;
        }

        if ($start_session === true) {
            $this->session_start($user['ID']);
        }

        return true;
    }

    public function get_info($data = 0, string $select_by = 'ID') {
        if ($data == 0 || empty($data))
            $data = $this->userId;

        $sql = new SqlQuery($this->dbInstance);

        $select = $sql->select()
            ->from(
                $this->tableName
            )->where(
                trim($select_by) . ' = :data', 
                [
                    'data' => $data
                ]
            )->execute();
        if ($select) 
            return $select;
        
        $this->append_error($sql->getErrorInfo());
        return false; 
    }

    public function create() {
        $sql = new SqlQuery($this->dbInstance);

        // remove ID from columns (Auto Increment)
        $columns = array_slice($this->get_table_columns(), 1);
        $data = array_slice($this->get_column_values(), 1);

        $create = $sql->insert(
                $this->tableName,
                $columns 
            )->values(
                $data
            )->execute();
        if ($create) 
            return true;
        
        $this->append_error($sql->getErrorInfo());
        return false;
    }

    public function update(int $id = 0) {
        if ($id === 0) 
            $id = $this->userId;

        $sql = new SqlQuery($this->dbInstance);
        $update = $sql->update($this->tableName)
                    ->set(
                        $this->get_table_columns(),
                        $this->get_column_values()
                    )
                    ->where('ID = :id', [
                        'id' => $id
                    ])->execute();
        if ($update)
            return true;
        
        $this->append_error($sql->getErrorInfo());
        return false; 
    }

    public function remove(int $id = 0) {
        if ($id === 0) 
            $id = $this->userId;

        $sql = new SqlQuery($this->dbInstance);
        $remove = $sql->delete($this->tableName)
                    ->where('ID = :id', [
                        'id' => $id 
                    ]);
        if ($remove)
            return true;
        
        $this->append_error($sql->getErrorInfo());
        return false; 
    }

    public function install_table() {
        $sql = '';
        $sql .= <<<QUERY
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
                ID INT(11) NOT NULL AUTO_INCREMENT, 
                user_email VARCHAR(255) NOT NULL,
                user_password VARCHAR(255) NOT NULL,
        QUERY;
        foreach ($this->userExtra as $key => $val) {
            if ($this->is_timestamp_column($key)) {
                $sql .= "{$key} TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
            } else {
                $sql .= "{$key} VARCHAR(255) NOT NULL,\n";
            }
        }
        $sql .= <<<QUERY
                PRIMARY KEY (ID)
            );
        QUERY;
        $this->dbInstance->query($sql);
    }

    public function get_table_columns() {
        $columns = array();
        $columns = [
            'ID', 
            'user_email', 
            'user_password'
        ];
        foreach ($this->userExtra as $key => $val) {
            array_push($columns, $key);
        }
        return $columns;
    }

    public function get_column_values() {
        $values = array();
        $values = [
            $this->userId,
            $this->userEmail,
            $this->userPassword
        ];
        foreach ($this->userExtra as $key => $val) {
            array_push($values, $val);
        }
        return $values;
    }

    private function is_timestamp_column($column_name) {
        $key_words = [
            'time', 'at' 
        ];

        foreach ($key_words as $word) {
            if (preg_match('/' . $word . '/i', $column_name)) {
                return true;
            }
        }

        return false;
    }

    public function verify_hash($password, $hash) {
        if (password_verify($password, $hash)) {
            return true;
        }
        return false;
    }

    public function is_valid_email(string $email, array $allowed_host = []) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {  
            if (array() !== $allowed_host) {
                $host = explode('@', $email)[1];
                if (in_array($host, $allowed_host)) {
                    return true;
                }
            } else {
                return true;
            }
            
        }

        return false;
    }

    public function get_error() {
        return $this->errInfo;
    }

    private function append_error(string $msg) {
        $this->errInfo .= $msg . "\n";
    }

}

