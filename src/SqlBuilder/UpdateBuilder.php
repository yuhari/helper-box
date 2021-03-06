<?php
/**
 *
 * The content is generated by using TextMate, and edited by yuhari.
 *
 *
 * sql update builder
 *
 *
 * @author 	   yuhari
 * @maintainer yuhari
 * @version    1.0.0
 * @modified   2018/07/26 16:42:04
 *
 */
namespace box\SqlBuilder ;

class UpdateBuilder extends AbstractBuilder {
	
	// set value clause
	protected $values = array() ;
	
	/**
	 * function description
	 *
	 * @param  default
	 * @return void
	 */
	public function getSqlString() {
		$clause = "UPDATE " 
			. $this->buildTables() 
			. ' SET ' . $this->buildValues() 
			. $this->buildWhere() ;
		
		return $clause ;
	}
	
	/**
	 * reset clause
	 *
	 * @param  default
	 * @return void
	 */
	public function reset() {
		return $this ;
	}
	
	/**
	 * add key-value clause that will update . 
	 *
	 * @param  array $spec
	 * @return $this
	 */
	public function update(array $spec, array $bind_values = array()) {
		foreach($spec as $k => $v) {
			if (is_string($k)) {
				$clause = "`$k`=$v" ;
			}else{
				$clause = trim($v) ;
			}
			
			$clause = $this->rebuildCondAndValues($clause , $bind_values) ;
			
			$this->values[] = $clause ;
		}
		
		return $this ;
	}
	
	/**
	 * build set value clause .
	 *
	 * @param  default
	 * @return string
	 */
	protected function buildValues() {
		if (empty($this->values)) {
			throw new \Exception('Set-Value is necessary in update-clause') ;
		}
		
		$clause = implode(', ' , $this->values) ;
		return $clause ;
	}
	
}
