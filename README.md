# version_update
resources version update. use json to display output and write by php.

这是一个资源版本更新的php项目。

通过对资源的md5识别，分别制作版本更新信息。

版本信息配置在 version_config.php 中

版本信息制作命令: php ./version_gen.php -v version_name 执行完后会根据当前资源文件和前一次资源文件对比，制作当前版本资源更新情况。

流程: 增删改资源文件后，执行版本信息生成脚本后，会在version文件夹中生成一个json文件来记录版本资源更新情况，当客户端访问 version_index.php 时会根据版本参数返回最高版本与给出版本之间的资源更新情况。

版本资源更新情况获取请使用格式 http://xxxxxxx.xxx/version_index.php?version=v1

会根据参数给出的版本名，来返回当前需要，增加(new)，更新(update)，删除(del)哪些资源文件，以及资源文件的地址。

返回信息格式为 : {"code":0,"data":{"version_prename":"","version_name":"v3","version_file":[{"file":"test1","operate":"new"},{"file":"test_dir\/test2","operate":"new"}]},"desc":"ok"}