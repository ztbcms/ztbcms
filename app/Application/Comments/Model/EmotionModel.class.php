<?php

// +----------------------------------------------------------------------
// | 表情模型
// +----------------------------------------------------------------------

namespace Comments\Model;

use Common\Model\Model;

class EmotionModel extends Model {

	protected $tableName = 'comments_emotion';
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('emotion_name', 'require', '表情名称不能为空！', 1, 'regex', 3),
		array('emotion_icon', 'require', '表情图标不能为空！', 1, 'regex', 3),
		array('emotion_name', '', '表情名称已经存在！', 0, 'unique', 1),
		array('emotion_icon', '', '表情图标已经存在！', 0, 'unique', 1),
	);

	/**
	 * 更新缓存
	 * @return type 成功返回true;
	 */
	public function emotion_cache() {
		$data = $this->where(array('isused' => 1))->select();
		$cacheList = array();
		foreach ($data as $r) {
			$cacheList['[' . $r['emotion_name'] . ']'] = $r;
		}
		cache('Emotion', $cacheList);
		return $cacheList;
	}

	//表情缓存，用于表情调用的
	public function cacheReplaceExpression($emotionPath = '', $classStyle = '') {
		//加载表情缓存
		$emotion = cache('Emotion');
		//表情存放路径
		if (empty($emotionPath)) {
			$config = cache('Config');
			$emotionPath = $config['siteurl'] . 'statics/images/emotion/';
		}
		//需要替换的标签
		$replace = array();
		foreach ($emotion as $lab => $info) {
			if ($lab) {
				$replace[$lab] = '<img src="' . $emotionPath . $info['emotion_icon'] . '" alt="' . $lab . '" title="' . $lab . '" ' . $classStyle . ' />';
			}
		}
		//进行缓存
		S('cacheReplaceExpression', $replace, 3600);
		return $replace;
	}

}
