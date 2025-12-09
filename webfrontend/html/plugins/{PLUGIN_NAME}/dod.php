<?php
/**
 * GoodWe Inverter DOD Control API
 * 
 * A simple HTTP API to read and set the Depth of Discharge (DOD) 
 * of GoodWe on-grid battery systems via LoxBerry
 * 
 * Usage:
 * GET  /plugins/{PLUGIN_NAME}/dod.php?action=get
 * POST /plugins/{PLUGIN_NAME}/dod.php?action=set&value=50
 */

// ============================================================================
// Configuration - EDIT THESE PATHS IF NEEDED
// ============================================================================

$lbhomedir = "/opt/loxberry";
$lbpplugindir = "{PLUGIN_NAME}";  // Change this to your plugin directory name
$lbpconfigdir = "$lbhomedir/config/plugins/$lbpplugindir";
$lbpbindir = "$lbhomedir/bin/plugins/$lbpplugindir";

// ============================================================================
// Helper: Get inverter IP from config
// ============================================================================

function getInverterIP() {
    global $lbpconfigdir;
    
    $config_file = "$lbpconfigdir/config.json";
    
    if (!file_exists($config_file)) {
        return null;
    }
    
    $config = json_decode(file_get_contents($config_file), true);
    return $config['InverterIP'] ?? null; ##Setze Hier alternativ deine Wechselrichter IP Adresse wenn kein goodwe2mqtt installiert ist
}

// ============================================================================
// Helper: Execute command with timeout and error handling
// ============================================================================

function executeCommand($cmd, $timeout = 15) {
    $cmd = "timeout $timeout " . $cmd . " 2>&1";
    return shell_exec($cmd);
}

// ============================================================================
// Main API Logic
// ============================================================================

// Set response header
header('Content-Type: application/json; charset=utf-8');

// Get inverter IP
$inverter_ip = getInverterIP();

if (!$inverter_ip) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Inverter IP not configured. Please check config.json"
    ]);
    exit(1);
}

// Get action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// ============================================================================
// ACTION: GET - Read current DOD value
// ============================================================================

if ($action === 'get') {
    global $lbpbindir;
    
    $cmd = "python3 $lbpbindir/getDOD.py \"$inverter_ip\"";
    $output = executeCommand($cmd);
    $dod = trim($output);
    
    if (is_numeric($dod)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "dod" => (int)$dod,
            "unit" => "%"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to read DOD from inverter",
            "details" => $output
        ]);
    }
}

// ============================================================================
// ACTION: SET - Set new DOD value
// ============================================================================

elseif ($action === 'set') {
    global $lbpbindir;
    
    // Get value parameter
    $value = $_POST['value'] ?? $_GET['value'] ?? null;
    
    // Validate parameter exists
    if ($value === null || $value === '') {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing 'value' parameter"
        ]);
        exit(1);
    }
    
    // Validate parameter is numeric
    if (!is_numeric($value)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid 'value' parameter. Must be a number."
        ]);
        exit(1);
    }
    
    $value = (int)$value;
    
    // Validate range
    if ($value < 0 || $value > 100) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Value must be between 0 and 100"
        ]);
        exit(1);
    }
    
    // Read current DOD
    $get_cmd = "python3 $lbpbindir/getDOD.py \"$inverter_ip\"";
    $current_dod = (int)trim(executeCommand($get_cmd));
    
    // Set new DOD
    $set_cmd = "python3 $lbpbindir/setDOD.py \"$inverter_ip\" \"$value\"";
    $set_output = executeCommand($set_cmd);
    
    // Wait for inverter to process
    sleep(2);
    
    // Verify new DOD
    $verify_cmd = "python3 $lbpbindir/getDOD.py \"$inverter_ip\"";
    $verified_dod = (int)trim(executeCommand($verify_cmd));
    
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "previous_dod" => $current_dod,
        "requested_dod" => $value,
        "current_dod" => $verified_dod,
        "unit" => "%",
        "verified" => ($verified_dod === $value)
    ]);
}

// ============================================================================
// Invalid Action
// ============================================================================

else {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid action. Use 'get' or 'set'",
        "example_get" => "/plugins/{PLUGIN_NAME}/dod.php?action=get",
        "example_set" => "/plugins/{PLUGIN_NAME}/dod.php?action=set&value=50"
    ]);
}
