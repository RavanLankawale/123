<?php

// Set the content type to application/vnd.apple.mpegurl for m3u8 file
header('Content-Type: application/vnd.apple.mpegurl');
header('Content-Disposition: inline; filename="playlist.m3u8"');

// URL of the content
$url = 'https://raw.githubusercontent.com/Justryuz/TV/refs/heads/main/HBOGO%20Asia.html';

// Fetch the content from the URL
$response = file_get_contents($url);

// Check if the data was successfully fetched
if ($response === FALSE) {
    die('Error fetching data.');
}

// Clean the fetched data by removing control characters (non-printable)
$cleaned_response = preg_replace('/[[:cntrl:]]/', '', $response);

// Attempt to decode the cleaned JSON data
$data = json_decode($cleaned_response, true);

// Check if JSON was properly decoded
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON data: ' . json_last_error_msg());
}

// Start creating the M3U playlist
echo "#EXTM3U\n";

// Allowed categories
$allowed_categories = ['English-Film', 'India-Film', 'HBOGO Asia'];

// Iterate over the data and generate M3U entries
foreach ($data as $item) {
    // Extract necessary fields
    $name = $item['name'] ?? 'Unknown Title';
    $category = $item['category'] ?? 'Unknown Category';
    $poster = $item['info']['poster'] ?? '';
    $video = $item['video'] ?? '';  // This will be used as the manifest URL
    $drm = $item['drm'] ?? '';
    $drmkey = $item['drmkey'] ?? '';  // This will be used as the license URL

    // Only process allowed categories
    if (in_array($category, $allowed_categories)) {
        // Check if video URL has .mpd extension
        if (strpos($video, '.mpd') !== false) {
            // Process with DRM and license key since it's an .mpd file
            if (!empty($drmkey)) {
                echo "#EXTINF:-1 tvg-id=\"\" tvg-logo=\"$poster\" group-title=\"$category\", $name\n";
                echo "#KODIPROP:inputstreamaddon=inputstream.adaptive\n";
                echo "#KODIPROP:inputstream.adaptive.manifest_type=mpd\n";
                echo "#KODIPROP:inputstream.adaptive.license_type=clearkey\n";
                echo "#KODIPROP:inputstream.adaptive.license_key=$drmkey\n";
                echo "$video\n";
            }
        } else {
            // Process without DRM and license key for non-.mpd files
            echo "#EXTINF:-1 tvg-id=\"\" tvg-logo=\"$poster\" group-title=\"$category\", $name\n";
            echo "$video\n";
        }
    }
}

?>
