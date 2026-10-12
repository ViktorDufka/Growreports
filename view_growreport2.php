<?php
session_start();

require($_SERVER['DOCUMENT_ROOT'] . '/../secure/db_config.php');

$growreport_id = (int)($_GET['id'] ?? $_POST['report_id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($action !== '') {
    ini_set('display_errors', 0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
error_reporting(E_ALL);

function vg2_json($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ========== AUTO-CREATE TABULKY PRO CO2 ==========
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_co2_2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        week_id INT NOT NULL,
        day_number TINYINT NOT NULL,
        label1 VARCHAR(20) NOT NULL DEFAULT 'Ráno',
        value1 SMALLINT UNSIGNED DEFAULT NULL,
        label2 VARCHAR(20) NOT NULL DEFAULT 'Večer',
        value2 SMALLINT UNSIGNED DEFAULT NULL,
        UNIQUE KEY unique_week_day (week_id, day_number),
        FOREIGN KEY (week_id) REFERENCES growreport_weeks2(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_co2_2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            week_id INT NOT NULL,
            day_number TINYINT NOT NULL,
            label1 VARCHAR(20) NOT NULL DEFAULT 'Ráno',
            value1 SMALLINT UNSIGNED DEFAULT NULL,
            label2 VARCHAR(20) NOT NULL DEFAULT 'Večer',
            value2 SMALLINT UNSIGNED DEFAULT NULL,
            UNIQUE KEY unique_week_day (week_id, day_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e2) {
        error_log("CO2 tabulka: " . $e2->getMessage());
    }
}

// ========== AUTO-CREATE TABULKY PRO KVĚTINÁČE A ZALÉVÁNÍ ==========
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_pots2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        pot_number TINYINT NOT NULL,
        volume DECIMAL(5,1) NOT NULL,
        UNIQUE KEY unique_report_pot (report_id, pot_number),
        FOREIGN KEY (report_id) REFERENCES growreports2(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_pots2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            pot_number TINYINT NOT NULL,
            volume DECIMAL(5,1) NOT NULL,
            UNIQUE KEY unique_report_pot (report_id, pot_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e2) {
        error_log("Pots tabulka: " . $e2->getMessage());
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_watering2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        week_id INT NOT NULL,
        day_number TINYINT NOT NULL,
        pot_number TINYINT NOT NULL,
        amount DECIMAL(5,1) NOT NULL DEFAULT 0,
        UNIQUE KEY unique_week_day_pot (week_id, day_number, pot_number),
        FOREIGN KEY (week_id) REFERENCES growreport_weeks2(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_watering2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            week_id INT NOT NULL,
            day_number TINYINT NOT NULL,
            pot_number TINYINT NOT NULL,
            amount DECIMAL(5,1) NOT NULL DEFAULT 0,
            UNIQUE KEY unique_week_day_pot (week_id, day_number, pot_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e2) {
        error_log("Watering tabulka: " . $e2->getMessage());
    }
}

// ========== AUTO-CREATE TABULKY PRO HNOJIVO ==========
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_hnojeno2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        week_id INT NOT NULL,
        day_number TINYINT NOT NULL,
        fertilizer_type VARCHAR(10) NOT NULL,
        name VARCHAR(100) NOT NULL,
        amount DECIMAL(6,1) NOT NULL DEFAULT 0,
        is_custom TINYINT NOT NULL DEFAULT 0,
        UNIQUE KEY unique_week_day_name (week_id, day_number, name),
        FOREIGN KEY (week_id) REFERENCES growreport_weeks2(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_hnojeno2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            week_id INT NOT NULL,
            day_number TINYINT NOT NULL,
            fertilizer_type VARCHAR(10) NOT NULL,
            name VARCHAR(100) NOT NULL,
            amount DECIMAL(6,1) NOT NULL DEFAULT 0,
            is_custom TINYINT NOT NULL DEFAULT 0,
            UNIQUE KEY unique_week_day_name (week_id, day_number, name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e2) {
        error_log("Hnojeno tabulka: " . $e2->getMessage());
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_custom_hnojeno2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        fertilizer_type VARCHAR(10) NOT NULL,
        item_index TINYINT NOT NULL,
        name VARCHAR(100) NOT NULL,
        UNIQUE KEY unique_report_type_idx (report_id, fertilizer_type, item_index),
        FOREIGN KEY (report_id) REFERENCES growreports2(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS growreport_custom_hnojeno2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            fertilizer_type VARCHAR(10) NOT NULL,
            item_index TINYINT NOT NULL,
            name VARCHAR(100) NOT NULL,
            UNIQUE KEY unique_report_type_idx (report_id, fertilizer_type, item_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e2) {
        error_log("Custom hnojeno tabulka: " . $e2->getMessage());
    }
}

if ($growreport_id === 0) {
    if ($action !== '') vg2_json(['success' => false, 'message' => 'ID growreportu není nastaveno.']);
    die("ID growreportu není nastaveno.");
}

$sql = "SELECT g.*, u.username AS author
        FROM growreports2 g
        JOIN uzivatele u ON g.user_id = u.id
        WHERE g.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $growreport_id]);
$growreport = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$growreport) {
    if ($action !== '') vg2_json(['success' => false, 'message' => 'Growreport nebyl nalezen.']);
    die("Growreport nebyl nalezen.");
}

$isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$growreport['user_id'];

if (!$isOwner && empty($growreport['share_enabled'])) {
    if ($action !== '') vg2_json(['success' => false, 'message' => 'Nemáte oprávnění.']);
    die("Tento growreport není sdílený.");
}

if ($action !== '') {
    if (!$isOwner) vg2_json(['success' => false, 'message' => 'Nemáte oprávnění upravovat tento growreport.']);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') vg2_json(['success' => false, 'message' => 'Neplatný požadavek.']);

    $json = json_decode(file_get_contents('php://input'), true) ?: [];

    $getWeekId = function ($wn) use ($pdo, $growreport_id) {
        $st = $pdo->prepare("SELECT id FROM growreport_weeks2 WHERE report_id=:rid AND week_number=:wn LIMIT 1");
        $st->execute(['rid' => $growreport_id, 'wn' => $wn]);
        return (int)$st->fetchColumn();
    };

    if ($action === 'add_week') {
        $st = $pdo->prepare("SELECT COALESCE(MAX(week_number),0) FROM growreport_weeks2 WHERE report_id=:rid");
        $st->execute(['rid' => $growreport_id]);
        $next = (int)$st->fetchColumn() + 1;

        $st = $pdo->prepare("SELECT start_day FROM growreport_weeks2 WHERE report_id=:rid ORDER BY week_number DESC LIMIT 1");
        $st->execute(['rid' => $growreport_id]);
        $lastStart = $st->fetchColumn();

        $st = $pdo->prepare("INSERT INTO growreport_weeks2 (report_id, week_number, text_content, start_day) VALUES (:rid, :wn, '', :sd)");
        $st->execute(['rid' => $growreport_id, 'wn' => $next, 'sd' => $lastStart ?: null]);
        vg2_json(['success' => true, 'week_number' => $next]);
    }

    if ($action === 'delete_week') {
        $wn = (int)($json['week'] ?? 0);
        if ($wn <= 1) {
            vg2_json(['success' => false, 'message' => 'První týden nelze smazat.']);
        }

        $step = 'načtení týdne';
        try {
            $st = $pdo->prepare("SELECT id, photo1, photo2, photo3 FROM growreport_weeks2 WHERE report_id=:rid AND week_number=:wn LIMIT 1");
            $st->execute(['rid' => $growreport_id, 'wn' => $wn]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                vg2_json(['success' => false, 'message' => "Týden $wn nebyl nalezen."]);
            }

            $step = 'začátek transakce';
            $pdo->beginTransaction();

            $step = 'mazání dnů';
            $pdo->prepare("DELETE FROM growreport_days2 WHERE week_id=:wid")->execute(['wid' => $row['id']]);

            $step = 'mazání CO2';
            $pdo->prepare("DELETE FROM growreport_co2_2 WHERE week_id=:wid")->execute(['wid' => $row['id']]);

            $step = 'mazání zalévání';
            try {
                $pdo->prepare("DELETE FROM growreport_watering2 WHERE week_id=:wid")->execute(['wid' => $row['id']]);
            } catch (Throwable $e) { /* tabulka nemusí existovat */ }

            $step = 'mazání hnojení';
            try {
                $pdo->prepare("DELETE FROM growreport_hnojeno2 WHERE week_id=:wid")->execute(['wid' => $row['id']]);
            } catch (Throwable $e) { /* tabulka nemusí existovat */ }

            $step = 'mazání týdne';
            $pdo->prepare("DELETE FROM growreport_weeks2 WHERE id=:id")->execute(['id' => $row['id']]);

            $step = 'přečíslování';
            $pdo->prepare("UPDATE growreport_weeks2 SET week_number = week_number - 1 WHERE report_id=:rid AND week_number > :wn")
                ->execute(['rid' => $growreport_id, 'wn' => $wn]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("delete_week chyba [$step]: " . $e->getMessage());
            vg2_json(['success' => false, 'message' => "Chyba při kroku: $step\n" . $e->getMessage()]);
        }

        $baseDir = realpath('uploads/growreports2');
        foreach (['photo1', 'photo2', 'photo3'] as $col) {
            if (!empty($row[$col])) {
                $real = realpath($row[$col]);
                if ($baseDir && $real && strpos($real, $baseDir . DIRECTORY_SEPARATOR) === 0 && is_file($real)) {
                    @unlink($real);
                }
            }
        }

        vg2_json(['success' => true]);
    }

    if ($action === 'save') {
        $weeks = $_POST['weeks'] ?? [];
        $uploadDir = 'uploads/growreports2/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($weeks as $idx => $week) {
            $wn = (int)($week['week_number'] ?? 0);
            if ($wn <= 0) continue;

            $text = (string)($week['text'] ?? '');
            $startDay = trim((string)($week['start_day'] ?? ''));

            $photos = [1 => null, 2 => null, 3 => null];
            for ($p = 1; $p <= 3; $p++) {
                if (isset($_FILES['weeks']['error'][$idx]["photo{$p}"]) &&
                    $_FILES['weeks']['error'][$idx]["photo{$p}"] === UPLOAD_ERR_OK) {
                    $origName = $_FILES['weeks']['name'][$idx]["photo{$p}"];
                    $tmp = $_FILES['weeks']['tmp_name'][$idx]["photo{$p}"];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExt, true)) continue;
                    if (@getimagesize($tmp) === false) continue;
                    $fn = uniqid('gr2_') . '.' . $ext;
                    if (move_uploaded_file($tmp, $uploadDir . $fn)) {
                        $photos[$p] = $uploadDir . $fn;
                    }
                }
            }

            $weekId = $getWeekId($wn);
            if ($weekId) {
                $st = $pdo->prepare("SELECT photo1, photo2, photo3 FROM growreport_weeks2 WHERE id=:id");
                $st->execute(['id' => $weekId]);
                $row = $st->fetch(PDO::FETCH_ASSOC);

                $st = $pdo->prepare("UPDATE growreport_weeks2
                                     SET text_content=:txt, photo1=:p1, photo2=:p2, photo3=:p3, start_day=:sd
                                     WHERE id=:id");
                $st->execute([
                    'txt' => $text,
                    'p1' => $photos[1] ?? $row['photo1'],
                    'p2' => $photos[2] ?? $row['photo2'],
                    'p3' => $photos[3] ?? $row['photo3'],
                    'sd' => $startDay !== '' ? $startDay : null,
                    'id' => $weekId
                ]);
            } else {
                $st = $pdo->prepare("INSERT INTO growreport_weeks2 (report_id, week_number, text_content, photo1, photo2, photo3, start_day)
                                     VALUES (:rid, :wn, :txt, :p1, :p2, :p3, :sd)");
                $st->execute([
                    'rid' => $growreport_id,
                    'wn' => $wn,
                    'txt' => $text,
                    'p1' => $photos[1],
                    'p2' => $photos[2],
                    'p3' => $photos[3],
                    'sd' => $startDay !== '' ? $startDay : null
                ]);
                $weekId = (int)$pdo->lastInsertId();
            }

            $hnojeno = array_filter(array_map('intval', explode(',', (string)($week['hnojeno'] ?? ''))));
            try {
                $hDaysStmt = $pdo->prepare("SELECT DISTINCT day_number FROM growreport_hnojeno2 WHERE week_id=:wid");
                $hDaysStmt->execute(['wid' => $weekId]);
                $dbHnojDays = $hDaysStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($dbHnojDays as $dhd) {
                    if (!in_array((int)$dhd, $hnojeno, true)) {
                        $hnojeno[] = (int)$dhd;
                    }
                }
            } catch (Throwable $e) {}

            $pdo->prepare("DELETE FROM growreport_days2 WHERE week_id=:wid")->execute(['wid' => $weekId]);
            $ins = $pdo->prepare("INSERT INTO growreport_days2 (week_id, day_number, zalito, hnojeno) VALUES (:wid, :dn, 0, :h)");
            for ($d = 1; $d <= 7; $d++) {
                $h = in_array($d, $hnojeno, true) ? 1 : 0;
                if ($h) {
                    $ins->execute(['wid' => $weekId, 'dn' => $d, 'h' => $h]);
                }
            }
        }

        vg2_json(['success' => true, 'report_id' => $growreport_id]);
    }

    if ($action === 'save_text') {
        $wn = (int)($json['week'] ?? 0);
        $text = (string)($json['text'] ?? '');
        $st = $pdo->prepare("UPDATE growreport_weeks2 SET text_content=:t WHERE report_id=:rid AND week_number=:wn");
        $st->execute(['t' => $text, 'rid' => $growreport_id, 'wn' => $wn]);
        vg2_json(['success' => true]);
    }

    if ($action === 'phase') {
        $wn = (int)($json['week'] ?? 0);
        $phase = trim((string)($json['phase'] ?? ''));
        $color = trim((string)($json['color'] ?? ''));
        if (!in_array($color, ['green', 'red', 'pink'], true)) {
            vg2_json(['success' => false, 'message' => 'Neplatná barva.']);
        }
        $st = $pdo->prepare("UPDATE growreport_weeks2 SET phase=:p, phase_color=:c WHERE report_id=:rid AND week_number=:wn");
        $st->execute(['p' => $phase, 'c' => $color, 'rid' => $growreport_id, 'wn' => $wn]);
        vg2_json(['success' => true]);
    }

    if ($action === 'start_day') {
        $wn = (int)($json['week'] ?? 0);
        $sd = trim((string)($json['start_day'] ?? ''));
        $st = $pdo->prepare("UPDATE growreport_weeks2 SET start_day=:sd WHERE report_id=:rid AND week_number=:wn");
        $st->execute(['sd' => $sd, 'rid' => $growreport_id, 'wn' => $wn]);
        vg2_json(['success' => true]);
    }

    if ($action === 'toggle_day') {
        $wn = (int)($json['week'] ?? 0);
        $day = (int)($json['day'] ?? 0);
        $field = (string)($json['type'] ?? '');
        $value = !empty($json['checked']) ? 1 : 0;

        if (!in_array($field, ['hnojeno'], true) || $day < 1 || $day > 7) {
            vg2_json(['success' => false, 'message' => 'Neplatná data.']);
        }

        $weekId = $getWeekId($wn);
        if (!$weekId) vg2_json(['success' => false, 'message' => 'Týden nebyl nalezen.']);

        $st = $pdo->prepare("INSERT INTO growreport_days2 (week_id, day_number, {$field}) VALUES (:wid, :dn, :val)
                             ON DUPLICATE KEY UPDATE {$field}=VALUES({$field})");
        $st->execute(['wid' => $weekId, 'dn' => $day, 'val' => $value]);
        vg2_json(['success' => true]);
    }

    // --- ULOŽIT CO2 (2 hodnoty PPM pro jeden den) ---
    if ($action === 'save_co2') {
        $wn = (int)($json['week'] ?? 0);
        $day = (int)($json['day'] ?? 0);
        if ($day < 1 || $day > 7) vg2_json(['success' => false, 'message' => 'Neplatný den.']);

        $weekId = $getWeekId($wn);
        if (!$weekId) vg2_json(['success' => false, 'message' => 'Týden nebyl nalezen.']);

        $label1 = mb_substr(trim((string)($json['label1'] ?? '')), 0, 20);
        $label2 = mb_substr(trim((string)($json['label2'] ?? '')), 0, 20);
        if ($label1 === '') $label1 = 'Ráno';
        if ($label2 === '') $label2 = 'Večer';

        $v1 = trim((string)($json['value1'] ?? ''));
        $v2 = trim((string)($json['value2'] ?? ''));
        if (($v1 !== '' && !preg_match('/^\d{1,4}$/', $v1)) || ($v2 !== '' && !preg_match('/^\d{1,4}$/', $v2))) {
            vg2_json(['success' => false, 'message' => 'Hodnota PPM musí být číslo s max. 4 číslicemi.']);
        }
        $v1 = $v1 === '' ? null : (int)$v1;
        $v2 = $v2 === '' ? null : (int)$v2;

        try {
            if ($v1 === null && $v2 === null && $label1 === 'Ráno' && $label2 === 'Večer') {
                $pdo->prepare("DELETE FROM growreport_co2_2 WHERE week_id=:wid AND day_number=:dn")
                    ->execute(['wid' => $weekId, 'dn' => $day]);
            } else {
                $st = $pdo->prepare("INSERT INTO growreport_co2_2 (week_id, day_number, label1, value1, label2, value2)
                                     VALUES (:wid, :dn, :l1, :v1, :l2, :v2)
                                     ON DUPLICATE KEY UPDATE label1=VALUES(label1), value1=VALUES(value1),
                                                             label2=VALUES(label2), value2=VALUES(value2)");
                $st->execute(['wid' => $weekId, 'dn' => $day, 'l1' => $label1, 'v1' => $v1, 'l2' => $label2, 'v2' => $v2]);
            }
        } catch (Throwable $e) {
            error_log("save_co2 chyba: " . $e->getMessage());
            vg2_json(['success' => false, 'message' => 'Chyba databáze při ukládání CO2: ' . $e->getMessage()]);
        }

        vg2_json(['success' => true, 'label1' => $label1, 'label2' => $label2, 'value1' => $v1, 'value2' => $v2]);
    }

    // --- ULOŽIT NASTAVENÍ KVĚTINÁČŮ ---
    if ($action === 'save_pots') {
        $pots = $json['pots'] ?? [];

        try {
            $pdo->prepare("DELETE FROM growreport_pots2 WHERE report_id=:rid")->execute(['rid' => $growreport_id]);

            $ins = $pdo->prepare("INSERT INTO growreport_pots2 (report_id, pot_number, volume) VALUES (:rid, :pn, :vol)");
            foreach ($pots as $pot) {
                $pn = (int)($pot['pot_number'] ?? 0);
                $vol = round(floatval($pot['volume'] ?? 0) * 2) / 2;
                if ($pn >= 1 && $pn <= 9 && $vol >= 0.5 && $vol <= 100) {
                    $ins->execute(['rid' => $growreport_id, 'pn' => $pn, 'vol' => $vol]);
                }
            }
        } catch (Throwable $e) {
            error_log("save_pots chyba: " . $e->getMessage());
            vg2_json(['success' => false, 'message' => 'Chyba: ' . $e->getMessage()]);
        }

        vg2_json(['success' => true]);
    }

    // --- ULOŽIT ZALÉVÁNÍ PRO DEN ---
    if ($action === 'save_watering') {
        $wn = (int)($json['week'] ?? 0);
        $day = (int)($json['day'] ?? 0);
        $amounts = $json['amounts'] ?? [];

        if ($day < 1 || $day > 7) vg2_json(['success' => false, 'message' => 'Neplatný den.']);

        $weekId = $getWeekId($wn);
        if (!$weekId) vg2_json(['success' => false, 'message' => 'Týden nebyl nalezen.']);

        try {
            $pdo->prepare("DELETE FROM growreport_watering2 WHERE week_id=:wid AND day_number=:dn")
                ->execute(['wid' => $weekId, 'dn' => $day]);

            $total = 0;
            $ins = $pdo->prepare("INSERT INTO growreport_watering2 (week_id, day_number, pot_number, amount) VALUES (:wid, :dn, :pn, :amt)");
            foreach ($amounts as $a) {
                $pn = (int)($a['pot_number'] ?? 0);
                $amt = round(floatval($a['amount'] ?? 0) * 2) / 2;
                if ($pn >= 1 && $pn <= 9 && $amt > 0) {
                    $ins->execute(['wid' => $weekId, 'dn' => $day, 'pn' => $pn, 'amt' => $amt]);
                    $total += $amt;
                }
            }
        } catch (Throwable $e) {
            error_log("save_watering chyba: " . $e->getMessage());
            vg2_json(['success' => false, 'message' => 'Chyba: ' . $e->getMessage()]);
        }

        vg2_json(['success' => true, 'total' => $total]);
    }

    // --- ULOŽIT HNOJIVO PRO DEN ---
    if ($action === 'save_hnojeno') {
        $wn = (int)($json['week'] ?? 0);
        $day = (int)($json['day'] ?? 0);
        $items = $json['items'] ?? [];
        $customNames = $json['custom_names'] ?? null;

        if ($day < 1 || $day > 7) vg2_json(['success' => false, 'message' => 'Neplatný den.']);

        $weekId = $getWeekId($wn);
        if (!$weekId) vg2_json(['success' => false, 'message' => 'Týden nebyl nalezen.']);

        if (count($items) > 20) {
            vg2_json(['success' => false, 'message' => 'Lze uložit maximálně 20 položek hnojiv.']);
        }

        $totalLiquid = 0.0;
        $totalDry = 0;
        $savedItems = [];

        try {
            $pdo->beginTransaction();

            if (is_array($customNames)) {
                $pdo->prepare("DELETE FROM growreport_custom_hnojeno2 WHERE report_id=:rid")->execute(['rid' => $growreport_id]);
                $insCust = $pdo->prepare("INSERT INTO growreport_custom_hnojeno2 (report_id, fertilizer_type, item_index, name)
                                          VALUES (:rid, :ft, :idx, :nm)");
                foreach (['liquid', 'dry'] as $ft) {
                    $list = $customNames[$ft] ?? [];
                    if (is_array($list)) {
                        foreach ($list as $idx => $nm) {
                            $nm = mb_substr(trim((string)$nm), 0, 100);
                            if ($nm !== '') {
                                $insCust->execute([
                                    'rid' => $growreport_id,
                                    'ft' => $ft,
                                    'idx' => (int)$idx,
                                    'nm' => $nm
                                ]);
                            }
                        }
                    }
                }
            }

            $pdo->prepare("DELETE FROM growreport_hnojeno2 WHERE week_id=:wid AND day_number=:dn")
                ->execute(['wid' => $weekId, 'dn' => $day]);

            $ins = $pdo->prepare("INSERT INTO growreport_hnojeno2 (week_id, day_number, fertilizer_type, name, amount, is_custom)
                                  VALUES (:wid, :dn, :ft, :nm, :amt, :ic)");

            foreach ($items as $item) {
                $name = mb_substr(trim((string)($item['name'] ?? '')), 0, 100);
                if ($name === '') continue;

                $type = ($item['type'] ?? '') === 'dry' ? 'dry' : 'liquid';
                $isCustom = !empty($item['is_custom']) ? 1 : 0;

                if ($type === 'liquid') {
                    $amt = round(floatval($item['amount'] ?? 0) * 2) / 2;
                    if ($amt < 0.5 || $amt > 500) continue;
                    $ins->execute([
                        'wid' => $weekId,
                        'dn' => $day,
                        'ft' => 'liquid',
                        'nm' => $name,
                        'amt' => $amt,
                        'ic' => $isCustom
                    ]);
                    $totalLiquid += $amt;
                    $savedItems[] = [
                        'type' => 'liquid',
                        'name' => $name,
                        'amount' => $amt,
                        'is_custom' => $isCustom
                    ];
                } else {
                    $amt = (int)($item['amount'] ?? 0);
                    if ($amt < 1 || $amt > 30) continue;
                    $ins->execute([
                        'wid' => $weekId,
                        'dn' => $day,
                        'ft' => 'dry',
                        'nm' => $name,
                        'amt' => $amt,
                        'ic' => $isCustom
                    ]);
                    $totalDry += $amt;
                    $savedItems[] = [
                        'type' => 'dry',
                        'name' => $name,
                        'amount' => $amt,
                        'is_custom' => $isCustom
                    ];
                }
            }

            $hasHnoj = ($totalLiquid > 0 || $totalDry > 0) ? 1 : 0;
            $stDay = $pdo->prepare("INSERT INTO growreport_days2 (week_id, day_number, zalito, hnojeno)
                                    VALUES (:wid, :dn, 0, :h)
                                    ON DUPLICATE KEY UPDATE hnojeno=VALUES(hnojeno)");
            $stDay->execute(['wid' => $weekId, 'dn' => $day, 'h' => $hasHnoj]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("save_hnojeno chyba: " . $e->getMessage());
            vg2_json(['success' => false, 'message' => 'Chyba databáze při ukládání hnojení: ' . $e->getMessage()]);
        }

        vg2_json([
            'success' => true,
            'total_liquid' => $totalLiquid,
            'total_dry' => $totalDry,
            'items' => $savedItems
        ]);
    }

    if ($action === 'share') {
        $st = $pdo->prepare("UPDATE growreports2 SET share_enabled=1, share_date=NOW() WHERE id=:id AND user_id=:uid");
        $st->execute(['id' => $growreport_id, 'uid' => $_SESSION['user_id']]);
        vg2_json(['success' => true]);
    }

    if ($action === 'delete') {
        $st = $pdo->prepare("DELETE FROM growreports2 WHERE id=:id AND user_id=:uid");
        $st->execute(['id' => $growreport_id, 'uid' => $_SESSION['user_id']]);
        vg2_json(['success' => true]);
    }

    vg2_json(['success' => false, 'message' => 'Neznámá akce.']);
}

// ========== NAČTENÍ DAT ==========
$stmt = $pdo->prepare("SELECT * FROM growreport_weeks2 WHERE report_id = :id ORDER BY week_number ASC");
$stmt->execute(['id' => $growreport_id]);
$weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Načtení nastavení květináčů
$potsConfig = [];
try {
    $ps = $pdo->prepare("SELECT pot_number, volume FROM growreport_pots2 WHERE report_id=:rid ORDER BY pot_number ASC");
    $ps->execute(['rid' => $growreport_id]);
    $potsConfig = $ps->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log("načtení květináčů: " . $e->getMessage());
}

// Načtení custom hnojiv
$customHnojenoConfig = ['liquid' => [], 'dry' => []];
try {
    $chs = $pdo->prepare("SELECT fertilizer_type, item_index, name FROM growreport_custom_hnojeno2 WHERE report_id=:rid ORDER BY item_index ASC");
    $chs->execute(['rid' => $growreport_id]);
    foreach ($chs->fetchAll(PDO::FETCH_ASSOC) as $cr) {
        $ft = $cr['fertilizer_type'] === 'dry' ? 'dry' : 'liquid';
        $customHnojenoConfig[$ft][] = $cr['name'];
    }
} catch (Throwable $e) {
    error_log("načtení custom hnojiv: " . $e->getMessage());
}

foreach ($weeks as &$w) {
    // Načtení hnojení
    $w['hnojeno'] = [];
    $w['hnojenoTotals'] = [];
    $w['hnojenoDays'] = [];
    try {
        $hs = $pdo->prepare("SELECT day_number, fertilizer_type, name, amount, is_custom FROM growreport_hnojeno2 WHERE week_id = :wid");
        $hs->execute(['wid' => $w['id']]);
        foreach ($hs->fetchAll(PDO::FETCH_ASSOC) as $hr) {
            $dn = (int)$hr['day_number'];
            $ft = $hr['fertilizer_type'] === 'dry' ? 'dry' : 'liquid';
            $amt = (float)$hr['amount'];
            if (!isset($w['hnojeno'][$dn])) $w['hnojeno'][$dn] = [];
            $w['hnojeno'][$dn][] = [
                'type' => $ft,
                'name' => $hr['name'],
                'amount' => $amt,
                'is_custom' => (int)$hr['is_custom']
            ];
            if (!isset($w['hnojenoTotals'][$dn])) {
                $w['hnojenoTotals'][$dn] = ['liquid' => 0.0, 'dry' => 0];
            }
            if ($ft === 'liquid') {
                $w['hnojenoTotals'][$dn]['liquid'] += $amt;
            } else {
                $w['hnojenoTotals'][$dn]['dry'] += (int)$amt;
            }
        }
        foreach ($w['hnojenoTotals'] as $dn => $tots) {
            if ($tots['liquid'] > 0 || $tots['dry'] > 0) {
                $w['hnojenoDays'][] = $dn;
            }
        }
    } catch (Throwable $e) {
        error_log("načtení hnojení: " . $e->getMessage());
    }

    $ds = $pdo->prepare("SELECT day_number, zalito, hnojeno FROM growreport_days2 WHERE week_id = :wid");
    $ds->execute(['wid' => $w['id']]);
    foreach ($ds->fetchAll(PDO::FETCH_ASSOC) as $d) {
        if ($d['hnojeno'] && !in_array((int)$d['day_number'], $w['hnojenoDays'], true)) {
            $w['hnojenoDays'][] = (int)$d['day_number'];
        }
    }

    $w['co2'] = [];
    try {
        $cs = $pdo->prepare("SELECT day_number, label1, value1, label2, value2 FROM growreport_co2_2 WHERE week_id = :wid");
        $cs->execute(['wid' => $w['id']]);
        foreach ($cs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $w['co2'][(int)$c['day_number']] = $c;
        }
    } catch (Throwable $e) {
        error_log("načtení CO2: " . $e->getMessage());
    }

    // Načtení zalévání
    $w['watering'] = [];
    $w['wateringTotals'] = [];
    $w['zalitoDays'] = [];
    try {
        $ws2 = $pdo->prepare("SELECT day_number, pot_number, amount FROM growreport_watering2 WHERE week_id = :wid");
        $ws2->execute(['wid' => $w['id']]);
        foreach ($ws2->fetchAll(PDO::FETCH_ASSOC) as $wr) {
            $dn = (int)$wr['day_number'];
            $pn = (int)$wr['pot_number'];
            $amt = (float)$wr['amount'];
            if (!isset($w['watering'][$dn])) $w['watering'][$dn] = [];
            $w['watering'][$dn][$pn] = $amt;
            if (!isset($w['wateringTotals'][$dn])) $w['wateringTotals'][$dn] = 0;
            $w['wateringTotals'][$dn] += $amt;
        }
        foreach ($w['wateringTotals'] as $dn => $total) {
            if ($total > 0) $w['zalitoDays'][] = $dn;
        }
    } catch (Throwable $e) {
        error_log("načtení zalévání: " . $e->getMessage());
    }
}
unset($w);

// Připravit watering data pro JS
$wateringJS = [];
foreach ($weeks as $w) {
    $wn = (int)$w['week_number'];
    $wateringJS[$wn] = $w['watering'];
}

// Připravit hnojeno data pro JS
$hnojenoJS = [];
foreach ($weeks as $w) {
    $wn = (int)$w['week_number'];
    $hnojenoJS[$wn] = $w['hnojeno'];
}

$predefinedLiquid = [
    'Melasový čaj', 'Moč', 'Kořenový Stimulátor', 'Banán tekutý', 'Banán tuhý',
    'Kopřiva-Řasa', 'Ptačí trus', 'Kukuřice enzym', 'Pivní enzym', 'Voda z bongu',
    'Pivní kvasnice', 'Kvasnice droždí', 'Skořápky hnojivo', 'Pivní kal'
];
$predefinedDry = [
    'Green Crack', 'Popel', 'Vápník-Křemík', 'Popel z trávy', 'Micorize'
];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Growreport: <?= htmlspecialchars($growreport['name']) ?></title>
    <link rel="stylesheet" href="styly.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; margin: 0; padding: 0; }
        .header-container { padding: 1.5rem; background-color: rgba(76,175,80,0.95); color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; border-radius: 0 0 15px 15px; text-align: center; }
        .header-container h1 { margin: 0; font-size: 2rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); letter-spacing: 1px; display: flex; align-items: center; justify-content: center; }
        .header-container h1 i { margin-right: 12px; }
        nav ul { list-style-type: none; display: flex; margin: 10px 0 0 0; padding: 0; flex-wrap: wrap; }
        nav ul li { margin-right: 20px; margin-bottom: 5px; }
        nav ul li a { color: white; text-decoration: none; font-weight: 600; transition: all 0.3s; padding: 5px 10px; border-radius: 4px; font-size: 16px; display: flex; align-items: center; }
        .growreport { width: 30cm; height: 10cm; border: 2px solid #ccc; margin: 20px auto; display: flex; flex-direction: column; }
        .growreport-header { display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: lightgray; height: 1cm; font-size: 18px; font-weight: bold; }
        .growreport-header button { padding: 5px 10px; font-size: 14px; cursor: pointer; }
        .growreport-header .growth-btn { background-color: green; color: white; }
        .growreport-header .flower-btn { background-color: red; color: white; }
        .growreport-header .dry-btn { background-color: pink; color: white; }
        .growreport-header .delete-week-btn { background-color: black; color: red; font-weight: bold; font-size: 16px; line-height: 1; border: 1px solid #333; margin-left: 4px; padding: 5px 10px; }
        .growreport-header .delete-week-btn:hover { background-color: #222; color: #ff4444; }
        .growreport-content { display: grid; grid-template-columns: repeat(5, 1fr); flex: 1; }
        .growreport-content div { border: 1px solid #ccc; display: flex; justify-content: center; align-items: center; position: relative; font-size: 12px; color: #000; overflow: hidden; flex-direction: column; }
        .growreport-content div span { font-size: 20px; color: white; font-weight: bold; }
        .growreport-content img { max-width: 100%; max-height: 200px; object-fit: contain; }
        .growreport-content input[type="file"] { position: absolute; opacity: 0; height: 100%; width: 100%; cursor: pointer; }
        .growreport-content input[type="file"]::-webkit-file-upload-button { visibility: hidden; }
        .growreport-content input[type="file"]::file-selector-button { visibility: hidden; }
        .growreport-content label { position: absolute; top: 10%; left: 50%; transform: translate(-50%, -50%); text-align: center; font-size: 14px; color: #888; pointer-events: none; }
        .growreport-content textarea { width: 90%; height: 100%; padding: 5px; resize: none; margin: auto; color: black; background-color: White; }
        .growreport-content button { margin-top: auto; padding: 5px 10px; font-size: 14px; cursor: pointer; }
        #add-week { display: inline-block; margin: 20px auto; padding: 10px 20px; font-size: 16px; cursor: pointer; }
        #save-growreport, #share-growreport, #delete-growreport { display: inline-block; margin: 20px auto; padding: 10px 20px; font-size: 16px; cursor: pointer; font-weight: 600; border: none; border-radius: 5px; color: white; transition: all 0.3s; }
        #save-growreport { background-color: #4CAF50; }
        #save-growreport:hover { background-color: #388E3C; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        #share-growreport { background-color: #ff9800; margin-left: 10px; }
        #share-growreport:hover { background-color: #f57c00; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        #delete-growreport { background-color: #f44336; margin-left: 10px; }
        #delete-growreport:hover { background-color: #d32f2f; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .growreport-buttons { display: flex; justify-content: center; margin: 20px 0; }
        .growreport-content input[type="checkbox"] { width: 20px; height: 34px; cursor: pointer; }
        .growreport-content label { font-size: 16px; margin-left: 5px; line-height: 20px; }
        .day-box { width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; position: relative; }
        .day-box .day-zalito { position: absolute; background-color: transparent; width: 33.333%; height: 100%; left: 0; }
        .day-box .day-hnojeno { position: absolute; background-color: transparent; width: 33.333%; height: 100%; left: 33.333%; right: auto; }
        .day-box .day-co2 { position: absolute; background-color: transparent; width: 33.334%; height: 100%; right: 0; }
        .day-box .day-co2.on { background-color: gray; }
        .day-box span { position: relative; z-index: 1; color: black; }
        textarea[placeholder="Sekce 2: Text"] { color: blue; border: 2px solid blue; cursor: pointer; }
        textarea[placeholder="Sekce 3: Text"] { color: brown; border: 2px solid brown; cursor: pointer; }
        textarea[placeholder="Sekce 1: Text"] { color: black; border: 2px solid black; }
        textarea[placeholder="Sekce 4: Text"] { color: #2E7D32; border: 2px solid #2E7D32; }
        .hidden { display: none; }
        .day { width: 1cm; height: 1cm; display: flex; align-items: center; justify-content: center; border: 1px solid #ccc; }
        .growreport-content > div:nth-child(5) > div:nth-child(1),
        .growreport-content > div:nth-child(5) > div:nth-child(2),
        .growreport-content > div:nth-child(5) > div:nth-child(3),
        .growreport-content > div:nth-child(5) > div:nth-child(4) { border: none !important; }
        .growreport-content > div:nth-child(5) > div:nth-child(1) > div,
        .growreport-content > div:nth-child(5) > div:nth-child(2) > div,
        .growreport-content > div:nth-child(5) > div:nth-child(3) > div,
        .growreport-content > div:nth-child(5) > div:nth-child(4) > div { border: none !important; }
        .day-box { border: none !important; }
        .growreport-content > div:nth-child(5) > div:nth-child(2) > div > div,
        .growreport-content > div:nth-child(5) > div:nth-child(3) > div > div { border: none !important; }
        .day-zalito[style*="background-color: blue"] { background-color: blue !important; }
        .day-hnojeno[style*="background-color: brown"] { background-color: brown !important; }

        /* CO2 */
        .growreport-content .co2-cell { height: 40px; width: 100%; border: none !important; display: flex; align-items: center; justify-content: center; }
        .growreport-content button.co2-btn { margin: 0 auto; padding: 0; width: 90%; height: 36px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f1f8e9; color: red; border: 1px solid #2E7D32; border-radius: 4px; font-size: 9px; line-height: 1.15; font-weight: 600; cursor: pointer; overflow: hidden; white-space: nowrap; }
        .growreport-content button.co2-btn:hover { background: #dcedc8; }
        .growreport-content button.co2-btn:disabled { cursor: default; color: red; opacity: 1; }
        .growreport-content button.co2-btn .co2-line { font-size: 9px; line-height: 1.15; color: red; }
        .growreport-content button.co2-btn .co2-plus { font-size: 12px; color: #888; font-weight: 400; }

        /* Zalito tlačítka (stejná výška jako den) */
        .growreport-content .zalito-cell { height: 40px; width: 100%; border: none !important; display: flex; align-items: center; justify-content: center; }
        .growreport-content button.zalito-btn { margin: 0 auto; padding: 0; width: 90%; height: 36px; display: flex; align-items: center; justify-content: center; background: #e3f2fd; color: blue; border: 1px solid #1565C0; border-radius: 4px; font-size: 18px; font-weight: 700; cursor: pointer; overflow: hidden; white-space: nowrap; }
        .growreport-content button.zalito-btn:hover { background: #bbdefb; }
        .growreport-content button.zalito-btn:disabled { cursor: default; color: blue; opacity: 1; }
        .growreport-content button.zalito-btn .zal-total { font-size: 18px; color: blue; font-weight: 700; }
        .growreport-content button.zalito-btn .zal-total.small { font-size: 13px; }
        .growreport-content button.zalito-btn .zal-plus { font-size: 12px; color: #888; font-weight: 400; }

        /* Hnojeno tlačítka */
        .growreport-content .hnojeno-cell { height: 40px; width: 100%; border: none !important; display: flex; align-items: center; justify-content: center; }
        .growreport-content button.hnojeno-btn { margin: 0 auto; padding: 0; width: 90%; height: 36px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #efebe9; border: 1px solid brown; border-radius: 4px; cursor: pointer; overflow: hidden; white-space: nowrap; }
        .growreport-content button.hnojeno-btn:hover { background: #d7ccc8; }
        .growreport-content button.hnojeno-btn:disabled { cursor: default; opacity: 1; }
        .growreport-content button.hnojeno-btn .hnoj-liquid { font-size: 13px; font-weight: 700; color: #1b5e20; }
        .growreport-content button.hnojeno-btn .hnoj-dry { font-size: 14px; font-weight: 700; color: brown; }
        .growreport-content button.hnojeno-btn.has-both .hnoj-liquid { font-size: 10px; line-height: 1.15; }
        .growreport-content button.hnojeno-btn.has-both .hnoj-dry { font-size: 11px; line-height: 1.15; }
        .growreport-content button.hnojeno-btn .hnoj-plus { font-size: 12px; color: #888; font-weight: 400; }

        /* Hnojeno dialog */
        .hnojeno-dialog-header { background-color: #5D4037; color: white; padding: 12px 20px; text-align: center; }
        .hnojeno-dialog-header h3 { margin: 0; font-size: 20px; }
        .hnojeno-dialog-body { padding: 14px 18px; max-height: 75vh; overflow-y: auto; }
        .hnoj-toggle-bar { display: flex; justify-content: center; gap: 10px; margin-bottom: 12px; }
        .hnoj-tab-btn { padding: 8px 16px; font-size: 14px; font-weight: 600; border: 2px solid #5D4037; border-radius: 6px; background: white; color: #5D4037; cursor: pointer; transition: all 0.2s; font-family: 'Montserrat', sans-serif; }
        .hnoj-tab-btn.active { background: #5D4037; color: white; }
        .hnoj-table-container { display: flex; gap: 14px; width: 100%; box-sizing: border-box; }
        .hnoj-col { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .hnoj-col-header { padding: 8px 10px; border-radius: 4px; font-weight: 700; font-size: 13px; text-align: center; margin-bottom: 8px; }
        .hnoj-col-header.liquid { background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
        .hnoj-col-header.dry { background-color: #efebe9; color: #5D4037; border: 1px solid #d7ccc8; }
        .hnoj-list { max-height: 340px; overflow-y: auto; padding-right: 4px; display: flex; flex-direction: column; gap: 5px; }
        .hnoj-row { display: flex; align-items: center; justify-content: space-between; padding: 6px 8px; background: #fafafa; border: 1px solid #e0e0e0; border-radius: 4px; gap: 6px; box-sizing: border-box; }
        .hnoj-row:hover { background: #f5f5f5; }
        .hnoj-row-left { display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; min-width: 0; }
        .hnoj-row-left input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; margin: 0; }
        .hnoj-row-left .hnoj-name { font-size: 13px; font-weight: 500; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hnoj-custom-name { flex: 1; min-width: 0; padding: 4px 6px; font-size: 12px; border: 1px solid #ccc; border-radius: 3px; font-family: 'Montserrat', sans-serif; }
        .hnoj-input-wrap { display: flex; align-items: center; gap: 2px; flex-shrink: 0; }
        .hnoj-step-btn { width: 22px; height: 26px; border: 1px solid #bbb; background: #eee; border-radius: 3px; font-weight: bold; cursor: pointer; font-size: 14px; padding: 0; line-height: 1; display: flex; align-items: center; justify-content: center; }
        .hnoj-step-btn:hover { background: #ddd; }
        .hnoj-qty { width: 54px; height: 26px; padding: 2px 4px; text-align: center; border: 1px solid #bbb; border-radius: 3px; font-size: 12px; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        .hnoj-qty:disabled { background: #f0f0f0; color: #999; }
        .hnoj-unit { font-size: 11px; color: #666; width: 28px; text-align: left; padding-left: 2px; }
        .hnoj-totals-bar { display: flex; justify-content: space-around; padding: 12px 10px 4px; margin-top: 10px; border-top: 1px solid #eee; font-size: 15px; font-weight: 600; }
        .hnoj-total-liquid { color: #1b5e20; }
        .hnoj-total-dry { color: #5D4037; }

        /* Zvětšené okénko CO2 */
        #co2-tip { position: fixed; display: none; z-index: 5000; pointer-events: none; background: #fff; border: 2px solid #2E7D32; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.35); color: red; font-weight: 700; padding: 6px 10px; box-sizing: border-box; text-align: center; white-space: nowrap; flex-direction: column; align-items: center; justify-content: center; }
        #co2-tip .tip-line { font-size: 20px; line-height: 1.3; color: red; }

        /* CO2 dialog */
        .co2-dialog-header { background-color: #2E7D32; color: white; padding: 12px 20px; text-align: center; }
        .co2-dialog-header h3 { margin: 0; font-size: 20px; }
        .co2-dialog-body { padding: 18px 20px; }
        .co2-row { display: flex; gap: 10px; margin-bottom: 12px; align-items: center; }
        .co2-row input { padding: 8px; font-size: 15px; border: 1px solid #bbb; border-radius: 4px; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        .co2-row .co2-label-input { flex: 1; min-width: 0; }
        .co2-row .co2-value-input { width: 90px; text-align: center; }
        .co2-hint { font-size: 12px; color: #777; text-align: center; margin-top: 4px; }

        /* Pot config dialog */
        .pots-dialog-header { background-color: #1565C0; color: white; padding: 12px 20px; text-align: center; }
        .pots-dialog-header h3 { margin: 0; font-size: 20px; }
        .pots-dialog-body { padding: 18px 20px; }
        .pots-count-row { display: flex; gap: 10px; margin-bottom: 16px; align-items: center; justify-content: center; }
        .pots-count-row label { font-weight: 600; font-size: 15px; }
        .pots-count-row select { padding: 8px 12px; font-size: 16px; border: 1px solid #bbb; border-radius: 4px; font-family: 'Montserrat', sans-serif; }
        .pot-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .pot-row span { font-weight: 600; min-width: 90px; }
        .pot-row input { padding: 8px; font-size: 15px; border: 1px solid #bbb; border-radius: 4px; width: 100px; text-align: center; font-family: 'Montserrat', sans-serif; }

        /* Watering dialog */
        .watering-dialog-header { background-color: #1565C0; color: white; padding: 12px 20px; text-align: center; }
        .watering-dialog-header h3 { margin: 0; font-size: 20px; }
        .watering-dialog-body { padding: 18px 10px; }
        .watering-pots-row { display: flex; gap: 8px; justify-content: center; overflow-x: auto; padding: 0 5px; }
        .pot-card { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 64px; }
        .pot-card .pot-lbl { font-weight: 700; font-size: 13px; color: #1565C0; }
        .pot-card .pot-vol { font-size: 11px; color: #666; margin-bottom: 2px; }
        /* Scroll picker */
        .sp-wrap { width: 64px; height: 120px; overflow-y: auto; scroll-snap-type: y mandatory; border: 2px solid #1565C0; border-radius: 8px; background: #f0f4ff; position: relative; -webkit-overflow-scrolling: touch; }
        .sp-wrap::-webkit-scrollbar { width: 0; }
        .sp-pad { height: 40px; flex-shrink: 0; }
        .sp-item { height: 40px; scroll-snap-align: center; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #bbb; flex-shrink: 0; user-select: none; }
        .sp-item.sp-sel { color: #1565C0; font-weight: 700; font-size: 22px; background: rgba(21,101,192,0.08); }
        .watering-total { text-align: center; font-size: 22px; color: blue; font-weight: 700; margin-top: 14px; }

        .preview-image { max-width: 100%; max-height: 200px; margin-top: 10px; display: none; border: 1px solid #ddd; border-radius: 4px; }
        .compression-info { font-size: 12px; color: #4CAF50; margin-top: 5px; text-align: center; display: none; }
        .loading-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(0, 0, 0, 0.1); border-radius: 50%; border-top-color: #4CAF50; animation: spin 1s ease-in-out infinite; margin-right: 5px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        footer { text-align: center; padding: 20px; background-color: rgba(76,175,80,0.95); color: white; margin-top: 50px; }
        .report-header { text-align: center; margin: 20px 0; padding: 10px; }
        .report-header h2 { margin-bottom: 5px; color: #2E7D32; }
        .report-header p { margin: 5px 0; color: #555; }
        .confirm-dialog-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.7); z-index: 1000; display: none; justify-content: center; align-items: center; }
        .confirm-dialog { background-color: #fff; border-radius: 8px; overflow: hidden; width: 90%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); animation: dialog-appear 0.3s ease; }
        @keyframes dialog-appear { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .confirm-header { background-color: #f44336; color: white; padding: 15px 20px; display: flex; align-items: center; justify-content: center; position: relative; }
        .confirm-header h3 { margin: 0; font-size: 22px; text-align: center; }
        .confirm-header i { font-size: 28px; margin-right: 15px; }
        .confirm-content { padding: 20px; font-size: 16px; color: #333; text-align: center; }
        .confirm-actions { display: flex; justify-content: center; padding: 15px 20px; background-color: #f8f8f8; border-top: 1px solid #eee; gap: 15px; }
        .confirm-button { padding: 10px 20px; font-size: 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; min-width: 100px; transition: all 0.2s; }
        .confirm-cancel { background-color: #e0e0e0; color: #333; }
        .confirm-cancel:hover { background-color: #d0d0d0; }
        .confirm-delete { background-color: #f44336; color: white; }
        .confirm-delete:hover { background-color: #d32f2f; }
        .confirm-ok { background-color: #4CAF50; color: white; }
        .confirm-ok:hover { background-color: #388E3C; }
        .confirm-ok-blue { background-color: #1565C0; color: white; }
        .confirm-ok-blue:hover { background-color: #0D47A1; }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <h1><i class="fas fa-seedling"></i> Growreports.cz - Detail Growreportu</h1>
        <nav><?php include('menu.php'); ?></nav>
    </div>
</header>

<main>
    <div class="report-header">
        <h2><?= htmlspecialchars($growreport['name']) ?></h2>
        <p><strong>Autor:</strong> <?= htmlspecialchars($growreport['author']) ?> | <strong>Vytvořeno:</strong> <?= date('d.m.Y H:i', strtotime($growreport['created_at'])) ?></p>
    </div>

    <div id="growreport-container" data-id="<?= (int)$growreport['id'] ?>">
        <?php foreach ($weeks as $week):
            $wn = (int)$week['week_number'];
            $bg = !empty($week['phase_color']) ? $week['phase_color'] : 'white';
        ?>
            <div class="growreport" data-week="<?= $wn ?>">
                <div class="growreport-header" style="background-color: <?= htmlspecialchars($bg) ?>">
                    <span id="week-title-<?= $wn ?>">
                        Týden <?= $wn ?> -
                        <?= htmlspecialchars($growreport['name']) ?> -
                        <?= htmlspecialchars($growreport['author']) ?>
                    </span>
                    <?php if ($isOwner): ?>
                    <div>
                        <button class="growth-btn" onclick="setColor(<?= $wn ?>, 'RŮST', 'green')">📈Růst</button>
                        <button class="flower-btn" onclick="setColor(<?= $wn ?>, 'KVĚT', 'red')">🌼Květ</button>
                        <button class="dry-btn" onclick="setColor(<?= $wn ?>, 'SUŠINA', 'pink')">🌿Sušina</button>
                        <button class="delete-week-btn" type="button" title="Smazat tento týden" onclick="showDeleteWeekConfirmation(<?= $wn ?>)">✕</button>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="growreport-content">
                    <?php for ($p = 1; $p <= 3; $p++):
                        $photo = $week['photo' . $p];
                    ?>
                    <div>
                        <?php if ($isOwner): ?>
                            <label for="file-input-<?= $wn ?>-<?= $p ?>">Sem Vložte Foto</label>
                            <input id="file-input-<?= $wn ?>-<?= $p ?>" type="file" accept="image/*" onchange="previewImage(event, this)">
                        <?php endif; ?>
                        <?php if (!empty($photo) && file_exists($photo)): ?>
                            <img src="<?= htmlspecialchars($photo) ?>" alt="Foto <?= $p ?>" style="display: block;">
                        <?php else: ?>
                            <p style="color: #888;"><i class="fas fa-image"></i> Žádná fotka</p>
                        <?php endif; ?>
                        <img class="preview-image" id="preview-<?= $wn ?>-<?= $p ?>" src="#" alt="Náhled">
                        <div class="compression-info" id="compression-info-<?= $wn ?>-<?= $p ?>"></div>
                    </div>
                    <?php endfor; ?>

                    <div>
                        <textarea id="weekText-<?= $wn ?>" <?= $isOwner ? '' : 'readonly' ?>><?= htmlspecialchars($week['text_content'] ?? '') ?></textarea>
                        <?php if ($isOwner): ?>
                            <button onclick="saveText(<?= $wn ?>)">⏬Uložit text</button>
                        <?php endif; ?>
                        <div id="responseMessage-text-<?= $wn ?>"></div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
                        <!-- DNY -->
                        <div>
                            <textarea placeholder="Sekce 1: Text" readonly>Dny</textarea>
                            <div>
                                <?php for ($i = 1; $i <= 7; $i++):
                                    $c = $week['co2'][$i] ?? null;
                                    $hasCo2 = $c && ($c['value1'] !== null || $c['value2'] !== null);
                                    $hasWater = in_array($i, $week['zalitoDays']);
                                ?>
                                    <div id="day-box-<?= $wn ?>-<?= $i ?>" class="day-box">
                                        <div id="day-zalito-<?= $wn ?>-<?= $i ?>" class="day-zalito" <?= $hasWater ? 'style="background-color: blue;"' : '' ?>></div>
                                        <div id="day-hnojeno-<?= $wn ?>-<?= $i ?>" class="day-hnojeno" <?= in_array($i, $week['hnojenoDays']) ? 'style="background-color: brown;"' : '' ?>></div>
                                        <div id="day-co2-<?= $wn ?>-<?= $i ?>" class="day-co2<?= $hasCo2 ? ' on' : '' ?>"></div>
                                        <span><?= $wn * 7 - (7 - $i) ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- ZALITO (tlačítka) -->
                        <div>
                            <textarea placeholder="Sekce 2: Text" readonly <?= $isOwner ? 'onclick="openPotsConfig()"' : '' ?>>Zalito</textarea>
                            <div>
                                <?php for ($i = 1; $i <= 7; $i++):
                                    $wTotal = $week['wateringTotals'][$i] ?? 0;
                                    $wTotalStr = ($wTotal == (int)$wTotal) ? (string)(int)$wTotal : number_format($wTotal, 1, '.', '');
                                ?>
                                    <div class="zalito-cell">
                                        <button type="button" class="zalito-btn" id="zal-<?= $wn ?>-<?= $i ?>"
                                                data-total="<?= $wTotalStr ?>"
                                                <?= $isOwner ? 'onclick="openWatering(' . $wn . ', ' . $i . ')"' : 'disabled' ?>>
                                            <?php if ($wTotal > 0): ?>
                                                <span class="zal-total <?= strlen($wTotalStr) > 2 ? 'small' : '' ?>"><?= $wTotalStr ?></span>
                                            <?php else: ?>
                                                <span class="zal-plus"><?= $isOwner ? '+' : '' ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- HNOJENO -->
                        <div>
                            <textarea placeholder="Sekce 3: Text" readonly <?= $isOwner ? 'onclick="openHnojeno(' . $wn . ', 1)"' : '' ?>>Hnojeno</textarea>
                            <div>
                                <?php for ($i = 1; $i <= 7; $i++):
                                    $hTotals = $week['hnojenoTotals'][$i] ?? ['liquid' => 0.0, 'dry' => 0];
                                    $hLiq = (float)($hTotals['liquid'] ?? 0);
                                    $hDry = (int)($hTotals['dry'] ?? 0);
                                    $hLiqStr = ($hLiq == (int)$hLiq) ? (string)(int)$hLiq : number_format($hLiq, 1, '.', '');
                                    $hasBoth = ($hLiq > 0 && $hDry > 0);
                                ?>
                                    <div class="hnojeno-cell">
                                        <button type="button" class="hnojeno-btn<?= $hasBoth ? ' has-both' : '' ?>" id="hnoj-<?= $wn ?>-<?= $i ?>"
                                                data-liquid="<?= $hLiqStr ?>"
                                                data-dry="<?= $hDry ?>"
                                                <?= $isOwner ? 'onclick="openHnojeno(' . $wn . ', ' . $i . ')"' : 'disabled' ?>>
                                            <?php if ($hLiq > 0 || $hDry > 0): ?>
                                                <?php if ($hLiq > 0): ?><span class="hnoj-liquid"><?= $hLiqStr ?> ml</span><?php endif; ?>
                                                <?php if ($hDry > 0): ?><span class="hnoj-dry"><?= $hDry ?></span><?php endif; ?>
                                            <?php else: ?>
                                                <span class="hnoj-plus"><?= $isOwner ? '+' : '' ?></span>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- CO2 -->
                        <div>
                            <textarea placeholder="Sekce 4: Text" readonly>CO2</textarea>
                            <div>
                                <?php for ($i = 1; $i <= 7; $i++):
                                    $c = $week['co2'][$i] ?? null;
                                    $l1 = $c['label1'] ?? 'Ráno';
                                    $l2 = $c['label2'] ?? 'Večer';
                                    $v1 = ($c && $c['value1'] !== null) ? (string)$c['value1'] : '';
                                    $v2 = ($c && $c['value2'] !== null) ? (string)$c['value2'] : '';
                                ?>
                                    <div class="co2-cell">
                                        <button type="button" class="co2-btn" id="co2-<?= $wn ?>-<?= $i ?>"
                                                data-l1="<?= htmlspecialchars($l1, ENT_QUOTES) ?>" data-v1="<?= htmlspecialchars($v1, ENT_QUOTES) ?>"
                                                data-l2="<?= htmlspecialchars($l2, ENT_QUOTES) ?>" data-v2="<?= htmlspecialchars($v2, ENT_QUOTES) ?>"
                                                <?= $isOwner ? 'onclick="openCo2(this, ' . $wn . ', ' . $i . ')"' : 'disabled' ?>>
                                            <?php if ($v1 === '' && $v2 === ''): ?>
                                                <span class="co2-plus"><?= $isOwner ? '+' : '' ?></span>
                                            <?php else: ?>
                                                <?php if ($v1 !== ''): ?><span class="co2-line"><?= htmlspecialchars(mb_strtoupper(mb_substr($l1, 0, 1)) . $v1) ?></span><?php endif; ?>
                                                <?php if ($v2 !== ''): ?><span class="co2-line"><?= htmlspecialchars(mb_strtoupper(mb_substr($l2, 0, 1)) . $v2) ?></span><?php endif; ?>
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- START DAY -->
                        <div>
                            <?php if ($isOwner): ?>
                                <button type="button" onclick="saveStartDay(<?= $wn ?>)">⏬Ulož</button>
                            <?php endif; ?>
                            <input type="text" id="start-day-<?= $wn ?>" placeholder="Např. čtvrtek" value="<?= htmlspecialchars($week['start_day'] ?? '') ?>" <?= $isOwner ? '' : 'readonly' ?> onchange="updateDays(<?= $wn ?>)">
                            <div id="days-<?= $wn ?>"></div>
                            <div id="responseMessage-<?= $wn ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($isOwner): ?>
        <button id="add-week" onclick="addWeek()">📥Další týden</button>
        <div class="growreport-buttons">
            <button id="save-growreport" type="button" onclick="saveGrowreport()">⏬Uložit Growreport</button>
            <button id="share-growreport" type="button" onclick="shareGrowreport()">🌟Pochlub se</button>
            <button id="delete-growreport" type="button" onclick="showDeleteConfirmation()">🗑️ Smazat growreport</button>
        </div>
    <?php endif; ?>
</main>

<!-- Delete growreport dialog -->
<div id="delete-confirmation" class="confirm-dialog-overlay">
    <div class="confirm-dialog">
        <div class="confirm-header"><i class="fas fa-exclamation-triangle"></i><h3>POZOR!</h3></div>
        <div class="confirm-content"><p>Opravdu chcete smazat tento growreport a všechna jeho data?</p><p><strong>Tato akce je nevratná!</strong></p></div>
        <div class="confirm-actions">
            <button id="cancel-delete" class="confirm-button confirm-cancel">Zrušit</button>
            <button id="confirm-delete" class="confirm-button confirm-delete">Smazat</button>
        </div>
    </div>
</div>

<!-- Delete week dialog -->
<div id="delete-week-confirmation" class="confirm-dialog-overlay">
    <div class="confirm-dialog">
        <div class="confirm-header"><i class="fas fa-exclamation-triangle"></i><h3>POZOR!</h3></div>
        <div class="confirm-content"><p>Opravdu chcete smazat <strong id="delete-week-label">tento týden</strong> včetně fotek, textu a zalití/hnojení?</p><p><strong>Tato akce je nevratná!</strong></p></div>
        <div class="confirm-actions">
            <button id="cancel-delete-week" class="confirm-button confirm-cancel">Zrušit</button>
            <button id="confirm-delete-week" class="confirm-button confirm-delete">Smazat týden</button>
        </div>
    </div>
</div>

<!-- CO2 dialog -->
<div id="co2-dialog" class="confirm-dialog-overlay">
    <div class="confirm-dialog" style="max-width: 400px;">
        <div class="co2-dialog-header"><h3 id="co2-dialog-title">CO2 (PPM)</h3></div>
        <div class="co2-dialog-body">
            <div class="co2-row">
                <input type="text" id="co2-label1" class="co2-label-input" maxlength="20" placeholder="Název (Ráno)">
                <input type="text" id="co2-value1" class="co2-value-input" maxlength="4" inputmode="numeric" placeholder="PPM">
            </div>
            <div class="co2-row">
                <input type="text" id="co2-label2" class="co2-label-input" maxlength="20" placeholder="Název (Večer)">
                <input type="text" id="co2-value2" class="co2-value-input" maxlength="4" inputmode="numeric" placeholder="PPM">
            </div>
            <div class="co2-hint">Název můžeš přejmenovat. Hodnota: max. 4 číslice.</div>
        </div>
        <div class="confirm-actions">
            <button type="button" id="co2-cancel" class="confirm-button confirm-cancel">Zrušit</button>
            <button type="button" id="co2-ok" class="confirm-button confirm-ok">OK</button>
        </div>
    </div>
</div>

<!-- Pot config dialog -->
<div id="pots-dialog" class="confirm-dialog-overlay">
    <div class="confirm-dialog" style="max-width: 420px;">
        <div class="pots-dialog-header"><h3>Nastavení květináčů</h3></div>
        <div class="pots-dialog-body">
            <div class="pots-count-row">
                <label for="pots-count">Počet květináčů:</label>
                <select id="pots-count" onchange="renderPotRows()">
                    <?php for ($pc = 1; $pc <= 9; $pc++): ?>
                        <option value="<?= $pc ?>"><?= $pc ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div id="pots-rows"></div>
        </div>
        <div class="confirm-actions">
            <button type="button" id="pots-cancel" class="confirm-button confirm-cancel">Zrušit</button>
            <button type="button" id="pots-save" class="confirm-button confirm-ok-blue">Uložit</button>
        </div>
    </div>
</div>

<!-- Watering dialog -->
<div id="watering-dialog" class="confirm-dialog-overlay">
    <div class="confirm-dialog" style="max-width: 700px;">
        <div class="watering-dialog-header"><h3 id="watering-dialog-title">Zalévání</h3></div>
        <div class="watering-dialog-body">
            <div id="watering-pots-row" class="watering-pots-row"></div>
            <div id="watering-total" class="watering-total">Celkem: 0 L</div>
        </div>
        <div class="confirm-actions">
            <button type="button" id="watering-cancel" class="confirm-button confirm-cancel">Zrušit</button>
            <button type="button" id="watering-ok" class="confirm-button confirm-ok-blue">OK</button>
        </div>
    </div>
</div>

<!-- Hnojeno dialog -->
<div id="hnojeno-dialog" class="confirm-dialog-overlay">
    <div class="confirm-dialog" style="max-width: 720px; width: 95%;">
        <div class="hnojeno-dialog-header">
            <h3 id="hnojeno-dialog-title">Hnojení – Den 1</h3>
        </div>
        <div class="hnojeno-dialog-body">
            <div class="hnoj-toggle-bar">
                <button type="button" id="hnoj-tab-pred" class="hnoj-tab-btn active" onclick="showHnojTable('pred')">Předvolená hnojiva</button>
                <button type="button" id="hnoj-tab-cust" class="hnoj-tab-btn" onclick="showHnojTable('cust')">Vlastní hnojiva</button>
            </div>

            <!-- Tabulka 1: Předvolená -->
            <div id="hnoj-table-pred" class="hnoj-table-container">
                <!-- Sloupec 1: Tekutá hnojiva -->
                <div class="hnoj-col">
                    <div class="hnoj-col-header liquid">💧 Tekutá hnojiva (0.5 – 500 ml)</div>
                    <div class="hnoj-list" id="hnoj-pred-liquid-list">
                        <?php foreach ($predefinedLiquid as $pName): ?>
                            <div class="hnoj-row">
                                <label class="hnoj-row-left">
                                    <input type="checkbox" class="hnoj-cb" data-type="liquid" data-custom="0" data-name="<?= htmlspecialchars($pName, ENT_QUOTES) ?>" onchange="hnojToggleItem(this)">
                                    <span class="hnoj-name" title="<?= htmlspecialchars($pName, ENT_QUOTES) ?>"><?= htmlspecialchars($pName) ?></span>
                                </label>
                                <div class="hnoj-input-wrap">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, -1)">-</button>
                                    <input type="number" class="hnoj-qty" min="0.5" max="500" step="0.5" placeholder="ml" disabled oninput="hnojQtyChanged(this)">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, 1)">+</button>
                                    <span class="hnoj-unit">ml</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sloupec 2: Suchá hnojiva -->
                <div class="hnoj-col">
                    <div class="hnoj-col-header dry">🌾 Suchá hnojiva (1 – 30 odměrek)</div>
                    <div class="hnoj-list" id="hnoj-pred-dry-list">
                        <?php foreach ($predefinedDry as $pName): ?>
                            <div class="hnoj-row">
                                <label class="hnoj-row-left">
                                    <input type="checkbox" class="hnoj-cb" data-type="dry" data-custom="0" data-name="<?= htmlspecialchars($pName, ENT_QUOTES) ?>" onchange="hnojToggleItem(this)">
                                    <span class="hnoj-name" title="<?= htmlspecialchars($pName, ENT_QUOTES) ?>"><?= htmlspecialchars($pName) ?></span>
                                </label>
                                <div class="hnoj-input-wrap">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, -1)">-</button>
                                    <input type="number" class="hnoj-qty" min="1" max="30" step="1" placeholder="odm." disabled oninput="hnojQtyChanged(this)">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, 1)">+</button>
                                    <span class="hnoj-unit">odm.</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabulka 2: Vlastní -->
            <div id="hnoj-table-cust" class="hnoj-table-container" style="display: none;">
                <!-- Sloupec 1: Vlastní tekutá -->
                <div class="hnoj-col">
                    <div class="hnoj-col-header liquid">💧 Vlastní tekutá hnojiva (0.5 – 500 ml)</div>
                    <div class="hnoj-list" id="hnoj-cust-liquid-list">
                        <?php for ($ci = 0; $ci < 10; $ci++): ?>
                            <div class="hnoj-row">
                                <div class="hnoj-row-left">
                                    <input type="checkbox" class="hnoj-cb" data-type="liquid" data-custom="1" onchange="hnojToggleItem(this)">
                                    <input type="text" class="hnoj-custom-name" maxlength="50" placeholder="Vlastní tekuté <?= $ci + 1 ?>" oninput="hnojCustomNameChanged(this)">
                                </div>
                                <div class="hnoj-input-wrap">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, -1)">-</button>
                                    <input type="number" class="hnoj-qty" min="0.5" max="500" step="0.5" placeholder="ml" disabled oninput="hnojQtyChanged(this)">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, 1)">+</button>
                                    <span class="hnoj-unit">ml</span>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Sloupec 2: Vlastní suchá -->
                <div class="hnoj-col">
                    <div class="hnoj-col-header dry">🌾 Vlastní suchá hnojiva (1 – 30 odměrek)</div>
                    <div class="hnoj-list" id="hnoj-cust-dry-list">
                        <?php for ($ci = 0; $ci < 10; $ci++): ?>
                            <div class="hnoj-row">
                                <div class="hnoj-row-left">
                                    <input type="checkbox" class="hnoj-cb" data-type="dry" data-custom="1" onchange="hnojToggleItem(this)">
                                    <input type="text" class="hnoj-custom-name" maxlength="50" placeholder="Vlastní suché <?= $ci + 1 ?>" oninput="hnojCustomNameChanged(this)">
                                </div>
                                <div class="hnoj-input-wrap">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, -1)">-</button>
                                    <input type="number" class="hnoj-qty" min="1" max="30" step="1" placeholder="odm." disabled oninput="hnojQtyChanged(this)">
                                    <button type="button" class="hnoj-step-btn" onclick="stepHnojQty(this, 1)">+</button>
                                    <span class="hnoj-unit">odm.</span>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="hnoj-totals-bar">
                <span class="hnoj-total-liquid">Celkem tekutá: <strong id="hnoj-total-liquid-val">0</strong> ml</span>
                <span class="hnoj-total-dry">Celkem suchá: <strong id="hnoj-total-dry-val">0</strong></span>
            </div>
        </div>
        <div class="confirm-actions">
            <button type="button" id="hnojeno-cancel" class="confirm-button confirm-cancel">Zrušit</button>
            <button type="button" id="hnojeno-save" class="confirm-button confirm-ok" style="background-color: #5D4037;">Uložit</button>
        </div>
    </div>
</div>

<!-- CO2 tip -->
<div id="co2-tip"></div>

<footer><p>&copy; 2025 Growreports.cz - Vytvořil Viktor Dufka</p></footer>

<script>
    const reportId = <?= (int)$growreport['id'] ?>;
    const API = 'view_growreport2.php?id=' + reportId + '&action=';
    const compressedFiles = {};
    let weekToDelete = null;
    let co2Ctx = null;
    let wateringCtx = null;
    let hnojenoCtx = null;

    // Pot config
    let potsConfig = <?= json_encode(array_map(function($p){ return ['pot_number'=>(int)$p['pot_number'],'volume'=>(float)$p['volume']]; }, $potsConfig), JSON_UNESCAPED_UNICODE) ?>;

    // Watering data: {week: {day: {pot: amount}}}
    const wateringData = <?= json_encode($wateringJS, JSON_UNESCAPED_UNICODE) ?>;

    // Hnojeno data: {week: {day: [...]}}
    let customHnojenoConfig = <?= json_encode($customHnojenoConfig, JSON_UNESCAPED_UNICODE) ?>;
    const hnojenoData = <?= json_encode($hnojenoJS, JSON_UNESCAPED_UNICODE) ?>;

    const TARGET_COMPRESSION_RATIO = 6.0;
    const COMPRESSION_QUALITY_START = 85;
    const COMPRESSION_MAX_WIDTH = 1500;
    const MAX_COMPRESSION_ATTEMPTS = 3;

    function postJson(action, payload) {
        return fetch(API + action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload || {})
        }).then(r => r.json());
    }

    // ========== INIT ==========
    document.addEventListener("DOMContentLoaded", function() {
        <?php foreach ($weeks as $week): ?>
            <?php if (!empty($week['start_day'])): ?>
                updateDays(<?= (int)$week['week_number'] ?>);
            <?php endif; ?>
        <?php endforeach; ?>

        document.getElementById('cancel-delete').addEventListener('click', hideDeleteConfirmation);
        document.getElementById('confirm-delete').addEventListener('click', confirmDeleteGrowreport);
        document.getElementById('cancel-delete-week').addEventListener('click', hideDeleteWeekConfirmation);
        document.getElementById('confirm-delete-week').addEventListener('click', confirmDeleteWeek);

        // CO2
        document.getElementById('co2-cancel').addEventListener('click', closeCo2);
        document.getElementById('co2-ok').addEventListener('click', saveCo2);
        ['co2-value1', 'co2-value2'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 4);
            });
        });

        // CO2 tip
        document.addEventListener('mouseover', function(e) {
            const cell = e.target.closest ? e.target.closest('.co2-cell') : null;
            if (cell) showCo2Tip(cell.querySelector('.co2-btn'));
        });
        document.addEventListener('mouseout', function(e) {
            const cell = e.target.closest ? e.target.closest('.co2-cell') : null;
            if (cell && !(e.relatedTarget && cell.contains(e.relatedTarget))) hideCo2Tip();
        });
        window.addEventListener('scroll', hideCo2Tip, true);

        // Pots dialog
        document.getElementById('pots-cancel').addEventListener('click', closePotsConfig);
        document.getElementById('pots-save').addEventListener('click', savePotsConfig);

        // Watering dialog
        document.getElementById('watering-cancel').addEventListener('click', closeWatering);
        document.getElementById('watering-ok').addEventListener('click', saveWatering);

        // Hnojeno dialog
        const hnojCancel = document.getElementById('hnojeno-cancel');
        if (hnojCancel) hnojCancel.addEventListener('click', closeHnojeno);
        const hnojSave = document.getElementById('hnojeno-save');
        if (hnojSave) hnojSave.addEventListener('click', saveHnojeno);
    });

    // ========== POT CONFIG ==========
    function openPotsConfig() {
        const countSel = document.getElementById('pots-count');
        countSel.value = potsConfig.length > 0 ? potsConfig.length : 1;
        renderPotRows();
        document.getElementById('pots-dialog').style.display = 'flex';
    }

    function closePotsConfig() {
        document.getElementById('pots-dialog').style.display = 'none';
    }

    function renderPotRows() {
        const count = parseInt(document.getElementById('pots-count').value);
        const container = document.getElementById('pots-rows');
        container.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            const existing = potsConfig.find(p => p.pot_number === i);
            const vol = existing ? existing.volume : 10;
            const row = document.createElement('div');
            row.className = 'pot-row';
            row.innerHTML = '<span>Květináč ' + i + ':</span>' +
                '<input type="number" id="pot-vol-' + i + '" step="0.5" min="0.5" max="100" value="' + vol + '">' +
                '<span>L</span>';
            container.appendChild(row);
        }
    }

    function savePotsConfig() {
        const count = parseInt(document.getElementById('pots-count').value);
        const pots = [];
        for (let i = 1; i <= count; i++) {
            const input = document.getElementById('pot-vol-' + i);
            let vol = parseFloat(input.value) || 0;
            vol = Math.round(vol * 2) / 2;
            if (vol < 0.5) vol = 0.5;
            if (vol > 100) vol = 100;
            pots.push({ pot_number: i, volume: vol });
        }

        postJson('save_pots', { pots: pots })
        .then(data => {
            if (data.success) {
                potsConfig = pots;
                closePotsConfig();
            } else {
                alert(data.message || 'Chyba při ukládání.');
            }
        })
        .catch(err => {
            console.error('Chyba:', err);
            alert('Nastala chyba.');
        });
    }

    // ========== WATERING ==========
    function openWatering(week, day) {
        if (potsConfig.length === 0) {
            openPotsConfig();
            return;
        }

        wateringCtx = { week: week, day: day };
        const dayNum = week * 7 - (7 - day);
        document.getElementById('watering-dialog-title').innerText = 'Zalévání – Den ' + dayNum;

        const row = document.getElementById('watering-pots-row');
        row.innerHTML = '';

        const existingDay = (wateringData[week] && wateringData[week][day]) ? wateringData[week][day] : {};

        potsConfig.forEach(function(pot) {
            const card = document.createElement('div');
            card.className = 'pot-card';

            const lbl = document.createElement('div');
            lbl.className = 'pot-lbl';
            lbl.textContent = 'K' + pot.pot_number;

            const vol = document.createElement('div');
            vol.className = 'pot-vol';
            vol.textContent = pot.volume + 'L';

            const picker = document.createElement('div');
            picker.className = 'sp-wrap';
            picker.id = 'sp-pot-' + pot.pot_number;

            const initVal = existingDay[pot.pot_number] ? parseFloat(existingDay[pot.pot_number]) : 0;
            initScrollPicker(picker, pot.volume, 0.5, initVal);

            card.appendChild(lbl);
            card.appendChild(vol);
            card.appendChild(picker);
            row.appendChild(card);
        });

        updateWateringTotal();
        document.getElementById('watering-dialog').style.display = 'flex';
    }

    function closeWatering() {
        document.getElementById('watering-dialog').style.display = 'none';
        wateringCtx = null;
    }

    function initScrollPicker(container, maxVal, step, initialVal) {
        const itemH = 40;
        container.innerHTML = '';

        const pad1 = document.createElement('div');
        pad1.className = 'sp-pad';
        container.appendChild(pad1);

        const vals = [];
        for (let v = 0; v <= maxVal + 0.001; v = Math.round((v + step) * 10) / 10) {
            vals.push(Math.round(v * 10) / 10);
        }

        vals.forEach(function(v) {
            const el = document.createElement('div');
            el.className = 'sp-item';
            el.dataset.val = v;
            el.textContent = (v % 1 === 0) ? v.toString() : v.toFixed(1);
            container.appendChild(el);
        });

        const pad2 = document.createElement('div');
        pad2.className = 'sp-pad';
        container.appendChild(pad2);

        // Scroll to initial value
        let idx = 0;
        for (let j = 0; j < vals.length; j++) {
            if (Math.abs(vals[j] - initialVal) < 0.01) { idx = j; break; }
        }
        container.scrollTop = idx * itemH;

        function highlight() {
            const ci = Math.round(container.scrollTop / itemH);
            const items = container.querySelectorAll('.sp-item');
            items.forEach(function(el, i) {
                el.classList.toggle('sp-sel', i === ci);
            });
            updateWateringTotal();
        }

        container.addEventListener('scroll', highlight);
        setTimeout(highlight, 50);

        container.getVal = function() {
            const ci = Math.round(this.scrollTop / itemH);
            const items = this.querySelectorAll('.sp-item');
            return (items[ci]) ? parseFloat(items[ci].dataset.val) : 0;
        };
    }

    function updateWateringTotal() {
        let total = 0;
        potsConfig.forEach(function(pot) {
            const picker = document.getElementById('sp-pot-' + pot.pot_number);
            if (picker && picker.getVal) {
                total += picker.getVal();
            }
        });
        total = Math.round(total * 10) / 10;
        const el = document.getElementById('watering-total');
        if (el) {
            const str = (total % 1 === 0) ? total.toString() : total.toFixed(1);
            el.textContent = 'Celkem: ' + str + ' L';
        }
    }

    function saveWatering() {
        if (!wateringCtx) return;
        const ctx = wateringCtx;

        const amounts = [];
        potsConfig.forEach(function(pot) {
            const picker = document.getElementById('sp-pot-' + pot.pot_number);
            const amt = picker ? picker.getVal() : 0;
            amounts.push({ pot_number: pot.pot_number, amount: amt });
        });

        postJson('save_watering', { week: ctx.week, day: ctx.day, amounts: amounts })
        .then(function(data) {
            if (data.success) {
                const total = Math.round((data.total || 0) * 10) / 10;

                // Update watering data cache
                if (!wateringData[ctx.week]) wateringData[ctx.week] = {};
                wateringData[ctx.week][ctx.day] = {};
                amounts.forEach(function(a) {
                    if (a.amount > 0) wateringData[ctx.week][ctx.day][a.pot_number] = a.amount;
                });

                // Update button display
                const btn = document.getElementById('zal-' + ctx.week + '-' + ctx.day);
                if (btn) {
                    btn.dataset.total = total;
                    renderZalitoBtn(btn, total);
                }

                // Update day-zalito stripe
                const stripe = document.getElementById('day-zalito-' + ctx.week + '-' + ctx.day);
                if (stripe) {
                    stripe.style.backgroundColor = total > 0 ? 'blue' : '';
                }

                closeWatering();
            } else {
                alert(data.message || 'Chyba při ukládání.');
            }
        })
        .catch(function(err) {
            console.error('Chyba:', err);
            alert('Nastala chyba.');
        });
    }

    function renderZalitoBtn(btn, total) {
        btn.innerHTML = '';
        if (total > 0) {
            const str = (total % 1 === 0) ? total.toString() : total.toFixed(1);
            const s = document.createElement('span');
            s.className = 'zal-total' + (str.length > 2 ? ' small' : '');
            s.textContent = str;
            btn.appendChild(s);
        } else {
            const s = document.createElement('span');
            s.className = 'zal-plus';
            s.textContent = '+';
            btn.appendChild(s);
        }
    }

    // ========== HNOJENO ==========
    function showHnojTable(tab) {
        const isPred = tab === 'pred';
        document.getElementById('hnoj-table-pred').style.display = isPred ? 'flex' : 'none';
        document.getElementById('hnoj-table-cust').style.display = isPred ? 'none' : 'flex';
        document.getElementById('hnoj-tab-pred').classList.toggle('active', isPred);
        document.getElementById('hnoj-tab-cust').classList.toggle('active', !isPred);
    }

    function hnojToggleItem(cb) {
        const row = cb.closest('.hnoj-row');
        const input = row.querySelector('.hnoj-qty');
        const isDry = cb.dataset.type === 'dry';
        const min = isDry ? 1 : 0.5;

        if (cb.checked) {
            input.disabled = false;
            let val = parseFloat(input.value);
            if (isNaN(val) || val <= 0) {
                input.value = min;
            }
            input.focus();
        } else {
            input.disabled = true;
            input.value = '';
        }
        updateHnojTotals();
    }

    function hnojQtyChanged(input) {
        const row = input.closest('.hnoj-row');
        const cb = row.querySelector('.hnoj-cb');
        const isDry = cb.dataset.type === 'dry';
        const max = isDry ? 30 : 500;

        let val = parseFloat(input.value);
        if (!isNaN(val) && val > 0) {
            if (val > max) {
                val = max;
                input.value = val;
            }
            cb.checked = true;
            input.disabled = false;
        } else {
            cb.checked = false;
            input.disabled = true;
            input.value = '';
        }
        updateHnojTotals();
    }

    function hnojCustomNameChanged(input) {
        // Keeps custom name in sync
    }

    function stepHnojQty(btn, dir) {
        const wrap = btn.closest('.hnoj-input-wrap');
        const input = wrap.querySelector('.hnoj-qty');
        const row = btn.closest('.hnoj-row');
        const cb = row.querySelector('.hnoj-cb');
        const isDry = cb.dataset.type === 'dry';
        const step = isDry ? 1 : 0.5;
        const min = isDry ? 1 : 0.5;
        const max = isDry ? 30 : 500;

        let cur = parseFloat(input.value);
        if (isNaN(cur) || cur <= 0) {
            if (dir > 0) {
                cb.checked = true;
                input.disabled = false;
                input.value = min;
            }
        } else {
            cur = Math.round((cur + dir * step) * 10) / 10;
            if (cur < min) {
                cb.checked = false;
                input.disabled = true;
                input.value = '';
            } else if (cur > max) {
                input.value = max;
            } else {
                input.value = isDry ? Math.round(cur) : (cur % 1 === 0 ? cur : cur.toFixed(1));
            }
        }
        updateHnojTotals();
    }

    function updateHnojTotals() {
        let totLiq = 0;
        let totDry = 0;

        document.querySelectorAll('#hnojeno-dialog .hnoj-row').forEach(function(row) {
            const cb = row.querySelector('.hnoj-cb');
            if (cb && cb.checked) {
                const input = row.querySelector('.hnoj-qty');
                const val = parseFloat(input.value) || 0;
                if (cb.dataset.type === 'dry') {
                    totDry += Math.round(val);
                } else {
                    totLiq += val;
                }
            }
        });

        totLiq = Math.round(totLiq * 10) / 10;
        document.getElementById('hnoj-total-liquid-val').textContent = (totLiq % 1 === 0) ? totLiq.toString() : totLiq.toFixed(1);
        document.getElementById('hnoj-total-dry-val').textContent = totDry.toString();
    }

    function openHnojeno(week, day) {
        hnojenoCtx = { week: week, day: day };
        const dayNum = week * 7 - (7 - day);
        document.getElementById('hnojeno-dialog-title').innerText = 'Hnojení – Den ' + dayNum;

        // Reset all rows
        document.querySelectorAll('#hnojeno-dialog .hnoj-row').forEach(function(row) {
            const cb = row.querySelector('.hnoj-cb');
            if (cb) cb.checked = false;
            const input = row.querySelector('.hnoj-qty');
            if (input) {
                input.value = '';
                input.disabled = true;
            }
        });

        // Populate custom names
        const custLiqInputs = document.querySelectorAll('#hnoj-cust-liquid-list .hnoj-custom-name');
        const custDryInputs = document.querySelectorAll('#hnoj-cust-dry-list .hnoj-custom-name');
        custLiqInputs.forEach(function(inp, i) {
            inp.value = (customHnojenoConfig && customHnojenoConfig.liquid && customHnojenoConfig.liquid[i]) ? customHnojenoConfig.liquid[i] : '';
        });
        custDryInputs.forEach(function(inp, i) {
            inp.value = (customHnojenoConfig && customHnojenoConfig.dry && customHnojenoConfig.dry[i]) ? customHnojenoConfig.dry[i] : '';
        });

        // Restore saved items for this day
        const saved = (hnojenoData[week] && hnojenoData[week][day]) ? hnojenoData[week][day] : [];
        saved.forEach(function(item) {
            let matched = false;
            if (!item.is_custom) {
                document.querySelectorAll('#hnoj-table-pred .hnoj-cb').forEach(function(cb) {
                    if (!matched && cb.dataset.name === item.name && cb.dataset.type === item.type) {
                        cb.checked = true;
                        const row = cb.closest('.hnoj-row');
                        const qtyInput = row.querySelector('.hnoj-qty');
                        qtyInput.disabled = false;
                        qtyInput.value = item.amount;
                        matched = true;
                    }
                });
            }
            if (!matched) {
                const listId = item.type === 'dry' ? 'hnoj-cust-dry-list' : 'hnoj-cust-liquid-list';
                const rows = document.querySelectorAll('#' + listId + ' .hnoj-row');
                let targetRow = null;
                rows.forEach(function(r) {
                    if (!targetRow) {
                        const nameInp = r.querySelector('.hnoj-custom-name');
                        if (nameInp && nameInp.value.trim() === item.name) {
                            targetRow = r;
                        }
                    }
                });
                if (!targetRow) {
                    rows.forEach(function(r) {
                        if (!targetRow) {
                            const nameInp = r.querySelector('.hnoj-custom-name');
                            if (nameInp && nameInp.value.trim() === '') {
                                nameInp.value = item.name;
                                targetRow = r;
                            }
                        }
                    });
                }
                if (targetRow) {
                    const cb = targetRow.querySelector('.hnoj-cb');
                    cb.checked = true;
                    const qtyInput = targetRow.querySelector('.hnoj-qty');
                    qtyInput.disabled = false;
                    qtyInput.value = item.amount;
                }
            }
        });

        showHnojTable('pred');
        updateHnojTotals();
        document.getElementById('hnojeno-dialog').style.display = 'flex';
    }

    function closeHnojeno() {
        document.getElementById('hnojeno-dialog').style.display = 'none';
        hnojenoCtx = null;
    }

    function renderHnojenoBtn(btn, liquid, dry) {
        btn.innerHTML = '';
        const liqVal = parseFloat(liquid) || 0;
        const dryVal = parseInt(dry, 10) || 0;
        btn.dataset.liquid = liqVal > 0 ? (liqVal % 1 === 0 ? liqVal.toString() : liqVal.toFixed(1)) : '0';
        btn.dataset.dry = dryVal > 0 ? dryVal.toString() : '0';

        const hasBoth = liqVal > 0 && dryVal > 0;
        btn.classList.toggle('has-both', hasBoth);

        if (liqVal > 0 || dryVal > 0) {
            if (liqVal > 0) {
                const liqStr = (liqVal % 1 === 0) ? liqVal.toString() : liqVal.toFixed(1);
                const sLiq = document.createElement('span');
                sLiq.className = 'hnoj-liquid';
                sLiq.textContent = liqStr + ' ml';
                btn.appendChild(sLiq);
            }
            if (dryVal > 0) {
                const sDry = document.createElement('span');
                sDry.className = 'hnoj-dry';
                sDry.textContent = dryVal.toString();
                btn.appendChild(sDry);
            }
        } else {
            const sPlus = document.createElement('span');
            sPlus.className = 'hnoj-plus';
            sPlus.textContent = '+';
            btn.appendChild(sPlus);
        }
    }

    function saveHnojeno() {
        if (!hnojenoCtx) return;
        const ctx = hnojenoCtx;

        const items = [];
        let itemCount = 0;

        document.querySelectorAll('#hnojeno-dialog .hnoj-row').forEach(function(row) {
            const cb = row.querySelector('.hnoj-cb');
            if (cb && cb.checked) {
                const isCustom = cb.dataset.custom === '1';
                let name = '';
                if (isCustom) {
                    const nameInput = row.querySelector('.hnoj-custom-name');
                    name = nameInput ? nameInput.value.trim() : '';
                } else {
                    name = cb.dataset.name || '';
                }

                const input = row.querySelector('.hnoj-qty');
                let amt = parseFloat(input ? input.value : 0) || 0;

                if (name !== '' && amt > 0) {
                    if (cb.dataset.type === 'dry') {
                        amt = Math.min(30, Math.max(1, Math.round(amt)));
                    } else {
                        amt = Math.min(500, Math.max(0.5, Math.round(amt * 2) / 2));
                    }
                    items.push({
                        type: cb.dataset.type,
                        name: name,
                        amount: amt,
                        is_custom: isCustom ? 1 : 0
                    });
                    itemCount++;
                }
            }
        });

        if (itemCount > 20) {
            alert('Lze vybrat maximálně 20 položek hnojiv celkem.');
            return;
        }

        const customNames = { liquid: [], dry: [] };
        document.querySelectorAll('#hnoj-cust-liquid-list .hnoj-custom-name').forEach(function(inp) {
            customNames.liquid.push(inp.value.trim());
        });
        document.querySelectorAll('#hnoj-cust-dry-list .hnoj-custom-name').forEach(function(inp) {
            customNames.dry.push(inp.value.trim());
        });

        postJson('save_hnojeno', {
            week: ctx.week,
            day: ctx.day,
            items: items,
            custom_names: customNames
        })
        .then(function(data) {
            if (data.success) {
                customHnojenoConfig = customNames;

                if (!hnojenoData[ctx.week]) hnojenoData[ctx.week] = {};
                hnojenoData[ctx.week][ctx.day] = data.items || [];

                const btn = document.getElementById('hnoj-' + ctx.week + '-' + ctx.day);
                if (btn) {
                    renderHnojenoBtn(btn, data.total_liquid, data.total_dry);
                }

                const stripe = document.getElementById('day-hnojeno-' + ctx.week + '-' + ctx.day);
                if (stripe) {
                    stripe.style.backgroundColor = (data.total_liquid > 0 || data.total_dry > 0) ? 'brown' : '';
                }

                closeHnojeno();
            } else {
                alert(data.message || 'Chyba při ukládání hnojení.');
            }
        })
        .catch(function(err) {
            console.error('Chyba při ukládání hnojení:', err);
            alert('Nastala chyba při ukládání hnojení.');
        });
    }

    // ========== CO2 ==========
    function showCo2Tip(btn) {
        if (!btn) return;
        const items = [
            { l: btn.dataset.l1, v: btn.dataset.v1 },
            { l: btn.dataset.l2, v: btn.dataset.v2 }
        ].filter(function(x) { return x.v !== '' && x.v !== undefined; });
        if (items.length === 0) { hideCo2Tip(); return; }

        const tip = document.getElementById('co2-tip');
        tip.innerHTML = '';
        items.forEach(function(x) {
            const d = document.createElement('div');
            d.className = 'tip-line';
            d.textContent = x.l + ': ' + x.v;
            tip.appendChild(d);
        });

        const r = btn.getBoundingClientRect();
        tip.style.display = 'flex';
        tip.style.minWidth = (r.width * 3) + 'px';
        tip.style.minHeight = (r.height * 3) + 'px';

        const tw = tip.offsetWidth;
        const th = tip.offsetHeight;
        let left = r.left + r.width / 2 - tw / 2;
        let top = r.top + r.height / 2 - th / 2;
        left = Math.max(5, Math.min(left, window.innerWidth - tw - 5));
        top = Math.max(5, Math.min(top, window.innerHeight - th - 5));
        tip.style.left = left + 'px';
        tip.style.top = top + 'px';
    }

    function hideCo2Tip() {
        document.getElementById('co2-tip').style.display = 'none';
    }

    function openCo2(btn, week, day) {
        hideCo2Tip();
        co2Ctx = { btn: btn, week: week, day: day };
        document.getElementById('co2-dialog-title').innerText = 'CO2 (PPM) – den ' + (week * 7 - (7 - day));
        document.getElementById('co2-label1').value = btn.dataset.l1 || 'Ráno';
        document.getElementById('co2-value1').value = btn.dataset.v1 || '';
        document.getElementById('co2-label2').value = btn.dataset.l2 || 'Večer';
        document.getElementById('co2-value2').value = btn.dataset.v2 || '';
        document.getElementById('co2-dialog').style.display = 'flex';
        document.getElementById('co2-value1').focus();
    }

    function closeCo2() {
        document.getElementById('co2-dialog').style.display = 'none';
        co2Ctx = null;
    }

    function renderCo2Btn(btn) {
        btn.innerHTML = '';
        var lines = [
            { l: btn.dataset.l1, v: btn.dataset.v1 },
            { l: btn.dataset.l2, v: btn.dataset.v2 }
        ];
        var any = false;
        lines.forEach(function(item) {
            if (item.v !== '' && item.v !== undefined) {
                any = true;
                var s = document.createElement('span');
                s.className = 'co2-line';
                s.textContent = (item.l || '').charAt(0).toUpperCase() + item.v;
                btn.appendChild(s);
            }
        });
        if (!any) {
            var s = document.createElement('span');
            s.className = 'co2-plus';
            s.textContent = '+';
            btn.appendChild(s);
        }

        var stripe = document.getElementById(btn.id.replace('co2-', 'day-co2-'));
        if (stripe) stripe.classList.toggle('on', any);
    }

    function saveCo2() {
        if (!co2Ctx) return;
        var ctx = co2Ctx;
        var payload = {
            week: ctx.week,
            day: ctx.day,
            label1: document.getElementById('co2-label1').value.trim(),
            value1: document.getElementById('co2-value1').value.trim(),
            label2: document.getElementById('co2-label2').value.trim(),
            value2: document.getElementById('co2-value2').value.trim()
        };

        postJson('save_co2', payload)
        .then(function(data) {
            if (data.success) {
                ctx.btn.dataset.l1 = data.label1;
                ctx.btn.dataset.l2 = data.label2;
                ctx.btn.dataset.v1 = data.value1 === null ? '' : String(data.value1);
                ctx.btn.dataset.v2 = data.value2 === null ? '' : String(data.value2);
                renderCo2Btn(ctx.btn);
                closeCo2();
            } else {
                alert(data.message || 'Chyba při ukládání CO2.');
            }
        })
        .catch(function(error) {
            console.error('Chyba při ukládání CO2:', error);
            alert('Nastala chyba při ukládání CO2.');
        });
    }

    // ========== DELETE ==========
    function showDeleteConfirmation() { document.getElementById('delete-confirmation').style.display = 'flex'; }
    function hideDeleteConfirmation() { document.getElementById('delete-confirmation').style.display = 'none'; }
    function confirmDeleteGrowreport() { hideDeleteConfirmation(); deleteGrowreport(); }

    function deleteGrowreport() {
        postJson('delete', { report_id: reportId })
        .then(function(data) {
            if (data.success) { alert("Growreport byl úspěšně smazán!"); window.location.href = "index.php"; }
            else { alert(data.message || "Chyba při mazání growreportu."); }
        })
        .catch(function(error) { console.error("Chyba:", error); alert("Nastala chyba: " + error); });
    }

    function showDeleteWeekConfirmation(week) {
        weekToDelete = week;
        document.getElementById('delete-week-label').innerText = 'Týden ' + week;
        document.getElementById('delete-week-confirmation').style.display = 'flex';
    }
    function hideDeleteWeekConfirmation() { document.getElementById('delete-week-confirmation').style.display = 'none'; weekToDelete = null; }
    function confirmDeleteWeek() {
        var week = weekToDelete;
        hideDeleteWeekConfirmation();
        if (!week) return;
        postJson('delete_week', { week: week })
        .then(function(data) { if (data.success) { location.reload(); } else { alert(data.message || "Chyba."); } })
        .catch(function(error) { console.error("Chyba:", error); alert("Nastala chyba."); });
    }

    // ========== SHARE ==========
    function shareGrowreport() {
        var hasImages = false;
        document.querySelectorAll(".growreport img:not(.preview-image)").forEach(function(img) {
            var src = img.getAttribute('src');
            if (src && src !== '#') hasImages = true;
        });
        if (!hasImages) { alert("Nejprve nahrajte alespoň jednu fotku."); return; }

        postJson('share', { report_id: reportId })
        .then(function(data) { if (data.success) { alert("Growreport sdílen!"); } else { alert(data.message || "Chyba."); } })
        .catch(function(error) { console.error("Chyba:", error); alert("Nastala chyba."); });
    }

    // ========== ADD WEEK ==========
    function addWeek() {
        postJson('add_week', {})
        .then(function(data) { if (data.success) { location.reload(); } else { alert(data.message || "Nepodařilo se."); } })
        .catch(function(error) { console.error("Chyba:", error); alert("Nastala chyba."); });
    }

    // ========== PHASE COLOR ==========
    function setColor(week, phase, color) {
        if (!window.confirm("Opravdu chcete uložit tuto barvu?")) return;
        postJson('phase', { week: week, phase: phase, color: color })
        .then(function(data) {
            if (data.success) {
                var header = document.querySelector('#week-title-' + week).closest(".growreport-header");
                header.style.backgroundColor = color;
            } else { alert('Chyba: ' + (data.message || '')); }
        })
        .catch(function(error) { console.error('Chyba:', error); });
    }

    // ========== IMAGE COMPRESSION ==========
    function compressImage(file, fileId) {
        return new Promise(function(resolve, reject) {
            var URL2 = window.URL || window.webkitURL;
            var originalUrl = URL2.createObjectURL(file);
            var infoElement = document.getElementById('compression-info-' + fileId);
            if (infoElement) { infoElement.innerHTML = '<div class="loading-spinner"></div> Komprimace...'; infoElement.style.display = 'block'; }

            var img = new Image();
            img.onload = function() {
                URL2.revokeObjectURL(originalUrl);
                var width = img.width, height = img.height;
                if (width > COMPRESSION_MAX_WIDTH) { var ratio = COMPRESSION_MAX_WIDTH / width; width = COMPRESSION_MAX_WIDTH; height = Math.round(height * ratio); }

                function compressWithQuality(quality, attempt) {
                    attempt = attempt || 1;
                    var canvas = document.createElement('canvas');
                    canvas.width = width; canvas.height = height;
                    var ctx = canvas.getContext('2d');
                    ctx.fillStyle = 'white'; ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        var newName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        var compressedFile = new File([blob], newName, { type: 'image/jpeg', lastModified: Date.now() });
                        var originalSize = file.size, compressedSize = compressedFile.size;
                        var compressionRatio = originalSize / compressedSize;

                        if (infoElement) {
                            infoElement.innerHTML = 'Komprimováno: ' + (originalSize / (1024 * 1024)).toFixed(2) + ' MB → ' + (compressedSize / (1024 * 1024)).toFixed(2) + ' MB<br>Kompresní poměr: ' + compressionRatio.toFixed(1) + '× menší';
                        }

                        if (Math.abs(compressionRatio - TARGET_COMPRESSION_RATIO) < 1.0 || attempt >= MAX_COMPRESSION_ATTEMPTS) {
                            resolve(compressedFile);
                        } else {
                            var newQuality = compressionRatio > TARGET_COMPRESSION_RATIO ? quality + (10 * (attempt === 1 ? 1 : 0.5)) : quality - (10 * (attempt === 1 ? 1 : 0.5));
                            newQuality = Math.max(10, Math.min(95, newQuality));
                            compressWithQuality(newQuality, attempt + 1);
                        }
                    }, 'image/jpeg', quality / 100);
                }
                compressWithQuality(COMPRESSION_QUALITY_START);
            };
            img.onerror = function() { if (infoElement) { infoElement.innerHTML = 'Komprese se nezdařila.'; infoElement.style.color = '#f44336'; } reject(new Error('Chyba')); };
            img.src = originalUrl;
        });
    }

    function previewImage(event, input) {
        var match = input.id.match(/file-input-(\d+)-(\d+)/);
        if (match) {
            var previewId = match[1] + '-' + match[2];
            var previewImg = document.getElementById('preview-' + previewId);
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = "block";
                    var oldImg = input.parentElement.querySelector('img:not(.preview-image)');
                    if (oldImg) oldImg.style.display = 'none';
                    var noPhoto = input.parentElement.querySelector('p');
                    if (noPhoto) noPhoto.style.display = 'none';
                    var label = input.previousElementSibling;
                    if (label && label.tagName === 'LABEL') label.style.display = "none";

                    compressImage(file, previewId)
                    .then(function(cf) { compressedFiles[input.id] = cf; })
                    .catch(function(err) { console.error('Chyba:', err); compressedFiles[input.id] = file; });
                };
                reader.readAsDataURL(file);
            }
        }
    }

    // ========== TOGGLE DAY (only hnojeno now) ==========
    function toggleDayValue(week, day, isChecked, type) {
        var targetElement = document.getElementById('day-' + type + '-' + week + '-' + day);
        if (targetElement) {
            targetElement.style.backgroundColor = isChecked ? 'brown' : '';
        }
        postJson('toggle_day', { week: week, day: day, checked: isChecked, type: type })
        .then(function(data) { if (!data.success) console.log('Chyba:', data.message); })
        .catch(function(error) { console.error('Chyba:', error); });
    }

    // ========== SAVE TEXT ==========
    function saveText(week) {
        var text = document.getElementById('weekText-' + week).value;
        var msg = document.getElementById('responseMessage-text-' + week);
        postJson('save_text', { week: week, text: text })
        .then(function(data) { msg.innerText = data.success ? 'Text uložen.' : 'Chyba: ' + data.message; msg.style.color = data.success ? 'green' : 'red'; })
        .catch(function(error) { msg.innerText = 'Chyba: ' + error.message; msg.style.color = 'red'; });
    }

    // ========== SAVE GROWREPORT ==========
    function saveGrowreport() {
        var formData = new FormData();
        document.querySelectorAll(".growreport").forEach(function(growreport, index) {
            var weekNumber = parseInt(growreport.getAttribute('data-week'), 10);
            var textArea = growreport.querySelector('textarea[id^="weekText-"]');
            var text = textArea ? textArea.value : '';

            formData.append('weeks[' + index + '][week_number]', weekNumber);
            formData.append('weeks[' + index + '][text]', text);

            var startDayInput = growreport.querySelector('input[id^="start-day-"]');
            if (startDayInput && startDayInput.value) {
                formData.append('weeks[' + index + '][start_day]', startDayInput.value.trim());
            }

            var hnojenoDays = [];
            growreport.querySelectorAll('input[id^="hnojeno-"]:checked').forEach(function(input) {
                hnojenoDays.push(input.value);
            });
            growreport.querySelectorAll('button[id^="hnoj-"]').forEach(function(btn) {
                var liq = parseFloat(btn.dataset.liquid) || 0;
                var dry = parseInt(btn.dataset.dry, 10) || 0;
                if (liq > 0 || dry > 0) {
                    var parts = btn.id.split('-');
                    var d = parts[2];
                    if (!hnojenoDays.includes(d)) hnojenoDays.push(d);
                }
            });
            formData.append('weeks[' + index + '][hnojeno]', hnojenoDays.join(','));

            growreport.querySelectorAll("input[type='file']").forEach(function(input, fileIndex) {
                if (input.files.length > 0) {
                    var fileToSend = compressedFiles[input.id] || input.files[0];
                    formData.append('weeks[' + index + '][photo' + (fileIndex + 1) + ']', fileToSend, fileToSend.name);
                }
            });
        });

        fetch(API + 'save', { method: "POST", body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { alert("Growreport uložen."); location.reload(); }
            else { alert(data.message || "Chyba."); }
        })
        .catch(function(error) { console.error("Chyba:", error); alert("Nastala chyba."); });
    }

    // ========== DAYS ==========
    function updateDays(week) {
        var startDayInput = document.getElementById('start-day-' + week);
        var daysContainer = document.getElementById('days-' + week);
        if (!startDayInput || !daysContainer) return;

        var dayNames = ["pondělí", "úterý", "středa", "čtvrtek", "pátek", "sobota", "neděle"];
        var startDay = startDayInput.value.replace(/^\s+|\s+$/g, '').toLowerCase();

        if (!dayNames.includes(startDay)) {
            daysContainer.innerHTML = "<p style='color: red;'>Neplatný den.</p>";
            return;
        }

        daysContainer.innerHTML = "";
        var startIndex = dayNames.indexOf(startDay);
        for (var i = 0; i < 7; i++) {
            var dayElement = document.createElement('div');
            dayElement.className = 'day';
            dayElement.innerText = dayNames[(startIndex + i) % 7];
            daysContainer.appendChild(dayElement);
        }
    }

    function saveStartDay(week) {
        var startDayValue = document.getElementById('start-day-' + week).value.trim();
        var messageElement = document.getElementById('responseMessage-' + week);
        if (!startDayValue) { alert("Neplatný den."); return; }

        postJson('start_day', { week: week, start_day: startDayValue })
        .then(function(data) {
            if (data.success) { messageElement.innerText = 'uloženo.'; messageElement.style.color = 'green'; }
            else { messageElement.innerText = 'Chyba: ' + (data.message || ''); messageElement.style.color = 'red'; }
        })
        .catch(function(error) { messageElement.innerText = 'Chyba: ' + error.message; messageElement.style.color = 'red'; });
    }
</script>
</body>
</html>
