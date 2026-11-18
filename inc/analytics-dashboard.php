<?php
/**
 * 数据大盘 —— 后台菜单页（纯 PHP + Chart.js CDN，无额外插件）。
 * 只读 inc/analytics.php 的聚合数据；权限 manage_options（站点管理员）。
 * GA4 对接位：页面顶部留了「渠道明细（GA4）」卡片位，明天接 Google Cloud 服务账号后
 * 把 ga4 拉数函数（ew_dashboard_ga4()，尚未创建）填进来即可，不影响今天已上线的部分。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', function () {
    add_menu_page(
        '数据大盘',
        '数据大盘',
        'manage_options',
        'ew-dashboard',
        'ew_dashboard_page',
        'dashicons-chart-bar',
        58
    );
} );

function ew_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( '无权限' );
    }
    $d = ew_dashboard_data();
    $kpi = $d['kpi'];
    $conv_rate = $kpi['pv'] > 0 ? round( 100 * $kpi['converts'] / $kpi['pv'], 2 ) : 0;
    $inq_rate  = $kpi['pv'] > 0 ? round( 100 * $d['inquiries_total_30d'] / $kpi['pv'], 2 ) : 0;
    $dwell_s   = round( $kpi['avg_dwell'] / 1000, 1 );

    $ew_ds_map = array( 'desktop' => '桌面', 'mobile' => '手机', 'tablet' => '平板' );
    $ew_ch_map = array(
        'direct'         => '直接访问',
        'google-organic' => '谷歌搜索',
        'search-other'   => '其他搜索',
        'social'         => '社交媒体',
    );

    $c = function ( $arr ) use ( $ew_ch_map, $ew_ds_map ) {
        $out = array();
        foreach ( $arr as $r ) {
            $k = $r['k'];
            $label = $k;
            if ( isset( $ew_ch_map[ $k ] ) ) { $label = $ew_ch_map[ $k ]; }
            elseif ( isset( $ew_ds_map[ $k ] ) ) { $label = $ew_ds_map[ $k ]; }
            elseif ( 0 === strpos( $k, 'ref:' ) ) { $label = '外链 ' . substr( $k, 4 ); }
            $out[] = array( 'k' => $label, 'v' => (int) $r['v'] );
        }
        return $out;
    };

    $ch_dim = $c( $d['dims']['ch'] );
    $ln_dim = $c( $d['dims']['ln'] );
    $ds_dim = $c( $d['dims']['ds'] );
    $kw_dim = array();
    foreach ( $d['dims']['kw'] as $r ) {
        if ( '' !== $r['k'] ) {
            $kw_dim[] = array( 'k' => $r['k'], 'v' => (int) $r['v'] );
        }
    }
    $clicks = $c( $d['clicks'] );
    $pages  = $c( $d['pages'] );
    $inq_prod = $c( $d['inq_by_product'] );
    $inq_ctry = $c( $d['inq_by_phone_ctry'] );
    $inq_dom  = $c( $d['inq_by_email_domain'] );
    $inq_hour = $c( $d['inq_by_hour'] );

    $bars = function ( $rows, $color = '#DA2735' ) {
        if ( ! $rows ) {
            return '<p class="ew-dash-empty">近 30 天暂无数据</p>';
        }
        $max = max( array_map( function ( $r ) { return $r['v']; }, $rows ) );
        $html = '<div class="ew-dash-bars">';
        foreach ( $rows as $r ) {
            $pct = $max > 0 ? ( 100 * $r['v'] / $max ) : 0;
            $html .= '<div class="ew-dash-bar-row"><span class="ew-dash-bar-k">' . esc_html( $r['k'] ) . '</span>'
                  . '<span class="ew-dash-bar-track"><span class="ew-dash-bar-fill" style="width:' . esc_html( round( $pct, 1 ) ) . '%;background:' . esc_attr( $color ) . '"></span></span>'
                  . '<span class="ew-dash-bar-v">' . (int) $r['v'] . '</span></div>';
        }
        return $html . '</div>';
    };
    ?>
    <div class="wrap ew-dash">
        <h1 class="ew-dash-h1">数据大盘 <span class="ew-dash-sub">近 30 天 · 更新于 <?php echo esc_html( date_i18n( 'Y-m-d H:i' ) ); ?></span></h1>

        <!-- ① 核心 KPI -->
        <div class="ew-dash-kpis">
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( number_format_i18n( $kpi['pv'] ) ); ?></div><div class="ew-kpi-l">浏览量 PV</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( number_format_i18n( $kpi['uv'] ) ); ?></div><div class="ew-kpi-l">独立访客 UV</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( number_format_i18n( $kpi['clicks'] ) ); ?></div><div class="ew-kpi-l">关键点击</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( number_format_i18n( $d['inquiries_total_30d'] ) ); ?></div><div class="ew-kpi-l">询盘</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( $conv_rate ); ?>%</div><div class="ew-kpi-l">点击→询盘转化</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( $inq_rate ); ?>%</div><div class="ew-kpi-l">PV→询盘率</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( $dwell_s ); ?>s</div><div class="ew-kpi-l">平均停留</div></div>
            <div class="ew-kpi"><div class="ew-kpi-v"><?php echo esc_html( number_format_i18n( $kpi['today_pv'] ) ); ?></div><div class="ew-kpi-l">今日 PV</div></div>
        </div>

        <!-- ② 30 天趋势 -->
        <div class="ew-dash-card">
            <h2 class="ew-dash-h2">30 天趋势：PV vs 询盘</h2>
            <canvas id="ewTrend" height="90"></canvas>
        </div>

        <div class="ew-dash-grid2">
            <!-- ③ 流量结构 -->
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">流量渠道</h2>
                <?php echo $bars( $ch_dim ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">语言分布（14 语言）</h2>
                <?php echo $bars( $ln_dim, '#2E7D32' ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">设备</h2>
                <?php echo $bars( $ds_dim, '#1a73e8' ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">搜索词 Top10（带 q 参数的外部搜索）</h2>
                <?php echo $bars( $kw_dim, '#9c27b0' ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">询盘来源：电话区号</h2>
                <?php echo $bars( $inq_ctry ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">询盘邮箱域 Top12</h2>
                <?php echo $bars( $inq_dom, '#e65100' ); ?>
            </div>
        </div>

        <div class="ew-dash-grid2">
            <!-- ④ 点击分析 -->
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">点击热榜 Top10（按钮 / 导航 / WhatsApp / 收藏）</h2>
                <?php echo $bars( $clicks ); ?>
            </div>
            <!-- ⑤ 页面热榜 -->
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">页面浏览量 Top10（PV / UV）</h2>
                <?php echo $bars( $pages, '#2E7D32' ); ?>
            </div>
        </div>

        <div class="ew-dash-grid2">
            <!-- ⑥ 询盘：按产品 -->
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">询盘按产品 Top12</h2>
                <?php echo $bars( $inq_prod ); ?>
            </div>
            <div class="ew-dash-card">
                <h2 class="ew-dash-h2">询盘时段分布（按小时）</h2>
                <?php echo $bars( $inq_hour, '#1a73e8' ); ?>
            </div>
        </div>

        <!-- 最近 20 条询盘 -->
        <div class="ew-dash-card">
            <h2 class="ew-dash-h2">最近 20 条询盘</h2>
            <?php if ( ! $d['inq_recent'] ) : ?>
                <p class="ew-dash-empty">暂无询盘</p>
            <?php else : ?>
                <table class="ew-dash-table">
                    <thead><tr>
                        <th>时间</th><th>姓名</th><th>邮箱</th><th>电话</th><th>产品</th><th>留言</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ( $d['inq_recent'] as $r ) : ?>
                        <tr>
                            <td><?php echo esc_html( date_i18n( 'Y-m-d H:i', strtotime( $r['post_date'] ) ) ); ?></td>
                            <td><?php echo esc_html( $r['name'] ); ?></td>
                            <td><?php echo esc_html( $r['email'] ); ?></td>
                            <td><?php echo esc_html( ( $r['ptry'] ? '+' . $r['ptry'] . ' ' : '' ) . $r['phone'] ); ?></td>
                            <td><?php echo esc_html( $r['product'] ); ?></td>
                            <td class="ew-dash-msg"><?php echo esc_html( mb_substr( $r['msg'], 0, 80 ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- GA4 预留位（明天接 Google Cloud 服务账号后填充） -->
        <div class="ew-dash-card ew-dash-ga4">
            <h2 class="ew-dash-h2">渠道明细（GA4）</h2>
            <p class="ew-dash-empty">待接入：Google Cloud 服务账号 + GA4 API（预计明天上线）。</p>
        </div>

        <style>
        .ew-dash{max-width:1200px}
        .ew-dash-h1{display:flex;align-items:baseline;gap:12px}
        .ew-dash-sub{font-size:12px;color:#64748b;font-weight:400}
        .ew-dash-kpis{display:grid;grid-template-columns:repeat(8,1fr);gap:12px;margin:20px 0}
        .ew-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px 12px;text-align:center}
        .ew-kpi-v{font-size:26px;font-weight:800;color:#0f172a;line-height:1.2}
        .ew-kpi-l{font-size:12px;color:#64748b;margin-top:4px}
        .ew-dash-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px;margin-bottom:16px}
        .ew-dash-h2{margin:0 0 14px;font-size:14px;font-weight:700;color:#0f172a}
        .ew-dash-grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .ew-dash-grid2 .ew-dash-card{margin-bottom:0}
        .ew-dash-bars{display:flex;flex-direction:column;gap:6px}
        .ew-dash-bar-row{display:grid;grid-template-columns:150px 1fr 50px;align-items:center;gap:8px;font-size:12px}
        .ew-dash-bar-k{color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .ew-dash-bar-track{height:10px;background:#f1f5f9;border-radius:5px;overflow:hidden}
        .ew-dash-bar-fill{display:block;height:100%;border-radius:5px}
        .ew-dash-bar-v{color:#0f172a;font-weight:700;text-align:right}
        .ew-dash-table{width:100%;border-collapse:collapse;font-size:12px}
        .ew-dash-table th{background:#f8fafc;text-align:left;padding:8px 10px;border-bottom:1px solid #e2e8f0;color:#475569}
        .ew-dash-table td{padding:8px 10px;border-bottom:1px solid #f1f5f9;color:#0f172a}
        .ew-dash-msg{max-width:340px;color:#475569}
        .ew-dash-empty{color:#94a3b8;font-size:12px}
        @media(max-width:900px){.ew-dash-kpis{grid-template-columns:repeat(4,1fr)}.ew-dash-grid2{grid-template-columns:1fr}.ew-dash-bar-row{grid-template-columns:100px 1fr 44px}}
        </style>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
        ( function () {
            var el = document.getElementById( 'ewTrend' );
            if ( ! el || 'undefined' === typeof Chart ) { return; }
            new Chart( el, {
                type: 'line',
                data: {
                    labels: <?php echo wp_json_encode( $d['trend']['labels'] ); ?>,
                    datasets: [
                        { label: 'PV', data: <?php echo wp_json_encode( $d['trend']['pv'] ); ?>, borderColor: '#DA2735', backgroundColor: 'rgba(218,39,53,.08)', fill: true, tension: .3, pointRadius: 0 },
                        { label: '询盘', data: <?php echo wp_json_encode( $d['trend']['inq'] ); ?>, borderColor: '#2E7D32', backgroundColor: 'transparent', tension: .3, pointRadius: 0 }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            } );
        }() );
        </script>
    <?php
    }
