<?php
/**
 *
 * The content is generated by using TextMate, and edited by yuhari.
 *
 *
 * Download Helper
 *
 *
 * @author 	   yuhari
 * @maintainer yuhari
 * @version    1.0.0
 * @modified   2019/07/30 18:28:33
 *
 */
namespace box\Downloader ;

use Data\TaskProgress\Record as TaskRecord ;

class DownloadHelper {
	
	protected $action ;
	
	protected $task_queue ;
	
	public function handle($tasks, $action = 'start') {
		
		$this->action = $action ;
		
		if (!is_array($tasks) && ($tasks instanceof TaskRecord)) {
			$tasks = [$tasks] ;
		}
		
		$this->task_queue = new \SplQueue() ;
		foreach($tasks as $task) {
			
			$tmp_task = [
				// 任务实体
				'entity' 	=> $task ,
				// 任务进程状态，
				'status' 	=> 0 ,
				// speed
				'speed'		=> [] ,
			] ;
			
			$this->task_queue->enqueue($tmp_task) ;
		}
		
		
	}
}
