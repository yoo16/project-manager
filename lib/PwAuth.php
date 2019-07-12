<?php
/**
 * PwAuth 
 * 
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwAuth
{
    public $auth_columns = [
        'id' => ['column' => 'login_name'],
        'password' => ['column' => 'password', 'password_hash' => PASSWORD_BCRYPT]
    ];

    /**
     * constructor
     *
     * @param array $params
     */
    function __construct($params = null)
    {
        if ($params['auth_columns']) $this->setAuthColumns($params['auth_columns']);
    }

    /**
     * set auth columns
     *
     * @param array $auth_columns
     * @return void
     */
    function setAuthColumns($auth_columns)
    { 
        $this->auth_columns = $auth_columns;
    }

    /**
     * verify
     *
     * @param Entity $model
     * @param array $values
     * @return boolean
     */
    function verify($id, $hash_password, $values)
    {
        if (!$this->auth_columns) exit('Not found auth columns in model');
        $id_column = $this->auth_columns['id']['column'];
        $password_column = $this->auth_columns['password']['column'];

        if ($id == $values[$id_column]) {
            $password = $values[$password_column];
            return (password_verify($password, $hash_password));
        }
        return false;
    }

    /**
     * verify
     *
     * @param Entity $model
     * @param array request$
     * @return Entity
     */
    function verifyByModel($model, $request)
    {
        if (!$model->auth_columns) exit('Not found auth columns in model');
        $id_column = $model->auth_columns['id']['column'];
        $password_column = $model->auth_columns['password']['column'];

        $id = $request[$id_column];
        $raw_password = $request[$password_column];

        $model->where($id_column, $id);
        $model->one();
        if ($model->value) {
            if (password_verify($raw_password, $model->value['password'])) {
                $model->rememberAuth();
            } else {
                $model->value = null;
            }
        }
        return $model;
    }

    /**
     * auth
     *
     * @param Entity $model
     * @param array $values
     * @return Entity
     */
    function authByModel($model, $values)
    {
        if (!$model->auth_columns) exit('Not found auth columns in model');
        $id_column = $model->auth_columns['id']['column'];
        $id = $values[$id_column];
        $password_column = $model->auth_columns['password']['column'];
        $password_hash = $model->auth_columns['password']['hash'];
        $password = PwAuth::hash($values[$password_column], $password_hash);

        $model->where($id_column, $id);
        $model->where($password_column, $password);
        $model->one();
        if ($model->value) $model->rememberAuth();
        return $model;
    }

    /**
     * hash
     *
     * @param string $value
     * @param string $hash_type
     * @return string
     */
    function hash($value, $hash_type)
    {
        $value = hash($hash_type, $value, false);
        return $value;
    }

    /**
     * hash password
     *
     * @param string $raw_password
     * @return string
     */
    static function hashPassword($raw_password, $options = [])
    {
        if (!$options['hash_type']) $options['hash_type'] = PASSWORD_BCRYPT;
        $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT, ['cost' => 12]);
        return $hashed_password;
    }
}
