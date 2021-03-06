<?php
/**
 *
 * The content is generated by using TextMate, and edited by yuhari.
 *
 *
 * 队列任务消费, 基于swoole的go、channel
 *
 *
 * @author 	   yuhari
 * @maintainer yuhari
 * @version    1.0.0
 * @modified   2020/09/21 13:47:37
 *
 */
namespace box ;

class ListHelper {
	
	protected $list = array() ;
	
	protected $consumerNum = 10 ;
	
	protected $handler ;
	
	public function __construct() {
		\Swoole\Runtime::enableCoroutine() ;
	}
	
	public function setConsumerNum($concurrent) {
		$this->consumerNum = $concurrent ;
		return $this ;
	}
	
	public function setListData($list) {
		$this->list = $list ;
		return $this ;
	}
	
	public function setHandler($handler, $args = array()) {
		$this->handler = array($handler, $args) ;
		return $this ;
	}
	
	public function handle() {
		
		$pack = array(
			'list' 		=> $this->list ,
			'con'		=> $this->consumerNum ,
			'handler'	=> $this->handler ,
		) ;
		
		
		go(function() use($pack){
			$chan = new \Swoole\Coroutine\Channel(50) ;
			
			foreach($pack['list'] as $record) {
				go(function() use($chan, $record){
					$chan->push($record) ;
				}) ;
			}
			
			$this->_consume($chan, $pack) ;
		}) ;
	}
	
	private function _consume($chan, $pack) {
		for($i = 0; $i <= $pack['con']; $i++) {
			list($func, $args) = $pack['handler'] ;
			
			go(function() use($chan, $func, $args, $i) {
				while(1) {
					$stats = $chan->stats() ;
					if ($stats['producer_num'] == 0 && $stats['queue_num'] == 0) {
						break ;
					}
					
					$record = $chan->pop() ;
					
					$t = array_merge(array($record, $i), $args) ;
					$r = call_user_func_array($func, $t) ;
				}
			}) ; 
		}
	}
}

