<?php
/**
 * 
 *
 * @author yuhari
 * @version $Id$
 * @copyright , 21 April, 2018
 * @package default
 */

/**
 * 多进程处理接口任务 
 */
namespace box ;

class ForkHelper {
	
	protected $tasks = array() ;
	
	protected $pids = array() ;
	
	public function setTask($tag , $taskName, $taskParams = array()) {
		$this->tasks[] = array($tag , $taskName, $taskParams) ;
	}
	
	public function run() {
		foreach($this->tasks as $k=>$task) {
			if (file_exists($task[1])) {				
				$this->createProcess($task, $k, 'file') ;
			}elseif(is_callable($task[1])){
				$this->createProcess($task, $k, 'callable') ;
			}
		}
	}
	
	protected function createProcess($task, $id, $type) {
		$pid = pcntl_fork() ;
		
		$this->pids[$id] = array(
			'pid' => $pid ,
			'tag' => $task[0] ,
		) ;
		
		switch ($pid) {
			case -1 :
				echo "fork error : {$id} \n" ;
				exit ;
			case 0 :
				$res = array() ;
				try{
					if ($type == 'file') {
						$res = require($task[1]) ;
					}elseif($type == 'callable') {
						$res = call_user_func_array($task[1], $task[2]) ;
					}
				}catch(\Exception $e) {
					echo $e->getMessage() . "\n" ;
				}
				
				//将执行返回值加入到共享内存中，以便在主进程中聚合
				$shm_key = ftok(__FILE__, 't') . getmypid() ;
				$data = json_encode($res) ;
				$shm_id = shmop_open($shm_key, 'c', 0777, strlen($data) + 10) ;
				shmop_write($shm_id, $data, 0) ;
				shmop_close($shm_id) ;
				exit;
				
			default : break ;
		}
	}
	
	public function wait() {
		$res = array() ;
		foreach($this->pids as $k => $v) {
			if ($pid = $v['pid']) {
				pcntl_waitpid($pid, $status) ;
				
				$shm_key = ftok(__FILE__, 't') . $pid ;
				$shm_id = shmop_open($shm_key, 'w', 0,0) ;
				$data = trim(shmop_read($shm_id, 0, shmop_size($shm_id))) ;
				$data = json_decode($data, true) ;
				
				$res[$v['tag']] = $data ;
				@shmop_close($shm_id) ;
				@shmop_delete($shm_id) ;
			}
		}
		
		return $res ;
	}
}
