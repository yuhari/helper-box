<?php
/**
 * 
 *
 * @author yuhari
 * @version 1.0
 * @copyright , 22 June, 2018
 * @package helper
 */

/**
 * 读取文件的reader
 * 实现了linux下常用的head,tail等操作
 */
namespace box ;

class FileReader {
	
	// 文件路径
	private $filePath ;
	// reader实例，一个SplFileObject
	private $readInstance ;
	
	public function __construct($file, $mode='r') {
		if ($file instanceof SplFileObject) {
			$this->readInstance = $file ;
		}else {
			$this->filePath = $file ;
			$this->readInstance = new SplFileObject($file, $mode) ;
		}
	}
	
	// 获取file实例
	public function getInstance() {
		return $this->readInstance ;
	}
	
	// 加锁
	private function lock() {
		$this->readInstance->flock(LOCK_SH) ;
	}
	
	// 解锁
	private function unlock() {
		$this->readInstance->flock(LOCK_UN) ;
	}
	
	// 统计文件总行数
	public function lines() {
		$this->lock() ;
		
		$i = 0 ;
		while($this->readInstance->valid()) {
			$buf = $this->readInstance->fread(1024*1024) ;
			$i += substr_count($buf, "\n") ;
		}
		
		$this->unlock() ;
		
		return $i ;
	}
	
	// 查询某个字符串出现的位置, start 起始行数后 ii 行内 
	// 返回起始行后的索引位置
	public function search($s, $start = 1, $ii = 0) {
		if ($s == '') {
			return false ;
		}
		
		$this->lock() ;
		
		$this->readInstance->seek($start - 1) ;		
		$sp = $this->readInstance->ftell() ;

		$pos = array() ;
		$l = $ii ;
		while(($l==0 || $ii>0) && $this->readInstance->valid()) {
			$np = $this->readInstance->ftell() ;
			$t = $this->readInstance->fgets() ;
			
			$i = substr_count($t, $s) ;
			
			if ($i > 0) {
				$lp = 0 ;
				for($j = 0; $j<$i; $j++) {
					$p = strpos($t, $s, $lp) ;
					$pos[] = $np + $p - $sp ;
					$lp = $p + strlen($s) ;
				}
			}
			
			$ii -- ;
		}
		
		$this->unlock() ;
		
		return $pos ;
	}
	
	// 读取前 i 行数据
	public function head($i) {
		$this->lock() ;
		
		$buf = array() ;
		$this->readInstance->seek(0) ;  // 文件索引归0
		while ($i>0 && $this->readInstance->valid()) {
			$buf[] = $this->readInstance->fgets() ;
			$i -- ;
		}
		
		$this->unlock() ;
		
		return $buf ;
	}
	
	// 读取末尾 i 行数据
	public function tail($i) {
		$this->lock() ;
		
		$buf = array() ;
		
		$p = -2 ;
		$head = false ;
		while($i>0) {
			$c = '' ;
			while ($c != "\n") {
				$r = $this->readInstance->fseek($p, SEEK_END) ;
				if ($r == 0) {
				 	$c = $this->readInstance->fgetc() ;
					$p -- ;
				}else{
					$head = true ;
					$this->readInstance->fseek(0, SEEK_SET) ;
					break ;
				}
			}
			
			$buf[] = $this->readInstance->fgets() ;
			$c = '' ;
			$i -- ;
			
			if ($head) break ; //到达文件头部就不在读取了
		}
		
		$this->unlock() ;
		
		return array_reverse($buf) ;
	}
	
	// 读取指定起始 start 行后 i 行的内容, start 从0开始, i = 0 则认为是到结尾
	public function slice($start, $i = 0) {
		$this->lock() ;

		$buf = array() ;
		$this->readInstance->seek($start - 1) ;
		$j = $i ;
		while (($j==0 || $i>0) && $this->readInstance->valid()) {
			$buf[] = $this->readInstance->fgets() ;
			$i -- ;
		}
		
		$this->unlock() ;
		
		return $buf ;
	}
}
