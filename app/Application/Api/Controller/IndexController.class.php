<?php

// +----------------------------------------------------------------------
// | ZtbCMS
// +----------------------------------------------------------------------

namespace Api\Controller;

use Common\Controller\CMS;

class IndexController extends CMS {

	public function token() {
		$token = \Libs\Util\Encrypt::authcode($_POST['token'], 'DECODE', C('CLOUD_USERNAME'));
		if (!empty($token)) {
			S($this->Cloud->getTokenKey(), $token, 3600);
			$this->success('验证通过');
			exit;
		}
		$this->error('验证失败');
	}

}
