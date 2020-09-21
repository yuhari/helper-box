# helper-box
Some tools that assist you, such as file reader, sql clause builder ...

> ### Downloader （下载器）
> ```
> <?php
> 		$helper = new \box\Downloader\Helper($source_path, $target_path) ;
> 		$helper->start() ;
> ```
> 
> ### ListHelper	（列表消费）
> ```
> <?php
> 		$helper = new \box\ListHelper() ;
> 		$helper->setConsumerNum(10)  # 设置消费者数量
> 				->setListData($list)  # 设置待消费队列数据
> 				->setHandler($consumer, $args...) # 设置消费者函数和必须额外参数
> ```
> 
> ### FileReader	（文件读取，仿`tail`、`head`等操作）
> ### ForkHelper （任务并行分发）
> ### ImageCompress （图片压缩）
> ### SqlBuilder （SQL语句构造器）