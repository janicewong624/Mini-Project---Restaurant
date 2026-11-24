<?php
require_once __DIR__ . '/../Data/ReportData.php';

class ReportService {
    private ReportData $data;

    public function __construct() {
        $this->data = new ReportData();
    }

    /** 'YYYY-MM-DD' 而且是真的有這一天才回傳，否則 null */
    private static function validDate(string $s): ?string {
        $d = DateTime::createFromFormat('Y-m-d', $s);
        return ($d && $d->format('Y-m-d') === $s) ? $s : null;
    }

    /**
     * 整理報表的日期範圍。沒填 = 本月 1 號到今天。
     * @return array{from:string,to:string,toExclusive:string,errors:string[]}
     */
    public function resolveRange(string $from, string $to): array {
        $errors = [];
        $f = self::validDate(trim($from));
        $t = self::validDate(trim($to));

        if (trim($from) !== '' && $f === null) $errors[] = "Start date is not a valid date.";
        if (trim($to)   !== '' && $t === null) $errors[] = "End date is not a valid date.";

        $f = $f ?? date('Y-m-01');
        $t = $t ?? date('Y-m-d');

        if ($f > $t) {
            $errors[] = "Start date must not be after end date. Showing this month instead.";
            $f = date('Y-m-01');
            $t = date('Y-m-d');
        }
        return [
            'from'        => $f,
            'to'          => $t,
            'toExclusive' => date('Y-m-d', strtotime($t . ' +1 day')),
            'errors'      => $errors,
        ];
    }

    /** Order Summary Report */
    public function orderSummary(array $range): array {
        $f = $range['from'];
        $t = $range['toExclusive'];
        $totals = $this->data->orderTotals($f, $t);

        $active = (int)$totals['totalOrders'] - (int)$totals['cancelled'];   // 沒被取消的訂單
        $totals['averageOrder'] = $active > 0 ? (float)$totals['orderValue'] / $active : 0.0;

        return [
            'totals'    => $totals,
            'byDay'     => $this->data->ordersByDay($f, $t),
            'topItems'  => $this->data->topItemsByQuantity($f, $t),
            'tables'    => $this->data->tableUsage($f, $t),
        ];
    }

    /**
     * Sales Report（只算已收款 Completed 的付款）
     * $group = 'day' | 'month' | ''（空 = 自動：範圍超過 62 天就用月，否則用日）
     */
    public function sales(array $range, string $group = ''): array {
        $f = $range['from'];
        $t = $range['toExclusive'];

        if ($group !== 'day' && $group !== 'month') {
            $days  = (strtotime($range['to']) - strtotime($range['from'])) / 86400 + 1;
            $group = $days > 62 ? 'month' : 'day';
        }

        $totals = $this->data->salesTotals($f, $t);
        $totals['average'] = (int)$totals['payments'] > 0 ? (float)$totals['revenue'] / (int)$totals['payments'] : 0.0;

        return [
            'group'      => $group,
            'totals'     => $totals,
            'byPeriod'   => $this->data->salesByPeriod($f, $t, $group),
            'byMethod'   => $this->data->salesByMethod($f, $t),
            'byType'     => $this->data->salesByOrderType($f, $t),
            'byCategory' => $this->data->salesByCategory($f, $t),
            'bestSellers'=> $this->data->bestSellers($f, $t),
        ];
    }
}
