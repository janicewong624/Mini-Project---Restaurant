<?php
/**
 * 報表共用的日期篩選列。
 *
 * @var Closure $e          htmlspecialchars 的簡寫（header.php 定義的）
 * @var array   $range      ReportService::resolveRange() 的結果（from, to, errors...）
 * @var bool    $showGroup  可選，Sales Report 才會設成 true
 * @var string  $group      可選，'day' 或 'month'
 */
$showGroup = $showGroup ?? false;
$group     = $group ?? '';

$today = date('Y-m-d');
$presets = [
    'Today'        => [$today, $today],
    'This month'   => [date('Y-m-01'), $today],
    'Last 30 days' => [date('Y-m-d', strtotime('-29 days')), $today],
    'This year'    => [date('Y-01-01'), $today],
];
?>
<form method="get" class="ob-filter no-print">
    <label>From <input type="date" name="from" class="form-control" value="<?= $e($range['from']) ?>"></label>
    <label>To <input type="date" name="to" class="form-control" value="<?= $e($range['to']) ?>"></label>
    <?php if ($showGroup): ?>
        <label>Group by
            <select name="group" class="form-control">
                <option value="day"   <?= $group === 'day'   ? 'selected' : '' ?>>Day</option>
                <option value="month" <?= $group === 'month' ? 'selected' : '' ?>>Month</option>
            </select>
        </label>
    <?php endif; ?>
    <button type="submit" class="btn btn-gold btn-sm">Apply</button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">Print</button>
</form>

<div class="ob-presets no-print">
    <?php foreach ($presets as $label => [$pf, $pt]): ?>
        <a href="?<?= $e(http_build_query(['from' => $pf, 'to' => $pt])) ?>"><?= $e($label) ?></a>
    <?php endforeach; ?>
</div>

<?php foreach ($range['errors'] as $err): ?>
    <div class="alert alert-error ob-alert"><?= $e($err) ?></div>
<?php endforeach; ?>

<p class="ob-range">Period: <strong><?= date('d/m/Y', strtotime($range['from'])) ?></strong> to
   <strong><?= date('d/m/Y', strtotime($range['to'])) ?></strong></p>
