<?php
/* database.class.php
 *
 * the main class to run and execute database queries for ease of reading and use in the 
 * rest of the application code
 */
 
$request = basename($_SERVER['REQUEST_URI']);
if ($request == 'database.class.php') { echo 'You cannot access this file directly'; exit(); }
 
class DataBase
{
	var $host;
	var $user;
	var $pass;
	var $name;
	var $link;
	var $error;
	var $query;
	var $rows;
	var $connected;
	
	function DataBase($host, $user, $pass, $name)
	{
		$link = @mysql_connect($host, $user, $pass, TRUE);
		if ($link)
		{
			$select = mysql_select_db($name, $link);
			if ($select)
			{
				$this->host = $host;
				$this->user = $user;
				$this->pass = $name;
				$this->name = $name;
				$this->link = $link;
				
				$this->connected = TRUE;
			}
			else
			{
				$this->error = mysql_error();
				$this->connected = FALSE;
			}
		}
		else
		{
			$this->error = 'Error connecting to MySQL Server';
			$this->connected = FALSE;
		}
	}
	
	public function Error()
	{
		return $this->error;
	}
	
	public function Sql_Link()
	{
		return $this->link;
	}
	
	// execute a given query and return true/false on success/fail
	public function run()
	{
		$argv = func_get_args();
		$protected_query = call_user_func_array(array($this, 'Q'), $argv);
		$this->query = $protected_query;
		
		$rs = mysql_query($protected_query, $this->link);
		
		if (!$rs)
		{
			echo mysql_error() . "\n" . $protected_query;
			$this->error = mysql_error();
			return FALSE; 
		}
		else
		{
			return TRUE; 
		}
	}
	
	// execute a given query and return insert id on success
	public function insert()
	{
		$argv = func_get_args();
		$protected_query = call_user_func_array(array($this, 'Q'), $argv);
		$this->query = $protected_query;
		
		$rs = mysql_query($protected_query, $this->link);
		if ($rs) { return mysql_insert_id($this->link); } else { $this->error = mysql_error(); return FALSE; }
	}
	
	// execute a simple mysql_result on a single row
	public function result()
	{
		$argv = func_get_args();
		$protected_query = call_user_func_array(array($this, 'Q'), $argv);
		$this->query = $protected_query;
		
		$rs = mysql_query($protected_query, $this->link);
		
		if (!$rs)
		{
			$this->error = mysql_error();
			return FALSE;
		}
		
		if (mysql_num_rows($rs) > 0)
		{
			$return = mysql_result($rs, 0);
		}
		else
		{
			$return = FALSE;
		}
		return $return;
	}
	
	public function assoc()
	{
		$argv = func_get_args();
		$protected_query = call_user_func_array(array($this, 'Q'), $argv);
		$this->query = $protected_query;
		
		$rs = mysql_query($protected_query, $this->link);
		
		if (!$rs)
		{
			$this->error = mysql_error();
			return false;
		}
		
		if (mysql_num_rows($rs) == 0)
		{
			$this->rows = mysql_num_rows($rs);
			return false;
		}
		
		$return = array();
		if (mysql_num_rows($rs) == 1)
		{
			$return = mysql_fetch_assoc($rs);
		}
		else
		{
			while ($entry = mysql_fetch_assoc($rs))
			{
				$return[] = $entry;
			}
		}
		return $return;
	}
	
	public function force_multi_assoc()
	{
		$argv = func_get_args();
		$protected_query = call_user_func_array(array($this, 'Q'), $argv);
		$this->query = $protected_query;
		
		$rs = mysql_query($protected_query, $this->link);
		if (!$rs)
		{
			$this->error = mysql_error();
			return FALSE;
		}
		if (mysql_num_rows($rs) == 0) { return false; }
		
		$return = array();
		while ($entry = mysql_fetch_assoc($rs))
		{
			$return[] = $entry;
		}
		return $return;
	}
	
	private function Q($_query)
	{
		$argv = func_get_args();
		$argc = func_num_args();
		$n = 1;			// first vararg $argv[1]

		$out = '';
		$quote = FALSE;		// quoted string state
		$slash = FALSE;		// backslash state

		// b - pointer to start of uncopied text
		// e - pointer to current input character
		// end - end of string pointer
		$end = strlen($_query);
		for ($b = $e = 0; $e < $end; ++$e)
		{
			$ch = $_query{$e};

			if ($quote !== FALSE)
			{
				if ($slash)
				{
					$slash = FALSE;
				}
				elseif ($ch === '\\')
				{
					$slash = TRUE;
				}
				elseif ($ch === $quote)
				{
					$quote = FALSE;
				}
			}
			elseif ($ch === "'" || $ch === '"')
			{
				$quote = $ch;
			}
			elseif ($ch === '?')
			{
				$out .= substr($_query, $b, $e - $b) .
					$this->_Q_escape($argv[$n], $n);
				$b = $e + 1;
				$n++;
			}
		}
		$out .= substr($_query, $b, $e - $b);

		// warn on arg count mismatch
		if ($argc != $n)
		{
			$adj = ($argc > $n) ? 'many' : 'few';
			trigger_error('Too ' . $adj . ' arguments ' .
					'(expected ' . $n . '; got ' . $argc . ')',
				E_USER_WARNING);
		}

		return $out;
	}

	private function _Q_escape($_value, $_position = FALSE)
	{
		static $r_position;

		// Save $_position to simplify recursive calls.
		if ($_position !== FALSE)
		{
			$r_position = $_position;
		}

		if (is_null($_value))
		{
			// The NULL value
			return 'NULL';
		}
		elseif (is_int($_value) || is_float($_value))
		{
			// All integer and float representations should be
			// safe for mysql (including 5e-12 notation)
			$result = "$_value";
		}
		elseif (is_array($_value))
		{
			// Arrays are written as a comma-separated list of
			// values.  Useful for IN, find_in_set(), etc.

			// KM, AS: PHP stoneage is crashing here, when the
			// _values array is missing a 0 index.. hence the array_values()
			$result = implode(', ', array_map(array($this, '_Q_escape'), array_values($_value)));
		}
		else
		{
			// Warn if given an unexpected value type
			if (!is_string($_value))
			{
				trigger_error('Unexpected value of type "' .
					gettype($_value) . '" in arg '.$r_position,
					E_USER_WARNING);
			}

			// Everything else gets escaped as a string 
			$result = "'" . addslashes($_value) . "'";
		}

		return $result;
	}
}


?>
