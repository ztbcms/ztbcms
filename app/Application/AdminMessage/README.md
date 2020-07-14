# ztbcms-AdminMessage

### 后台消息管理
1、所有消息列表
2、未读消息列表
3、系统消息列表

### 创建后台消息
```php
/**
  * @param $title         消息标题
  * @param $content       消息内容
  * @param $target        消息来源
  * @param $target_type   消息源类型
  * @param $sender        发送者
  * @param $sender_type   发送者类型
  * @param $receiver      接收者（管理员id），当传入 0 时，会给每一个管理员添加记录
  * @param $receiver_type 接收者类型
  * @return array
*/
\AdminMessage\Service\AdminMessageService::createAdminMessage($title,$content,$target,$target_type,$sender,$sender_type,$receiver,$receiver_type);
```

