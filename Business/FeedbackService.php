<?php
require_once __DIR__ . '/../data/FeedbackData.php';

class FeedbackService {
    private FeedbackData $data;

    public function __construct() {
        $this->data = new FeedbackData();
    }

    /** 驗證評分與留言，回傳錯誤訊息陣列（空陣列 = 通過） */
    private function validate(string $rating, string $comment): array {
        $errors = [];
        $rating = trim($rating);

        if ($rating === '') {
            $errors[] = "Please enter a rating.";
        } elseif (!ctype_digit($rating)) {
            $errors[] = "Invalid data, rating must be a whole number.";
        } elseif ((int)$rating < 1 || (int)$rating > 5) {
            $errors[] = "Rating should be between 1 and 5.";
        }

        if (mb_strlen($comment) > 500) {
            $errors[] = "Comment must not exceed 500 characters.";
        }
        return $errors;
    }

    public function submit(string $userID, string $rating, string $comment): array {
        $comment = trim($comment);
        $errors = $this->validate($rating, $comment);

        if (!$this->data->userExists($userID)) {
            $errors[] = "User does not exist.";
        }
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }
        $id = $this->data->insert($userID, (int)$rating, $comment);
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    public function getAll(): array {
        return $this->data->findAll();
    }

    public function getById(string $id): ?array {
        return $this->data->findById($id);
    }

    public function update(string $id, string $rating, string $comment): array {
        $comment = trim($comment);
        $errors = $this->validate($rating, $comment);

        if ($this->data->findById($id) === null) {
            $errors[] = "Feedback record not found.";
        }
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }
        $this->data->update($id, (int)$rating, $comment);
        return ['success' => true, 'errors' => []];
    }

    public function delete(string $id): array {
        if ($this->data->findById($id) === null) {
            return ['success' => false, 'errors' => ["Feedback record not found."]];
        }
        $this->data->delete($id);
        return ['success' => true, 'errors' => []];
    }

    public function recent(int $limit = 12): array { return $this->data->findRecent($limit); }

    public function summary(): array { return $this->data->summary(); }

    private function cleanDate(?string $d): ?string {
    return ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : null;
    }
    public function filtered(string $rating, string $from, string $to): array {
        $r = ctype_digit($rating) && $rating >= 1 && $rating <= 5 ? (int)$rating : null;
        return $this->data->findFiltered($r, $this->cleanDate($from), $this->cleanDate($to));
    }
    public function stats(string $from, string $to): array {
        $dist  = $this->data->distribution($this->cleanDate($from), $this->cleanDate($to));
        $count = array_sum($dist);
        $sum   = 0; foreach ($dist as $star => $c) $sum += $star * $c;
        return ['dist' => $dist, 'count' => $count, 'avg' => $count ? round($sum / $count, 1) : 0];
    }
}