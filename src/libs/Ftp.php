<?php

namespace zhost\libs;

$config = [
    'host'     => '43.226.43.6',
    'port'     => '21',
    'username' => 'host705114622',
    'password' => '506I0488ee',
];

$ftp = new Ftp($config);
$res = $ftp->size('/wwwroot/1.php');
var_dump($res);
$res = $ftp->get('1.php','/wwwroot/1.asp');
var_dump($res);

class Ftp
{
    private $connection;

    public function __construct($config)
    {
        $this->connect($config);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * config check
     *
     * @param $config
     *
     * @return array
     */
    private function configCheck($config)
    {
        if (is_array($config)) {
            if (!isset($config['host'])) {
                return $this->response(-1, 'ftp server connect failed.');
            }
            if (!isset($config['port'])) {
                return $this->response(-1, 'ftp server connect failed.');
            }
            if (!isset($config['username'])) {
                return $this->response(-1, 'ftp server connect failed.');
            }
            if (!isset($config['password'])) {
                return $this->response(-1, 'ftp server connect failed.');
            }
        } else {
            return $this->response(-1, 'ftp server connect failed.');
        }
    }

    /**
     * connect the ftp server and logined
     *
     * @param $config
     *
     * @return array
     */
    private function connect($config)
    {
        $this->configCheck($config);

        $this->connection = @ftp_connect($config['host'], $config['port']);
        if ($this->connection == false) {
            return $this->response(-1, 'ftp server connect failed.');
        }

        if (!@ftp_login($this->connection, $config['username'], $config['password'])) {
            return $this->response(-1, 'ftp server login failed.');
        }

        @ftp_pasv($this->connection, true);

    }

    /**
     * close the ftp connection
     */
    private function close()
    {
        if (!empty($this->connection)) {
            ftp_close($this->connection);
        }
    }

    /**
     * get a file size
     *
     * @param $remote
     *
     * @return array
     */
    public function size($remote)
    {
        // get size
        $size = @ftp_size($this->connection, $remote);

        // result
        if ($size === -1) {
            return $this->response(-1, 'the file size get failed.');
        } else {
            $result['size'] = $size;

            return $this->response(0, $result);
        }
    }

    public function get($local, $remote)
    {
        // get size
        $result = @ftp_get($this->connection, $local, $remote, FTP_BINARY);

        // result
        if ($result) {
            return $this->response(0, 'the file download success.');
        } else {
            return $this->response(-1, 'the file download failed.');
        }
    }

    /**
     * response
     *
     * @param $code
     * @param $message
     *
     * @return array
     */
    private function response($code, $message)
    {
        $result['code'] = $code;
        if (is_array($message)) {
            $result = array_merge($result, $message);
        } else {
            $result['code'] = $message;
        }

        if ($code === 0) {
            return $result;
        } else {
            die(json_encode($result));
        }
    }
}