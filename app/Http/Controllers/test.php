hiiiiiiiiiiiiiiii
<?php
echo"asdasdasd";
$zipFile = 'api/vendor.zip';  // Path to your zip file
$extractPath = 'api/';  // Path to the directory where you want to extract the files

// Create a new ZipArchive instance
$zip = new ZipArchive;

// Open the zip file
if ($zip->open($zipFile) === TRUE) {
    // Extract all the files
    $zip->extractTo($extractPath);

    // Close the zip file
    $zip->close();

    echo 'Files extracted successfully.';
} else {
    echo 'Failed to open the zip file.';
}

?>
