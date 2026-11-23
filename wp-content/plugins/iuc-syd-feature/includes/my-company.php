<?php
/**
 * Mitt företag: medlemmens företagskort och kontaktpersoner.
 *
 * Fungerar som Strapi-versionen (MittForetag.jsx + api/my-company):
 * - IUC Syd lägger in och ändrar företag och kontaktpersoner i admin.
 * - Medlemmen kopplas till företaget via sitt företagsnamn (BuddyPress-fältet
 *   "Företag" eller user meta iuc_company), samma som i private-requests.php.
 * - Medlemmen kan bara läsa företaget och sätta företagets samtycke för
 *   godkända kontaktpersoner.
 *
 * Användning: [iuc_my_company]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const IUC_MC_COMPANY        = 'iuc_company';
const IUC_MC_CONTACT_PERSON = 'iuc_contact_person';

/**
 * Fält på företaget utöver namn (rubrik) och beskrivning (innehåll).
 */
function iuc_mc_company_fields() {
	return array(
		'iuc_contact_name'  => 'Kontaktperson (namn)',
		'iuc_contact_email' => 'Kontaktperson (e-post)',
		'iuc_city'          => 'Ort',
		'iuc_industry'      => 'Bransch',
		'iuc_email'         => 'E-post',
		'iuc_phone'         => 'Telefon',
	);
}

/**
 * Textfält på kontaktpersonen utöver namn (rubrik).
 */
function iuc_mc_contact_text_fields() {
	return array(
		'iuc_job_title' => 'Yrkestitel eller roll',
		'iuc_cp_email'  => 'E-post',
		'iuc_cp_phone'  => 'Telefon',
	);
}

function iuc_mc_approval_statuses() {
	return array(
		'pending'  => 'Väntar på godkännande',
		'approved' => 'Godkänd',
		'rejected' => 'Nekad',
	);
}



/* ------------------------------------------------------------------
 * Posttyper. Bara administratörer kan se och ändra dem i admin.
 * ------------------------------------------------------------------ */

function iuc_mc_admin_only_capabilities() {
	return array(
		'edit_post'              => 'manage_options',
		'read_post'              => 'manage_options',
		'delete_post'            => 'manage_options',
		'edit_posts'             => 'manage_options',
		'edit_others_posts'      => 'manage_options',
		'delete_posts'           => 'manage_options',
		'publish_posts'          => 'manage_options',
		'read_private_posts'     => 'manage_options',
		'delete_private_posts'   => 'manage_options',
		'delete_published_posts' => 'manage_options',
		'edit_private_posts'     => 'manage_options',
		'edit_published_posts'   => 'manage_options',
		'create_posts'           => 'manage_options',
	);
}

function iuc_mc_register_post_types() {
	register_post_type(
		IUC_MC_COMPANY,
		array(
			'labels'              => array(
				'name'          => 'Företag',
				'singular_name' => 'Företag',
				'menu_name'     => 'Företag',
				'all_items'     => 'Alla företag',
				'add_new_item'  => 'Lägg till företag',
				'edit_item'     => 'Redigera företag',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'menu_icon'           => 'dashicons-building',
			'supports'            => array( 'title', 'editor' ),
			'map_meta_cap'        => false,
			'capabilities'        => iuc_mc_admin_only_capabilities(),
		)
	);

	register_post_type(
		IUC_MC_CONTACT_PERSON,
		array(
			'labels'              => array(
				'name'          => 'Kontaktpersoner',
				'singular_name' => 'Kontaktperson',
				'all_items'     => 'Kontaktpersoner',
				'add_new_item'  => 'Lägg till kontaktperson',
				'edit_item'     => 'Redigera kontaktperson',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=' . IUC_MC_COMPANY,
			'show_in_nav_menus'   => false,
			'supports'            => array( 'title' ),
			'map_meta_cap'        => false,
			'capabilities'        => iuc_mc_admin_only_capabilities(),
		)
	);
}
add_action( 'init', 'iuc_mc_register_post_types' );

// Rubrikfältet ska heta "Namn" i stället för "Lägg till rubrik".
add_filter(
	'enter_title_here',
	function ( $text, $post ) {
		if ( IUC_MC_COMPANY === $post->post_type ) {
			return 'Företagets namn (exakt som medlemmen skrev vid registreringen)';
		}
		if ( IUC_MC_CONTACT_PERSON === $post->post_type ) {
			return 'Kontaktpersonens namn';
		}
		return $text;
	},
	10,
	2
);

/* ------------------------------------------------------------------
 * Admin: rutor för företagsuppgifter och kontaktperson
 * ------------------------------------------------------------------ */

function iuc_mc_add_meta_boxes() {
	add_meta_box( 'iuc_mc_company', 'Företagsuppgifter', 'iuc_mc_company_meta_box', IUC_MC_COMPANY, 'normal', 'high' );
	add_meta_box( 'iuc_mc_contact_person', 'Kontaktperson', 'iuc_mc_contact_person_meta_box', IUC_MC_CONTACT_PERSON, 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'iuc_mc_add_meta_boxes' );

function iuc_mc_company_meta_box( $post ) {
	wp_nonce_field( 'iuc_mc_admin_save', 'iuc_mc_admin_nonce' );
	echo '<p>Beskrivningen skrivs i den stora textrutan ovanför.</p>';
	foreach ( iuc_mc_company_fields() as $key => $label ) {
		iuc_mc_admin_text_input( $key, $label, get_post_meta( $post->ID, $key, true ) );
	}
}

function iuc_mc_contact_person_meta_box( $post ) {
	wp_nonce_field( 'iuc_mc_admin_save', 'iuc_mc_admin_nonce' );

	$company_id = (int) get_post_meta( $post->ID, 'iuc_company_id', true );
	$companies  = get_posts(
		array(
			'post_type'   => IUC_MC_COMPANY,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		)
	);
	?>
	<p>
		<label for="iuc_company_id"><strong>Företag</strong></label><br>
		<select id="iuc_company_id" name="iuc_company_id">
			<option value="0">Välj företag</option>
			<?php foreach ( $companies as $company ) : ?>
				<option value="<?php echo esc_attr( $company->ID ); ?>" <?php selected( $company_id, $company->ID ); ?>>
					<?php echo esc_html( $company->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
	foreach ( iuc_mc_contact_text_fields() as $key => $label ) {
		iuc_mc_admin_text_input( $key, $label, get_post_meta( $post->ID, $key, true ) );
	}

	$status = get_post_meta( $post->ID, 'iuc_approval_status', true );
	$status = $status ? $status : 'pending';
	?>
	<p>
		<label for="iuc_approval_status"><strong>Status</strong></label><br>
		<select id="iuc_approval_status" name="iuc_approval_status">
			<?php foreach ( iuc_mc_approval_statuses() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
	iuc_mc_admin_checkbox( 'iuc_is_main_contact', 'Huvudkontakt', get_post_meta( $post->ID, 'iuc_is_main_contact', true ) );
	iuc_mc_admin_checkbox( 'iuc_company_consent', 'Företagets samtycke', get_post_meta( $post->ID, 'iuc_company_consent', true ) );
	iuc_mc_admin_checkbox( 'iuc_person_consent', 'Personens samtycke', get_post_meta( $post->ID, 'iuc_person_consent', true ) );
}

function iuc_mc_admin_text_input( $key, $label, $value ) {
	printf(
		'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
		esc_attr( $key ),
		esc_html( $label ),
		esc_attr( $value )
	);
}

function iuc_mc_admin_checkbox( $key, $label, $value ) {
	printf(
		'<p><label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label></p>',
		esc_attr( $key ),
		checked( '1', $value, false ),
		esc_html( $label )
	);
}

function iuc_mc_admin_can_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! isset( $_POST['iuc_mc_admin_nonce'] ) ) {
		return false;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['iuc_mc_admin_nonce'] ) );
	return wp_verify_nonce( $nonce, 'iuc_mc_admin_save' ) && current_user_can( 'manage_options' );
}

function iuc_mc_save_company( $post_id ) {
	if ( ! iuc_mc_admin_can_save( $post_id ) ) {
		return;
	}
	foreach ( iuc_mc_company_fields() as $key => $label ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] );
		$value = in_array( $key, array( 'iuc_email', 'iuc_contact_email' ), true )
			? sanitize_email( $value )
			: sanitize_text_field( $value );
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post_' . IUC_MC_COMPANY, 'iuc_mc_save_company' );

function iuc_mc_save_contact_person( $post_id ) {
	if ( ! iuc_mc_admin_can_save( $post_id ) ) {
		return;
	}

	$company_id = isset( $_POST['iuc_company_id'] ) ? absint( $_POST['iuc_company_id'] ) : 0;
	update_post_meta( $post_id, 'iuc_company_id', $company_id );

	foreach ( iuc_mc_contact_text_fields() as $key => $label ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] );
		$value = 'iuc_cp_email' === $key ? sanitize_email( $value ) : sanitize_text_field( $value );
		update_post_meta( $post_id, $key, $value );
	}

	$status = isset( $_POST['iuc_approval_status'] ) ? sanitize_key( wp_unslash( $_POST['iuc_approval_status'] ) ) : 'pending';
	if ( ! array_key_exists( $status, iuc_mc_approval_statuses() ) ) {
		$status = 'pending';
	}
	update_post_meta( $post_id, 'iuc_approval_status', $status );

	foreach ( array( 'iuc_is_main_contact', 'iuc_company_consent', 'iuc_person_consent' ) as $key ) {
		update_post_meta( $post_id, $key, empty( $_POST[ $key ] ) ? '0' : '1' );
	}
}
add_action( 'save_post_' . IUC_MC_CONTACT_PERSON, 'iuc_mc_save_contact_person' );

// Kolumner i listan över kontaktpersoner.
add_filter(
	'manage_' . IUC_MC_CONTACT_PERSON . '_posts_columns',
	function ( $columns ) {
		$date = $columns['date'];
		unset( $columns['date'] );
		$columns['iuc_company'] = 'Företag';
		$columns['iuc_status']  = 'Status';
		$columns['date']        = $date;
		return $columns;
	}
);

add_action(
	'manage_' . IUC_MC_CONTACT_PERSON . '_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'iuc_company' === $column ) {
			$company_id = (int) get_post_meta( $post_id, 'iuc_company_id', true );
			echo $company_id ? esc_html( get_the_title( $company_id ) ) : '—';
		}
		if ( 'iuc_status' === $column ) {
			$statuses = iuc_mc_approval_statuses();
			$status   = get_post_meta( $post_id, 'iuc_approval_status', true );
			echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $statuses['pending'] );
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------
 * Hämta data
 * ------------------------------------------------------------------ */

/**
 * Företaget för en användare. Matchas på företagsnamnet, precis som
 * company_id i Strapi. Publicerat företag går före utkast.
 */
function iuc_mc_find_company_for_user( $user_id ) {
	// iuc_get_request_company() finns i private-requests.php.
	$name = function_exists( 'iuc_get_request_company' )
		? iuc_get_request_company( $user_id )
		: get_user_meta( $user_id, 'iuc_company', true );
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return null;
	}

	foreach ( array( array( 'publish' ), array( 'draft', 'private', 'pending' ) ) as $statuses ) {
		$found = get_posts(
			array(
				'post_type'   => IUC_MC_COMPANY,
				'post_status' => $statuses,
				'title'       => $name,
				'numberposts' => 1,
			)
		);
		if ( $found ) {
			return $found[0];
		}
	}
	return null;
}

/**
 * Kontaktpersoner för ett företag. Nekade tas inte med.
 * Huvudkontakten först, sedan äldst först.
 */
function iuc_mc_get_contact_persons( $company_id ) {
	$posts = get_posts(
		array(
			'post_type'   => IUC_MC_CONTACT_PERSON,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'ASC',
			'meta_query'  => array(
				array(
					'key'   => 'iuc_company_id',
					'value' => (int) $company_id,
				),
			),
		)
	);

	$contact_persons = array();
	foreach ( $posts as $post ) {
		$contact_person = iuc_mc_to_contact_person( $post );
		if ( 'rejected' !== $contact_person['approval_status'] ) {
			$contact_persons[] = $contact_person;
		}
	}

	// usort är inte stabil i äldre PHP, så index används som andra nyckel.
	$order = array_flip( wp_list_pluck( $contact_persons, 'id' ) );
	usort(
		$contact_persons,
		function ( $a, $b ) use ( $order ) {
			if ( $a['is_main_contact'] !== $b['is_main_contact'] ) {
				return $a['is_main_contact'] ? -1 : 1;
			}
			return $order[ $a['id'] ] - $order[ $b['id'] ];
		}
	);

	return $contact_persons;
}

function iuc_mc_to_contact_person( $post ) {
	$status = get_post_meta( $post->ID, 'iuc_approval_status', true );
	return array(
		'id'              => $post->ID,
		'name'            => $post->post_title,
		'job_title'       => (string) get_post_meta( $post->ID, 'iuc_job_title', true ),
		'email'           => (string) get_post_meta( $post->ID, 'iuc_cp_email', true ),
		'phone'           => (string) get_post_meta( $post->ID, 'iuc_cp_phone', true ),
		'approval_status' => $status ? $status : 'pending',
		'is_main_contact' => '1' === get_post_meta( $post->ID, 'iuc_is_main_contact', true ),
		'company_consent' => '1' === get_post_meta( $post->ID, 'iuc_company_consent', true ),
		'person_consent'  => '1' === get_post_meta( $post->ID, 'iuc_person_consent', true ),
	);
}

// IUC har godkänt personen och både företaget och personen har samtyckt.
function iuc_mc_is_visible_to_members( $contact_person ) {
	return 'approved' === $contact_person['approval_status']
		&& $contact_person['company_consent']
		&& $contact_person['person_consent'];
}

function iuc_mc_get_status( $contact_person ) {
	if ( 'pending' === $contact_person['approval_status'] ) {
		return array( 'label' => 'Väntar på godkännande', 'tone' => 'waiting' );
	}
	if ( $contact_person['is_main_contact'] ) {
		return array( 'label' => 'Huvudkontakt', 'tone' => 'main' );
	}
	if ( iuc_mc_is_visible_to_members( $contact_person ) ) {
		return array( 'label' => 'Visas för medlemmar', 'tone' => 'visible' );
	}
	if ( $contact_person['company_consent'] ) {
		return array( 'label' => 'Väntar på personens samtycke', 'tone' => 'waiting' );
	}
	return null;
}

function iuc_mc_initials( $name ) {
	$words    = preg_split( '/\s+/', trim( (string) $name ) );
	$initials = '';
	foreach ( array_slice( array_filter( $words ), 0, 2 ) as $word ) {
		$initials .= mb_substr( $word, 0, 1 );
	}
	return mb_strtoupper( $initials );
}

/* ------------------------------------------------------------------
 * CSS laddas bara på sidor som har shortcoden
 * ------------------------------------------------------------------ */

function iuc_mc_enqueue_styles() {
	$post = get_post();
	if ( ! $post || ! has_shortcode( $post->post_content, 'iuc_my_company' ) ) {
		return;
	}
	$path = IUC_PORTAL_FEATURES_PATH . 'assets/my-company.css';
	wp_enqueue_style(
		'iuc-my-company',
		IUC_PORTAL_FEATURES_URL . 'assets/my-company.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'iuc_mc_enqueue_styles' );

/* ------------------------------------------------------------------
 * Shortcoden [iuc_my_company]
 * ------------------------------------------------------------------ */

function iuc_my_company_shortcode() {
	if ( ! is_user_logged_in() ) {
		return iuc_mc_render_message(
			'Logga in för att se ert företag.',
			wp_login_url( get_permalink() ),
			'Gå till inloggningen'
		);
	}

	$company = iuc_mc_find_company_for_user( get_current_user_id() );
	if ( ! $company ) {
		return iuc_mc_render_message( 'Ditt konto är inte kopplat till något företag i portalen. Kontakta IUC Syd.' );
	}

	$contact_persons   = iuc_mc_get_contact_persons( $company->ID );
	$is_member_preview = isset( $_GET['vy'] ) && 'medlem' === sanitize_key( wp_unslash( $_GET['vy'] ) );
	$page_url          = remove_query_arg( array( 'vy', 'samtycke', 'kontakt' ) );
	$consent_state     = isset( $_GET['samtycke'] ) ? sanitize_key( wp_unslash( $_GET['samtycke'] ) ) : '';

	ob_start();
	?>
	<main class="mf-page">
		<div class="mf-container">
			<header class="mf-page-header">
				<div>
					<h1 class="mf-page-title">
						<?php echo $is_member_preview ? 'Så ser andra medlemmar kortet' : 'Mitt företag'; ?>
					</h1>
					<p class="mf-page-intro">
						<?php
						echo $is_member_preview
							? 'Andra medlemmar ser företagsuppgifterna och de kontaktpersoner som har samtycke från både företaget och personen.'
							: 'Så ser ert företagskort ut, vilka som är kopplade till det och vilka behov ni har registrerat.';
						?>
					</p>
				</div>
				<?php if ( $is_member_preview ) : ?>
					<a class="mf-button-quiet" href="<?php echo esc_url( $page_url ); ?>">Tillbaka till Mitt företag</a>
				<?php else : ?>
					<a class="mf-button-quiet" href="<?php echo esc_url( add_query_arg( 'vy', 'medlem', $page_url ) ); ?>">Se hur andra medlemmar ser kortet</a>
				<?php endif; ?>
			</header>

			<?php if ( $is_member_preview ) : ?>
				<div class="mf-preview">
					<?php iuc_mc_render_company_card( $company, false ); ?>
					<?php iuc_mc_render_member_preview( array_filter( $contact_persons, 'iuc_mc_is_visible_to_members' ) ); ?>
				</div>
			<?php else : ?>
				<div class="mf-layout">
					<div class="mf-column">
						<?php iuc_mc_render_company_card( $company, true ); ?>
						<?php iuc_mc_render_contact_person_list( $contact_persons, $consent_state ); ?>
					</div>
					<div class="mf-column">
						<?php // Identifierade behov och Engagemang i communityt byggs separat och hamnar i den här kolumnen. ?>
						<?php iuc_mc_render_participations( array() ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</main>
	<?php
	return ob_get_clean();
}
add_shortcode( 'iuc_my_company', 'iuc_my_company_shortcode' );

function iuc_mc_render_message( $text, $link_url = '', $link_text = '' ) {
	ob_start();
	?>
	<main class="mf-page">
		<div class="mf-container">
			<div class="mf-card mf-message" role="alert">
				<p><?php echo esc_html( $text ); ?></p>
				<?php if ( $link_url ) : ?>
					<a class="mf-button" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_text ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php
	return ob_get_clean();
}

function iuc_mc_render_company_card( $company, $show_edit_notice ) {
	$city        = get_post_meta( $company->ID, 'iuc_city', true );
	$industry    = get_post_meta( $company->ID, 'iuc_industry', true );
	$email       = get_post_meta( $company->ID, 'iuc_email', true );
	$phone       = get_post_meta( $company->ID, 'iuc_phone', true );
	$location    = implode( ' · ', array_filter( array( $city, $industry ) ) );
	$description = $company->post_content;
	?>
	<section class="mf-card" aria-label="<?php echo esc_attr( $company->post_title ); ?>">
		<div class="mf-company-head">
			<span class="mf-avatar mf-avatar-large" aria-hidden="true"><?php echo esc_html( iuc_mc_initials( $company->post_title ) ); ?></span>
			<div>
				<h2 class="mf-company-name"><?php echo esc_html( $company->post_title ); ?></h2>
				<?php if ( $location ) : ?>
					<p class="mf-muted"><?php echo esc_html( $location ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $description ) : ?>
			<p class="mf-company-description"><?php echo nl2br( esc_html( wp_strip_all_tags( $description ) ) ); ?></p>
		<?php endif; ?>

		<?php if ( $email || $phone ) : ?>
			<p class="mf-company-contact">
				<?php if ( $email ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<?php if ( $show_edit_notice ) : ?>
			<p class="mf-notice">Företagsuppgifter läggs in och uppdateras av IUC Syd. Hör av er om något är fel.</p>
		<?php endif; ?>
	</section>
	<?php
}

function iuc_mc_render_contact_person_list( $contact_persons, $consent_state ) {
	?>
	<section class="mf-card" aria-labelledby="mf-contacts-title">
		<h2 id="mf-contacts-title" class="mf-card-title">Kontaktpersoner</h2>
		<p class="mf-card-intro">
			Andra medlemmar ser bara huvudkontakten. Namn, roll och e-post visas först när både
			företaget och personen har gett samtycke.
		</p>

		<?php if ( 'error' === $consent_state ) : ?>
			<p class="mf-error" role="alert">Samtycket sparades inte. Ladda om sidan och försök igen.</p>
		<?php elseif ( 'saved' === $consent_state ) : ?>
			<p class="mf-saved" role="status">Samtycket är sparat.</p>
		<?php endif; ?>

		<?php if ( empty( $contact_persons ) ) : ?>
			<p class="mf-empty">Inga kontaktpersoner ännu.</p>
		<?php else : ?>
			<ul class="mf-list">
				<?php foreach ( $contact_persons as $contact_person ) : ?>
					<?php iuc_mc_render_contact_person_row( $contact_person ); ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<?php
}

function iuc_mc_render_contact_person_row( $contact_person ) {
	$status      = iuc_mc_get_status( $contact_person );
	$checkbox_id = 'mf-consent-' . $contact_person['id'];
	?>
	<li class="mf-row">
		<span class="mf-avatar" aria-hidden="true"><?php echo esc_html( iuc_mc_initials( $contact_person['name'] ) ); ?></span>
		<div class="mf-row-text">
			<p class="mf-row-name"><?php echo esc_html( $contact_person['name'] ); ?></p>
			<?php if ( $contact_person['job_title'] ) : ?>
				<p class="mf-row-detail"><?php echo esc_html( $contact_person['job_title'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="mf-row-actions">
			<?php if ( $status ) : ?>
				<span class="mf-badge mf-badge-<?php echo esc_attr( $status['tone'] ); ?>"><?php echo esc_html( $status['label'] ); ?></span>
			<?php endif; ?>

			<?php if ( 'approved' === $contact_person['approval_status'] ) : ?>
				<form class="mf-consent-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="iuc_company_consent">
					<input type="hidden" name="contact_person" value="<?php echo esc_attr( $contact_person['id'] ); ?>">
					<?php wp_nonce_field( 'iuc_company_consent', 'iuc_company_consent_nonce' ); ?>
					<input type="hidden" name="company_consent" value="0">
					<label class="mf-consent" for="<?php echo esc_attr( $checkbox_id ); ?>">
						<input
							id="<?php echo esc_attr( $checkbox_id ); ?>"
							type="checkbox"
							name="company_consent"
							value="1"
							<?php checked( $contact_person['company_consent'] ); ?>
							onchange="this.form.submit()"
						>
						Samtycke
						<span class="mf-visually-hidden"> för <?php echo esc_html( $contact_person['name'] ); ?></span>
					</label>
					<noscript><button type="submit" class="mf-button-quiet">Spara</button></noscript>
				</form>
			<?php endif; ?>
		</div>
	</li>
	<?php
}

function iuc_mc_render_participations( $participations ) {
	$labels = array(
		'deltar' => 'Deltar',
		'anmald' => 'Anmäld',
	);
	?>
	<section class="mf-card" aria-labelledby="mf-participation-title">
		<h2 id="mf-participation-title" class="mf-card-title">Deltar i och anmäld till</h2>
				<?php if ( empty( $participations ) ) : ?>
			<p class="mf-empty">Inga projekt eller anmälningar ännu.</p>
		<?php else : ?>
		<ul class="mf-list mf-list-plain">
			<?php foreach ( $participations as $participation ) : ?>
				<li class="mf-row">
					<div class="mf-row-text">
						<p class="mf-row-name"><?php echo esc_html( $participation['title'] ); ?></p>
						<p class="mf-row-detail"><?php echo esc_html( $participation['detail'] ); ?></p>
					</div>
					<span class="mf-badge mf-badge-neutral"><?php echo esc_html( $labels[ $participation['status'] ] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
		<span class="mf-badge mf-badge-waiting mf-badge-standalone">kommer ksk senare</span>
	</section>
	<?php
}

function iuc_mc_render_member_preview( $visible_contact_persons ) {
	?>
	<section class="mf-card" aria-labelledby="mf-preview-contacts-title">
		<h2 id="mf-preview-contacts-title" class="mf-card-title">Kontaktpersoner</h2>
		<?php if ( empty( $visible_contact_persons ) ) : ?>
			<p class="mf-empty">
				Inga kontaktpersoner visas för andra medlemmar ännu. Det krävs samtycke från både
				företaget och personen.
			</p>
		<?php else : ?>
			<ul class="mf-list">
				<?php foreach ( $visible_contact_persons as $contact_person ) : ?>
					<li class="mf-row">
						<span class="mf-avatar" aria-hidden="true"><?php echo esc_html( iuc_mc_initials( $contact_person['name'] ) ); ?></span>
						<div class="mf-row-text">
							<p class="mf-row-name"><?php echo esc_html( $contact_person['name'] ); ?></p>
							<?php if ( $contact_person['job_title'] ) : ?>
								<p class="mf-row-detail"><?php echo esc_html( $contact_person['job_title'] ); ?></p>
							<?php endif; ?>
							<?php if ( $contact_person['email'] ) : ?>
								<a class="mf-row-link" href="<?php echo esc_url( 'mailto:' . $contact_person['email'] ); ?>"><?php echo esc_html( $contact_person['email'] ); ?></a>
							<?php endif; ?>
						</div>
						<?php if ( $contact_person['is_main_contact'] ) : ?>
							<span class="mf-badge mf-badge-main">Huvudkontakt</span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<?php
}

/* ------------------------------------------------------------------
 * Tar emot samtycket
 * ------------------------------------------------------------------ */

function iuc_mc_handle_company_consent() {
	$redirect_url = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect_url = remove_query_arg( array( 'samtycke', 'kontakt' ), $redirect_url );

	$nonce = isset( $_POST['iuc_company_consent_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['iuc_company_consent_nonce'] ) )
		: '';
	if ( ! wp_verify_nonce( $nonce, 'iuc_company_consent' ) ) {
		wp_safe_redirect( add_query_arg( 'samtycke', 'error', $redirect_url ) );
		exit;
	}

	// Företaget slås alltid upp från den inloggade användaren, aldrig från formuläret.
	$company           = iuc_mc_find_company_for_user( get_current_user_id() );
	$contact_person_id = isset( $_POST['contact_person'] ) ? absint( $_POST['contact_person'] ) : 0;
	$contact_person    = $contact_person_id ? get_post( $contact_person_id ) : null;

	$belongs_to_company = $company
		&& $contact_person
		&& IUC_MC_CONTACT_PERSON === $contact_person->post_type
		&& (int) get_post_meta( $contact_person_id, 'iuc_company_id', true ) === (int) $company->ID;

	// Samtycke kan ges först när IUC Syd har godkänt kontaktpersonen.
	if ( ! $belongs_to_company || 'approved' !== get_post_meta( $contact_person_id, 'iuc_approval_status', true ) ) {
		wp_safe_redirect( add_query_arg( 'samtycke', 'error', $redirect_url ) );
		exit;
	}

	$company_consent = isset( $_POST['company_consent'] ) && '1' === $_POST['company_consent'] ? '1' : '0';
	update_post_meta( $contact_person_id, 'iuc_company_consent', $company_consent );

	wp_safe_redirect( add_query_arg( 'samtycke', 'saved', $redirect_url ) );
	exit;
}
add_action( 'admin_post_iuc_company_consent', 'iuc_mc_handle_company_consent' );