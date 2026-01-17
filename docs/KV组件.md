# KV 组件文档

## 概述

KV 组件是 ZTBCMS 框架提供的一个键值对存储组件，用于快速保存和获取键值对数据。与系统自带的 `cache()` 缓存功能相比，KV 组件提供了独立的键值存储功能，支持持久化存储到数据库，并可与 Redis 缓存结合使用，提供更高的性能和灵活性。

## 功能特性

- **键值对存储**：基于数据库表实现键值对的持久化存储
- **缓存集成**：支持与 Redis 缓存无缝集成，自动管理缓存
- **原子操作**：提供原子性的添加操作（addKv），确保并发安全
- **超时管理**：支持基于更新时间的超时删除机制
- **简单易用**：提供静态方法调用，使用方便快捷

## 环境要求

- PHP 7.2+
- MySQL 5.6+
- Redis（可选，用于缓存功能）

## 配置说明

### 数据库配置

KV 组件使用 `kv` 数据表存储键值对，表结构如下：

```sql
CREATE TABLE `kv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT '',
  `value` text,
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

表结构说明：
- `key`：键名，唯一索引，用于快速查找
- `value`：值，使用 text 类型存储，支持大文本
- `create_time`：创建时间
- `update_time`：更新时间，用于超时判断

### Redis 缓存配置

如需使用缓存功能，需要在 `tp6/config/cache.php` 中配置 Redis 缓存：

```php
return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'redis'),
    
    // 缓存连接配置
    'stores'  => [
        'redis' => [
            'type'       => 'redis',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.database', 0),
            'timeout'    => 0,
            'expire'     => null,
            'persistent' => false,
            'prefix'     => '',
        ],
    ],
];
```

## API 接口说明

### 基础操作方法

#### addKv($key, $value)

添加键值对（若已存在则失败）。

**参数说明：**
- `$key` (string): 键名
- `$value` (mixed): 值

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 原子性操作，确保并发安全
- 如果 key 已存在，返回 false
- 底层使用数据库唯一索引保证原子性

**示例：**
```php
use app\common\service\kv\KV;

// 添加成功
$result = KV::addKv('user:1:name', '张三'); // true

// 重复添加失败
$result = KV::addKv('user:1:name', '李四'); // false
```

#### setKv($key, $value)

设置键值对（存在则覆盖）。

**参数说明：**
- `$key` (string): 键名
- `$value` (mixed): 值

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 如果 key 不存在，自动创建
- 如果 key 已存在，覆盖原有值

**示例：**
```php
use app\common\service\kv\KV;

// 设置值
KV::setKv('user:1:name', '张三');

// 覆盖值
KV::setKv('user:1:name', '李四'); // 成功覆盖
```

#### getKv($key, $defaultValue = null)

获取键对应的值。

**参数说明：**
- `$key` (string): 键名
- `$defaultValue` (mixed): 默认值（可选）

**返回值：**
- (mixed): 成功返回值，失败返回默认值

**示例：**
```php
use app\common\service\kv\KV;

// 获取存在的值
$name = KV::getKv('user:1:name'); // 返回 '李四'

// 获取不存在的值，返回默认值
$age = KV::getKv('user:1:age', 18); // 返回 18
```

#### delKv($key)

删除键值对。

**参数说明：**
- `$key` (string): 键名

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 同时删除数据库和缓存中的数据

**示例：**
```php
use app\common\service\kv\KV;

// 删除键值对
KV::delKv('user:1:name');
```

### 带缓存的操作方法

#### setKvWithCache($key, $value, $ttl = 60)

设置键值对并添加缓存。

**参数说明：**
- `$key` (string): 键名
- `$value` (mixed): 值
- `$ttl` (int): 缓存时间（秒），默认 60 秒

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 先写入数据库，成功后写入缓存
- 提高后续读取性能

**示例：**
```php
use app\common\service\kv\KV;

// 设置值并缓存 5 分钟
KV::setKvWithCache('user:1:profile', $profileData, 300);
```

#### getKvWithCache($key, $defaultValue = null, $ttl = 60)

获取键对应的值（带缓存）。

**参数说明：**
- `$key` (string): 键名
- `$defaultValue` (mixed): 默认值（可选）
- `$ttl` (int): 缓存时间（秒），默认 60 秒

**返回值：**
- (mixed): 成功返回值，失败返回默认值

**特性：**
- 先尝试从缓存读取，缓存不存在则从数据库读取
- 读取数据库成功后自动写入缓存
- 大大提高读取性能

**示例：**
```php
use app\common\service\kv\KV;

// 第一次读取：从数据库读取并写入缓存
$profile = KV::getKvWithCache('user:1:profile', null, 300);

// 第二次读取：直接从缓存读取
$profile = KV::getKvWithCache('user:1:profile', null, 300);
```

### 超时管理方法

#### delExpiredKv($key, $ttl)

删除超时的键值对。

**参数说明：**
- `$key` (string): 键名
- `$ttl` (int): 超时时间（秒）

**返回值：**
- (bool): 已删除返回 true，未删除返回 false

**特性：**
- 基于 `update_time` 字段判断超时
- 仅当当前时间 - 更新时间 > ttl 时才删除
- 用于实现锁的超时机制

**示例：**
```php
use app\common\service\kv\KV;

// 删除 1 小时前的数据
$deleted = KV::delExpiredKv('lock:task1', 3600);
```

## 使用场景

### 场景 1：配置存储

存储系统配置，支持快速读取：

```php
use app\common\service\kv\KV;

// 存储配置
KV::setKvWithCache('config:site_name', '我的网站', 3600);
KV::setKvWithCache('config:max_users', 1000, 3600);

// 读取配置
$siteName = KV::getKvWithCache('config:site_name', '默认网站名');
$maxUsers = KV::getKvWithCache('config:max_users', 500);
```

### 场景 2：临时数据存储

存储临时数据，如验证码、临时令牌等：

```php
use app\common\service\kv\KV;

// 存储验证码（10分钟有效）
KV::setKvWithCache('verify:code:' . $phone, $code, 600);

// 验证验证码
$inputCode = '123456';
$storedCode = KV::getKv('verify:code:' . $phone);
if ($inputCode === $storedCode) {
    // 验证通过
    KV::delKv('verify:code:' . $phone); // 使用后删除
}
```

### 场景 3：分布式锁（配合 Lock 组件）

KV 组件是 Lock 组件的基础，用于实现分布式锁：

```php
use app\common\service\kv\KV;

$key = 'lock:task1';
$value = uniqid(); // 唯一值标识锁的持有者

// 尝试获取锁
if (KV::addKv($key, $value)) {
    try {
        // 执行业务逻辑
        // ...
    } finally {
        // 释放锁
        KV::delKv($key);
    }
} else {
    // 获取锁失败
    echo '任务正在执行中，请稍后再试';
}
```

## 性能优化建议

1. **优先使用缓存方法**：对于读取频繁的数据，优先使用 `getKvWithCache` 和 `setKvWithCache` 方法
2. **合理设置 TTL**：根据业务需求设置合适的缓存时间，避免缓存雪崩
3. **Key 命名规范**：使用有意义的 Key 命名，建议格式：`业务:对象:ID:属性`
4. **批量操作**：如需批量操作，建议封装批量方法，减少数据库交互次数
5. **监控和清理**：定期清理不再使用的 Key，避免数据无限增长

## 注意事项

1. **Redis 依赖**：使用缓存功能需要配置 Redis，否则缓存方法会失效
2. **唯一索引**：数据库表 `key` 字段必须有唯一索引，确保 `addKv` 的原子性
3. **序列化**：存储复杂数据类型时，建议手动序列化（如 json_encode）
4. **超时机制**：`delExpiredKv` 基于 `update_time` 判断，不是基于缓存 TTL
5. **并发安全**：`addKv` 是原子操作，可用于实现简单的分布式锁

## 与其他存储方案对比

| 特性 | KV 组件 | Cache 缓存 | Session | Cookie |
|------|---------|------------|---------|--------|
| 存储位置 | 数据库 + Redis | Redis/File | File/Redis | 客户端 |
| 持久化 | 是 | 否（可配置） | 否 | 是 |
| 跨服务器 | 是 | 是 | 否 | 是 |
| 性能 | 高（有缓存） | 高 | 中 | 低 |
| 适用场景 | 配置、临时数据 | 频繁读取数据 | 用户会话 | 客户端数据 |

## 常见问题

**Q: KV 组件和 Cache 组件有什么区别？**
A: KV 组件数据持久化到数据库，即使 Redis 重启数据也不会丢失；Cache 组件数据通常只存储在内存中，重启后数据会丢失。

**Q: 如何选择是否使用缓存方法？**
A: 对于读取频率高、变化不频繁的数据，建议使用缓存方法；对于实时性要求高、变化频繁的数据，建议直接使用基础方法。

**Q: Key 的命名有什么建议？**
A: 建议使用 `业务:对象:ID:属性` 的格式，如 `user:1001:profile`，便于管理和维护。

**Q: 如何处理缓存穿透问题？**
A: 可以在 `getKvWithCache` 方法中设置合理的默认值，避免频繁查询数据库。
