# chinyoo-red

CHINYOO 独立站的 WordPress 主题，对应线上 `wp-content/themes/chinyoo-red`。

站点是 [chinyoo.com](https://www.chinyoo.com)（温州正源电子，电气测量仪器：万用表、测电笔、钳形表、其他）。纯红 `#DA2735` + 白，Montserrat 字体。站点多语言，Polylang 14 种语言（en / pl / cs / de / fr / nl / it / ja / ko / ru / ar / ro / es / zh），内容以英文为源，逐语言翻译。

## 目录结构

- `functions.php` — 主题入口，按顺序加载 `inc/` 里各模块
- `inc/` — 业务逻辑基本都在这一层
  - `i18n.php` — Polylang 字符串翻译辅助
  - `theme-setup.php` — 基础注册（菜单、侧栏、图片尺寸等）
  - `product-import.php` / `product-import-upload.php` — 商品批量导入（含分类树、语言翻译）
  - `product-localization.php` — 产品页逐语言内容
  - `product-editor.php` — 后台商品编辑表单
  - `geo-faq.php` + `geo-faq-strings-*.php` — 产品页 FAQ（结构化数据，每种语言一个文件）
  - `contact-panel.php` — 询盘表单（带 Turnstile 人机验证）
  - `inquiry-handler.php` — 询盘提交
  - `notify.php` — 钉钉 / 邮件通知（询盘和注册共用）
  - `account*.php` / `wishlist.php` — 客户登录、注册、收藏
  - `scenarios.php` / `scenario-content.php` — 应用场景页
  - `chinyoo-filter.php` — 商品列表筛选（激活条件 chips）
  - `localized-hubs.php` — 语言感知 URL 助手。Polylang 的 `home_url()` 对带路径的链接不加语言前缀，所以这类链接都得走 permalink
- `template-parts/` — 模板片段（分类侧栏、商品卡片、询盘表单等）
- `assets/` — css / js / 图片
- `data/product-identities.json` — 商品导入的型号识别清单
- `docs/OPS.md` — **部署与运维速查，动代码之前先读这个**

## 几个要知道的事

- **部署方式**：服务器上不是 git 检出，是直接文件拷贝。上传后逐个 `php -l`，然后**必须清 FastCGI 缓存**，否则前台还是旧页面。细节在 `docs/OPS.md`。
- **不要整目录覆盖**：出现过服务器版本比仓库新、仓库里没有的修正。先 `md5sum` 比对，再逐文件打补丁。
- **商品计数**：侧栏 / 页脚 / 移动端分类条的计数都走 `ew_cat_product_count()`（整棵子树、按当前语言过滤）；"All products" 走 `ew_total_product_count()`（按当前语言）。分类归档页主查询也被 Polylang 按语言过滤，这两个函数要和它保持一致，不然侧栏数字和进页看到的商品数对不上（之前踩过：侧栏显示 104，进页只有 52）。
- 页面模板：商品页 `single-product.php`，分类/标签/搜索归档 `archive-product.php`，首页 `front-page.php`。
