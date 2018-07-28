<?php

use box\SqlBuilder\{InsertBuilder, SelectBuilder, UpdateBuilder, Factory} ;

$sub = (new SelectBuilder)->from('user_test')->limit(10)->alias('collection')->select(array('id','name')) ;
echo $sub->getSqlString() . "\n" ;

$builder = new InsertBuilder() ;
$builder->in('user')->fields('id,name')->insert(array('2','?'), array('yuhari','calors','politt'))->duplicate(array('name','id' => 'values(id)+1')) ;
echo $builder->getSqlString() . "\n" ;

$builder = new UpdateBuilder() ;
$builder->from('user')->from($sub)->update(array('id=3'))->update(array('id'=>'?'), array(3))->andWhere('user.id=?', array(4))->orWhere('collection.id<?', array(100)) ;

echo $builder->getSqlString() . "\n" ;

$factory = new Factory('select') ;
$factory->from('user')->andWhere('id=1') ;
$factory->switch('select')->from('user_test')->limit(10)->alias('collection')->select(array('id','name'))  ;

echo $factory->sqlSnap() ."\n";

var_dump($builder->getBindValues()) ;