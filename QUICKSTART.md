# ⚡ Quick Start Guide

Get the GoodWe DOD API running in 5 minutes!

## Prerequisites

- LoxBerry with SSH access
- GoodWe inverter IP address
- Basic terminal knowledge

## Installation (TL;DR)

```bash
# 1. SSH into LoxBerry
ssh loxberry@192.168.1.15

# 2. Clone repository
cd /opt/loxberry/bin/plugins
sudo git clone https://github.com/YOUR_USERNAME/loxberry-goodwe-dod-control.git goodwe_discharge
cd goodwe_discharge

# 3. Install dependencies
pip3 install goodwe
# or: sudo pip3 install goodwe

# 4. Rename directories
mv bin/plugins/{PLUGIN_NAME} bin/plugins/goodwe_discharge
mv webfrontend/html/plugins/{PLUGIN_NAME} webfrontend/html/plugins/goodwe_discharge

# 5. Update PHP
nano webfrontend/html/plugins/goodwe_discharge/dod.php
# Change line 18: {PLUGIN_NAME} → goodwe_discharge

# 6. Copy files
sudo cp -r bin/plugins/goodwe_discharge/* /opt/loxberry/bin/plugins/goodwe_discharge/
sudo cp -r webfrontend/html/plugins/goodwe_discharge /opt/loxberry/webfrontend/html/plugins/

# 7. Set permissions
sudo chmod +x /opt/loxberry/bin/plugins/goodwe_discharge/*.py
sudo chmod 644 /opt/loxberry/webfrontend/html/plugins/goodwe_discharge/*.php

# 8. Create config
sudo mkdir -p /opt/loxberry/config/plugins/goodwe_discharge
sudo tee /opt/loxberry/config/plugins/goodwe_discharge/config.json > /dev/null << 'EOF'
{"InverterIP": "192.168.1.150"}
EOF

# 9. Test
python3 /opt/loxberry/bin/plugins/goodwe_discharge/getDOD.py 192.168.1.150
curl "http://192.168.1.15/plugins/goodwe_discharge/dod.php?action=get"
```

Done! 🎉

## First API Call

```bash
# Read DOD
curl "http://192.168.1.15/plugins/goodwe_discharge/dod.php?action=get"

# Set DOD to 50%
curl "http://192.168.1.15/plugins/goodwe_discharge/dod.php?action=set&value=50"
```

## Common Issues

| Problem | Solution |
|---------|----------|
| `goodwe library not found` | Run: `sudo pip3 install goodwe` |
| Connection timeout | Check inverter IP and network connectivity: `ping 192.168.1.150` |
| Permission denied | Use `sudo` for installation commands |
| Config not found | Create `/opt/loxberry/config/plugins/goodwe_discharge/config.json` |

## Next Steps

- See **README.md** for detailed API documentation
- See **INSTALLATION.md** for step-by-step guide with explanations
- Check **Examples** section in README for integration code

## Need Help?

1. Check the troubleshooting section in **INSTALLATION.md**
2. Review the full **README.md**
3. Open an issue on GitHub
