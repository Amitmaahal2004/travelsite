<?php
$sec = static fn(string $key): ?array => $sections[$key] ?? null;
?>

<?php if ($hero = $sec('hero')): ?>
<section class="hero-cinematic">
    <div class="hero-bg-media">
        <img src="<?= e(upload_url($hero['background_image'] ?: 'destinations/hero-fjord.jpg')) ?>" alt="<?= e($hero['title']) ?>">
    </div>
    <div class="hero-scrim"></div>
    <div class="container-editorial hero-content">
        <div class="hero-eyebrow"><?= e($hero['badge_text'] ?: brand('website_name')) ?></div>
        <h1 class="hero-title"><?= e($hero['title']) ?></h1>
        <p class="hero-subtitle"><?= e($hero['subtitle']) ?></p>
        <div class="hero-cta-row">
            <a href="<?= url($hero['cta_link'] ?: '/destinations') ?>" class="btn-brand btn-brand-accent">
                <?= e($hero['cta_text'] ?: 'Explore Destinations') ?> <?= svg_icon('arrow-right', 16) ?>
            </a>
            <a href="<?= url('/custom-enquiry') ?>" class="btn-brand btn-brand-outline-light">
                Bespoke Atelier Enquiry
            </a>
        </div>

        <form action="<?= url('/packages') ?>" method="GET" class="hero-search-dock">
            <div class="search-field">
                <label for="hero-dest">Destination</label>
                <select id="hero-dest" name="destination">
                    <option value="">Anywhere Extraordinary</option>
                    <?php foreach ($allDestinations as $ad): ?>
                        <option value="<?= e($ad['slug']) ?>"><?= e($ad['name']) ?> (<?= e($ad['country_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-field">
                <label for="hero-cat">Travel Style</label>
                <select id="hero-cat" name="category">
                    <option value="">All Styles</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-field">
                <label for="hero-q">Keyword or Highlight</label>
                <input id="hero-q" type="text" name="q" placeholder="Fjord, Safari, Lagoon...">
            </div>
            <div class="search-field">
                <label for="hero-budget">Max Budget</label>
                <select id="hero-budget" name="max_price">
                    <option value="">Flexible</option>
                    <option value="4000">Up to $4,000</option>
                    <option value="5000">Up to $5,000</option>
                    <option value="6500">Up to $6,500</option>
                </select>
            </div>
            <button type="submit" class="btn-brand btn-brand-primary" style="white-space:nowrap;">
                <?= svg_icon('search', 16) ?> Find Voyages
            </button>
        </form>
    </div>
</section>
<?php endif; ?>

<?php if ($destSec = $sec('featured_destinations')): ?>
<section class="section-pad">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($destSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($destSec['title']) ?></h2>
                <p class="section-lead"><?= e($destSec['subtitle']) ?></p>
            </div>
            <a href="<?= url($destSec['cta_link'] ?: '/destinations') ?>" class="btn-brand btn-brand-outline">
                <?= e($destSec['cta_text'] ?: 'View All Destinations') ?> <?= svg_icon('arrow-right', 16) ?>
            </a>
        </div>

        <div class="destinations-bento reveal">
            <?php foreach ($featuredDestinations as $i => $dest): ?>
                <a href="<?= url('/destination/' . $dest['slug']) ?>" class="dest-tile <?= $i === 0 ? 'is-lead' : '' ?>">
                    <img src="<?= e(upload_url($dest['hero_image'])) ?>" alt="<?= e($dest['name']) ?>">
                    <div class="dest-tile-scrim"></div>
                    <?php if ((float) $dest['starting_price'] > 0): ?>
                        <span class="dest-tile-price">From <?= e(money($dest['starting_price'])) ?></span>
                    <?php endif; ?>
                    <div class="dest-tile-body">
                        <div class="dest-tile-loc"><?= e($dest['location_label']) ?></div>
                        <h3><?= e($dest['name']) ?></h3>
                        <?php if ($i === 0): ?>
                            <p><?= e($dest['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($catSec = $sec('travel_categories')): ?>
<section class="section-pad band-sand">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($catSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($catSec['title']) ?></h2>
                <p class="section-lead"><?= e($catSec['subtitle']) ?></p>
            </div>
            <a href="<?= url($catSec['cta_link'] ?: '/categories') ?>" class="btn-brand btn-brand-outline">
                <?= e($catSec['cta_text'] ?: 'Browse Categories') ?> <?= svg_icon('arrow-right', 16) ?>
            </a>
        </div>

        <div class="categories-strip reveal">
            <?php foreach (array_slice($categories, 0, 7) as $cat): ?>
                <a href="<?= url('/category/' . $cat['slug']) ?>" class="category-card-editorial">
                    <img src="<?= e(upload_url($cat['image'])) ?>" alt="<?= e($cat['name']) ?>">
                    <div class="category-card-inner">
                        <div class="category-icon-badge"><?= svg_icon($cat['icon'] ?: 'compass', 18) ?></div>
                        <h3><?= e($cat['name']) ?></h3>
                        <span><?= (int) $cat['package_count'] ?> curated <?= (int) $cat['package_count'] === 1 ? 'journey' : 'journeys' ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($pkgSec = $sec('featured_packages')): ?>
<section class="section-pad">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($pkgSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($pkgSec['title']) ?></h2>
                <p class="section-lead"><?= e($pkgSec['subtitle']) ?></p>
            </div>
            <a href="<?= url($pkgSec['cta_link'] ?: '/packages') ?>" class="btn-brand btn-brand-outline">
                <?= e($pkgSec['cta_text'] ?: 'Explore All Packages') ?> <?= svg_icon('arrow-right', 16) ?>
            </a>
        </div>

        <div class="packages-grid reveal">
            <?php foreach ($featuredPackages as $pkg):
                $inWish = in_array((int) $pkg['id'], $wishlistIds ?? [], true);
                $effectivePrice = $pkg['discount_price'] ?: $pkg['base_price'];
            ?>
                <article class="pkg-card">
                    <div class="pkg-media">
                        <a href="<?= url('/package/' . $pkg['slug']) ?>">
                            <img src="<?= e(upload_url($pkg['featured_image'])) ?>" alt="<?= e($pkg['title']) ?>">
                        </a>
                        <span class="pkg-badge-top"><?= (int) $pkg['duration_days'] ?> Days / <?= (int) $pkg['duration_nights'] ?> Nights</span>
                        <button type="button"
                                class="pkg-wish-btn <?= $inWish ? 'active' : '' ?>"
                                data-wishlist-btn
                                data-item-type="package"
                                data-item-id="<?= (int) $pkg['id'] ?>"
                                data-endpoint="<?= url('/api/wishlist/toggle') ?>"
                                aria-label="Save to wishlist">
                            <?= svg_icon('heart', 17) ?>
                        </button>
                    </div>
                    <div class="pkg-body">
                        <div class="pkg-meta-row">
                            <span><?= e($pkg['destination_name']) ?> &bull; <?= e($pkg['category_name']) ?></span>
                            <span>&#9733; <?= e(number_format((float) $pkg['rating'], 1)) ?></span>
                        </div>
                        <h3><a href="<?= url('/package/' . $pkg['slug']) ?>"><?= e($pkg['title']) ?></a></h3>
                        <p class="pkg-excerpt"><?= e($pkg['subtitle']) ?></p>
                        <div class="pkg-footer">
                            <div class="pkg-price">
                                <small>Per traveller from</small>
                                <?php if (!empty($pkg['discount_price']) && (float) $pkg['discount_price'] < (float) $pkg['base_price']): ?>
                                    <del><?= e(money($pkg['base_price'])) ?></del>
                                <?php endif; ?>
                                <strong><?= e(money($effectivePrice)) ?></strong>
                            </div>
                            <a href="<?= url('/package/' . $pkg['slug']) ?>" class="btn-brand btn-brand-primary btn-sm-pill">
                                View Itinerary
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($whySec = $sec('why_choose_us')): ?>
<section class="section-pad band-forest">
    <div class="container-editorial">
        <div class="why-grid">
            <div class="reveal">
                <span class="section-eyebrow"><?= e($whySec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($whySec['title']) ?></h2>
                <p class="section-lead"><?= e($whySec['subtitle']) ?></p>

                <div class="pillars-grid">
                    <div class="pillar-box">
                        <div class="pillar-num">01</div>
                        <h4>Firsthand Vetted Sanctuaries</h4>
                        <p>Every lodge, private yacht, and sanctuary is personally inspected by our regional leads.</p>
                    </div>
                    <div class="pillar-box">
                        <div class="pillar-num">02</div>
                        <h4>Dedicated Specialist</h4>
                        <p>One named Journey Architect oversees your itinerary from enquiry to return home.</p>
                    </div>
                    <div class="pillar-box">
                        <div class="pillar-num">03</div>
                        <h4>Real-Time Trip Portal</h4>
                        <p>Track confirmations, traveller manifests, payments, and official PDF invoices anytime.</p>
                    </div>
                    <div class="pillar-box">
                        <div class="pillar-num">04</div>
                        <h4>Private-Access Curation</h4>
                        <p>After-hours museum openings, conservation naturalists, and local culinary hosts.</p>
                    </div>
                </div>
            </div>

            <div class="framed-portrait reveal">
                <img src="<?= e(upload_url($whySec['background_image'] ?: 'destinations/kyoto-sanctuary.jpg')) ?>" alt="Why choose us">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($customSec = $sec('custom_experience')): ?>
<section class="section-pad">
    <div class="container-editorial">
        <div class="atelier-grid">
            <div class="atelier-image-wrap reveal">
                <img src="<?= e(upload_url($customSec['background_image'] ?: 'media/custom-atelier.jpg')) ?>" alt="<?= e($customSec['title']) ?>">
            </div>
            <div class="reveal">
                <span class="section-eyebrow"><?= e($customSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($customSec['title']) ?></h2>
                <p class="section-lead"><?= e($customSec['subtitle']) ?></p>
                <div class="atelier-chips">
                    <a href="<?= url('/custom-enquiry?style=Honeymoon+%26+Romance') ?>" class="atelier-chip">Honeymoon &amp; Romance</a>
                    <a href="<?= url('/custom-enquiry?style=Multi-Generational+Villa') ?>" class="atelier-chip">Multi-Generational Villa</a>
                    <a href="<?= url('/custom-enquiry?style=Private+Aviation+Safari') ?>" class="atelier-chip">Private Aviation Safari</a>
                    <a href="<?= url('/custom-enquiry?style=Culinary+%26+Wine') ?>" class="atelier-chip">Culinary &amp; Wine</a>
                    <a href="<?= url('/custom-enquiry?style=Sabbatical+Expedition') ?>" class="atelier-chip">Sabbatical Expedition</a>
                </div>
                <a href="<?= url($customSec['cta_link'] ?: '/custom-enquiry') ?>" class="btn-brand btn-brand-primary">
                    <?= e($customSec['cta_text'] ?: 'Request Custom Itinerary') ?> <?= svg_icon('arrow-right', 16) ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($howSec = $sec('how_it_works')): ?>
<section class="section-pad band-sand">
    <div class="container-editorial">
        <div class="section-head centered reveal">
            <span class="section-eyebrow"><?= e($howSec['badge_text']) ?></span>
            <h2 class="section-title"><?= e($howSec['title']) ?></h2>
            <p class="section-lead"><?= e($howSec['subtitle']) ?></p>
        </div>
        <div class="steps-rail reveal">
            <div class="step-item">
                <div class="step-index">01</div>
                <h4>Select Your Blueprint</h4>
                <p>Browse signature itineraries or initiate a custom tour request tailored to your dates.</p>
            </div>
            <div class="step-item">
                <div class="step-index">02</div>
                <h4>Submit Booking Request</h4>
                <p>Enter traveller profiles, special preferences, and receive an instant booking reference.</p>
            </div>
            <div class="step-item">
                <div class="step-index">03</div>
                <h4>Specialist Assignment</h4>
                <p>A dedicated Travel Specialist confirms lodge inventory, transfers, and milestone invoices.</p>
            </div>
            <div class="step-item">
                <div class="step-index">04</div>
                <h4>Seamless Departure</h4>
                <p>Download PDF documents, track status updates live, and travel with 24/7 concierge care.</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (($testSec = $sec('testimonials')) && !empty($testimonials)): ?>
<section class="section-pad band-navy">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($testSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($testSec['title']) ?></h2>
            </div>
            <a href="<?= url($testSec['cta_link'] ?: '/testimonials') ?>" class="btn-brand btn-brand-outline-light">
                <?= e($testSec['cta_text'] ?: 'Read All Stories') ?>
            </a>
        </div>
        <div class="testimonials-grid reveal">
            <?php foreach (array_slice($testimonials, 0, 3) as $t): ?>
                <div class="testimonial-card">
                    <div>
                        <div class="quote-glyph">&ldquo;</div>
                        <p class="testimonial-quote"><?= e($t['quote']) ?></p>
                    </div>
                    <div class="testimonial-author">
                        <strong><?= e($t['customer_name']) ?></strong>
                        <span><?= e($t['customer_location']) ?> &bull; <?= e($t['trip_taken'] ?: $t['package_title']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (($offerSec = $sec('seasonal_offers')) && !empty($seasonalOffers)): ?>
<section class="section-pad">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($offerSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($offerSec['title']) ?></h2>
                <p class="section-lead"><?= e($offerSec['subtitle']) ?></p>
            </div>
            <a href="<?= url('/packages?offer=1') ?>" class="btn-brand btn-brand-outline">All Seasonal Privileges</a>
        </div>

        <div class="offers-grid reveal">
            <?php foreach (array_slice($seasonalOffers, 0, 2) as $offer): ?>
                <div class="offer-banner">
                    <img src="<?= e(upload_url($offer['banner_image'] ?: 'destinations/hero-fjord.jpg')) ?>" alt="<?= e($offer['title']) ?>">
                    <div class="offer-banner-content">
                        <span class="badge-pill" style="background:var(--brand-accent);color:#111;">
                            <?= $offer['discount_type'] === 'percentage' ? ((float) $offer['discount_value'] . '% Privilege') : (money($offer['discount_value']) . ' Credit') ?>
                        </span>
                        <h3 style="font-size:1.85rem;color:#fff;margin-top:12px;"><?= e($offer['title']) ?></h3>
                        <p style="font-size:0.92rem;color:rgba(255,255,255,0.85);margin:0;"><?= e($offer['description']) ?></p>
                        <div>
                            <button type="button" class="promo-code-chip" data-copy-code="<?= e($offer['code']) ?>">
                                Code: <?= e($offer['code']) ?> &bull; Click to Copy
                            </button>
                        </div>
                        <a href="<?= url($offer['package_slug'] ? '/book/' . $offer['package_slug'] : '/packages?offer=1') ?>" class="btn-brand btn-brand-accent btn-sm-pill">
                            Reserve With Offer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (($blogSec = $sec('blog')) && !empty($blogPosts)): ?>
<section class="section-pad-sm">
    <div class="container-editorial">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><?= e($blogSec['badge_text']) ?></span>
                <h2 class="section-title"><?= e($blogSec['title']) ?></h2>
            </div>
            <a href="<?= url($blogSec['cta_link'] ?: '/blog') ?>" class="btn-brand btn-brand-outline">
                <?= e($blogSec['cta_text'] ?: 'Read The Journal') ?>
            </a>
        </div>

        <div class="blog-editorial-grid reveal">
            <?php $leadPost = $blogPosts[0]; ?>
            <a href="<?= url('/blog/' . $leadPost['slug']) ?>" class="blog-lead-card">
                <img src="<?= e(upload_url($leadPost['featured_image'])) ?>" alt="<?= e($leadPost['title']) ?>">
                <div class="blog-lead-content">
                    <span class="section-eyebrow" style="color:var(--brand-accent);"><?= e($leadPost['category']) ?> &bull; <?= e($leadPost['read_time']) ?></span>
                    <h3><?= e($leadPost['title']) ?></h3>
                    <p style="color:rgba(255,255,255,0.82);margin:0;"><?= e($leadPost['excerpt']) ?></p>
                </div>
            </a>

            <div class="blog-side-stack">
                <?php foreach (array_slice($blogPosts, 1, 2) as $sp): ?>
                    <a href="<?= url('/blog/' . $sp['slug']) ?>" class="blog-side-card">
                        <img src="<?= e(upload_url($sp['featured_image'])) ?>" alt="<?= e($sp['title']) ?>">
                        <div>
                            <span class="section-eyebrow" style="margin-bottom:4px;"><?= e($sp['category']) ?> &bull; <?= e($sp['read_time']) ?></span>
                            <h4><?= e($sp['title']) ?></h4>
                            <p style="font-size:0.86rem;color:var(--ink-muted);margin:0;"><?= e($sp['excerpt']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($newsSec = $sec('newsletter')): ?>
<section class="section-pad-sm" style="padding-bottom:84px;">
    <div class="container-editorial">
        <div class="newsletter-box reveal">
            <div>
                <span class="section-eyebrow"><?= e($newsSec['badge_text']) ?></span>
                <h2 style="font-size:2.1rem;color:var(--brand-primary);"><?= e($newsSec['title']) ?></h2>
                <p style="margin:0;color:var(--ink-muted);"><?= e($newsSec['subtitle']) ?></p>
            </div>
            <div>
                <form action="<?= url('/api/newsletter') ?>" method="POST" class="newsletter-form" data-newsletter-form>
                    <?= \Core\Csrf::field() ?>
                    <input type="email" name="email" required placeholder="Enter your email address" aria-label="Email address">
                    <button type="submit" class="btn-brand btn-brand-primary"><?= e($newsSec['cta_text'] ?: 'Subscribe') ?></button>
                </form>
                <div data-newsletter-msg style="font-size:0.84rem;margin-top:8px;padding-left:14px;"></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
