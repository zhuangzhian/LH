+-----------------------------------------------+
  **DBShop 电子商务网店系统**
+-----------------------------------------------+
DBShop电子商务网店系统，是一套使用PHP语言构建，基于ZendFramework 2 框架开发的在线购物网店系统。


+-----------------------------------------------+
  **DBShop 电子商务网店系统环境要求**
+-----------------------------------------------+
1、操作系统方面，DBShop网店系统具有跨平台特性，可运行在 Linux/Unix/MAC OS/Windows 等操作系统下。DBShop
网店系统推荐使用 Linux/Unix 操作系统，不仅免费，而且能够提供更好的稳定性和负载能力。

2、除操作系统外，需要 web 服务器（如 Apache、Nginx 、IIS 等，不推荐IIS，web服务器必须开启rewrite模块），PHP 5.3.23及以上版本，MySql 5.0及以上版本。

3、如果您是虚拟空间用户，需要咨询空间商您目前使用的空间是否已经安装有上述支持软件。MySql账户需要拥有 CREATE、
DROP、ALTER 等执行权限，虚拟空间不低于50M，数据库空间不低于10M，如果您不十分了解请咨询空间商。


+-----------------------------------------------+
  **DBShop 电子商务网店系统安装**
+-----------------------------------------------+
将程序目录下的所有文件，上传到虚拟空间您需要安装的位置，需要特别注意在使用ftp软件上传程序时，一定要使用二进制方式上传。
上传完成后，打开如下网址

http://您的域名/程序目录/

当您第一次使用时，系统会自动跳转到安装目录，您只需要按照提示一步一步顺利完成安装，即可使用。

系统管理后台：http://您的域名/程序目录/admin/


+-----------------------------------------------+
  **DBShop 电子商务网店系统重新安装**
+-----------------------------------------------+
如果您需要重新安装，删除data目录下的install.lock文件，然后按照上面的安装顺序，安装即可。


+-----------------------------------------------+
  **DBShop 电子商务网店系统更新升级**
+-----------------------------------------------+
DBShop系统并不推崇手动更新，程序的事情就让程序去做好了。因此我们在系统管理后台提供了在线升级功能，点击一个按钮，即可在线更新升级。


+-----------------------------------------------+
  **DBShop 电子商务网店系统迁移**
+-----------------------------------------------+
涉及两种迁移方式：

1、数据迁移：必须要保证迁移前，两个系统的版本是一样的，且可正常运行。这里有两种迁移方法
   1）通过数据库工具，将原始数据导入新系统中，然后将原始网站目录下data、public、/module/Extendapp(如果该目录存在) 目录覆盖到要迁移网站即可。[特别注意：不要覆盖data目录下的Database.ini.php文件，这是数据库配置文件]
   2）在原始网站后台通过数据库备份功能备份完毕，然后将/data/moduledata/Dbsql 、/data/moduledata/moduledataback、/data/moduledata/moduleini、/public 、/module/Extendapp(如果该目录存在) 目录，覆盖到要迁移网站，然后登入新后台的数据库备份功能，点击导入即可。

2、整站迁移：相对简单，系统后台数据库备份下，然后将整个网站迁移，迁移完毕后，重新安装，安装完毕，直接进入系统后台的数据库备份页面，将其导入，再将原网站data/moduledata 这个目录，覆盖到到新网站一遍（因为重新安装，对原有的一些设置进行了初始化，这些设置是以文本方式存在的）。。
如果在迁移过程中数据库密码有修改的话，可参考下面 数据库密码修改 的方式进行处理。

重要提示：
1）如果迁移只是在同一个服务器内进行目录切换，且数据库不变，无需进行重装，迁移目录即可。
2）如果是迁移到不同的服务器，假如不想重装，那么把数据库导入到服务器内，将程序迁移进服务器，然后修改程序内的数据库连接信息即可。
3）如果您的系统中安装了插件，在迁移的过程中务必把插件文件和插件配置文件一并迁移，否则插件或者系统不能正常运行。插件目录 /module/Extendapp 插件配置文件目录 /datamoduledata/moduleini 请将这两个目录及目录内的文件，进行迁移。

最后 也是最重要的一步了，删除 /data/cache/modulecache/ 目录下面的所有文件，然后通过浏览器浏览站点即可

如果是从一个域名换到另一个域名，上面的处理就可以了。假如是从一个根目录迁移到一个二级目录下面，那么广告需要去后台编辑保存一下，商品详情中的商品图片路径修改下。
商品详情中的商品图片路径修改 有一个简单修改方法，使用sql语句，要修改的数据表是 dbshop_goods_extend 修改前建议备份该表，防止修改失败给您造成不必要的损失，sql语句如下
UPDATE `dbshop_goods_extend` SET `goods_body`=REPLACE(`goods_body`, '原路径', '将要替换的路径');
下面举一个例子，我们将 /public/upload/ 路径替换为 /shop/public/update/ 语句如下
UPDATE `dbshop_goods_extend` SET `goods_body`=REPLACE(`goods_body`, '/public/upload/', '/shop/public/upload/');
如果想更精确些，可以把/public前面的部分也加上，如 src="/public/upload/ （当然您也可以加入更多匹配条件）那么替换语句是
UPDATE `dbshop_goods_extend` SET `goods_body`=REPLACE(`goods_body`, 'src="/public/upload/', 'src="/shop/public/upload/');

+-----------------------------------------------+
 **DBShop 电子商务网店系统数据库密码修改**
+-----------------------------------------------+
有一种情况，当运行了一段时间后，觉得自己的数据库连接密码设置的过于简单，担心安全问题。这时想把简单的密码修改为复杂一点的，方法如下：
首先 在数据库管理工具中将简单密码修改为复杂密码，这里无需多说，您使用的什么管理工具就用什么管理工具修改
然后 修改DBShop下面 /data/Database.ini.php 这个文件内容，里面的 password 对应的就是密码设置，设置好后保存
最后 也是最重要的一步了，删除 /data/cache/modulecache/ 目录下面的所有文件，然后通过浏览器浏览站点即可


+-----------------------------------------------+
 **DBShop 电子商务网店系统相关支持网址**
+-----------------------------------------------+
官方网站：http://www.dbshop.net/
官方论坛：http://bbs.dbshop.net/
帮助教程：http://help.dbshop.net/


+-----------------------------------------------+
 **DBShop 电子商务网店系统联系方式**
+-----------------------------------------------+
电子邮件：support@dbshop.net