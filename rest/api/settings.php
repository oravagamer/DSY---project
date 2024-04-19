<?php
function get_settings() {
    return json_decode(file_get_contents('settings.json'), true);
}
