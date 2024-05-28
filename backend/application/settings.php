<?php
const settingsPath = "../settings.json";

function getSettings(): object {
    return json_decode(file_get_contents(settingsPath));
}
