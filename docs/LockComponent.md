# Lock 组件文档

## 概述

Lock 组件是 ZTBCMS 框架提供的一个分布式锁实现，基于 KV 组件构建。它利用 KV 组件的原子性操作特性，实现了简单易用的锁机制，用于解决分布式系统中的并发控制问题。

## 功能特性

- **分布式锁支持**：基于数据库唯一索引实现，支持多服务器环境
- **超时机制**：支持锁自动超时释放，防止死锁
- **简单易用**：提供静态方法调用，API 简洁明了
- **基于 KV 组件**：复用 KV 组件的存储和缓存能力
- **原子性操作**：利用数据库唯一索引保证锁操作的原子性

## 环境要求

- PHP 7.2+
- MySQL 5.6+
- Redis（可选，用于提升性能）
- 已配置 KV 组件（参考 [KV 组件文档](./KV组件.md)）

## 配置说明

Lock 组件依赖 KV 组件，使用前请确保：

1. 已创建 `kv` 数据表（参考 KV 组件文档）
2. 已配置 Redis 缓存（可选，但推荐）
3. `key` 字段有唯一索引

## 实现原理

### 加锁原理

Lock 组件的加锁流程：

1. **尝试加锁**：调用 `KV::addKv()` 尝试添加键值对
   - 如果 key 不存在，添加成功，获得锁
   - 如果 key 已存在，添加失败，锁已被占用

2. **超时处理**：如果加锁失败，检查锁是否超时
   - 调用 `KV::delExpiredKv()` 检查锁是否超时
   - 如果超时，删除旧锁

3. **重试加锁**：超时删除后，再次尝试加锁

### 解锁原理

调用 `KV::delKv()` 删除键值对，释放锁。

### 数据结构

锁使用 KV 表存储，结构如下：

```
key: lock:{resource}          # 锁的键名，建议格式：lock:业务:资源ID
value: {unique_id}            # 锁的值，用于标识锁的持有者
update_time: {timestamp}      # 更新时间，用于超时判断
```

## API 接口说明

### acquire($key, $val, $ttl = 5)

申请锁。

**参数说明：**
- `$key` (string): 锁的键名
- `$val` (string): 锁的值，用于标识锁的持有者
- `$ttl` (int): 锁的超时时间（秒），默认 5 秒

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 原子性操作，确保只有一个客户端能获得锁
- 支持超时机制，防止死锁
- 如果锁已被占用且未超时，返回 false
- 如果锁已超时，自动删除旧锁并尝试获取

**示例：**
```php
use app\common\service\lock\Lock;

$key = 'lock:task1';
$val = uniqid(); // 唯一值标识当前持有者

if (Lock::acquire($key, $val, 10)) {
    try {
        // 获得锁，执行业务逻辑
        echo '获得锁，开始执行任务';
        // ...
    } finally {
        // 释放锁
        Lock::release($key);
    }
} else {
    // 未获得锁
    echo '任务正在执行中，请稍后再试';
}
```

### release($key)

释放锁。

**参数说明：**
- `$key` (string): 锁的键名

**返回值：**
- (bool): 成功返回 true，失败返回 false

**特性：**
- 删除锁对应的键值对
- 同时删除缓存中的数据

**示例：**
```php
use app\common\service\lock\Lock;

// 释放锁
Lock::release('lock:task1');
```

## 使用场景

### 场景 1：定时任务防重复执行

防止定时任务在多台服务器上重复执行：

```php
use app\common\service\lock\Lock;

$key = 'lock:cron:send_email';
$val = gethostname() . ':' . getmypid(); // 标识执行者

if (Lock::acquire($key, $val, 3600)) {
    try {
        // 发送邮件逻辑
        // ...
        echo '邮件发送完成';
    } finally {
        Lock::release($key);
    }
} else {
    echo '邮件任务已在执行中';
}
```

### 场景 2：库存扣减

防止超卖，确保库存扣减的原子性：

```php
use app\common\service\lock\Lock;

$productId = 1001;
$key = 'lock:stock:' . $productId;
$val = uniqid();

if (Lock::acquire($key, $val, 5)) {
    try {
        // 查询库存
        $stock = getProductStock($productId);
        
        if ($stock > 0) {
            // 扣减库存
            reduceStock($productId, 1);
            echo '扣减成功';
        } else {
            echo '库存不足';
        }
    } finally {
        Lock::release($key);
    }
} else {
    echo '系统繁忙，请稍后再试';
}
```

### 场景 3：缓存更新

防止缓存击穿，只允许一个请求更新缓存：

```php
use app\common\service\lock\Lock;
use app\common\service\kv\KV;

$userId = 1001;
$cacheKey = 'user:' . $userId . ':profile';
$lockKey = 'lock:' . $cacheKey;

// 先尝试从缓存读取
$profile = KV::getKvWithCache($cacheKey);

if (!$profile) {
    // 缓存不存在，尝试获取锁
    if (Lock::acquire($lockKey, uniqid(), 10)) {
        try {
            // 再次检查缓存（防止多个请求同时进入）
            $profile = KV::getKvWithCache($cacheKey);
            
            if (!$profile) {
                // 从数据库查询
                $profile = getUserFromDB($userId);
                // 写入缓存
                KV::setKvWithCache($cacheKey, $profile, 3600);
            }
        } finally {
            Lock::release($lockKey);
        }
    } else {
        // 未获得锁，等待后重试或返回默认值
        usleep(100000); // 等待 100ms
        $profile = KV::getKvWithCache($cacheKey, []);
    }
}

return $profile;
```

### 场景 4：订单处理

确保订单处理的串行化：

```php
use app\common\service\lock\Lock;

$orderId = '202401010001';
$key = 'lock:order:' . $orderId;
$val = uniqid();

if (Lock::acquire($key, $val, 30)) {
    try {
        // 查询订单状态
        $status = getOrderStatus($orderId);
        
        if ($status === 'pending') {
            // 处理订单
            processOrder($orderId);
            echo '订单处理完成';
        } else {
            echo '订单状态不正确';
        }
    } finally {
        Lock::release($key);
    }
} else {
    echo '订单正在处理中，请稍后再试';
}
```

## 最佳实践

### 1. Key 命名规范

建议使用统一的 Key 命名格式：

```
lock:{业务}:{资源ID}
```

示例：
- `lock:order:1001` - 订单锁
- `lock:user:1001:balance` - 用户余额锁
- `lock:cron:send_email` - 邮件任务锁
- `lock:stock:1001` - 库存锁

### 2. Value 的设计

Value 应该能唯一标识锁的持有者，建议使用：

```php
$val = sprintf('%s:%s:%s', 
    gethostname(),    // 服务器名
    getmypid(),       // 进程ID
    uniqid()          // 唯一ID
);
```

这样在排查问题时可以知道锁被谁持有。

### 3. TTL 设置建议

- **短任务**：5-10 秒（如库存扣减）
- **中等任务**：30-60 秒（如订单处理）
- **长任务**：300-600 秒（如定时任务）
- **根据业务**：TTL 应该略大于业务执行的最大时间

### 4. 错误处理

始终使用 try-finally 确保锁被释放：

```php
if (Lock::acquire($key, $val, $ttl)) {
    try {
        // 业务逻辑
    } catch (\Exception $e) {
        // 异常处理
        Log::error($e->getMessage());
        throw $e;
    } finally {
        // 确保释放锁
        Lock::release($key);
    }
}
```

### 5. 避免死锁

- 设置合理的 TTL，防止死锁
- 避免在锁内调用可能长时间阻塞的操作
- 不要在锁内再获取其他锁（避免死锁）

### 6. 锁粒度控制

- **细粒度锁**：锁单个资源（如 `lock:order:1001`），并发度高
- **粗粒度锁**：锁一类资源（如 `lock:order`），实现简单但并发度低

根据业务需求选择合适的粒度。

## 性能优化

### 1. 使用缓存

Lock 组件基于 KV 组件，可以充分利用 KV 组件的缓存功能：

```php
// KV 组件会自动缓存，无需额外配置
// 确保 config/cache.php 中配置了 Redis
```

### 2. 减少锁持有时间

```php
if (Lock::acquire($key, $val, $ttl)) {
    try {
        // 只锁定必要的操作
        $data = calculateData(); // 耗时操作放在锁外
        
        // 锁内只执行原子操作
        saveData($data);
    } finally {
        Lock::release($key);
    }
}
```

### 3. 锁分段

对于热点资源，可以使用锁分段提高并发：

```php
$userId = 1001;
$segment = $userId % 10; // 10 个分段
$key = 'lock:user:' . $segment;

if (Lock::acquire($key, $val, $ttl)) {
    // 操作用户数据
}
```

## 注意事项

1. **非重入锁**：当前实现是非重入锁，同一线程不能多次获取同一把锁
2. **非公平锁**：不保证等待线程获取锁的顺序
3. **数据库依赖**：锁的可靠性依赖数据库的唯一索引
4. **时钟同步**：服务器时钟应该同步，否则可能影响超时判断
5. **性能考虑**：高并发场景下，数据库可能成为瓶颈，建议配合 Redis 使用

## 与其他锁方案对比

| 特性 | Lock (基于 KV) | Redis 锁 | 文件锁 | MySQL 锁 |
|------|----------------|----------|--------|----------|
| 分布式支持 | 是 | 是 | 否 | 是 |
| 性能 | 中 | 高 | 高 | 中 |
| 可靠性 | 高 | 高 | 低 | 高 |
| 实现复杂度 | 简单 | 中等 | 简单 | 中等 |
| 超时机制 | 内置 | 需实现 | 需实现 | 需实现 |
| 依赖 | MySQL + Redis | Redis | 文件系统 | MySQL |

## 监控和排查

### 查看当前锁

```php
use app\common\model\kv\KvModel;

// 查看所有锁
$locks = KvModel::where('key', 'like', 'lock:%')->select();

foreach ($locks as $lock) {
    echo sprintf("锁: %s\n", $lock->key);
    echo sprintf("持有者: %s\n", $lock->value);
    echo sprintf("更新时间: %s\n", $lock->update_time);
    echo "---\n";
}
```

### 清理超时锁

```php
use app\common\service\kv\KV;

// 清理所有超时的锁（超过 1 小时）
$locks = KvModel::where('key', 'like', 'lock:%')->select();

foreach ($locks as $lock) {
    KV::delExpiredKv($lock->key, 3600);
}
```

## 常见问题

**Q: Lock 组件和 Redis 分布式锁有什么区别？**
A: Lock 组件基于数据库实现，可靠性高但性能略低；Redis 锁基于 Redis 实现，性能高但需要额外维护 Redis。Lock 组件的优势是与 ZTBCMS 框架集成更好，使用更简单。

**Q: 锁超时了但业务还没执行完怎么办？**
A: 应该：
1. 增加 TTL 时间
2. 优化业务逻辑，减少执行时间
3. 考虑使用锁续期机制（需要自行实现）

**Q: 如何避免死锁？**
A: Lock 组件通过 TTL 机制自动防止死锁，但建议：
1. 设置合理的 TTL
2. 不要在锁内再获取其他锁
3. 使用 try-finally 确保锁释放

**Q: 支持锁重入吗？**
A: 当前实现不支持锁重入。如果需要重入锁，需要自行实现，记录持有者和重入次数。

**Q: 高并发下性能如何？**
A: 由于基于数据库，高并发下性能会受到数据库限制。建议：
1. 必须配置 Redis 缓存
2. 减少锁粒度
3. 减少锁持有时间
4. 考虑使用锁分段

## 扩展建议

### 实现可重入锁

```php
class ReentrantLock {
    private static $holders = []; // 记录当前线程持有的锁
    
    public static function acquire($key, $val, $ttl = 5) {
        $threadId = spl_object_id((object)$_SERVER);
        
        if (isset(self::$holders[$key]) && self::$holders[$key] === $val) {
            // 已持有锁，重入
            return true;
        }
        
        if (Lock::acquire($key, $val, $ttl)) {
            self::$holders[$key] = $val;
            return true;
        }
        
        return false;
    }
    
    public static function release($key, $val) {
        $threadId = spl_object_id((object)$_SERVER);
        
        if (isset(self::$holders[$key]) && self::$holders[$key] === $val) {
            unset(self::$holders[$key]);
            return Lock::release($key);
        }
        
        return false;
    }
}
```

### 实现公平锁

可以使用队列机制实现公平锁，确保等待线程按顺序获取锁。

### 实现锁续期

在锁即将过期时自动续期，适用于执行时间不确定的长任务。
