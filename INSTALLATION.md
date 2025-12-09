# 📋 Detailed Installation Guide

This guide walks you through the complete installation process step-by-step.

## Prerequisites Checklist

Before you start, make sure you have:

- [ ] LoxBerry installed and running
- [ ] SSH access to your LoxBerry system
- [ ] Your GoodWe inverter IP address (e.g., `192.168.1.150`)
- [ ] Internet connection for the LoxBerry to download dependencies
- [ ] Basic knowledge of Linux command line

## Installation Steps

### 1. SSH into Your LoxBerry

Connect to your LoxBerry via SSH:

```bash
ssh loxberry@192.168.1.15
# or
ssh loxberry@YOUR_LOXBERRY_IP
```

You'll be prompted for a password (default is usually "loxberry" or "raspberry").

### 2. Choose Your Plugin Name

Decide on a plugin directory name. Examples:
- `goodwe_discharge`
- `goodwe-dod`
- `goodwe_dod_control`
- `goodwe_discharge_control`

We'll use `goodwe_discharge` in this guide.

### 3. Clone or Download the Repository

**Option A: Using Git (Recommended)**

```bash
cd /opt/loxberry/bin/plugins
sudo git clone https://github.com/YOUR_USERNAME/loxberry-goodwe-dod-control.git goodwe_discharge
cd goodwe_discharge
```

If you get "Permission denied" errors, try:

```bash
sudo -s  # Switch to root
cd /opt/loxberry/bin/plugins
git clone https://github.com/YOUR_USERNAME/loxberry-goodwe-dod-control.git goodwe_discharge
cd goodwe_discharge
exit  # Exit root
```

**Option B: Manual Download via ZIP**

1. Go to GitHub repository
2. Click "Code" → "Download ZIP"
3. Transfer the ZIP file to your LoxBerry (via SCP or USB)
4. On LoxBerry:

```bash
cd /opt/loxberry/bin/plugins
sudo mkdir goodwe_discharge
sudo unzip loxberry-goodwe-dod-control.zip -d goodwe_discharge
cd goodwe_discharge
```

### 4. Install Python Dependencies

The GoodWe library is required. Install it:

```bash
pip3 install goodwe
```

If that doesn't work, try:

```bash
sudo pip3 install goodwe
```

Or (on some systems):

```bash
python3 -m pip install goodwe
```

**Verify installation:**

```bash
python3 -c "import goodwe; print('GoodWe library installed successfully!')"
```

You should see: `GoodWe library installed successfully!`

If you get an error, the library wasn't installed properly. Try the installation command again.

### 5. Rename Plugin Directories

The repository contains placeholder `{PLUGIN_NAME}` directories. You need to rename them to your chosen plugin name:

```bash
# Navigate to the plugin directory
cd /opt/loxberry/bin/plugins/goodwe_discharge

# Rename bin directory
mv bin/plugins/{PLUGIN_NAME} bin/plugins/goodwe_discharge

# Rename webfrontend directory  
mv webfrontend/html/plugins/{PLUGIN_NAME} webfrontend/html/plugins/goodwe_discharge
```

### 6. Update PHP Configuration

Open the PHP file and replace the placeholder:

```bash
# Using nano editor
nano /opt/loxberry/bin/plugins/goodwe_discharge/webfrontend/html/plugins/goodwe_discharge/dod.php
```

Find this line (around line 18):
```php
$lbpplugindir = "{PLUGIN_NAME}";
```

Change it to:
```php
$lbpplugindir = "goodwe_discharge";
```

Save the file:
- Press `Ctrl+O` (to save)
- Press `Enter`
- Press `Ctrl+X` (to exit nano)

### 7. Copy Files to Correct Locations

Copy the bin scripts to the LoxBerry bin directory:

```bash
sudo cp -r /opt/loxberry/bin/plugins/goodwe_discharge/bin/plugins/goodwe_discharge/* /opt/loxberry/bin/plugins/goodwe_discharge/
```

Copy the webfrontend files:

```bash
sudo cp -r /opt/loxberry/bin/plugins/goodwe_discharge/webfrontend/html/plugins/goodwe_discharge /opt/loxberry/webfrontend/html/plugins/
```

### 8. Set File Permissions

Make Python scripts executable:

```bash
sudo chmod +x /opt/loxberry/bin/plugins/goodwe_discharge/*.py
```

Set correct permissions for PHP:

```bash
sudo chmod 644 /opt/loxberry/webfrontend/html/plugins/goodwe_discharge/*.php
```

### 9. Create Configuration File

Create the config directory:

```bash
sudo mkdir -p /opt/loxberry/config/plugins/goodwe_discharge
```

Create the config file with your inverter IP:

```bash
sudo nano /opt/loxberry/config/plugins/goodwe_discharge/config.json
```

Paste this content (replace `192.168.1.150` with your actual inverter IP):

```json
{
  "InverterIP": "192.168.1.150"
}
```

Save with `Ctrl+O`, `Enter`, `Ctrl+X`

### 10. Verify Installation

Test if the Python scripts can connect to your inverter:

```bash
python3 /opt/loxberry/bin/plugins/goodwe_discharge/getDOD.py 192.168.1.150
```

**Expected output:**
A number between 0-100, e.g.:
```
30
```

**If you get an error:**
- Check if the inverter IP is correct
- Ensure inverter is powered on and connected to the network
- Try pinging the inverter first:
  ```bash
  ping 192.168.1.150
  ```

### 11. Test the API Endpoint

From your LoxBerry, test the API:

```bash
curl "http://localhost/plugins/goodwe_discharge/dod.php?action=get"
```

**Expected response:**
```json
{"status":"success","dod":30,"unit":"%"}
```

### 12. Test from Another Computer

Test from a different machine on your network:

```bash
# Replace with your LoxBerry IP
curl "http://192.168.1.15/plugins/goodwe_discharge/dod.php?action=get"
```

## Troubleshooting

### Problem: "Permission denied" when copying files

**Solution:** Use `sudo` and make sure you're in the correct directory:

```bash
sudo ls /opt/loxberry/bin/plugins/goodwe_discharge/
```

### Problem: Python script says "goodwe library not found"

**Solution:** Install the library again:

```bash
sudo pip3 install --upgrade goodwe
```

Or try:

```bash
python3 -m pip install goodwe
```

### Problem: "Connection refused" or "Connection timeout"

**Solution:** 
1. Verify inverter IP is correct
2. Check network connectivity:
   ```bash
   ping 192.168.1.150  # Replace with your inverter IP
   ```
3. Make sure inverter is powered on
4. Check if it's on the same network as LoxBerry

### Problem: PHP returns "Inverter IP not configured"

**Solution:**
1. Check if config file exists:
   ```bash
   cat /opt/loxberry/config/plugins/goodwe_discharge/config.json
   ```
2. Make sure it contains the correct IP:
   ```json
   {"InverterIP": "192.168.1.150"}
   ```

### Problem: Cannot SSH into LoxBerry

**Solution:**
1. Make sure LoxBerry is on the network and powered on
2. Use the correct IP address
3. Use correct username (usually `loxberry`)
4. Default password is usually `loxberry` or `raspberry`

### Problem: "pip3: command not found"

**Solution:** LoxBerry usually uses Python 3. Try one of these:

```bash
pip install goodwe
python3 -m pip install goodwe
apt-get install python3-pip  # Then try pip3 install again
```

## Post-Installation

After successful installation, you can:

1. **Create automation scripts** that call the API
2. **Integrate with Home Assistant** (see README for examples)
3. **Set up cron jobs** to adjust DOD on schedule
4. **Create custom dashboards** using the API

## Uninstalling

If you want to remove the plugin:

```bash
# Remove bin files
sudo rm -rf /opt/loxberry/bin/plugins/goodwe_discharge

# Remove web files
sudo rm -rf /opt/loxberry/webfrontend/html/plugins/goodwe_discharge

# Remove config
sudo rm -rf /opt/loxberry/config/plugins/goodwe_discharge

# Optional: Remove Python library (if not used by other plugins)
sudo pip3 uninstall goodwe
```

## Getting Help

If you're stuck:

1. Check the **Troubleshooting** section above
2. Re-read the steps carefully - most issues come from missed steps
3. Check file permissions with: `ls -la /opt/loxberry/bin/plugins/goodwe_discharge/`
4. Check if LoxBerry can reach the inverter: `ping YOUR_INVERTER_IP`
5. Open an issue on GitHub with error messages

## Next Steps

Once installation is complete:

- Read the main **README.md** for API usage examples
- Try the API from different programming languages
- Set up automation based on your needs
- Consider security - restrict access from untrusted networks
