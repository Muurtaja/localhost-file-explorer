<?php
$BASE_DIR = __DIR__;

function validateDirectory($targetDir, $baseDir)
{
    return $targetDir && strpos($targetDir, $baseDir) === 0 && is_dir($targetDir);
}

function listFiles($dir)
{
    $files = scandir($dir);
    $result = ['directories' => [], 'files' => []];

    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;

        if ($file != '.' && $file != '..') {
            if (is_dir($filePath)) {
                $result['directories'][] = $file;
            } elseif (is_readable($filePath)) {
                $result['files'][] = $file;
            }
        }
    }

    return $result;
}

function displayFileExplorer($currentDir, $baseDir)
{
    $targetDir = realpath($baseDir . '/' . $currentDir);

    if (validateDirectory($targetDir, $baseDir)) {
        echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Localhost File Explorer</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                <style>
                    body {
                        background-color: #f8f9fa;
                    }

                    .container {
                        background-color: #fff;
                        border-radius: 10px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        padding: 20px;
                        margin-top: 30px;
                    }

                    .breadcrumb {
                        background-color: #e9ecef;
                    }

                    .breadcrumb-item a {
                        color: #007bff;
                        text-decoration: none;
                    }

                    .breadcrumb-item a:hover {
                        text-decoration: underline;
                    }

                    .search-container {
                        margin-bottom: 15px;
                    }

                    .form-control {
                        border-radius: 20px;
                    }

                    .list-group-item {
                        cursor: pointer;
                        border: none;
                        border-bottom: 1px solid #ddd;
                    }

                    .list-group-item:last-child {
                        border-bottom: none;
                    }

                    .list-group-item a {
                        color: #343a40;
                        text-decoration: none;
                    }

                    .list-group-item a:hover {
                        text-decoration: underline;
                    }

                    .context-menu {
                        display: none;
                        position: absolute;
                        background-color: #fff;
                        border-radius: 5px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        z-index: 1000;
                    }

                    .context-menu-item {
                        padding: 10px;
                        cursor: pointer;
                        user-select: none;
                    }

                    .context-menu-item:hover {
                        background-color: #f8f9fa;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2>Localhost File Explorer</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="?dir=">Home</a></li>';

        // Breadcrumb navigation
        $pathParts = explode('/', $currentDir);
        $pathAccumulated = '';
        foreach ($pathParts as $pathPart) {
            if ($pathPart !== '') {
                $pathAccumulated .= '/' . $pathPart;
                echo '<li class="breadcrumb-item"><a href="?dir=' . urlencode($pathAccumulated) . '">' . htmlspecialchars($pathPart) . '</a></li>';
            }
        }

        echo '</ol>
                    </nav>
                    <div class="search-container">
                        <label for="search">Search:</label>
                        <input type="search" id="search" name="search" class="form-control" placeholder="Enter search query">
                    </div>
                    <ul class="list-group" id="fileList">';

        // Display parent directory link
        if ($currentDir !== '') {
            $parentDir = dirname($currentDir);
            $parentDirLink = $parentDir ? '?dir=' . urlencode($parentDir) : '';
            echo '<li class="list-group-item"><a href="' . $parentDirLink . '">[..]</a></li>';
        }

        // List directories and files
        $listing = listFiles($targetDir);

        foreach ($listing['directories'] as $directory) {
            $dirPath = $currentDir . '/' . $directory;
            echo '<li class="list-group-item" oncontextmenu="showContextMenu(event, \'' . $dirPath . '\')"><a href="?dir=' . urlencode($dirPath) . '">' . htmlspecialchars($directory) . '</a></li>';
        }

        foreach ($listing['files'] as $file) {
            echo '<li class="list-group-item" oncontextmenu="showContextMenu(event, \'' . $file . '\')">' . htmlspecialchars($file) . '</li>';
        }

        $CONTEXT_DOCUMENT_ROOT = $_SERVER["CONTEXT_DOCUMENT_ROOT"];
        echo '</ul>
                    <div class="context-menu" id="contextMenu">
                        <div class="context-menu-item" onclick="openWithVSCode()">Open with VS Code</div>
                        <div class="context-menu-item" onclick="directAccess()">Direct Access</div>
                    </div>
                </div>
                <script>
                    document.getElementById("search").addEventListener("input", function() {
                        var query = this.value.toLowerCase();
                        var fileList = document.getElementById("fileList").getElementsByTagName("li");

                        for (var i = 0; i < fileList.length; i++) {
                            var itemText = fileList[i].innerText.toLowerCase();
                            if (itemText.includes(query)) {
                                fileList[i].style.display = "block";
                            } else {
                                fileList[i].style.display = "none";
                            }
                        }
                    });

                    function showContextMenu(event, item) {
                        event.preventDefault();
                        var contextMenu = document.getElementById("contextMenu");
                        contextMenu.style.left = (event.clientX + window.scrollX) + "px";
                        contextMenu.style.top = (event.clientY + window.scrollY) + "px";
                        contextMenu.style.display = "block";
                        contextMenu.setAttribute("data-item", item);

                        document.addEventListener("click", function hideContextMenu() {
                            contextMenu.style.display = "none";
                            document.removeEventListener("click", hideContextMenu);
                        });
                    }

                    function openWithVSCode() {
                        var item = document.getElementById("contextMenu").getAttribute("data-item");
                        var vscode = "vscode://file/' . $CONTEXT_DOCUMENT_ROOT . $currentDir . '" + (item.startsWith("/") ? "" : "/") + item;
                        console.log(vscode);
                        window.open(vscode,"_blank");
                    }

                    function directAccess() {
                        var item = document.getElementById("contextMenu").getAttribute("data-item");
                        window.open(item,"_blank");
                    }
                </script>
                <footer class="text-center mt-4">
                    <p>&copy; ' . date('Y') . ' Developed with &#10084; by <a target="_blank" href="https://github.com/Muurtaja">muurtaja</a>. It is open to contribute.</p>
                </footer>
            </body>
            </html>';
    } else {
        echo 'Invalid directory';
    }
}

// Get the requested directory or set it to the base directory
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '';
displayFileExplorer($currentDir, $BASE_DIR);
