main:
	echo "Hello ZTBCMS"

# 使用PHP内置服务器运行	
serve:
	@echo "🚀 点击访问 ==> http://localhost:8081/"
	@echo ''
	@php -S 127.0.0.1:8081 -t ./

# 初始化ubuntu运行环境
setup-ubuntu-env:
	sudo apt-get update && sudo apt-get install apache2 php5 php5-curl php5-gd php5-mysql
	# 可选安装mysql
	# sudo apt-get mysql-client

# 初始化环境	 	
setup-env:
	-sudo rm ./app/Common/Conf/dataconfig.php
	-sudo rm ./app/Application/Install/install.lock
	sudo chmod -R 777 ./d ./runtime ./app/Application/Install ./app/Common/Conf ./app/Common/Conf ./app/Common/Conf/addition.php \
		./app/Template ./statics
	# d目录Apache有写权限
	-chown -R www-data d/
	@echo "Finish!"

# 清除runtime
clean-runtime:
	@rm -f runtime/*.php
	@rm -f runtime/*/*.php
	@rm -f runtime/*/*/*.php
	@rm -f runtime/*/*/*/*.php		
	@echo "清空完毕!"

# 清除安装目录
clean-install:
	-@rm install.php
	-@rm -rf app/Application/Install
	-@rm -rf statics/extres/install
	@echo '清除安装目录完毕!'	
	
	
.PHONY: main serve setup-env setup-ubuntu-env clean-runtime clean-install
