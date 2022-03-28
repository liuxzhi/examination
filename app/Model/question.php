<?php
namespace  App\Model;
use think\Model;

class question extends Model{


    /**
     * 模型名称
     * @var string
     */
    protected $name = "question";
    /**
     * 主键值
     * @var string
     */
    protected $key = "id";
    /**
     * 数据表名称
     * @var string
     */
    protected $table = "question";
}