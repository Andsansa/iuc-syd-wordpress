<?php
/**
 * Plugin Name: IUC Syd Medlemsportal – Startsida
 * Description: Startsida för IUC Syds medlemsportal baserad på prototyp v4, grafisk profil och användartester.
 * Version: 1.0.2
 * Author: IUC Syd Projektteam
 * Text Domain: iuc-syd-startsida
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stoppa direktåtkomst
}

define( 'IUC_STARTSIDA_VERSION', '1.0.3' );
define( 'IUC_STARTSIDA_PATH', plugin_dir_path( __FILE__ ) );
define( 'IUC_STARTSIDA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Ladda in stilmall och JavaScript när shortcoden används eller på startsidan.
 */
function iuc_startsida_enqueue_assets() {
	global $post;

	$has_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'iuc_syd_startsida' );

	if ( is_front_page() || $has_shortcode ) {
		wp_enqueue_style(
			'iuc-startsida-style',
			IUC_STARTSIDA_URL . 'assets/css/startsida.css',
			array(),
			IUC_STARTSIDA_VERSION
		);

		wp_enqueue_script(
			'iuc-startsida-script',
			IUC_STARTSIDA_URL . 'assets/js/startsida.js',
			array(),
			IUC_STARTSIDA_VERSION,
			true
		);

		wp_localize_script(
			'iuc-startsida-script',
			'iucPortalData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'iuc_portal_nonce' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'iuc_startsida_enqueue_assets' );

/**
 * Hämtar data till startsidan.
 * Kopplas enkelt mot riktiga CPT:er (t.ex. 'iuc_event', 'iuc_nyhet', BuddyPress) i nästa steg.
 */
function iuc_startsida_get_data() {
	$current_user = wp_get_current_user();
	$user_name    = is_user_logged_in() ? $current_user->display_name : 'Anna Lindqvist';
	$first_name   = explode( ' ', trim( $user_name ) )[0];
	$company_name = is_user_logged_in()
		? ( get_user_meta( $current_user->ID, 'iuc_company_name', true ) ?: 'Höglunds Verkstad AB' )
		: 'Höglunds Verkstad AB';

	$amnen = array(
		'Automation',
		'Artificiell intelligens',
		'Cybersäkerhet',
		'Effektivisering',
		'Hållbarhet',
		'Kompetensutveckling',
	);

	// 1. Fäst nyhet / aktivitet från IUC Syd
	$featured_iuc = array(
		'badge'    => 'FÄST ÖVERST · AKTIVITET',
		'title'    => 'Anmälan öppen: höstens Kickstart AI',
		'excerpt'  => 'Introduktionsworkshop för medlemsföretag som vill komma igång med AI i produktionen. Begränsat antal platser.',
		'deadline' => 'Anmälan stänger 30 okt',
		'cta_text' => 'Läs mer och anmäl',
		'cta_url'  => '#event-kickstart-ai',
		'image'    => IUC_STARTSIDA_URL . 'assets/img/hero-industri.jpg', // Valfritt omslagsfoto
	);

	// 2. Medlemsnyheter från IUC Syd (hämtar riktiga inlägg om kategorin finns, annars prototypdata)
	$news_items = array();
	$news_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
		)
	);

	if ( $news_query->have_posts() ) {
		while ( $news_query->have_posts() ) {
			$news_query->the_post();
			$news_items[] = array(
				'date'    => get_the_date( 'j M' ),
				'title'   => get_the_title(),
				'excerpt' => wp_trim_words( get_the_excerpt(), 16 ),
				'url'     => get_permalink(),
			);
		}
		wp_reset_postdata();
	} else {
		$news_items = array(
			array(
				'date'    => '8 okt',
				'title'   => 'Ny omgång av Produktionslyftet startar i vår',
				'excerpt' => 'Intresseanmälan är nu öppen för tillverkande medlemsföretag i Skåne.',
				'url'     => '#',
			),
			array(
				'date'    => '1 okt',
				'title'   => 'Så får ni ut mest av medlemsportalen',
				'excerpt' => 'Kort guide till nätverksforum, företagskort och personlig rådgivning.',
				'url'     => '#',
			),
			array(
				'date'    => '24 sep',
				'title'   => 'Nytt stöd för kompetensutveckling i industrin',
				'excerpt' => 'Sammanfattning av aktuella insatser och finansieringsstöd för er som medlem.',
				'url'     => '#',
			),
		);
	}

	// 3. Event och aktiviteter (med koppling till ämnesområde för filtrering)
	$events = array(
		array(
			'day'      => '14',
			'month'    => 'OKT',
			'title'    => 'Nätverksträff: Automation i praktiken',
			'location' => 'Utvecklingscentret, Malmö',
			'topic'    => 'Automation',
			'url'      => '#',
		),
		array(
			'day'      => '22',
			'month'    => 'OKT',
			'title'    => 'Webinar: Cybersäkerhet för tillverkande företag',
			'location' => 'Digitalt',
			'topic'    => 'Cybersäkerhet',
			'url'      => '#',
		),
		array(
			'day'      => '5',
			'month'    => 'NOV',
			'title'    => 'Workshop: Effektivisering och 5S',
			'location' => 'Hos medlemsföretag i Skåne',
			'topic'    => 'Effektivisering',
			'url'      => '#',
		),
	);

	// 4. Nya medlemsföretag (efterfrågat i användartestet för att välkomna nya medlemmar)
	$new_members = array(
		array(
			'name'     => 'Lund Plåtbearbetning AB',
			'city'     => 'Lund',
			'industry' => 'Plåtbearbetning',
			'url'      => '#',
		),
		array(
			'name'     => 'Helsingborg Legotillverkning',
			'city'     => 'Helsingborg',
			'industry' => 'Skärande bearbetning',
			'url'      => '#',
		),
	);

	// 5. Inlägg i Nätverk & Forum (med hopfällbara kommentarer som standard)
	$forum_posts = array(
		array(
			'id'           => 101,
			'initials'     => 'ES',
			'author'       => 'Erik Sandberg',
			'company'      => 'Nordisk Plåtteknik AB',
			'company_url'  => '#foretag-nordisk-plat',
			'time'         => '2 tim sedan',
			'post_type'    => 'Fråga till nätverket',
			'topic'        => 'Automation',
			'is_iuc_reply' => true,
			'body'         => 'Någon som har erfarenhet av att koppla ihop ERP-systemet med en robotcell? Vi funderar på hur vi hanterar orderdata på ett säkert sätt.',
			'comments'     => array(
				array(
					'author'  => 'Maria Bergström',
					'company' => 'Ystad Pumpteknik AB',
					'is_iuc'  => false,
					'text'    => 'Vi gjorde det i våras. Det som tog mest tid var att bestämma vilket system som äger orderdatan.',
				),
				array(
					'author'  => 'IUC Syd',
					'company' => 'Rådgivare IUC Syd',
					'is_iuc'  => true,
					'text'    => 'Vi kan koppla ihop er med fler medlemsföretag som jobbat med detta. Hör av er om ni vill boka ett kort avstämningsmöte.',
				),
			),
		),
		array(
			'id'           => 102,
			'initials'     => 'MB',
			'author'       => 'Maria Bergström',
			'company'      => 'Ystad Pumpteknik AB',
			'company_url'  => '#foretag-ystad-pump',
			'time'         => '3 dagar sedan',
			'post_type'    => 'Söker samarbete',
			'topic'        => 'Cybersäkerhet',
			'is_iuc_reply' => false,
			'body'         => 'Vi vill gå igenom vår cybersäkerhet inför skärpta kundkrav (NIS2). Finns det fler företag i Skåne som står inför samma sak och vill utbyta erfarenheter?',
			'comments'     => array(
				array(
					'author'  => 'Johan Nilsson',
					'company' => 'Trelleborg Verktyg AB',
					'is_iuc'  => false,
					'text'    => 'Vi håller precis på med en kartläggning och deltar gärna i ett gemensamt erfarenhetsutbyte.',
				),
			),
		),
	);

	return array(
		'user_name'    => $user_name,
		'first_name'   => $first_name,
		'company_name' => $company_name,
		'amnen'        => $amnen,
		'featured_iuc' => $featured_iuc,
		'news_items'   => $news_items,
		'events'       => $events,
		'new_members'  => $new_members,
		'forum_posts'  => $forum_posts,
	);
}

/**
 * Shortcode: [iuc_syd_startsida]
 * Lägg in denna shortcode på den sida i WordPress som är vald som Startsida.
 */
function iuc_startsida_render_shortcode() {
	$data = iuc_startsida_get_data();

	ob_start();
	include IUC_STARTSIDA_PATH . 'templates/startsida-view.php';
	return ob_get_clean();
}
add_shortcode( 'iuc_syd_startsida', 'iuc_startsida_render_shortcode' );

function iuc_startsida_fullscreen_template( $template ) {
	$current_post  = get_queried_object();
	$has_shortcode = ( $current_post instanceof WP_Post ) && has_shortcode( $current_post->post_content, 'iuc_syd_startsida' );

	if ( is_front_page() || $has_shortcode ) {
		$canvas_template = IUC_STARTSIDA_PATH . 'templates/canvas.php';
		if ( file_exists( $canvas_template ) ) {
			return $canvas_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'iuc_startsida_fullscreen_template', PHP_INT_MAX );