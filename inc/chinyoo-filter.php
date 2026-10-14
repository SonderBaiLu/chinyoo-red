<?php
/**
 * Chinyoo 路径式产品筛选（自写，复刻 UNI-T 段格式）
 *
 * URL 结构：/product-categories/{cat}/{attr}-{value}/.../
 * 例：/product-categories/digital-multimeters/display_count-6000/capacitance-yes/
 *
 * 筛选数据来源：产品（simple + variable 父）通过 term_relationships 挂 pa_* 术语。
 * 变体产品不直接参与分类页筛选，其规格聚合到父产品。
 *
 * 依赖：WooCommerce + product_cat taxonomy + 已注册的 pa_* 全局属性。
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Chinyoo_Product_Filter {

	/** 可筛选维度：slug => 前端标签 */
	private static $dims = null;

	/** 懒加载返回筛选维度，标签经 ew__ 走 Polylang 翻译。 */
	private static function dims() {
		if ( self::$dims === null ) {
			self::$dims = array(
				'display_count' => ew__('Display Count'),
				'backlight'     => ew__('Backlight'),
				'temperature'   => ew__('Temperature'),
				'capacitance'   => ew__('Capacitance'),
				'frequency'     => ew__('Frequency'),
				'data_hold'     => ew__('Data Hold'),
			);
		}
		return self::$dims;
	}

	/** 当前激活筛选 [attr => [values]]（懒加载） */
	private static $active = null;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_filters_to_query' ), 999 );
	}

	/**
	 * 注册路径式筛选的 rewrite rule（top 优先级，抢占带筛选段的分类 URL）。
	 * 顺序关键（top 规则按注册先后匹配）：
	 *   1) 分页规则先注册：/cat/page/N/ 必须保留 paged，否则会被通用筛选
	 *      规则吞成 wpc_filter_path='page/N'，导致分页失效。
	 *   2) 通用筛选规则。
	 *   3) 基础分类规则兜底：/product-categories/{cat}/（即使 WC 的
	 *      category_base 未配置成 product-categories 也能解析）。
	 * 注意：需要在 flush_rewrite_rules() 后生效（部署脚本里调）。
	 */
	public static function register_rewrite() {
		// 0) 静态页 /product-categories/ 的分页必须先匹配：/product-categories/page/N/
		//    若漏掉，下面规则 1 会把 "page" 当分类 slug → 404。
		//    需要该页面 slug 确实是 product-categories（用 pagename 解析）。
		add_rewrite_rule(
			'^product-categories/page/([0-9]+)/?$',
			'index.php?pagename=product-categories&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^product-categories/([^/]+)/page/([0-9]+)/?$',
			'index.php?product_cat=$matches[1]&paged=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^product-categories/([^/]+)/([^/]+(?:/[^/]+)*)/?$',
			'index.php?product_cat=$matches[1]&wpc_filter_path=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^product-categories/([^/]+)/?$',
			'index.php?product_cat=$matches[1]',
			'top'
		);
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'wpc_filter_path';
		return $vars;
	}

	/**
	 * 解析当前激活筛选。
	 * @return array [attr => [values...]]
	 */
	public static function active() {
		if ( self::$active !== null ) {
			return self::$active;
		}
		self::$active = array();
		$path = get_query_var( 'wpc_filter_path' );
		if ( $path ) {
			$segs = explode( '/', $path );
			$n    = count( $segs );
			for ( $i = 0; $i < $n; $i++ ) {
				// 跳过 /page/N 分页段（只走 /cat/{filters}/page/2/ 场景，
				// 纯分页 /cat/page/2/ 已被高优先级规则接管）。
				if ( 'page' === $segs[ $i ] && isset( $segs[ $i + 1 ] ) && is_numeric( $segs[ $i + 1 ] ) ) {
					$i++;
					continue;
				}
				$dash = strpos( $segs[ $i ], '-' );
				if ( $dash === false ) {
					continue;
				}
				$attr  = substr( $segs[ $i ], 0, $dash );
				$value = substr( $segs[ $i ], $dash + 1 );
				if ( isset( self::dims()[ $attr ] ) && '' !== $value ) {
					self::$active[ $attr ][] = sanitize_title( $value );
				}
			}
		}
		return self::$active;
	}

	/** 是否产品分类页 */
	public static function is_category_page() {
		$obj = get_queried_object();
		return is_tax( 'product_cat' ) && $obj && isset( $obj->term_id );
	}

	/** 在主查询上注入筛选 tax_query（仅产品分类归档） */
	public static function apply_filters_to_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! $query->is_tax( 'product_cat' ) ) {
			return;
		}
		$active = self::active();
		if ( ! empty( $active ) ) {
			$tax_query = $query->get( 'tax_query' );
			$tax_query = is_array( $tax_query ) ? $tax_query : array();
			foreach ( $active as $attr => $values ) {
				$tax_query[] = array(
					'taxonomy' => 'pa_' . $attr,
					'field'    => 'slug',
					'terms'    => array_values( $values ),
					'operator' => 'IN', // 系列父可能挂同一属性的多个 term，任一命中即可
				);
			}
			$query->set( 'tax_query', $tax_query );
		}
		// 从筛选路径提取分页（/cat/{filters}/page/2/ 场景），恢复 paged。
		$path = get_query_var( 'wpc_filter_path' );
		if ( $path && preg_match( '#(?:^|/)page/(\d+)/?$#', $path, $m ) ) {
			$query->set( 'paged', (int) $m[1] );
		}
	}

	/**
	 * 计数：当前条件（分类 + 其他已选筛选）+ 该属性值 的已发布产品数。
	 * 用于筛选器每个选项旁的数字。
	 */
	public static function count_for( $attr, $value ) {
		global $wpdb;
		if ( ! self::is_category_page() ) {
			return 0;
		}
		$cat_id    = (int) get_queried_object()->term_id;
		$value_term = get_term_by( 'slug', $value, 'pa_' . $attr );
		if ( ! $value_term ) {
			return 0;
		}
		$active = self::active();

		$sql = "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
			JOIN {$wpdb->term_relationships} tr_c ON tr_c.object_id = p.ID
			JOIN {$wpdb->term_taxonomy} tt_c ON tt_c.term_taxonomy_id = tr_c.term_taxonomy_id
			JOIN {$wpdb->term_relationships} tr_a ON tr_a.object_id = p.ID
			JOIN {$wpdb->term_taxonomy} tt_a ON tt_a.term_taxonomy_id = tr_a.term_taxonomy_id
			WHERE p.post_type = 'product' AND p.post_status = 'publish'
				AND tt_c.taxonomy = 'product_cat' AND tt_c.term_id = %d
				AND tt_a.taxonomy = 'pa_{$attr}' AND tt_a.term_id = %d";
		$args = array( $cat_id, (int) $value_term->term_id );

		foreach ( $active as $a => $vals ) {
			if ( $a === $attr ) {
				continue; // 当前属性自身不计入"其他条件"
			}
			$place = implode( ',', array_fill( 0, count( $vals ), '%s' ) );
			$sql .= " AND p.ID IN (
				SELECT tr_x.object_id FROM {$wpdb->term_relationships} tr_x
				JOIN {$wpdb->term_taxonomy} tt_x ON tt_x.term_taxonomy_id = tr_x.term_taxonomy_id
				WHERE tt_x.taxonomy = %s AND tt_x.term_id IN (
					SELECT t2.term_id FROM {$wpdb->terms} t2 WHERE t2.slug IN ($place)
				))";
			$args[] = 'pa_' . $a;
			foreach ( $vals as $vv ) {
				$args[] = $vv;
			}
		}

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
	}

	/**
	 * 该分类下产品实际挂载的某属性术语（用于渲染选项列表）。
	 * @return WP_Term[]
	 */
	private static function terms_in_category( $cat_id, $attr ) {
		global $wpdb;
		$cat_id = (int) $cat_id;
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT t.term_id, t.name, t.slug FROM {$wpdb->terms} t
			JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
			JOIN {$wpdb->posts} p ON p.ID = tr.object_id
			JOIN {$wpdb->term_relationships} tr_c ON tr_c.object_id = p.ID
			JOIN {$wpdb->term_taxonomy} tt_c ON tt_c.term_taxonomy_id = tr_c.term_taxonomy_id
			WHERE tt.taxonomy = %s AND p.post_type = 'product' AND p.post_status = 'publish'
				AND tt_c.taxonomy = 'product_cat' AND tt_c.term_id = %d
			ORDER BY t.name ASC",
			'pa_' . $attr,
			$cat_id
		) );
		$terms = array();
		foreach ( (array) $rows as $row ) {
			$t = get_term_by( 'id', $row->term_id, 'pa_' . $attr );
			if ( $t && ! is_wp_error( $t ) ) {
				$terms[] = $t;
			}
		}
		return $terms;
	}

	/**
	 * 生成切换某筛选值的 URL（选中则移除，未选中则追加）。
	 */
	public static function build_url( $toggle_attr, $toggle_value ) {
		$cat  = get_queried_object();
		// 用 get_term_link 拿当前语言分类 URL（home_url 带路径不会加语言前缀）
		$base = get_term_link( $cat );
		if ( is_wp_error( $base ) ) {
			$base = home_url( '/product-categories/' . $cat->slug . '/' );
		}
		$changed = self::active();
		$current = isset( $changed[ $toggle_attr ] ) ? $changed[ $toggle_attr ] : array();
		if ( in_array( $toggle_value, $current, true ) ) {
			$current = array_values( array_diff( $current, array( $toggle_value ) ) );
		} else {
			$current[] = $toggle_value;
		}
		if ( $current ) {
			$changed[ $toggle_attr ] = array_values( array_unique( $current ) );
		} else {
			unset( $changed[ $toggle_attr ] );
		}
		$segs = array();
		foreach ( $changed as $a => $vals ) {
			foreach ( $vals as $v ) {
				$segs[] = $a . '-' . $v;
			}
		}
		return $base . ( $segs ? implode( '/', $segs ) . '/' : '' );
	}

	/**
	 * 渲染侧边筛选器（分类页调用）。
	 */
	public static function render_sidebar() {
		if ( ! self::is_category_page() ) {
			return;
		}
		$cat_id = (int) get_queried_object()->term_id;
		$active = self::active();
		echo '<aside class="chinyoo-filters">';
		foreach ( self::dims() as $attr => $label ) {
			$terms = self::terms_in_category( $cat_id, $attr );
			if ( empty( $terms ) ) {
				continue;
			}
			echo '<div class="cf-group">';
			echo '<h3 class="cf-title">' . esc_html( $label ) . '</h3>';
			echo '<ul class="cf-list">';
			foreach ( $terms as $t ) {
				$count   = self::count_for( $attr, $t->slug );
				$checked = isset( $active[ $attr ] ) && in_array( $t->slug, $active[ $attr ], true );
				$url     = self::build_url( $attr, $t->slug );
				echo '<li>';
				echo '<a class="cf-row' . ( $checked ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '" rel="nofollow">';
				echo '<input type="checkbox" ' . checked( $checked, true, false ) . ' tabindex="-1" aria-hidden="true">';
				echo '<span class="cf-name">' . esc_html( $t->name ) . '</span>';
				echo '<span class="cf-count">(' . (int) $count . ')</span>';
				echo '</a>';
				echo '</li>';
			}
			echo '</ul></div>';
		}
		echo '</aside>';
	}

	/**
	 * 渲染当前激活筛选的 chips（可点击移除）。
	 */
	public static function render_active_chips() {
		if ( ! self::is_category_page() ) {
			return;
		}
		$active = self::active();
		if ( empty( $active ) ) {
			return;
		}
		echo '<div class="cf-chips">';
		foreach ( $active as $attr => $vals ) {
			foreach ( $vals as $v ) {
				$dims = self::dims();
				$label = isset( $dims[ $attr ] ) ? $dims[ $attr ] : $attr;
				echo '<a class="cf-chip" href="' . esc_url( self::build_url( $attr, $v ) ) . '" rel="nofollow" title="' . sprintf( ew_esc_attr__( 'Remove %s' ), esc_attr( $label ) ) . '">';
				echo esc_html( $label ) . ': ' . esc_html( $v );
				echo ' <span aria-hidden="true">×</span>';
				echo '</a>';
			}
		}
		echo '</div>';
	}
}

Chinyoo_Product_Filter::init();
