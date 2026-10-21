<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'QR-code vragenlijst';
$manager = null;
$surveyUrl = '';
$qrImageUrl = '';
$error = '';
$isLoopbackHost = static function (string $host): bool {
    $host = strtolower(rtrim(trim($host, '[]'), '.'));

    return $host === 'localhost'
        || str_ends_with($host, '.localhost')
        || $host === 'localhost.localdomain'
        || str_ends_with($host, '.localhost.localdomain')
        || $host === '::1'
        || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && str_starts_with($host, '127.');
};

try {
    $manager = currentManager();
    if ($manager === null) {
        header('Location: login.php', true, 303);
        exit;
    }

    $baseUrl = getenv('TABLETALK_BASE_URL') ?: '';
    if ($baseUrl === '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!preg_match('/\A[a-zA-Z0-9.-]+(?::[0-9]{1,5})?\z/', $host)) {
            throw new RuntimeException('Stel TABLETALK_BASE_URL in op het publieke adres van de website.');
        }

        $hostName = preg_replace('/:[0-9]{1,5}\z/', '', $host);
        if ($isLoopbackHost($hostName)) {
            $addresses = gethostbynamel(gethostname()) ?: [];
            $addresses = array_values(array_filter($addresses, static function (string $address): bool {
                return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                    && !str_starts_with($address, '127.')
                    && !str_starts_with($address, '169.254.');
            }));
            usort($addresses, static function (string $left, string $right): int {
                $priority = static function (string $address): int {
                    if (str_starts_with($address, '192.168.')) {
                        return 0;
                    }
                    if (str_starts_with($address, '10.')) {
                        return 1;
                    }
                    if (preg_match('/\A172\.(?:1[6-9]|2[0-9]|3[01])\./', $address)) {
                        return 2;
                    }

                    return 3;
                };

                return $priority($left) <=> $priority($right);
            });

            if ($addresses === []) {
                throw new RuntimeException('Het netwerkadres van deze server kon niet worden bepaald. Stel TABLETALK_BASE_URL in op een adres dat andere apparaten kunnen bereiken.');
            }

            $port = preg_match('/(:[0-9]{1,5})\z/', $host, $portMatch) ? $portMatch[1] : '';
            $host = $addresses[0] . $port;
        }

        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $path = $directory === '/' || $directory === '.' ? '' : '/' . trim($directory, '/');
        $baseUrl = $scheme . '://' . $host . $path;
    }

    $parsedBaseUrl = filter_var($baseUrl, FILTER_VALIDATE_URL) ? parse_url($baseUrl) : false;
    if ($parsedBaseUrl === false
        || !in_array($parsedBaseUrl['scheme'] ?? '', ['http', 'https'], true)
        || empty($parsedBaseUrl['host'])
        || isset($parsedBaseUrl['user'])
        || isset($parsedBaseUrl['pass'])
        || isset($parsedBaseUrl['query'])
        || isset($parsedBaseUrl['fragment'])) {
        throw new RuntimeException('Het publieke websiteadres is ongeldig. Controleer TABLETALK_BASE_URL.');
    }
    if ($isLoopbackHost($parsedBaseUrl['host'])) {
        throw new RuntimeException('TABLETALK_BASE_URL mag niet naar localhost verwijzen. Gebruik het netwerkadres van de server of een publiek domein.');
    }

    $surveyUrl = rtrim($baseUrl, '/') . '/index.php?vestiging_id=' . (int)$manager['vestiging_id'];
    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
        'size' => '320x320',
        'data' => $surveyUrl,
        'qzone' => 2,
    ], '', '&', PHP_QUERY_RFC3986);
} catch (PDOException $exception) {
    $error = 'De QR-code kon niet worden geladen. Controleer de databaseverbinding en probeer het opnieuw.';
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

$establishment = $manager === null ? null : ['naam' => $manager['vestiging_naam']];
require __DIR__ . '/includes/header.php';
?>
<style>
    .qr-card { max-width: 520px; margin: 0 auto; text-align: center; }
    .qr-image { display: block; width: min(100%, 320px); height: auto; margin: 20px auto; image-rendering: pixelated; }
    .survey-url { overflow-wrap: anywhere; }
    @media print {
        .site-header, .site-footer, .no-print { display: none !important; }
        body { background: #fff; }
        main { width: 100%; margin: 0; }
        .card { border: 0; box-shadow: none; }
    }
</style>
<main>
    <section class="card qr-card">
        <h1>QR-code voor de vragenlijst</h1>
        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escapeHtml($error) ?></p>
            <p><a href="qr-codes.php">Opnieuw proberen</a></p>
        <?php else: ?>
            <p>Vestiging: <strong><?= escapeHtml($manager['vestiging_naam']) ?></strong></p>
            <img
                class="qr-image"
                src="<?= escapeHtml($qrImageUrl) ?>"
                alt="QR-code voor de vragenlijst van <?= escapeHtml($manager['vestiging_naam']) ?>"
                width="320"
                height="320"
                onerror="this.hidden = true; document.getElementById('qr-error').hidden = false;"
            >
            <p class="error qr-error" id="qr-error" role="alert" hidden>De QR-code kan niet worden geladen. Controleer de internetverbinding en probeer het opnieuw.</p>
            <p>Scan de code met een telefoon om de vragenlijst te openen.</p>
            <p class="survey-url"><a href="<?= escapeHtml($surveyUrl) ?>"><?= escapeHtml($surveyUrl) ?></a></p>
            <div class="no-print">
                <button type="button" onclick="window.print()">QR-code afdrukken</button>
                <p><a href="dashboard.php">Terug naar het dashboard</a></p>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
