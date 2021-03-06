<?php
/**
 *
 * The content is generated by using TextMate, and edited by yuhari.
 *
 *
 * Downloader
 *
 *
 * @author 	   yuhari
 * @maintainer yuhari
 * @version    1.0.0
 * @modified   2019/07/26 18:55:36
 *
 */
namespace box\Downloader ;

class Downloader {
	
	protected $worker ;
	
	protected $client ;
	
	protected $url ;
	
	protected $fileName ;
	
	protected $start ;
	
	protected $length ;
	
	protected $offset ;
	
	public function __construct($worker, $client, $url, $fileName, $start, $length) {
		
		$this->worker 	= $worker ;
		$this->client 	= $client ;
		$this->url 		= $url ;
		$this->fileName = $fileName ;
		$this->start 	= $start ;
		$this->length 	= $length ;
	}
	
	public function download() {
		
		$this->offset = $this->start ;
		
		$file = fopen($this->fileName, 'rb+') ;
		fseek($file, $this->start, SEEK_SET) ;
		
		$resp = $this->client->request('GET', $this->url, [
			'stream' => true ,
			'headers' => [
				'Range' => 'bytes='. $this->start . '-' . ($this->start + $this->length)
			]
		]) ;
				
		$loaded = 0 ;
		while (!$resp->getBody()->eof()) {
			$size = 1024 * 5 ;
			$data = $resp->getBody()->read($size) ;
			
			$loaded += strlen($data) ;
			
			fwrite($file, $data) ;
			
			$this->worker->write(serialize([
				'type' => 'range' ,
				'data' => strlen($data) ,
			])) ;
			
			if ($loaded >= $this->length) break ;
		}
		
		fclose($file) ;
	}
}
