<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: db.class.php 1059 2011-03-01 07:25:09Z monkey $
*/


class ucclient_db {
	var $querynum = 0;
    /**
     * @var PDO
     */
	var $link;
	var $histories;

	var $dbhost;
	var $dbuser;
	var $dbpw;
    var $dbname;
	var $dbcharset;
	var $pconnect;
	var $tablepre;
	var $time;

	var $goneaway = 5;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0, $tablepre='', $time = 0) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->dbcharset = $dbcharset;
		$this->pconnect = $pconnect;
		$this->tablepre = $tablepre;
		$this->time = $time;

        $dsn = 'mysql:dbname='.$dbname.';host='.$dbhost;
        if(!$this->link =  new PDO($dsn, $dbuser, $dbpw)) {
            $this->halt('Can not connect to MySQL server');
        }

	}

	function fetch_array($query) {
	    $query = $this->link->query($query);
		return $query->fetchAll();
	}

	function result_first($sql) {
		$query = $this->query($sql);
		return $this->result($query, 0);
	}

	function fetch_first($sql) {
		$query = $this->query($sql);
		return $this->fetch_array($query);
	}

	function fetch_all($sql, $id = '') {
		$arr = array();
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$id ? $arr[$data[$id]] = $data : $arr[] = $data;
		}
		return $arr;
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
        return $this->link->exec($sql);
	}

	function affected_rows() {
        return 0;
	}

	function error() {
		return $this->link->errorInfo();
	}

	function errno() {
        return $this->link->errorCode();
	}

	function result($query, $row) {
		$query = $this->link->query($query);
		return $query->fetchColumn($row);
	}

	function num_rows($query) {
		$query = $this->link->query($query);
		return $query->fetchAll();
	}

	function num_fields($query) {
        $query = $this->link->exec($query);
		return $query;
	}

	function free_result($query) {
        $query = $this->link->query($query);
        return $query->fetchAll();
	}

	function insert_id() {
		return ($id = $this->link->lastInsertId()) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
        $query = $this->link->exec($query);
		return $query;
	}

	function fetch_fields($query) {
        $query = $this->link->exec($query);
        return $query;
	}

	function version() {
		return '';
	}

	function close() {
		return '';
	}

	function halt($message = '', $sql = '') {
		$error = $this->link->errorInfo();
		$errorno = $this->link->errorCode();
		if($errorno == 2006 && $this->goneaway-- > 0) {
			$this->connect($this->dbhost, $this->dbuser, $this->dbpw, $this->dbname, $this->dbcharset, $this->pconnect, $this->tablepre, $this->time);
			$this->query($sql);
		} else {
			$s = '';
			if($message) {
				$s = "<b>UCenter info:</b> $message<br />";
			}
			if($sql) {
				$s .= '<b>SQL:</b>'.htmlspecialchars($sql).'<br />';
			}
			$s .= '<b>Error:</b>'.$error.'<br />';
			$s .= '<b>Errno:</b>'.$errorno.'<br />';
			$s = str_replace(UC_DBTABLEPRE, '[Table]', $s);
			exit($s);
		}
	}
}

?>