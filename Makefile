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
	@echo "tp6 清空完毕!"

# 使用PHP内置服务器运行TP8
serve-tp8:
	@echo "🚀 点击访问 ==> http://localhost:8082/"
	@echo ''
	@php -S 127.0.0.1:8082 -t ./tp8/public/

# 清理tp8
clean-tp8:
	@rm -f tp8/runtime/*.php
	@rm -f tp8/runtime/*/*.php
	@rm -f tp8/runtime/*/*/*.php
	@rm -f tp8/runtime/*/*/*/*.php
	@echo "tp8 清空完毕!"

# 安装TP8依赖
install-tp8:
	cd tp8 && composer install

# 清除TP8安装目录
clean-install-tp8:
	-@rm -rf tp8/app/install
	@echo '清除tp8安装目录完毕!'

.PHONY: main serve serve-tp8 clean clean-tp8 clean-install clean-install-tp8 install-tp8
