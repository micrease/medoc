
# Starrocks Docker安装

[Starrocks文档](https://docs.starrocks.io/zh-cn/3.1/introduction/what_is_starrocks)

运行模式分为"存算分离"和"存算一体"两种方式,必须要首次启动前做好配置,两种模式不能切换.默认是存算一体.

## 存算一体安装(默认运行模式)

[官方安装文档](https://docs.starrocks.io/zh-cn/3.1/quick_start/deploy_with_docker)
官方默认安装的是2.5版本,我们要安装最新版本

指定版本可以在tags中查看版本号:https://hub.docker.com/r/starrocks/allin1-ubuntu/tags
```shell
#安装指定版本
#docker pull starrocks/allin1-ubuntu:3.0-latest
#docker pull starrocks/allin1-ubuntu:2.5-latest
#docker pull starrocks/allin1-ubuntu:3.0.4
#docker pull starrocks/allin1-ubuntu:latest

#安装最新版本,当前最新是3.1-rc01
sudo docker run -p 9030:9030 -p 8030:8030 -p 8040:8040 \
    -itd starrocks.docker.scarf.sh/starrocks/allin1-ubuntu:latest
    
#登录
mysql -P9030 -h127.0.0.1 -uroot
```
## 存算分离安装
* 存算分离安装需要先准备好配置文件,将conf修改为存算分离运行模式,再启动。
* 可以先安装存算一体方式搭建起来，然后进入容器把fe/conf copy出来。
* 找到容器内配置方件所在位置:/data/deploy/starrocks/fe/conf
* 挂载到宿主机位置:/root/starrocks/fe/conf
* 然后修改/root/starrocks/fe/conf/fe.conf

```shell
#进入容器,查看配置
docker exec -it {容器ID} /bin/bash
#把配置文件,copy到宿主机
docker copy {容器ID}:/data/deploy/starrocks/fe/conf  ./fe_conf
```

fe/fe.conf
配置参考:https://docs.starrocks.io/zh-cn/3.1/deployment/deploy_shared_data
```shell
#增加配置项
run_mode = shared_data
#S3配置项,可以搭建minio做为S3使用
aws_s3_path = ..
```

```shell
#安装指定版本
#docker pull starrocks/allin1-ubuntu:3.0-latest
#docker pull starrocks/allin1-ubuntu:2.5-latest
#docker pull starrocks/allin1-ubuntu:3.0.4
#docker pull starrocks/allin1-ubuntu:latest

#安装最新版本,当前最新是3.1-rc01
# -v参数指定本地配置文件
sudo docker run -p 9030:9030 -p 8030:8030 -p 8040:8040 \
    -v /root/starrocks/fe/conf:/data/deploy/starrocks/fe/conf
    -itd starrocks.docker.scarf.sh/starrocks/allin1-ubuntu:latest
    
#登录
mysql -P9030 -h127.0.0.1 -uroot
```

## minio windows
```shell
PS> Invoke-WebRequest -i "https://dl.minio.org.cn/server/minio/release/windows-amd64/minio.exe" -OutFile "C:\minio.exe"
PS> C:\minio.exe server E:\minio_data --console-address ":9001"
```
