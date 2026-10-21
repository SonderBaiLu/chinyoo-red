# 运维速查

## 部署

服务器上**没有 git 检出**，主题是直接的文件拷贝，路径为 `wp-content/themes/chinyoo-red`。

更新流程：

1. 本地改完 → 推送本仓库
2. 把改动文件上传到服务器对应路径，属主设为 `www:www`
3. 上传后逐个 `php -l` 校验
4. **必须清 FastCGI 缓存**，否则前台仍是旧页面

```bash
find /www/server/fastcgi_cache -mindepth 1 -delete
```

站点没装 WP 缓存插件，`wp-config.php` 里也没有缓存相关常量。

## 注意：服务器版本可能比仓库新

曾出现服务器上的 `functions.php`、`inc/inquiry-handler.php`、`assets/js/main.js`、`assets/css/main.css`
含有仓库里没有的修正（news 归档 hreflang、GA4 `generate_lead` 埋点、公司名 `Electronic`、正文表格移动端规则）。

**不要整目录覆盖**：先 `md5sum` 比对，再逐个打补丁，否则会把这些改动回退。

## Cloudflare Turnstile 人机验证

### 覆盖范围

| 位置 | 实现 | 落点 |
|---|---|---|
| 询盘表单 | 主题代码 | `inc/turnstile.php`、`template-parts/inquiry-form.php` |
| WP 登录 / 注册 / 评论 | 插件 `Simple CAPTCHA with Cloudflare Turnstile` | 插件后台设置 |

### 密钥

- **站点密钥**：写在 `inc/turnstile.php`，可被 `wp-config.php` 的 `CHINYOO_TURNSTILE_SITE_KEY` 覆盖。可公开。
- **保密密钥**：只放 `wp-content/mu-plugins/chinyoo-turnstile-keys.php`，**不进本仓库**，也不写 `wp-config.php`。

### 踩过的坑

插件激活时会写入 `cfturnstile_tested = 'no'`，而它的集成代码加载条件是「该值为空或 `yes`」。
不跑插件后台的「测试密钥」流程，登录/注册/评论的保护会被**静默跳过**——页面看起来正常，实际没有组件。

```php
update_option( 'cfturnstile_tested', 'yes' );
```

### 关闭方式

- 只关询盘表单：删掉 `wp-content/mu-plugins/chinyoo-turnstile-keys.php`（校验整体跳过，表单恢复原样）
- 关 WP 登录 / 注册 / 评论：停用插件，或把插件目录改名

### 失败放行策略

`cfturnstile_failover = on` + `cfturnstile_failsafe_type = allow`：Cloudflare 接口不可达时放行，避免挡死真人。
主题侧 `ew_turnstile_verify()` 同样在请求失败时放行并写 `error_log`。

## 后台登录入口

登录路径已被 `WPS Hide Login` 改成自定义地址，`/wp-login.php` 与未登录访问 `/wp-admin/` 都会返回 404
（这是插件的预期行为，不是故障）。

- 修改位置：后台 **设置 → WPS Hide Login**
- 忘记路径时恢复默认：

```bash
mv wp-content/plugins/wps-hide-login /root/wps-hide-login.bak
```

## 回滚

部署前的完整主题备份在服务器 `/root/ew-theme-backup.tgz`。
