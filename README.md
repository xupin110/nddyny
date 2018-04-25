# nddyny
1. 使用[SwooleDistributed](http://sd.youwoxing.net/)做的。  
2. 在web页面可以随时开启和关闭进程任务，并实时显示任务产生的内容。  
3. 很方便就可以使用phpwebdriver。有一个完整的例子。（[视频介绍](https://www.bilibili.com/video/av22196681/)）  
4. 支持集群部署。
#### 两种执行任务的方式
1. 使用ssh2登录指定服务器，并在前端显示操作产生的内容。
2. 执行项目中的方法，自由控制输出的内容到前端。

#### 项目的视频介绍地址
[bilibili](https://www.bilibili.com/video/av22196681/) 1080P。

#### nddyny算是外挂在SwooleDistributed中的，如果你已经用他写了自己的项目，只需要修改的4处，就可以使用nddyny  
全局搜索nddyny项目的内容“ **TODO nddyny**”，按搜索到的内容修改自己的项目并把nddyny目录放到根目录就好了。

#### 可以干嘛呢？
视频里有例子介绍，请点击进入 [视频介绍](https://www.bilibili.com/video/av22196681/) 。

#### 其它
[nddyny-backend ● Web端项目](http://github.com/nddyny/nddyny-backend)   
[nddyny-extra ● webdriver用到的程序](https://github.com/nddyny/nddyny-extra)   
[视频介绍](https://www.bilibili.com/video/av22196681/)
<br>

## 准备工作
安装并能运行[SwooleDistributed](http://sd.youwoxing.net/)版本 3.1.10  
写文档的时候最新版本就是 3.1.10

## 安装
#### (可选) 安装php扩展 ssh2
**使用ssh2进程任务时需要安装**  
pecl install ssh2 安装不了  
去pecl官网下吧 http://pecl.php.net/package/ssh2  
有时候需要翻墙有时候不需要
#### (可选) 给服务器安装字体
**使用webdriver时需要安装**  
```
# 其它中文字体也可以
apt-get install xfonts-wqy
```
#### 正式开始
```
# 安装SwooleDistributed和nddyny的依赖
composer install
cd nddyny/
composer install

# 进入配置目录，准备修改配置
cd config/

# common.php
# 看注释就能看懂了，没有需要注意的。
# 可以不做改动。
cp common.php.bak common.php

# custom.php
# 注意填对 mysql 和 redis 配置。
cp custom.php.bak custom.php

# nginx.conf
# upstream server、server_name、location root 这三种填对。
# (这一条可以不改、这一条可以不改、这一条可以不改) 部署时把 listen 37092 处的 root 指向的路径从 dist 改成 www (原因看 http://github.com/nddyny/nddyny-backend 部署时的操作)
cp nginx.conf.bak nginx.conf

# mysql.sql
# 这是创建表用的
```
#### 运行
和SwooleDistributed一样，在项目根目录执行
```
php src/bin/start_swoole_server.php start
```
## 集群
#### 下面是SwooleDistributed的consul配置方法，consul配好集群功能就好了
先下载consul二进制可执行文件，放到项目的src/bin/exec目录
**以下例子中**   
第一个consul ip: 172.19.199.193  
第二个consul ip: 172.19.199.194
#### 修改配置文件 custom.php
```
$custom['consul.enable'] = true;
$custom['consul.start_join'] = [
	'172.19.199.193', '172.19.199.194'
];
```
分别在2个consul所在的服务器中执行
```
consul agent -bootstrap-expect 2 -server -data-dir /tmp/consul -node=n3 -bind=172.19.199.193 -datacenter=nddyny-datacenter
```
```
consul agent -server -data-dir /tmp/consul -node=n4 -bind=172.19.199.194 -datacenter=nddyny-datacenter -join 172.19.199.193
```
**参数解释：( 复制SwooleDistributed文档中的)**  
-bootstrap-expect:集群期望的节点数，只有节点数量达到这个值才会选举leader。  
-server： 运行在server模式  
-data-dir：指定数据目录，其他的节点对于这个目录必须有读的权限  
-node：指定节点的名称  
-bind：为该节点绑定一个地址  
-datacenter: 数据中心没名称，  
-join：加入到已有的集群中
