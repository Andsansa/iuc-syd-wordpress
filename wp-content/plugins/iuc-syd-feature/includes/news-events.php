<?php
/**
 * News and events published by IUC Syd administrators.
 *
 * - Admin: a content type ("Nyheter & event") where administrators create,
 *   edit and delete news and events.
 * - Members: the shortcode [iuc_news] shows them to logged-in users.
 *
 * Usage: [iuc_news]  [iuc_news type="event" limit="10"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IUC_NEWS_TYPE_META     = 'iuc_news_type';      // news | event
const IUC_NEWS_DATE_META     = 'iuc_event_date';     // Y-m-d\TH:i (site time)
const IUC_NEWS_LOCATION_META = 'iuc_event_location';

/**
 * The kinds of posts an administrator can publish.
 */
function iuc_news_types() {
	return array(
		'news'  => 'Nyhet',
		'event' => 'Event',
	);
}

/* ---------------------------------------------------------------------
 * Content type (admin side)
 * ------------------------------------------------------------------ */

function iuc_register_news() {
	$caps = array(
		'edit_post',
		'read_post',
		'delete_post',
		'edit_posts',
		'edit_others_posts',
		'delete_posts',
		'publish_posts',
		'read_private_posts',
		'delete_private_posts',
		'delete_published_posts',
		'edit_private_posts',
		'edit_published_posts',
	);

	register_post_type(
		'iuc_news',
		array(
			'labels'              => array(
				'name'          => 'Nyheter & event',
				'singular_name' => 'Nyhet eller event',
				'menu_name'     => 'Nyheter & event',
				'all_items'     => 'Alla nyheter & event',
				'add_new'       => 'Ny nyhet eller event',
				'add_new_item'  => 'Lägg till nyhet eller event',
				'edit_item'     => 'Redigera',
				'not_found'     => 'Inga nyheter eller event ännu.',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'menu_position'       => 3,
			'menu_icon'           => 'dashicons-megaphone',
			'supports'            => array( 'title', 'editor' ),
			'map_meta_cap'        => false,
			'capabilities'        => array_fill_keys( $caps, 'manage_options' ),
		)
	);
}
add_action( 'init', 'iuc_register_news' );

/* ---------------------------------------------------------------------
 * Meta box: type, date and place
 * ------------------------------------------------------------------ */

function iuc_news_add_meta_box() {
	add_meta_box(
		'iuc-news-details',
		'Typ och eventinformation',
		'iuc_news_render_meta_box',
		'iuc_news',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'iuc_news_add_meta_box' );

function iuc_news_render_meta_box( $post ) {
	$types = iuc_news_types();
	$type  = get_post_meta( $post->ID, IUC_NEWS_TYPE_META, true );
	$date  = get_post_meta( $post->ID, IUC_NEWS_DATE_META, true );
	$place = get_post_meta( $post->ID, IUC_NEWS_LOCATION_META, true );

	if ( ! isset( $types[ $type ] ) ) {
		$type = 'news';
	}

	wp_nonce_field( 'iuc_news_save', 'iuc_news_nonce' );
	?>
	<p>
		<label for="iuc-news-type"><strong>Typ</strong></label><br>
		<select id="iuc-news-type" name="iuc_news_type">
			<?php foreach ( $types as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<div id="iuc-news-event-fields">
		<p>
			<label for="iuc-news-date"><strong>Datum och tid</strong></label><br>
			<input id="iuc-news-date" name="iuc_event_date" type="datetime-local" value="<?php echo esc_attr( $date ); ?>">
		</p>
		<p>
			<label for="iuc-news-place"><strong>Plats</strong></label><br>
			<input id="iuc-news-place" name="iuc_event_location" type="text" class="regular-text" maxlength="200" value="<?php echo esc_attr( $place ); ?>">
		</p>
		<p class="description">Event utan datum visas alltid. Event med datum döljs när tiden har passerat.</p>
	</div>

	<script>
		( function () {
			var select = document.getElementById( 'iuc-news-type' );
			var box    = document.getElementById( 'iuc-news-event-fields' );

			function toggle() {
				box.style.display = 'event' === select.value ? '' : 'none';
			}

			select.addEventListener( 'change', toggle );
			toggle();
		}() );
	</script>
	<?php
}

function iuc_news_save_meta( $post_id ) {
	if ( ! isset( $_POST['iuc_news_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['iuc_news_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'iuc_news_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$type = isset( $_POST['iuc_news_type'] )
		? sanitize_key( wp_unslash( $_POST['iuc_news_type'] ) )
		: 'news';

	if ( ! isset( iuc_news_types()[ $type ] ) ) {
		$type = 'news';
	}

	update_post_meta( $post_id, IUC_NEWS_TYPE_META, $type );

	if ( 'event' !== $type ) {
		delete_post_meta( $post_id, IUC_NEWS_DATE_META );
		delete_post_meta( $post_id, IUC_NEWS_LOCATION_META );
		return;
	}

	$date = isset( $_POST['iuc_event_date'] )
		? sanitize_text_field( wp_unslash( $_POST['iuc_event_date'] ) )
		: '';

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $date ) ) {
		update_post_meta( $post_id, IUC_NEWS_DATE_META, $date );
	} else {
		delete_post_meta( $post_id, IUC_NEWS_DATE_META );
	}

	$place = isset( $_POST['iuc_event_location'] )
		? sanitize_text_field( wp_unslash( $_POST['iuc_event_location'] ) )
		: '';

	update_post_meta( $post_id, IUC_NEWS_LOCATION_META, $place );
}
add_action( 'save_post_iuc_news', 'iuc_news_save_meta' );

/* ---------------------------------------------------------------------
 * Admin list columns
 * ------------------------------------------------------------------ */

function iuc_news_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new['iuc_type']  = 'Typ';
			$new['iuc_event'] = 'Eventdatum';
		}
	}

	return $new;
}
add_filter( 'manage_iuc_news_posts_columns', 'iuc_news_columns' );

function iuc_news_column_content( $column, $post_id ) {
	if ( 'iuc_type' === $column ) {
		$types = iuc_news_types();
		$type  = get_post_meta( $post_id, IUC_NEWS_TYPE_META, true );

		echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : '–' );
	}

	if ( 'iuc_event' === $column ) {
		$date = get_post_meta( $post_id, IUC_NEWS_DATE_META, true );

		echo esc_html( $date ? iuc_news_format_date( $date ) : '–' );
	}
}
add_action( 'manage_iuc_news_posts_custom_column', 'iuc_news_column_content', 10, 2 );

/* ---------------------------------------------------------------------
 * Member view
 * ------------------------------------------------------------------ */

/**
 * Format a stored event date (site time) for display.
 */
function iuc_news_format_date( $value ) {
	$timestamp = strtotime( $value );

	return $timestamp ? date_i18n( 'j F Y, H:i', $timestamp ) : '';
}

/**
 * Meta query for the chosen filter: all, news or event.
 */
function iuc_news_meta_query( $filter ) {
	$now = wp_date( 'Y-m-d\TH:i' );

	$news = array(
		'key'   => IUC_NEWS_TYPE_META,
		'value' => 'news',
	);

	$events = array(
		'relation' => 'AND',
		array(
			'key'   => IUC_NEWS_TYPE_META,
			'value' => 'event',
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => IUC_NEWS_DATE_META,
				'value'   => $now,
				'compare' => '>=',
				'type'    => 'CHAR',
			),
			array(
				'key'     => IUC_NEWS_DATE_META,
				'compare' => 'NOT EXISTS',
			),
		),
	);

	if ( 'news' === $filter ) {
		return array( $news );
	}

	if ( 'event' === $filter ) {
		return array( $events );
	}

	return array(
		'relation' => 'OR',
		$news,
		$events,
	);
}

/**
 * Display news and events.
 *
 * Usage: [iuc_news] or [iuc_news type="event" limit="10"]
 */
function iuc_news_shortcode( $atts ) {
	if ( ! is_user_logged_in() ) {
		return sprintf(
			'<p><a href="%s">%s</a></p>',
			esc_url( home_url( '/' ) ),
			esc_html__( 'Logga in för att läsa nyheter och event.', 'iuc-portal' )
		);
	}

	$atts = shortcode_atts(
		array(
			'type'  => 'all',
			'limit' => 20,
		),
		$atts,
		'iuc_news'
	);

	$allowed = array( 'all', 'news', 'event' );
	$filter  = sanitize_key( $atts['type'] );
	$filter  = in_array( $filter, $allowed, true ) ? $filter : 'all';
	$limit   = max( 1, min( 50, absint( $atts['limit'] ) ) );

	// Members can switch between all / news / events when the page shows all.
	$show_tabs = 'all' === $filter;

	if ( $show_tabs && isset( $_GET['visa'] ) ) {
		$chosen = sanitize_key( wp_unslash( $_GET['visa'] ) );

		if ( in_array( $chosen, $allowed, true ) ) {
			$filter = $chosen;
		}
	}

	$path = IUC_PORTAL_FEATURES_PATH . 'assets/news.css';

	wp_enqueue_style(
		'iuc-syd-news',
		IUC_PORTAL_FEATURES_URL . 'assets/news.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0'
	);

	$items = get_posts(
		array(
			'post_type'      => 'iuc_news',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => iuc_news_meta_query( $filter ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);

	// Events are easier to read soonest-first.
	if ( 'event' === $filter ) {
		usort(
			$items,
			static function ( $a, $b ) {
				$da = get_post_meta( $a->ID, IUC_NEWS_DATE_META, true );
				$db = get_post_meta( $b->ID, IUC_NEWS_DATE_META, true );

				if ( $da === $db ) {
					return 0;
				}

				if ( '' === $da ) {
					return 1;
				}

				if ( '' === $db ) {
					return -1;
				}

				return strcmp( $da, $db );
			}
		);
	}

	$types = iuc_news_types();
	$tabs  = array(
		'all'   => 'Alla',
		'news'  => 'Nyheter',
		'event' => 'Event',
	);

	ob_start();
	?>

	<section class="iuc-news" aria-labelledby="iuc-news-title">
		<h2 id="iuc-news-title">Nyheter och event</h2>

		<?php if ( $show_tabs ) : ?>
			<nav class="iuc-news-tabs" aria-label="Filtrera">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a
						href="<?php echo esc_url( 'all' === $key ? remove_query_arg( 'visa' ) : add_query_arg( 'visa', $key ) ); ?>"
						<?php echo $key === $filter ? 'aria-current="true" class="is-active"' : ''; ?>
					>
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( ! $items ) : ?>
			<p class="iuc-news-empty">Inga nyheter eller event just nu.</p>
		<?php endif; ?>

		<?php foreach ( $items as $item ) : ?>
			<?php
			$type  = get_post_meta( $item->ID, IUC_NEWS_TYPE_META, true );
			$date  = get_post_meta( $item->ID, IUC_NEWS_DATE_META, true );
			$place = get_post_meta( $item->ID, IUC_NEWS_LOCATION_META, true );
			?>
			<article class="iuc-news-card iuc-news-<?php echo esc_attr( $type ); ?>">
				<header>
					<span class="iuc-news-badge">
						<?php echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : '' ); ?>
					</span>
					<h3><?php echo esc_html( get_the_title( $item ) ); ?></h3>

					<p class="iuc-news-meta">
						<?php if ( 'event' === $type && $date ) : ?>
							<span><?php echo esc_html( iuc_news_format_date( $date ) ); ?></span>
						<?php else : ?>
							<span>Publicerad <?php echo esc_html( get_the_date( 'j F Y', $item ) ); ?></span>
						<?php endif; ?>

						<?php if ( 'event' === $type && $place ) : ?>
							<span><?php echo esc_html( $place ); ?></span>
						<?php endif; ?>
					</p>
				</header>

				<div class="iuc-news-body">
					<?php echo wp_kses_post( apply_filters( 'the_content', $item->post_content ) ); ?>
				</div>
			</article>
		<?php endforeach; ?>
	</section>

	<?php
	return ob_get_clean();
}
add_shortcode( 'iuc_news', 'iuc_news_shortcode' );