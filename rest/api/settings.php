<?php
function get_settings(): array {
    return json_decode(file_get_contents('settings.json'), true);
}
