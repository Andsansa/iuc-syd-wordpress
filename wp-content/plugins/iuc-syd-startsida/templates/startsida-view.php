<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$initials = '';
foreach ( explode( ' ', $data['user_name'] ) as $part ) {
	$initials .= strtoupper( mb_substr( $part, 0, 1 ) );
}
?>

<div class="iuc-portal-wrapper">

	<!-- TOPPNAVIGATION -->
	<header class="iuc-topbar">
		<div class="iuc-topbar__inner">
			<!-- Klickbar logotyp enligt användartest -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="iuc-logo" aria-label="Gå till startsidan för IUC Syd Medlemsportal">
				<span class="iuc-logo__circle">IUC</span>
				<span class="iuc-logo__text">
					<strong>SYD</strong>
					<small>MEDLEMSPORTAL</small>
				</span>
			</a>

			<!-- Försvenskad och tydlig huvudmeny -->
			<nav class="iuc-nav" aria-label="Huvudmeny">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="iuc-nav__link is-active">Start</a>
				<a href="#natverk-forum" class="iuc-nav__link">Nätverk &amp; Forum</a>
				<a href="#medlemsforetag" class="iuc-nav__link">Medlemsföretag</a>
				<a href="#aktuellt-event" class="iuc-nav__link">Aktuellt &amp; Event</a>
				<a href="#mitt-foretag" class="iuc-nav__link">Mitt företag</a>
			</nav>

			<!-- Personlig profil / Mitt företag uppe till höger (efterfrågat i användartest) -->
			<div class="iuc-topbar__actions">
				<a href="#personlig-kontakt" class="iuc-btn iuc-btn--outline-light">
					Personlig kontakt med IUC
				</a>
				<a href="#mitt-foretag" class="iuc-user-badge" title="Hantera ditt företag och din profil">
					<span class="iuc-avatar"><?php echo esc_html( mb_substr( $initials, 0, 2 ) ); ?></span>
					<span class="iuc-user-badge__info">
						<strong><?php echo esc_html( $data['user_name'] ); ?></strong>
						<small><?php echo esc_html( $data['company_name'] ); ?></small>
					</span>
				</a>
			</div>
		</div>
	</header>

	<!-- HERO / VÄLKOMST- & SNABBSÖKSSEKTION -->
	<section class="iuc-hero">
		<div class="iuc-container iuc-hero__grid">
			<div class="iuc-hero__content">
				<span class="iuc-eyebrow">VÄLKOMMEN TILLBAKA, <?php echo esc_html( mb_strtoupper( $data['first_name'] ) ); ?></span>
				<h1 class="iuc-hero__title">Samlad kunskap och samverkan för skånsk industri</h1>
				<p class="iuc-hero__lead">
					Till vänster hittar du nyheter, stöd och aktiviteter från <strong>IUC Syd</strong>.
					Till höger möter du <strong>andra medlemsföretag</strong> för att bolla utmaningar och hitta samarbeten.
				</p>

				<!-- Global snabbsök för företag & ämnen direkt på startsidan (Kosovars feedback) -->
				<form class="iuc-global-search" role="search" onsubmit="return false;">
					<label for="iuc-global-search-input" class="screen-reader-text">Sök medlemsföretag, ämne eller inlägg</label>
					<input
						type="search"
						id="iuc-global-search-input"
						class="iuc-global-search__input"
						placeholder="Sök medlemsföretag, t.ex. Automation, Plåtbearbetning..."
					>
					<button type="button" id="iuc-global-search-btn" class="iuc-btn iuc-btn--drivkraft">
						Sök i portalen
					</button>
				</form>
			</div>

			<!-- Visuellt industri-kort med bild + snabbknapp (efterfrågat av Helen & Linda) -->
			<aside class="iuc-hero__card">
				<div class="iuc-hero__card-body">
					<h2 class="iuc-gold-heading">Behöver ni bolla en utmaning?</h2>
					<p>Ställ en fråga till nätverket eller skicka ett personligt ärende direkt till IUC Syds rådgivare.</p>
					<div class="iuc-hero__card-actions">
						<button type="button" class="iuc-btn iuc-btn--drivkraft" data-open-modal="iuc-new-post-modal">
							+ Skapa inlägg i nätverket
						</button>
						<a href="#personlig-kontakt" class="iuc-link-light">
							Skicka personligt till IUC Syd &rarr;
						</a>
					</div>
				</div>
			</aside>
		</div>
	</section>

	<!-- HUVUDINNEHÅLL: TYDLIG TVÅSPALTS-STRUKTUR -->
	<main class="iuc-container iuc-main-grid">

		<!-- VÄNSTER SPALT: FRÅN IUC SYD (Officiell avsändare i Petrol & Syre) -->
		<section class="iuc-col-iuc" aria-labelledby="heading-fran-iuc">
			<div class="iuc-section-banner iuc-section-banner--iuc">
				<div>
					<span class="iuc-sender-tag iuc-sender-tag--iuc">OFFICIELL INFORMATION</span>
					<h2 id="heading-fran-iuc">Från IUC Syd</h2>
				</div>
				<span class="iuc-section-banner__sub">Nyheter, aktiviteter &amp; stöd</span>
			</div>

			<!-- Fäst överst -->
			<article class="iuc-card iuc-card--featured">
				<div class="iuc-card__meta">
					<span class="iuc-badge iuc-badge--petrol"><?php echo esc_html( $data['featured_iuc']['badge'] ); ?></span>
					<span class="iuc-deadline"><?php echo esc_html( $data['featured_iuc']['deadline'] ); ?></span>
				</div>
				<h3><?php echo esc_html( $data['featured_iuc']['title'] ); ?></h3>
				<p><?php echo esc_html( $data['featured_iuc']['excerpt'] ); ?></p>
				<a href="<?php echo esc_url( $data['featured_iuc']['cta_url'] ); ?>" class="iuc-btn iuc-btn--petrol-drivkraft">
					<?php echo esc_html( $data['featured_iuc']['cta_text'] ); ?>
				</a>
			</article>

			<!-- Aktuellt & Event -->
			<div class="iuc-card iuc-card--syre" id="aktuellt-event">
				<div class="iuc-card__header">
					<h3>Aktuellt &amp; Event att anmäla sig till</h3>
					<a href="#alla-event" class="iuc-text-link">Alla event &rarr;</a>
				</div>
				<ul class="iuc-event-list">
					<?php foreach ( $data['events'] as $event ) : ?>
						<li class="iuc-event-item" data-topic="<?php echo esc_attr( $event['topic'] ); ?>">
							<div class="iuc-date-box" aria-hidden="true">
								<strong><?php echo esc_html( $event['day'] ); ?></strong>
								<span><?php echo esc_html( $event['month'] ); ?></span>
							</div>
							<div class="iuc-event-item__info">
								<span class="iuc-topic-pill"><?php echo esc_html( $event['topic'] ); ?></span>
								<h4><?php echo esc_html( $event['title'] ); ?></h4>
								<p class="iuc-muted"><?php echo esc_html( $event['location'] ); ?> · Anmälan via Ungapped</p>
							</div>
							<a href="<?php echo esc_url( $event['url'] ); ?>" class="iuc-btn iuc-btn--small-petrol">
								Anmäl
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Medlemsnyheter -->
			<div class="iuc-card">
				<div class="iuc-card__header">
					<h3>Medlemsnyheter</h3>
					<a href="#alla-nyheter" class="iuc-text-link">Alla nyheter &rarr;</a>
				</div>
				<ul class="iuc-news-list">
					<?php foreach ( $data['news_items'] as $news ) : ?>
						<li class="iuc-news-item">
							<time class="iuc-news-item__date"><?php echo esc_html( $news['date'] ); ?></time>
							<div>
								<h4><a href="<?php echo esc_url( $news['url'] ); ?>"><?php echo esc_html( $news['title'] ); ?></a></h4>
								<p><?php echo esc_html( $news['excerpt'] ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Stöd och erbjudanden -->
			<div class="iuc-card iuc-card--syre">
				<span class="iuc-badge iuc-badge--outline">MEDLEMSFÖRMÅN</span>
				<h3>Förtur i utvecklingsprojekt &amp; rådgivning</h3>
				<p>Som medlem i IUC Syd har ni förtur till behovsanalyser, coachning och samverkansprojekt inom produktion och omställning.</p>
				<a href="#stod-erbjudanden" class="iuc-text-link">Se ert medlemserbjudande &rarr;</a>
			</div>
		</section>

		<!-- HÖGER SPALT: NÄTVERK & FORUM (Medlemsföretagens yta i Vitt & Platina) -->
		<section class="iuc-col-network" id="natverk-forum" aria-labelledby="heading-natverk">
			<div class="iuc-section-banner iuc-section-banner--network">
				<div>
					<span class="iuc-sender-tag iuc-sender-tag--member">MEDLEMSNÄTVERK</span>
					<h2 id="heading-natverk">Nätverk &amp; Forum</h2>
				</div>
				<button type="button" class="iuc-btn iuc-btn--petrol-drivkraft" data-open-modal="iuc-new-post-modal">
					+ Skriv inlägg
				</button>
			</div>

			<!-- Filter och ämnesval med "Rensa filter" (Samirs feedback) -->
			<div class="iuc-filter-bar" aria-label="Filtrera inlägg efter ämne">
				<div class="iuc-filter-bar__top">
					<span class="iuc-filter-label">Filtrera på ämne:</span>
					<button type="button" id="iuc-clear-filters" class="iuc-clear-btn" hidden>
						Rensa filter &times;
					</button>
				</div>
				<div class="iuc-chip-group">
					<button type="button" class="iuc-chip is-active" data-filter-topic="all">Alla ämnen</button>
					<?php foreach ( $data['amnen'] as $amne ) : ?>
						<button type="button" class="iuc-chip" data-filter-topic="<?php echo esc_attr( $amne ); ?>">
							<?php echo esc_html( $amne ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Flöde med inlägg från medlemsföretag -->
			<div class="iuc-feed" id="iuc-forum-feed">
				<?php foreach ( $data['forum_posts'] as $post ) : ?>
					<article class="iuc-post-card" data-topic="<?php echo esc_attr( $post['topic'] ); ?>">
						<header class="iuc-post-card__header">
							<div class="iuc-post-author">
								<span class="iuc-avatar iuc-avatar--petrol"><?php echo esc_html( $post['initials'] ); ?></span>
								<div>
									<strong><?php echo esc_html( $post['author'] ); ?></strong>
									<!-- Klickbar länk direkt till företagskortet (Kosovars feedback) -->
									<a href="<?php echo esc_url( $post['company_url'] ); ?>" class="iuc-company-link">
										<?php echo esc_html( $post['company'] ); ?>
									</a>
								</div>
							</div>
							<span class="iuc-post-time"><?php echo esc_html( $post['time'] ); ?></span>
						</header>

						<div class="iuc-post-tags">
							<span class="iuc-tag iuc-tag--type"><?php echo esc_html( $post['post_type'] ); ?></span>
							<span class="iuc-tag iuc-tag--topic"><?php echo esc_html( $post['topic'] ); ?></span>
							<?php if ( $post['is_iuc_reply'] ) : ?>
								<span class="iuc-tag iuc-tag--iuc-answered">Besvarad av IUC Syd</span>
							<?php endif; ?>
						</div>

						<p class="iuc-post-card__body"><?php echo esc_html( $post['body'] ); ?></p>

						<!-- Hopfällbara kommentarer som standard (Samirs feedback) -->
						<div class="iuc-comments-section">
							<button
								type="button"
								class="iuc-toggle-comments"
								aria-expanded="false"
								data-target="comments-<?php echo esc_attr( $post['id'] ); ?>"
							>
								Visa <?php echo count( $post['comments'] ); ?> kommentarer
							</button>

							<div id="comments-<?php echo esc_attr( $post['id'] ); ?>" class="iuc-comments-list" hidden>
								<?php foreach ( $post['comments'] as $comment ) : ?>
									<div class="iuc-comment <?php echo $comment['is_iuc'] ? 'is-iuc-comment' : ''; ?>">
										<div class="iuc-comment__head">
											<strong><?php echo esc_html( $comment['author'] ); ?></strong>
											<span>· <?php echo esc_html( $comment['company'] ); ?></span>
										</div>
										<p><?php echo esc_html( $comment['text'] ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<!-- Välkomna nya medlemsföretag (Kristines feedback) -->
			<div class="iuc-card iuc-card--platina" id="medlemsforetag">
				<div class="iuc-card__header">
					<h3>Nya medlemsföretag i nätverket</h3>
					<a href="#alla-medlemmar" class="iuc-text-link">Alla medlemsföretag &rarr;</a>
				</div>
				<ul class="iuc-member-list">
					<?php foreach ( $data['new_members'] as $member ) : ?>
						<li class="iuc-member-item">
							<div>
								<strong><?php echo esc_html( $member['name'] ); ?></strong>
								<span class="iuc-muted"> · <?php echo esc_html( $member['city'] ); ?> (<?php echo esc_html( $member['industry'] ); ?>)</span>
							</div>
							<a href="<?php echo esc_url( $member['url'] ); ?>" class="iuc-text-link">Visa kort &rarr;</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Personlig kontakt med IUC Syd -->
			<div class="iuc-card iuc-card--syre" id="personlig-kontakt">
				<h3>Ett behov som bara ska till IUC Syd?</h3>
				<p>Vill ni diskutera en utmaning konfidentiellt innan ni delar den öppet? Skicka ett personligt ärende – endast IUC Syds rådgivare ser meddelandet.</p>
				<a href="#personligt-formular" class="iuc-btn iuc-btn--petrol-drivkraft">
					Skicka personligt meddelande till IUC Syd
				</a>
			</div>
		</section>

	</main>

	<!-- MODAL FÖR ATT SKAPA INLÄGG (Lindas feedback om tydlig pop-up & synlighetsinfo) -->
	<div id="iuc-new-post-modal" class="iuc-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
		<div class="iuc-modal__backdrop" data-close-modal></div>
		<div class="iuc-modal__dialog">
			<header class="iuc-modal__header">
				<h2 id="modal-title">Skapa inlägg i Nätverk &amp; Forum</h2>
				<button type="button" class="iuc-modal__close" data-close-modal aria-label="Stäng fönster">&times;</button>
			</header>

			<form id="iuc-new-post-form" class="iuc-modal__body">
				<div class="iuc-form-row">
					<label for="iuc-post-type">Vad vill ni göra?</label>
					<select id="iuc-post-type" name="post_type" required>
						<option value="Fråga till nätverket">Ställa en fråga / få råd från andra företag</option>
						<option value="Söker samarbete">Söka samarbetspartner kring en utmaning</option>
						<option value="Fråga till IUC Syd">Ställa en öppen fråga till IUC Syd</option>
					</select>
				</div>

				<div class="iuc-form-row">
					<label for="iuc-post-topic">Ämnesområde</label>
					<select id="iuc-post-topic" name="topic" required>
						<?php foreach ( $data['amnen'] as $amne ) : ?>
							<option value="<?php echo esc_attr( $amne ); ?>"><?php echo esc_html( $amne ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="iuc-form-row">
					<label for="iuc-post-content">Beskriv er fråga eller ert behov</label>
					<textarea id="iuc-post-content" name="content" rows="4" required placeholder="Beskriv kort vad ni arbetar med och vilken typ av erfarenhet eller samarbete ni söker..."></textarea>
				</div>

				<!-- Tydlig synlighetsförklaring (efterfrågat av Linda & Pia Jape) -->
				<div class="iuc-visibility-notice">
					<strong>Vem ser inlägget?</strong> Alla godkända medlemsföretag och IUC Syd kan läsa och kommentera. Vill ni bara nå IUC Syd, använd <em>Personlig kontakt med IUC Syd</em>.
				</div>

				<footer class="iuc-modal__footer">
					<button type="button" class="iuc-btn iuc-btn--outline-petrol" data-close-modal>Avbryt</button>
					<button type="submit" class="iuc-btn iuc-btn--petrol-drivkraft">Publicera i nätverket</button>
				</footer>
			</form>
		</div>
	</div>

	<!-- SYSTEMFEEDBACK / TOAST (efterfrågat i användartesterna) -->
	<div id="iuc-toast" class="iuc-toast" role="status" aria-live="polite" hidden></div>

</div>

