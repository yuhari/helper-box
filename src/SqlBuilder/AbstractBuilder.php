<?php
/**
 *
 * The content is generated by using TextMate, and edited by yuhari.
 *
 *
 * abstract builder class
 * abstract class contains `from`,`join`,`where` clause,
 * other clause will be achieved in the specific class .
 *
 *
 * @author 	   yuhari
 * @maintainer yuhari
 * @version    1.0.0
 * @modified   2018/07/25 18:06:16
 *
 */
namespace box\SqlBuilder ;

abstract class AbstractBuilder {
	
	// from
	protected $from = array() ;
	// from table index.
	protected $from_key = -1 ;
	// join table
	protected $join = array() ;
	
	// where
	protected $where = array() ;
	
	// bind values set .
	protected $bind_values = array() ;
	
	// table alias 
	protected $alias ;
	// sub need alias ;
	protected $sub_need_alias = true ;
	
	// construct elements to string .
	abstract public function getSqlString() ;
	
	// reset clause elements .
	abstract public function reset() ;
	
	public function __toString() {
		$clause = $this->getSqlString() ;
		return $clause ;
	}
	
	/**
	 * set table alias
	 *
	 * @param  string $name
	 * @return $this
	 */
	public function alias($name) {
		$this->alias = $name ;
		
		return $this ;
	}
	
	/**
	 * add a from element into the query
	 *
	 * @param  string $table
	 * @return $this
	 */
	public function from($spec) {
		if ($spec instanceof SelectBuilder) {
			// sub select query.
			$spec = $query_string = $this->getSubQueryString($spec) ;
		} 
			
		$this->addFrom($spec) ;
		
		return $this ;
	}
	
	/**
	 * function description
	 *
	 * @param  string $t
	 * @return void
	 */
	protected function addFrom($t) {
		$this->from[] = array($t) ;
		$this->from_key ++ ;
	}
	
	/**
	 * add a join table and column
	 *
	 * @param  string $type , 'LEFT', 'RIGHT', 'INNER' ..
	 * @param  string $spec , table or sub select clause .
	 * @param  string $cond , on condition .
	 * @param  array  $bind_values , bind values .
	 * @return $this
	 */
	public function join($type, $spec, $cond = null , array $bind_values = array()) {
		$type = strtoupper(ltrim("$type JOIN")) ;
		
		if ($spec instanceof SelectBuilder) {
			// sub select query.
			$spec = $this->getSubQueryString($spec) ;
		}

		$cond = $this->rebuildCondAndValues($cond, $bind_values) ;

		$from_key = $this->from_key < 0 ? 0 : $this->from_key ;
		$this->join[$from_key][] = trim("$type $spec ON $cond") ;
		
		return $this ;
	}
	
	/**
	 * add a left join
	 *
	 * @param  ...
	 * @return $this
	 */
	public function leftJoin($spec, $cond = null , array $bind_values = array()) {
		return $this->join('LEFT', $spec, $cond , $bind_values) ;
	}
	
	/**
	 * add a where condition , not support sub select in where clause .
	 *
	 * @param  string $type , 'AND', 'OR'
	 * @param  string $cond , where condition clause .
	 * @param  array $bind_values 
	 * @return void
	 */
	public function where($type, $cond, array $bind_values = array()) {
		$type = strtoupper(ltrim("$type")) ;
		
		$cond = $this->rebuildCondAndValues($cond, $bind_values) ;
		if (empty($this->where)) {
			$this->where[] = $cond ;
		}else{
			$this->where[] = trim("$type $cond") ;
		}
		
		return $this ;
	}
	
	/**
	 * function `where` while type = 'AND'
	 *
	 * @param  ...
	 * @return $this
	 */
	public function andWhere($cond, array $bind_values = array()) {
		return $this->where('AND', $cond, $bind_values) ;
	}
	
	/**
	 * function `where` while type = 'OR'
	 *
	 * @param  ...
	 * @return $this
	 */
	public function orWhere($cond, array $bind_values = array()) {
		return $this->where('OR', $cond, $bind_values) ;
	}
	
	/**
	 * build from .. join string ;
	 *
	 * @param  null
	 * @return string
	 */
	protected function buildTables() {
		if (empty($this->from)) {
			return '' ;
		}
		
		$t = array() ;
		foreach($this->from as $k => $v) {
			if (isset($this->join[$k])) {
				$v = array_merge($v, $this->join[$k]) ;
			}

			$t = array_merge($t, array(implode(' ', $v))) ;
		}
		
		return implode(',' , $t) ;
	}
	
	/**
	 * build where condition string
	 *
	 * @param  null
	 * @return string
	 */
	protected function buildWhere() {
		$clause = '' ;

		if (!empty($this->where)) {
			$clause = ' WHERE ' . implode(' ', $this->where) ;
		}
		
		return $clause ;
	}
	
	/**
	 * get sub query sql string .
	 *
	 * @param  SelectBuilder $spec
	 * @return string
	 */
	protected function getSubQueryString(SelectBuilder $spec) {
		if (($name = $spec->getAlias()) || !$this->sub_need_alias) {
			$this->bindValues($spec->getBindValues()) ;
			$query_string = (string)$spec ;
			
			if ($this->sub_need_alias) {
				$spec = "($query_string) AS $name" ;
			} else {
				$spec = "($query_string)" ;
			}
		} else {
			throw new \Exception("argument `name` in sub query is necessary.") ;
		}
		
		return $spec ;
	}
	
	/**
	 * rebuild condition string .
	 * symbol `?` means placeholder value , replace `?` into placeholder key .
	 *
	 * @param  string $cond
	 * @param  array $bind_values
	 * @return string
	 */
	protected function rebuildCondAndValues($cond, array &$bind_values = array()) {
		$l = preg_split('#(\?)#', $cond, null, PREG_SPLIT_DELIM_CAPTURE) ;
		foreach($l as $k => $v) {
			if ($v == '?') {
				$bind_value = array_shift($bind_values) ;
				
				$placeholder = "__" . (count($this->bind_values) + 1) . "__" ;
				$l[$k] = ':' . $placeholder ;
				
				$this->bind_values[$placeholder] = $bind_value ;
			}
		}
		
		$cond = implode('' , $l) ;
		return $cond ;
	}
	
	/**
	 * get bind values
	 *
	 * @param  default
	 * @return array $bind_values
	 */
	public function getBindValues() {
		return $this->bind_values ;
	}
	
	/**
	 * set bind values
	 *
	 * @param  array $bind_values
	 * @return $this 
	 */
	public function bindValues(array $bind_values = array()) {
		$this->bind_values = array_merge($this->bind_values, $bind_values) ;
		return $this ;
	}
	
	/**
	 * return query alias
	 *
	 * @param  default
	 * @return string
	 */
	protected function getAlias() {
		return $this->alias ;
	}
}
