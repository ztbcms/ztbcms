<?php

// +----------------------------------------------------------------------
// | 模块绑定域名
// +----------------------------------------------------------------------

namespace Domains\Behavior;

class Domains {

	public function app_init($param) {
		$Domains_list = cache('Domains_list');
		$domain = $_SERVER['HTTP_HOST'];
		if ($Domains_list[$domain]) {
			C('DEFAULT_MODULE', $Domains_list[$domain]);
		}
	}

}
