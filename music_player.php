<?php
$settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
$bgMusic = isset($settings['background_music']) ? $settings['background_music'] : 'default.mp3';
?>
<audio autoplay loop>
    <source src="music/<?=htmlspecialchars($bgMusic)?>" type="audio/mpeg">
</audio>
