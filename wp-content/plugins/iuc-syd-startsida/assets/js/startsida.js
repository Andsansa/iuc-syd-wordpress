document.addEventListener('DOMContentLoaded', () => {
	const topicChips = document.querySelectorAll('.iuc-chip');
	const clearFiltersBtn = document.getElementById('iuc-clear-filters');
	const forumPosts = document.querySelectorAll('.iuc-post-card');
	const eventItems = document.querySelectorAll('.iuc-event-item');
	const searchInput = document.getElementById('iuc-global-search-input');
	const searchBtn = document.getElementById('iuc-global-search-btn');
	const toast = document.getElementById('iuc-toast');

	function showToast(message) {
		if (!toast) return;
		toast.textContent = message;
		toast.hidden = false;
		setTimeout(() => {
			toast.hidden = true;
		}, 4000);
	}

	// 1. Ämnesfiltrering + Rensa filter (Samirs feedback)
	function applyTopicFilter(selectedTopic) {
		topicChips.forEach((chip) => {
			chip.classList.toggle('is-active', chip.dataset.filterTopic === selectedTopic);
		});

		forumPosts.forEach((post) => {
			const match = selectedTopic === 'all' || post.dataset.topic === selectedTopic;
			post.style.display = match ? '' : 'none';
		});

		eventItems.forEach((item) => {
			const match = selectedTopic === 'all' || item.dataset.topic === selectedTopic;
			item.style.display = match ? '' : 'none';
		});

		if (clearFiltersBtn) {
			clearFiltersBtn.hidden = selectedTopic === 'all';
		}
	}

	topicChips.forEach((chip) => {
		chip.addEventListener('click', () => {
			applyTopicFilter(chip.dataset.filterTopic);
		});
	});

	if (clearFiltersBtn) {
		clearFiltersBtn.addEventListener('click', () => {
			applyTopicFilter('all');
			if (searchInput) searchInput.value = '';
			showToast('Alla filter har rensats.');
		});
	}

	// 2. Global snabbsök på startsidan (Kosovars feedback)
	function handleSearch() {
		const term = (searchInput?.value || '').trim().toLowerCase();
		if (!term) {
			applyTopicFilter('all');
			return;
		}

		let hits = 0;
		forumPosts.forEach((post) => {
			const visible = post.textContent.toLowerCase().includes(term);
			post.style.display = visible ? '' : 'none';
			if (visible) hits++;
		});

		if (clearFiltersBtn) clearFiltersBtn.hidden = false;
		showToast(`Visar ${hits} träffar för "${term}".`);
	}

	if (searchBtn) searchBtn.addEventListener('click', handleSearch);
	if (searchInput) {
		searchInput.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') handleSearch();
		});
	}

	// 3. Hopfällbara kommentarer som standard
	document.querySelectorAll('.iuc-toggle-comments').forEach((btn) => {
		btn.addEventListener('click', () => {
			const target = document.getElementById(btn.dataset.target);
			if (!target) return;
			const isExpanded = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', String(!isExpanded));
			target.hidden = isExpanded;
			btn.textContent = isExpanded
				? btn.textContent.replace('Dölj', 'Visa')
				: btn.textContent.replace('Visa', 'Dölj');
		});
	});

	// 4. Modal för att skapa nytt inlägg
	const modal = document.getElementById('iuc-new-post-modal');
	document.querySelectorAll('[data-open-modal]').forEach((trigger) => {
		trigger.addEventListener('click', () => {
			if (modal) modal.hidden = false;
		});
	});

	document.querySelectorAll('[data-close-modal]').forEach((closer) => {
		closer.addEventListener('click', () => {
			if (modal) modal.hidden = true;
		});
	});

	// 5. Förhandsvisning vid publicering av nytt inlägg i skalet
	const newPostForm = document.getElementById('iuc-new-post-form');
	const forumFeed = document.getElementById('iuc-forum-feed');

	if (newPostForm && forumFeed) {
		newPostForm.addEventListener('submit', (e) => {
			e.preventDefault();
			const postType = document.getElementById('iuc-post-type').value;
			const topic = document.getElementById('iuc-post-topic').value;
			const content = document.getElementById('iuc-post-content').value.trim();

			if (!content) return;

			const article = document.createElement('article');
			article.className = 'iuc-post-card';
			article.dataset.topic = topic;
			article.innerHTML = `
				<header class="iuc-post-card__header">
					<div class="iuc-post-author">
						<span class="iuc-avatar iuc-avatar--petrol">DU</span>
						<div>
							<strong>Ditt företag</strong>
							<a href="#mitt-foretag" class="iuc-company-link">Höglunds Verkstad AB</a>
						</div>
					</div>
					<span class="iuc-post-time">Just nu</span>
				</header>
				<div class="iuc-post-tags">
					<span class="iuc-tag iuc-tag--type"></span>
					<span class="iuc-tag iuc-tag--topic"></span>
				</div>
				<p class="iuc-post-card__body"></p>
			`;
			article.querySelector('.iuc-tag--type').textContent = postType;
			article.querySelector('.iuc-tag--topic').textContent = topic;
			article.querySelector('.iuc-post-card__body').textContent = content;

			forumFeed.prepend(article);
			newPostForm.reset();
			if (modal) modal.hidden = true;
			showToast('Ditt inlägg har publicerats i Nätverk & Forum!');
		});
	}
});