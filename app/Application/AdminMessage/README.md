# ztbcms-AdminMessage

### 后台消息管理
1、所有消息列表   
2、未读消息列表  
3、系统消息列表  
4、支持消息类型区分

#### 创建消息
```php
/**
  * @param $title         消息标题
  * @param $content       消息内容
  * @param $receiver      接收者（例如管理员id）
  * @return array
*/
\AdminMessage\Service\AdminMessageService::createAdminMessage($title,$content,$receiver);
```

#### 创建群发消息
```php
/**
  * @param $title         消息标题
  * @param $content       消息内容
  * @return array
*/
\AdminMessage\Service\AdminMessageService::createGroupMessage($title,$content);
```

#### 创建系统通知
```php
/**
  * @param $title         消息标题
  * @param $content       消息内容
  * @return array
*/
\AdminMessage\Service\AdminMessageService::createSystemMessage($title,$content);
```

使用场景模拟：  
1、手动发送给某个管理员消息
消息来源     $target = 'system'  
消息来源类型  $target_type = 'system'  
系统类型     $type  = '' （默认）

2、创建系统消息   
消息来源     $target = 'system'  
消息来源类型  $target_type = 'system'  
系统类型     $type  = 'system'

3、张三发送消息通知李四，注意审查  
消息来源     $target = '张三的id'  
消息来源类型  $target_type = 'admin_id'

4、用户下单后通知管理员  
消息来源    $target = '订单号'  
消息来源类型 $target_type = '20200715224515'
