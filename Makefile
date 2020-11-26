main:
	echo "Hello ZTBCMS"

# 使用PHP内置服务器运行	
serve:
	@echo "🚀 点击访问 ==> http://localhost:8081/"
	@echo ''
	@php -S 127.0.0.1:8081 -t ./tp6/public/

# 清除安装目录
clean-install:
	-@rm -rf tp6/app/install
	@echo '清除安装目录完毕!'

# 清理tp6
clean:
	@rm -f tp6/runtime/*.php
	@rm -f tp6/runtime/*/*.php
	@rm -f tp6/runtime/*/*/*.php
	@rm -f tp6/runtime/*/*/*/*.php
	@echo "清空完毕!"
	
	
.PHONY: main serve setup-env setup-ubuntu-env clean-runtime clean-install
