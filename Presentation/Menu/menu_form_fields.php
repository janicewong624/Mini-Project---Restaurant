<input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">

<div class="form-group">
    <label class="field" for="foodName">Menu name</label>
    <input class="form-control" type="text" id="foodName" name="foodName" maxlength="100"
           value="<?= $e($item['foodName'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label class="field" for="category">Category</label>
    <?php $currentCategory = (string)($item['category'] ?? ''); ?>
    <select class="form-control" id="category" name="category" required>
        <option value="" disabled <?= $currentCategory === '' ? 'selected' : '' ?>>Choose a category</option>
        <?php foreach (MenuService::categories() as $category): ?>
            <option value="<?= $e($category) ?>" <?= $currentCategory === $category ? 'selected' : '' ?>>
                <?= $e($category) ?>
            </option>
        <?php endforeach; ?>
        <?php if ($currentCategory !== '' && !in_array($currentCategory, MenuService::categories(), true)): ?>
            <option value="<?= $e($currentCategory) ?>" selected><?= $e($currentCategory) ?> (current)</option>
        <?php endif; ?>
    </select>
</div>

<div class="form-group">
    <label class="field" for="price">Price (RM)</label>
    <input class="form-control" type="number" id="price" name="price" min="0.01" max="99999.99"
           step="0.01" value="<?= $e($item['price'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label class="field" for="description">Description</label>
    <textarea class="form-control" id="description" name="description" maxlength="500"><?= $e($item['description'] ?? '') ?></textarea>
</div>

<div class="form-group">
    <label class="field" for="image">Photo</label>
    <?php
    $currentImage = basename(str_replace('\\', '/', (string)($item['image'] ?? '')));
    $currentImageValid = preg_match('/\.(jpe?g|png|webp)$/i', $currentImage) === 1;
    ?>
    <?php if ($currentImageValid): ?>
        <img class="menu-form-photo" src="<?= $base ?>Assets/images/menu/<?= rawurlencode($currentImage) ?>"
             alt="<?= $e($item['foodName'] ?? 'Current menu photo') ?>">
        <label class="menu-remove-photo">
            <input type="checkbox" name="remove_image" value="1">
            Remove current photo
        </label>
    <?php endif; ?>
    <input class="form-control" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
    <small class="auth-hint">JPG, PNG, or WebP; maximum 5 MB. Uploading a new photo replaces the current one.</small>
</div>

<label class="menu-popular-option">
    <input type="checkbox" name="isPopular" value="1" <?= !empty($item['isPopular']) ? 'checked' : '' ?>>
    <span><strong>Show in Popular Dishes</strong><small>Feature this item on the home page.</small></span>
</label>

<div class="menu-form-actions">
    <button type="submit" class="btn btn-gold btn-sm"><?= $e($buttonText) ?></button>
    <a href="index.php" class="btn btn-secondary btn-sm">Cancel</a>
</div>
