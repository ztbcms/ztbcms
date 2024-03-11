<?php
/**
 * Author: Jayin Taung <tonjayin@gmail.com>
 */

namespace app\common\model\kv;

use think\Model;

class KvModel extends Model
{
    protected $name = 'kv';

    protected $autoWriteTimestamp = true;
}