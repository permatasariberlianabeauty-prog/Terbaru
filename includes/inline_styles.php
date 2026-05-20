<?php
// ============================================================
// NOXARA - includes/inline_styles.php
// Embed CSS inline to ensure styles ALWAYS load regardless of
// external file availability
// ============================================================
$cssFile = __DIR__ . '/../assets/css/style.css';
$cssFile2 = __DIR__ . '/../assets/css/mobile.css';
$cssFile3 = __DIR__ . '/../assets/css/animations.css';

$css = '';
if (file_exists($cssFile))  $css .= file_get_contents($cssFile);
if (file_exists($cssFile2)) $css .= file_get_contents($cssFile2);
if (file_exists($cssFile3)) $css .= file_get_contents($cssFile3);

if ($css) {
    echo '<style>' . $css . '</style>';
}
