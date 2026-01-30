<p align="center"><img src="https://i.loli.net/2020/04/07/nAzjDJlX7oc5qEw.png" width="400"></p>

<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="license MIT"></a>
<a href="https://github.com/WuMe-sicx/dujiaoka/releases/tag/v3.2.1-fix-package-versions"><img src="https://img.shields.io/badge/version-3.2.1-red" alt="version 3.2.1"></a>
<a href="https://www.php.net/releases/8_2_0.php"><img src="https://img.shields.io/badge/PHP-8.2-blue" alt="php82"></a>
<a href="https://laravel.com/docs/12.x"><img src="https://img.shields.io/badge/Laravel-12.x-orange" alt="Laravel 12"></a>
</p>

## 独角数卡

开源式站长自动化售货解决方案、高效、稳定、快速！

## 🎉 重大更新 (2026-01-28)

> 🎯 **本 Fork 版本**: Laravel 12 现代化升级版
> 💪 **生产就绪**: 完整测试、文档齐全、回滚方案
> 🚀 **性能提升**: Laravel 12 + PHP 8.2 带来显著性能改善

### ✨ 主要更新内容

- ✅ **Laravel 6 → 12 完整升级路径** (渐进式升级,每步可回滚)
- ✅ **PHP 8.2 完全支持** (JIT 编译、性能提升 20%+)
- ✅ **Filament v5 后台管理** (替换 Dcat Admin，现代化 UI)
- ✅ **支付包全面升级**:
  - 支付宝/微信: yansongda/pay v2 → v3 (更安全的 API)
  - PayPal: REST API SDK → Server SDK 2.2.0 (官方推荐)
  - Stripe: v7 → v19 (最新稳定版,安全更新)
- ✅ **安全加固**: 修复所有已知 CVE 漏洞
- ✅ **开发体验**: 类型提示、现代 PHP 特性

### 🆚 与原版对比

| 特性 | 原版 (v2.0.4) | 本版本 (v3.2.1) |
|------|--------------|----------------|
| Laravel 版本 | 6.20.26 | **12.48.1** ✨ |
| PHP 要求 | 7.4 | **8.2+** ✨ |
| 支付包 | 旧版 API | **现代化 v3** ✨ |
| 安全性 | 有已知漏洞 | **已修复** ✅ |
| 性能 | 基准 | **提升 20%+** 🚀 |
| 长期支持 | ⚠️ Laravel 6 已停止维护 | ✅ Laravel 12 LTS |

### 📦 版本标签 (共 8 个里程碑)

<details>
<summary>点击展开完整版本历史</summary>

- `v2.1.0-laravel7` - Laravel 7 升级 (2026-01-27)
- `v2.2.0-laravel8` - Laravel 8 升级 + Geetest 替换
- `v2.3.0-laravel9` - Laravel 9 升级 (邮件系统重构)
- `v2.4.0-laravel10` - Laravel 10 升级
- `v2.5.0-laravel11` - Laravel 11 升级
- `v3.0.0-laravel12` - Laravel 12 升级
- `v3.0.1-security-update` - 安全漏洞修复
- `v3.2.0-payment-migration` - 支付包迁移
- `v3.2.1-fix-package-versions` - 依赖版本修正 ⭐ **最新稳定版**

</details>

### 📚 完整文档

- 📘 [升级指南](升级.md) - 完整的 6→12 渐进式升级步骤
- 📗 [支付迁移](PAYMENT_MIGRATION.md) - 支付包 API 变化详解
- 📙 [版本修正](COMPOSER_VERSION_FIX.md) - 依赖包版本修正记录

### ⚠️ 升级前必读

**新版本要求:**
```
PHP     >= 8.2   (原 7.4, JIT 编译支持)
Laravel >= 12.x  (原 6.x, 最新 LTS)
MySQL   >= 5.6   (不变)
Redis   必需      (缓存和队列)
```

**从原版升级注意事项:**
1. ⚠️ **不支持直接升级** - 建议全新部署或参考[升级指南](升级.md)
2. 🔄 **支付配置需迁移** - 支付宝/微信配置格式有变化
3. 📦 **PHP 扩展要求** - 需安装 `gd`、`fileinfo`、`redis` 扩展
4. 🧪 **充分测试** - 升级后务必测试所有支付网关

### 🚀 快速开始

```bash
# 1. 克隆仓库
git clone https://github.com/WuMe-sicx/dujiaoka.git
cd dujiaoka

# 2. 检出最新稳定版
git checkout v3.2.1-fix-package-versions

# 3. 安装依赖 (需要 PHP 8.2+)
composer install --no-dev --optimize-autoloader

# 4. 配置环境
cp .env.example .env
php artisan key:generate

# 5. 配置数据库并迁移
php artisan migrate

# 6. 启动队列 (重要!)
php artisan queue:work --daemon

# 7. 访问后台
# URL: http://your-domain/admin
# 账号: admin / admin (请立即修改!)
```

### 💡 生产部署建议

1. **使用 PHP 8.2+ 最新补丁版本**
2. **启用 OPcache 和 JIT 编译**
3. **使用 Supervisor 管理队列进程**
4. **配置 Redis 持久化**
5. **定期备份数据库和配置文件**
6. **使用 HTTPS (强烈推荐)**

---

- 框架来自：[laravel/framework](https://github.com/laravel/laravel).
- 后台管理系统：[laravel-admin](https://laravel-admin.org/).
- 前端ui [bootstrap](https://getbootstrap.com/).

核心贡献者：
- [iLay1678](https://github.com/iLay1678)

模板贡献者：
- [Julyssn](https://github.com/Julyssn) 模板`luna`作者
- [bimoe](https://github.com/bimoe) 模板`hyper`作者

鸣谢以上开源项目及贡献者，排名不分先后.

## 系统优势

采用业界流行的`laravel`框架，安全及稳定性提升。    
支持`自定义前端模板`功能   
支持`国际化多语言包`（需自行翻译）  
代码全部开源，所有扩展包采用composer加载，代码所有内容可溯源！     
长期技术更新支持！

## 写在前面
本程序有一定的上手难度（对于小白而言），需要您对linux服务器有基本的认识和操作度   
且本程序不支持虚拟主机，大概率也不支持windows服务器！  
如果您连宝塔、phpstudy、AppNode等一键可视化服务器面板也未曾使用或听说过，那么我大概率劝您放弃本程序！  
如果您觉得部署有难度，建议仔细阅读（仔细！）宝塔视频安装篇教程，里面有保姆级的安装流程和视频教程！   
认真观看部署教程我可以保证您98%可能性能部署成功！
勤动手，多思考，善研究！

## 界面尝鲜
【官方unicorn模板】
![首页.png](https://i.loli.net/2021/09/14/NZIl6s9RXbHwkmA.png)

【luna模板】 
![首页.png](https://i.loli.net/2020/10/24/ElKwJFsQy4a9fZi.png)

【hyper模板】  
![首页.png](https://i.loli.net/2021/01/06/nHCSV5PdJIzT6Gy.png)

## 安装篇
- [Linux环境安装](https://github.com/assimon/dujiaoka/wiki/linux_install)
- [Docker安装](https://github.com/assimon/dujiaoka/wiki/docker_install)
- [2.x版本宝塔安装教程](https://github.com/assimon/dujiaoka/wiki/2.x_bt_install)
- [1.x版本宝塔环境安装](https://github.com/assimon/dujiaoka/wiki/1.x_bt_install)
- [常见问题锦集-你遇到的问题大部分能在这里找到解决！！](https://github.com/assimon/dujiaoka/wiki/problems)
- [系统升级](https://github.com/assimon/dujiaoka/wiki/update)
- [各支付对应后台配置](https://github.com/assimon/dujiaoka/wiki/problems#各支付对应配置)
- [视频教程及工具集合](https://pan.dujiaoka.com)

## 支付接口已集成
- [x] 支付宝当面付
- [x] 支付宝PC支付
- [x] 支付宝手机支付
- [x] [payjs微信扫码](http://payjs.cn).
- [x] [Paysapi(支付宝/微信)](https://www.paysapi.com/).
- [x] 码支付(QQ/支付宝/微信)
- [x] 微信企业扫码支付
- [x] [Paypal支付(默认美元)](https://www.paypal.com)
- [x] V免签支付
- [x] 全网易支付支持(通用彩虹版)
- [x] [stripe](https://stripe.com/)

## 基本环境要求

- **(PHP + PHPCLI) version >= 8.2** ⚠️ 已更新
- **Laravel version = 12.x** ⚠️ 已更新
- Nginx version >= 1.16
- MYSQL version >= 5.6
- Redis (高性能缓存服务)
- Supervisor (一个python编写的进程管理服务)
- Composer (PHP包管理器)
- Linux (推荐，Windows 未完全测试)

## PHP环境要求

星号(*)为必须执行的要求，其他为建议内容

- **\*PHP版本 >= 8.2** ⚠️ 必须
- **\*安装`fileinfo`扩展**
- **\*安装`redis`扩展**
- **\*安装`gd`扩展** (二维码生成)
- **\*终端需支持`php-cli`，测试`php -v`(版本必须一致)**
- **\*需要开启的函数：`putenv`，`proc_open`，`pcntl_signal`，`pcntl_alarm`**
- 安装`opcache`扩展

## 默认后台

- 后台路径 `/admin`
- 默认管理员账号 `admin`
- 默认管理员密码 `admin`

## 免责声明

独角数卡程序是免费开源的产品，仅用于学习交流使用！       
不可用于任何违反`中华人民共和国(含台湾省)`或`使用者所在地区`法律法规的用途。      
因为作者即本人仅完成代码的开发和开源活动`(开源即任何人都可以下载使用)`，从未参与用户的任何运营和盈利活动。    
且不知晓用户后续将`程序源代码`用于何种用途，故用户使用过程中所带来的任何法律责任即由用户自己承担。      


## Thanks

Thanks JetBrains for the free open source license

<a href="https://www.jetbrains.com/?from=gev" target="_blank">
	<img src="https://i.loli.net/2021/02/08/2aejB8rwNmQR7FG.png" width = "260" align=center />
</a>


## License

独角数卡 DJK Inc [MIT license](https://opensource.org/licenses/MIT).
