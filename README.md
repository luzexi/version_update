# version_update
resources version update. use json to display output and write by php.

这是一个资源版本更新的php项目。

通过对资源的md5识别，分别制作版本更新信息。

版本信息配置在 version_config.php 中

版本信息制作命令: php ./version_gen.php -v version_name

版本获取请使用格式 http://192.168.1.251:83/version_index.php?version=v1

会根据参数给出的版本名，来返回当前需要，增加(new)，更新(update)，删除(del)哪些资源文件，以及资源文件的地址。

返回信息格式为 : {"code":0,"data":{"version_prename":"","version_name":"v3","version_file":[{"file":"test1","operate":"new"},{"file":"test_dir\/test2","operate":"new"}]},"desc":"ok"}