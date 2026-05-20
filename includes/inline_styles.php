<?php
// ============================================================
// NOXARA - includes/inline_styles.php
// Embed CSS inline - 100% guaranteed to load from server
// ============================================================
$cssFile  = __DIR__ . '/../assets/css/style.css';
$cssFile2 = __DIR__ . '/../assets/css/mobile.css';
$cssFile3 = __DIR__ . '/../assets/css/animations.css';

$css = '';
if (file_exists($cssFile))  $css .= file_get_contents($cssFile);
if (file_exists($cssFile2)) $css .= "\n" . file_get_contents($cssFile2);
if (file_exists($cssFile3)) $css .= "\n" . file_get_contents($cssFile3);

// Extra enhanced styles injected inline
$extraCss = '
/* spin keyframe for loading */
@keyframes spin{to{transform:rotate(360deg)}}
/* smooth card hover */
.section-card:hover{border-color:rgba(255,215,0,0.12)}
/* nav active glow */
.nav-item.active svg{filter:drop-shadow(0 0 6px rgba(255,215,0,0.6))}
/* skeleton shimmer */
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* improved mobile tap */
@media(hover:none){.btn:active{opacity:0.85}.menu-item:active{transform:scale(0.93)}}
';

echo '<style>' . $css . $extraCss . '</style>';
echo "\n";
// Also embed JS inline for guaranteed loading
$jsMain   = __DIR__ . '/../assets/js/main.js';
$jsAnim   = __DIR__ . '/../assets/js/animations.js';
$jsLucide = __DIR__ . '/../assets/js/lucide.min.js';
$js = '';
if (file_exists($jsMain))   $js .= file_get_contents($jsMain);
if (file_exists($jsAnim))   $js .= "\n" . file_get_contents($jsAnim);
if ($js) {
    echo '<script>' . $js . '</script>';
}
echo "\n";
// Embed lucide inline as ultimate fallback (runs after DOM ready)
if (file_exists($jsLucide)) {
    echo '<script>' . file_get_contents($jsLucide) . '</script>';
}
echo "\n";
