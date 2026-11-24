<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Business/MenuService.php';

$isAdmin = ($_SESSION['role'] ?? '') === 'Administrator';
if ($isAdmin) {
    require_once __DIR__ . '/../includes/customer_admin_guard.php';
    $csrfToken = customer_admin_csrf_token();
}

$categories = MenuService::categories();

$icons = [
    'Bubble Milk Tea' => '🧋',
    'Noodle Soups' => '🍜',
    'Spaghetti' => '🍝',
    'Special Items' => '✨',
    'Nyonya Series' => '🌶️',
    'Beverages' => '🥤',
    'Western Food' => '🥩',
    'Rice' => '🍚',
];

$menuImageName = static function ($value): string {
    $filename = basename(str_replace('\\', '/', trim((string)$value)));
    return preg_match('/\.(jpe?g|png|webp)$/i', $filename) ? $filename : '';
};

$items = [];
$databaseError = '';
try {
    $items = (new MenuService())->getAll();
} catch (PDOException $error) {
    $databaseError = 'The menu is unavailable. Check that MySQL is running and apply Database/menu_setup.sql.';
}

$flash = is_string($_SESSION['menu_flash'] ?? null) ? $_SESSION['menu_flash'] : '';
unset($_SESSION['menu_flash']);
$adminError = is_string($_SESSION['menu_error'] ?? null) ? $_SESSION['menu_error'] : '';
unset($_SESSION['menu_error']);

$grouped = [];
foreach ($items as $item) {
    $category = trim((string)($item['category'] ?? ''));
    $category = $category !== '' ? $category : 'Special Items';
    $grouped[$category][] = $item;
}

$displayCategories = $categories;
foreach (array_keys($grouped) as $category) {
    if (!in_array($category, $displayCategories, true)) {
        $displayCategories[] = $category;
    }
}

$initialCategoryIndex = 0;
foreach ($displayCategories as $index => $category) {
    if (!empty($grouped[$category])) {
        $initialCategoryIndex = $index;
        break;
    }
}

$pageTitle = 'Menu';
$bodyClass = 'menu-page';
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-hero">
    <div class="menu-hero-content">
        <p class="menu-eyebrow">DineNest Collection</p>
        <h1 class="menu-title">Discover Our Menu</h1>
        <p class="menu-intro">From comforting classics to refreshing signatures, find your next favourite.</p>
    </div>
</section>

<div class="menu-shell">
    <div class="menu-content">
        <div class="menu-toolbar">
            <div>
                <p class="menu-toolbar-label" id="menu-item-count">
                    <?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?>
                </p>
                <h2>Our Selection</h2>
            </div>
            <div class="menu-toolbar-actions">
                <?php if ($isAdmin): ?>
                    <a class="btn btn-gold btn-sm" href="menu_add.php">+ Add Menu Item</a>
                <?php endif; ?>
                <label class="menu-search">
                    <span class="sr-only">Search menu</span>
                    <input type="search" id="menu-search" placeholder="Search dishes & drinks">
                </label>
            </div>
        </div>

        <?php if ($databaseError !== ''): ?>
            <div class="alert alert-error menu-alert"><?= $e($databaseError) ?></div>
        <?php endif; ?>
        <?php if ($flash !== ''): ?>
            <div class="alert alert-success menu-alert"><?= $e($flash) ?></div>
        <?php endif; ?>
        <?php if ($adminError !== ''): ?>
            <div class="alert alert-error menu-alert"><?= $e($adminError) ?></div>
        <?php endif; ?>

        <nav class="menu-category-navigation" aria-label="Menu categories">
            <button type="button" class="menu-category-arrow" id="menu-category-previous"
                    aria-label="Previous category"<?= $initialCategoryIndex === 0 ? ' disabled' : '' ?>>‹</button>
            <div class="menu-category-tabs" id="menu-category-tabs">
                <?php foreach ($displayCategories as $index => $category): ?>
                    <button type="button" class="menu-category-tab"
                            data-category-index="<?= $index ?>"
                            aria-current="<?= $index === $initialCategoryIndex ? 'true' : 'false' ?>">
                        <span><?= $e($icons[$category] ?? '•') ?></span>
                        <?= $e($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <button type="button" class="menu-category-arrow" id="menu-category-next"
                    aria-label="Next category"<?= $initialCategoryIndex >= count($displayCategories) - 1 ? ' disabled' : '' ?>>›</button>
            <span class="menu-category-count" id="menu-category-count">
                <?= $initialCategoryIndex + 1 ?> / <?= count($displayCategories) ?>
            </span>
        </nav>

        <div class="menu-category-stage" id="menu-category-stage" tabindex="0"
             aria-label="Menu categories. Swipe horizontally or use the arrow buttons to browse.">
        <?php foreach ($displayCategories as $index => $category): ?>
            <?php
            $categoryId = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $category));
            $categoryItems = $grouped[$category] ?? [];
            ?>
            <section class="menu-category" id="<?= $e($categoryId) ?>"
                     data-category-name="<?= $e(strtolower($category)) ?>"
                     data-category-index="<?= $index ?>"<?= $index !== $initialCategoryIndex ? ' hidden' : '' ?>>
                <header class="menu-category-header">
                    <div class="menu-category-icon"><?= $e($icons[$category] ?? '•') ?></div>
                    <div>
                        <p>Menu Selection</p>
                        <h2><?= $e($category) ?></h2>
                    </div>
                </header>

                <?php if ($categoryItems): ?>
                    <div class="menu-grid">
                        <?php foreach ($categoryItems as $item): ?>
                            <article class="menu-card" data-search="<?= $e(strtolower($item['foodName'] . ' ' . ($item['description'] ?? '') . ' ' . $category)) ?>">
                                <div class="menu-card-photo">
                                    <?php $imageName = $menuImageName($item['image'] ?? ''); ?>
                                    <?php if (!empty($item['isPopular'])): ?><span class="menu-popular-badge">Popular</span><?php endif; ?>
                                    <?php if ($imageName !== ''): ?>
                                        <img src="<?= $base ?>Assets/images/menu/<?= rawurlencode($imageName) ?>"
                                             alt="<?= $e($item['foodName']) ?>" loading="lazy"
                                             onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                                    <?php endif; ?>
                                    <span class="menu-card-placeholder"<?= $imageName !== '' ? ' hidden' : '' ?>><?= $e($icons[$category] ?? '•') ?></span>
                                </div>
                                <div class="menu-card-body">
                                    <h3><?= $e($item['foodName']) ?></h3>
                                    <?php if (!empty($item['description'])): ?>
                                        <p><?= $e($item['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="menu-card-price">
                                    <span>RM</span>
                                    <strong><?= number_format((float)$item['price'], 2) ?></strong>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <div class="menu-card-actions">
                                        <a href="menu_edit.php?id=<?= rawurlencode((string)$item['menuItemID']) ?>">Edit</a>
                                        <form method="post" action="menu_delete.php" onsubmit="return confirm('Delete this menu item?');">
                                            <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= $e($item['menuItemID']) ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="menu-empty">
                        <span><?= $e($icons[$category] ?? '•') ?></span>
                        <p>New selections coming soon</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

        <p class="menu-no-results" id="menu-no-results" hidden>No menu items match your search.</p>
        </div>
    </div>
</div>

<script>
const menuSearch = document.getElementById('menu-search');
const menuCards = Array.from(document.querySelectorAll('.menu-card'));
const menuCategories = Array.from(document.querySelectorAll('.menu-category'));
const categoryTabs = Array.from(document.querySelectorAll('.menu-category-tab'));
const previousCategory = document.getElementById('menu-category-previous');
const nextCategory = document.getElementById('menu-category-next');
const categoryCount = document.getElementById('menu-category-count');
const categoryStage = document.getElementById('menu-category-stage');
const noResults = document.getElementById('menu-no-results');
const menuItemCount = document.getElementById('menu-item-count');
let availableCategoryIndexes = menuCategories.map((category, index) => index);
let activeCategoryIndex = <?= $initialCategoryIndex ?>;

const showCategory = (index, scrollTab = true) => {
    activeCategoryIndex = index;
    menuCategories.forEach((category, categoryIndex) => {
        category.hidden = categoryIndex !== activeCategoryIndex;
    });

    categoryTabs.forEach((tab, tabIndex) => {
        const active = tabIndex === activeCategoryIndex;
        tab.setAttribute('aria-current', active ? 'true' : 'false');
        if (active && scrollTab) {
            tab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    });

    const position = availableCategoryIndexes.indexOf(activeCategoryIndex);
    previousCategory.disabled = position <= 0;
    nextCategory.disabled = position < 0 || position >= availableCategoryIndexes.length - 1;
    categoryCount.textContent = position < 0
        ? `0 / ${availableCategoryIndexes.length}`
        : `${position + 1} / ${availableCategoryIndexes.length}`;
};

const moveCategory = (direction) => {
    const position = availableCategoryIndexes.indexOf(activeCategoryIndex);
    const nextPosition = Math.max(0, Math.min(availableCategoryIndexes.length - 1, position + direction));
    if (availableCategoryIndexes[nextPosition] !== undefined) {
        showCategory(availableCategoryIndexes[nextPosition]);
    }
};

categoryTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
        if (availableCategoryIndexes.includes(index)) {
            showCategory(index, false);
        }
    });
});
previousCategory.addEventListener('click', () => moveCategory(-1));
nextCategory.addEventListener('click', () => moveCategory(1));

menuSearch.addEventListener('input', () => {
    const query = menuSearch.value.trim().toLowerCase();
    let visibleCount = 0;

    menuCards.forEach(card => {
        const visible = !query || card.dataset.search.includes(query);
        card.hidden = !visible;
        if (visible) visibleCount++;
    });

    availableCategoryIndexes = [];
    menuCategories.forEach((category, index) => {
        const hasMatches = category.querySelectorAll('.menu-card:not([hidden])').length > 0;
        categoryTabs[index].hidden = query !== '' && !hasMatches;
        if (query === '' || hasMatches) {
            availableCategoryIndexes.push(index);
        }
    });

    noResults.hidden = visibleCount !== 0;
    menuItemCount.textContent = `${visibleCount} item${visibleCount === 1 ? '' : 's'}`;
    if (availableCategoryIndexes.length === 0) {
        menuCategories.forEach(category => { category.hidden = true; });
        previousCategory.disabled = true;
        nextCategory.disabled = true;
        categoryCount.textContent = '0 / 0';
        return;
    }
    if (!availableCategoryIndexes.includes(activeCategoryIndex)) {
        activeCategoryIndex = availableCategoryIndexes[0];
    }
    showCategory(activeCategoryIndex, false);
});

categoryStage.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
        moveCategory(-1);
    } else if (event.key === 'ArrowRight') {
        moveCategory(1);
    }
});

let swipeStart = null;
categoryStage.addEventListener('touchstart', (event) => {
    const touch = event.changedTouches[0];
    swipeStart = { x: touch.clientX, y: touch.clientY };
}, { passive: true });

categoryStage.addEventListener('touchend', (event) => {
    if (!swipeStart) return;
    const touch = event.changedTouches[0];
    const deltaX = touch.clientX - swipeStart.x;
    const deltaY = touch.clientY - swipeStart.y;
    if (Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2) {
        moveCategory(deltaX < 0 ? 1 : -1);
    }
    swipeStart = null;
}, { passive: true });

showCategory(activeCategoryIndex, false);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
