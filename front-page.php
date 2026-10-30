<?php
/**
 * Chinyoo Red — Front Page（对标 EXTECH 首页）
 * 1 Split Hero（左文案 + 右图）→ 2 Tough Tools 深色行业带
 * 3 Find Your Industry or Application（4 列图卡）→ 4 Featured Products
 * 5 Tech Tips → 6 Testimonials → 7 FAQ (GEO) → Inquiry
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

// 浏览商品链接（shop 页）
$ew_browse_url = '';
if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) ) {
	$ew_browse_url = get_permalink( wc_get_page_id( 'shop' ) );
}
if ( ! $ew_browse_url ) {
	$ew_browse_url = get_post_type_archive_link( 'product' );
}
if ( ! $ew_browse_url ) {
	$ew_browse_url = ew_page_url( 'product-categories' );
}

// 产品真实图：variable → 第一个 variation 的 _model_gallery[0]（与详情页一致，
// 真实产品照片如 XT266C-01.jpg）；否则退回 variation/parent 的缩略图。
$ew_p4_gallery_ids = function ( $pid ) {
	$raw = get_post_meta( (int) $pid, '_model_gallery', true );
	if ( is_array( $raw ) ) {
		$ids = $raw;
	} elseif ( is_string( $raw ) && '' !== trim( $raw ) ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$ids = $decoded;
		} else {
			$ids = array_map( 'trim', explode( ',', $raw ) );
		}
	} else {
		$ids = array();
	}
	return array_values( array_filter( array_map( 'absint', $ids ) ) );
};
$ew_real_img = function ( $pid, $size = 'medium_large' ) use ( $ew_p4_gallery_ids ) {
	$pid = absint( $pid );
	$p   = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
	if ( ! $p ) { return ''; }
	$ids = array();
	if ( $p->is_type( 'variable' ) ) {
		foreach ( (array) $p->get_children() as $vid ) {
			$g = $ew_p4_gallery_ids( (int) $vid );
			if ( ! empty( $g ) ) {
				$ids[] = (int) $g[0];
			} else {
				$ids[] = (int) get_post_thumbnail_id( (int) $vid );
			}
		}
	} else {
		$g = $ew_p4_gallery_ids( $pid );
		if ( ! empty( $g ) ) {
			$ids[] = (int) $g[0];
		} else {
			$ids[] = (int) get_post_thumbnail_id( $pid );
		}
	}
	$ids[] = (int) get_post_thumbnail_id( $pid );
	foreach ( array_filter( $ids ) as $aid ) {
		$u = wp_get_attachment_image_url( $aid, $size );
		if ( $u ) { return $u; }
	}
	return '';
};

// Featured 产品（product_hot=1）
$ew_featured = new WP_Query( array(
	'post_type' => 'product', 'post_status' => 'publish',
	'posts_per_page' => 5, 'no_found_rows' => true,
	'meta_query' => array( array( 'key' => 'product_hot', 'value' => '1' ) ),
) );
$ew_featured_products = $ew_featured->have_posts()
	? array_map( 'wc_get_product', wp_list_pluck( $ew_featured->posts, 'ID' ) )
	: array();

// Tech Tips：最新 4 篇文章
$ew_tips = new WP_Query( array(
	'post_type' => 'post', 'post_status' => 'publish',
	'posts_per_page' => 4, 'no_found_rows' => true,
) );

// 客户评价（中间高亮轮播，对标 EXTECH quote carousel）
$ew_quotes = array(
	array(
		'text'  => ew__( 'When accuracy and safety are non-negotiable, our customers need equipment they can rely on. CHINYOO is focused on delivering durable, high-quality instruments with features that support safe, efficient diagnostics.' ),
		'author'=> ew__( 'Procurement Director, Global Business Development' ),
	),
	array(
		'text'  => ew__( 'These are tough tools for tough budgets — designed with the quality people expect from a factory-direct manufacturer.' ),
		'author'=> ew__( 'Product Manager, Electrical Distribution' ),
	),
	array(
		'text'  => ew__( 'User-friendly design matters. CHINYOO instruments are built to reduce task complexity, so professionals can really focus on getting the job done right.' ),
		'author'=> ew__( 'VP, Test & Measurement' ),
	),
);
?>

<!-- ===== 1. Split Hero（左白底文案 + 右全幅图，对标 EXTECH） ===== -->
<div class="hero">
	<div class="hero-copy">
		<p class="hero-overline" data-css-animate-type="slidedown" data-css-animate="1">CHINYOO INSTRUMENTS <span>·</span> OEM/ODM FACTORY</p>
		<h1 data-css-animate-type="slidedown" data-css-animate="2"><?php ew_esc_html_e('Digital Multimeters, Voltage Testers and Clamp Meters, Factory-Direct'); ?></h1>
		<p class="hero-lede" data-css-animate-type="slidedown" data-css-animate="3"><?php ew_esc_html_e('What do you need to measure today? Tell us the application and we will match the model.'); ?></p>
		<p class="hero-note" data-css-animate-type="slidedown" data-css-animate="4"><?php ew_esc_html_e('Multimeters, test pens, clamp meters and battery testers — made in our own factory since 2008.'); ?></p>
		<div class="btns" data-css-animate-type="slidedown" data-css-animate="5">
			<a class="btn-accent" href="#industries"><?php ew_esc_html_e("What's your application? Explore the options."); ?></a>
			<a class="btn-ghost" href="<?php echo esc_url( $ew_browse_url ); ?>"><?php ew_esc_html_e('Browse Products'); ?></a>
		</div>
		<ul class="hero-stats" data-css-animate-type="fadeIn" data-css-animate="6" aria-label="<?php ew_esc_attr_e('Factory key figures'); ?>">
			<li><b>18+</b><span><?php ew_esc_html_e('Years Experience'); ?></span></li>
			<li><b>127</b><span><?php ew_esc_html_e('Product Models'); ?></span></li>
			<li><b>40+</b><span><?php ew_esc_html_e('Export Countries'); ?></span></li>
		</ul>
	</div>
	<div class="hero-media" aria-hidden="true">
		<img src="<?php echo esc_url( EW_THEME_URI . '/assets/images/hero-home-dt9205a-v2.webp' ); ?>" alt="" width="1448" height="1086" fetchpriority="high">
	</div>
</div>

<!-- ===== 2. Tough Tools for Any Industry（深色全幅带，对标 EXTECH） ===== -->
<section class="band">
	<div class="band__bg" aria-hidden="true">
		<img src="<?php echo esc_url( EW_THEME_URI . '/assets/images/slide-2.jpg' ); ?>" alt="" loading="lazy">
	</div>
	<div class="container">
		<div class="band__card" data-animation="1">
			<h2><?php ew_esc_html_e('Tough Tools for Any Industry'); ?></h2>
			<p><?php ew_esc_html_e('CHINYOO is the brand to turn to for top-quality'); ?> <strong><?php ew_esc_html_e('Test and Measurement'); ?></strong> <?php ew_esc_html_e('products for professionals and tradespeople. Our instruments help you prevent, identify and troubleshoot problems across a wide range of applications, including:'); ?></p>
			<ul class="band__list">
				<?php foreach ( ew_scenarios() as $ew_sc ) : ?>
					<li><?php ew_esc_html_e( $ew_sc['title'] ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a class="btn-accent" href="<?php echo esc_url( $ew_browse_url ); ?>"><?php ew_esc_html_e('See Our Latest Products'); ?></a>
		</div>
	</div>
</section>

<!-- ===== 3. Find Your Industry or Application（4 列图卡 + LEARN MORE，对标 EXTECH） ===== -->
<section id="industries" data-animation-wrap>
	<div class="container">
		<h2 class="ind-title" data-animation="1"><?php ew_esc_html_e('Find Your Industry or Application'); ?></h2>
		<div class="ind-grid mobile-card-carousel" aria-label="<?php ew_esc_attr_e( 'Industry applications' ); ?>">
			<?php $ew_ind_i = 2; ?>
			<?php foreach ( ew_scenarios() as $ew_slug => $ew_sc ) : ?>
				<?php $ew_ind_url = ew_scenario_url( $ew_slug ); ?>
				<div class="ind-card" data-animation="<?php echo esc_attr( $ew_ind_i++ ); ?>">
					<a class="ind-card__img" href="<?php echo esc_url( $ew_ind_url ); ?>">
						<?php if ( ! empty( $ew_sc['img'] ) ) : ?>
							<img src="<?php echo esc_url( wp_get_attachment_image_url( $ew_sc['img'], 'medium_large' ) ); ?>" alt="<?php echo ew_esc_attr__( $ew_sc['title'] ); ?>" loading="lazy">
						<?php else : ?>
							<span class="ph"><b><?php ew_esc_html_e( $ew_sc['title'] ); ?></b></span>
						<?php endif; ?>
					</a>
					<h3><a href="<?php echo esc_url( $ew_ind_url ); ?>"><?php ew_esc_html_e( $ew_sc['title'] ); ?></a></h3>
					<a class="ind-card__link" href="<?php echo esc_url( $ew_ind_url ); ?>"><?php ew_esc_html_e('Learn More'); ?> <span aria-hidden="true">›</span></a>
				</div>
			<?php endforeach; ?>
			<div class="ind-card" data-animation="<?php echo esc_attr( $ew_ind_i++ ); ?>">
				<a class="ind-card__img" href="<?php echo esc_url( $ew_browse_url ); ?>">
					<img src="<?php echo esc_url( EW_THEME_URI . '/assets/images/slide-3.jpg' ); ?>" alt="<?php ew_esc_attr_e('All products'); ?>" loading="lazy">
				</a>
				<h3><a href="<?php echo esc_url( $ew_browse_url ); ?>"><?php ew_esc_html_e('All Products'); ?></a></h3>
				<a class="ind-card__link" href="<?php echo esc_url( $ew_browse_url ); ?>"><?php ew_esc_html_e('Learn More'); ?> <span aria-hidden="true">›</span></a>
			</div>
			<div class="ind-card" data-animation="<?php echo esc_attr( $ew_ind_i++ ); ?>">
				<a class="ind-card__img" href="#inquiry">
					<img src="<?php echo esc_url( EW_THEME_URI . '/assets/images/3123.jpg' ); ?>" alt="<?php ew_esc_attr_e('OEM and ODM programs'); ?>" loading="lazy">
				</a>
				<h3><a href="#inquiry"><?php ew_esc_html_e('OEM / ODM Programs'); ?></a></h3>
				<a class="ind-card__link" href="#inquiry"><?php ew_esc_html_e('Learn More'); ?> <span aria-hidden="true">›</span></a>
			</div>
		</div>
	</div>
</section>

<!-- ===== 4. Featured Products ===== -->
<?php if ( ! empty( $ew_featured_products ) ) : ?>
	<section class="alt" data-animation-wrap>
		<div class="container">
			<div class="sec-head" data-animation="1">
				<h2 class="sec-title"><?php ew_esc_html_e('Featured'); ?> <em><?php ew_esc_html_e('Products'); ?></em></h2>
				<a class="sec-link" href="<?php echo esc_url( $ew_browse_url ); ?>"><?php ew_esc_html_e('View All'); ?> <span class="sec-link__arrow" aria-hidden="true">→</span></a>
			</div>
			<div class="featured-carousel" data-autoplay="4500">
				<div class="prod-grid" id="featured-carousel-track">
					<?php foreach ( $ew_featured_products as $ew_i => $ew_p ) : if ( ! $ew_p ) { continue; } ?>
						<?php get_template_part( 'template-parts/product-card', null, array( 'product' => $ew_p, 'anim' => $ew_i + 2 ) ); ?>
					<?php endforeach; ?>
				</div>
				<div class="featured-carousel__nav">
					<button type="button" class="featured-carousel__btn featured-carousel__prev" aria-label="<?php ew_esc_attr_e('Previous products'); ?>"><span aria-hidden="true">‹</span></button>
					<button type="button" class="featured-carousel__btn featured-carousel__next" aria-label="<?php ew_esc_attr_e('Next products'); ?>"><span aria-hidden="true">›</span></button>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ===== 5. Tech Tips（最新文章卡片，对标 EXTECH Tech Tips） ===== -->
<?php if ( $ew_tips->have_posts() ) : ?>
	<section data-animation-wrap>
		<div class="container">
			<div class="sec-head" data-animation="1">
				<h2 class="sec-title"><?php ew_esc_html_e('Tech'); ?> <em><?php ew_esc_html_e('Tips'); ?></em></h2>
				<a class="sec-link" href="<?php echo esc_url( ew_page_url( 'news' ) ); ?>"><?php ew_esc_html_e('View All Articles'); ?> <span class="sec-link__arrow" aria-hidden="true">→</span></a>
			</div>
			<div class="tips-grid mobile-card-carousel" aria-label="<?php ew_esc_attr_e( 'Tech tips' ); ?>">
				<?php $ew_tips_i = 2; ?>
				<?php while ( $ew_tips->have_posts() ) : $ew_tips->the_post(); ?>
					<?php
					$ew_tip_cats = get_the_category();
					$ew_tip_eyebrow = $ew_tip_cats ? $ew_tip_cats[0]->name : ew__('Tech Tips');
					?>
					<article class="tip-card" data-animation="<?php echo esc_attr( $ew_tips_i++ ); ?>">
						<a class="tip-card__img" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
							<?php else : ?><span class="ph" style="width:100%;height:100%;border-radius:0"><?php ew_esc_html_e('Tech Tips'); ?></span><?php endif; ?>
						</a>
						<span class="tip-card__eyebrow"><?php echo esc_html( $ew_tip_eyebrow ); ?></span>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<a class="tip-card__link" href="<?php the_permalink(); ?>"><?php ew_esc_html_e('Read More'); ?> <span aria-hidden="true">›</span></a>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ===== 6. Testimonials（三卡轮播，中间高亮，对标 EXTECH quote carousel） ===== -->
<section class="quotes-sec" data-animation-wrap>
	<div class="container">
		<div class="quotes" id="ew-quotes" data-animation="1">
			<?php foreach ( $ew_quotes as $ew_q ) : ?>
				<figure class="quote-card">
					<span class="quote-card__mark" aria-hidden="true">&ldquo;</span>
					<blockquote><?php echo esc_html( $ew_q['text'] ); ?></blockquote>
					<figcaption><?php echo esc_html( $ew_q['author'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<div class="quotes-nav">
			<button type="button" class="quotes-btn q-prev" aria-label="<?php ew_esc_attr_e('Previous quote'); ?>">&lsaquo;</button>
			<div class="quotes-dots" role="tablist" aria-label="<?php ew_esc_attr_e('Quotes'); ?>">
				<button type="button" class="is-active" aria-label="<?php ew_esc_attr_e('Quote 1'); ?>"></button>
				<button type="button" aria-label="<?php ew_esc_attr_e('Quote 2'); ?>"></button>
				<button type="button" aria-label="<?php ew_esc_attr_e('Quote 3'); ?>"></button>
			</div>
			<button type="button" class="quotes-btn q-next" aria-label="<?php ew_esc_attr_e('Next quote'); ?>">&rsaquo;</button>
		</div>
	</div>
</section>
<script>
(function () {
	var wrap = document.getElementById('ew-quotes');
	if (!wrap) { return; }
	var cards = Array.prototype.slice.call(wrap.querySelectorAll('.quote-card'));
	var dots = Array.prototype.slice.call(document.querySelectorAll('#ew-quotes ~ .quotes-nav .quotes-dots button'));
	var idx = 0;
	function render() {
		var order = [cards[(idx + 2) % 3], cards[idx], cards[(idx + 1) % 3]];
		order.forEach(function (c, i) {
			wrap.appendChild(c);
			c.classList.toggle('is-active', i === 1);
		});
		dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
	}
	var prev = document.querySelector('#ew-quotes ~ .quotes-nav .q-prev');
	var next = document.querySelector('#ew-quotes ~ .quotes-nav .q-next');
	if (prev) { prev.addEventListener('click', function () { idx = (idx + 2) % 3; render(); }); }
	if (next) { next.addEventListener('click', function () { idx = (idx + 1) % 3; render(); }); }
	dots.forEach(function (d, i) { d.addEventListener('click', function () { idx = i; render(); }); });
	render();
})();
</script>

<!-- ===== 7. FAQ (GEO) — 通用问答手风琴 + FAQPage JSON-LD（供 AI 搜索抽取） ===== -->
<?php
$ew_home_faq = function_exists( 'ew_geo_faq' ) ? ew_geo_faq( 'general' ) : array();
if ( ! empty( $ew_home_faq ) ) :
	$ew_home_faq_i = 0;
	?>
	<section class="alt" data-animation-wrap>
		<div class="container">
			<div class="sec-head" data-animation="1">
				<h2 class="sec-title"><?php ew_esc_html_e('FAQs'); ?></h2>
				<a class="sec-link" href="#inquiry"><?php ew_esc_html_e('Talk to Us'); ?> <span class="sec-link__arrow" aria-hidden="true">→</span></a>
			</div>
			<div class="product-faq home-faq">
				<div class="product-faq__list">
					<?php foreach ( $ew_home_faq as $ew_home_qa ) : ?>
						<?php $ew_home_faq_i++; ?>
						<details class="product-faq__item"<?php echo ( 1 === $ew_home_faq_i ) ? ' open' : ''; ?>>
							<summary><?php echo ew_faq_emph( $ew_home_qa['q'] ); ?></summary>
							<div class="product-faq__answer">
								<p><?php echo ew_faq_emph( $ew_home_qa['a'] ); ?></p>
							</div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ===== 8. Inquiry CTA（品牌深色模块收尾，#inquiry 锚点供头部 CTA 使用） ===== -->
<section class="alt-accent" id="inquiry" data-animation-wrap>
	<div class="container">
		<div class="sec-head" data-animation="1">
			<div><h2 class="sec-title"><?php ew_esc_html_e('Send an'); ?> <em><?php ew_esc_html_e('Inquiry'); ?></em></h2><p class="sec-sub"><?php ew_esc_html_e('Tell us which instruments you need and your target quantity. Reply within 24 hours.'); ?></p></div>
		</div>
		<div class="inquiry-grid">
			<div class="inquiry-proof" data-animation="3">
				<span class="inquiry-proof__eyebrow"><?php ew_esc_html_e('Direct factory support'); ?></span>
				<h3><?php ew_esc_html_e('Tell us what you need.'); ?><em><?php ew_esc_html_e('We will help you build the right solution.'); ?></em></h3>
				<p><?php ew_esc_html_e('Share your target models, quantity or OEM requirements and our team will connect you with practical product and sourcing support.'); ?></p>
				<ul class="inquiry-proof__list">
					<li><span aria-hidden="true">↗</span><?php ew_esc_html_e('Response within 24 hours'); ?></li>
					<li><span aria-hidden="true">◆</span><?php ew_esc_html_e('Free sample evaluation for bulk buyers'); ?></li>
					<li><span aria-hidden="true">✓</span><?php ew_esc_html_e('Direct technical support'); ?></li>
				</ul>
			</div>
			<div class="inquiry-form-wrap" data-animation="4">
				<?php get_template_part( 'template-parts/inquiry-form', null, array( 'product_name' => '', 'anim' => '' ) ); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
