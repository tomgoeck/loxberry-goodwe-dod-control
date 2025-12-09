# LoxBerry Plugin: GoodWe DoD Control API (PHP + Python)

Mit diesem LoxBerry-Plugin kannst du den **Depth of Discharge (DoD)** deines **GoodWe**-Wechselrichters per **HTTP API** auslesen und setzen.

✅ **GET**: aktuellen DoD auslesen  
✅ **SET**: DoD auf einen Wert 0–100% setzen (mit Validierung & Rückprüfung)  
✅ JSON-Antworten, klare Fehlermeldungen, Timeout-Schutz

> Kurz: Du bekommst eine lokale URL wie  
> `http://<LOXBERRY_IP>/plugins/<PLUGIN_NAME>/dod.php?action=set&value=80`  
> und kannst damit den DoD aus Automationen (Node-RED, Home Assistant, Skripte, …) setzen.

---

## Inhalt

- [1. Voraussetzungen](#1-voraussetzungen)
- [2. Was ist im Repo enthalten](#2-was-ist-im-repo-enthalten)
- [3. Installation (empfohlen)](#3-installation-empfohlen)
- [4. Konfiguration (Inverter-IP)](#4-konfiguration-inverter-ip)
- [5. API benutzen (Beispiele)](#5-api-benutzen-beispiele)
- [6. Node-RED Integration](#6-node-red-integration)
- [7. Troubleshooting](#7-troubleshooting)
- [8. Sicherheitshinweise](#8-sicherheitshinweise)
- [9. Lizenz](#9-lizenz)

---

## 1. Voraussetzungen

**Hardware**
- LoxBerry (Raspberry Pi, NUC etc.)
- GoodWe Inverter im gleichen Netzwerk

**Software**
- Python 3.6+ (normalerweise auf LoxBerry vorhanden)
- PHP/Webserver (bei LoxBerry vorhanden)
- Python-Paket: `goodwe >= 0.3.0`

---

## 2. Was ist im Repo enthalten
Wenn du Goodwe2MQTT installiert hast auf dem Loxberry, empfehle ich es einfach in das Verzeichnis zu legen, da dort die Inverter-IP hinterlegt ist. Alternativ kannst du die Inverter IP im dod.php Script manuell festlegen. 

Struktur (mit Platzhalter `{PLUGIN_NAME}`):

```
bin/plugins/{PLUGIN_NAME}/
  ├─ getDOD.py     # DoD lesen
  └─ setDOD.py     # DoD setzen

webfrontend/html/plugins/{PLUGIN_NAME}/
  └─ dod.php       # HTTP REST Endpoint

requirements.txt   # Python Dependency (goodwe)
```

**Wie es funktioniert:**  
HTTP Request → `dod.php` → ruft Python (`getDOD.py` / `setDOD.py`) → spricht den GoodWe Inverter an → JSON Response.

---

## 3. Installation (empfohlen)

> Es gibt zwei Installationswege:
> - Schnell: siehe **QUICKSTART.md** (5 Minuten)
> - Ausführlich: siehe **INSTALLATION.md** (Step-by-step)

Hier die empfohlene Standard-Installation in klaren Schritten:

### 3.1 Per SSH auf den LoxBerry

```bash
ssh loxberry@<DEIN_LOXBERRY_IP>
```

### 3.2 Plugin-Name festlegen

Wähle einen Ordnernamen, z.B.:
- `goodwe_discharge`
- `goodwe_dod_control`

Im Beispiel verwenden wir: **goodwe_discharge**.

### 3.3 Repository kopieren

**Option A (empfohlen): Git Clone**

```bash
cd /opt/loxberry/bin/plugins
sudo git clone https://github.com/YOUR_USERNAME/loxberry-goodwe-dod-control.git goodwe_discharge
cd goodwe_discharge
```

**Option B: ZIP herunterladen und entpacken** (siehe INSTALLATION.md).

### 3.4 Python Dependency installieren

```bash
pip3 install -r requirements.txt
```

oder (wenn nötig):

```bash
sudo pip3 install -r requirements.txt
```

### 3.5 Platzhalter `{PLUGIN_NAME}` umbenennen

```bash
mv bin/plugins/{PLUGIN_NAME} bin/plugins/goodwe_discharge
mv webfrontend/html/plugins/{PLUGIN_NAME} webfrontend/html/plugins/goodwe_discharge
```

### 3.6 PHP-Datei anpassen (Plugin-Name eintragen)

Öffne:

```bash
nano webfrontend/html/plugins/goodwe_discharge/dod.php
```

Suche die Zeile:

```php
$lbpplugindir = "{PLUGIN_NAME}";
```

Ändere sie zu:

```php
$lbpplugindir = "goodwe_discharge";
```

(Das ist wichtig, damit LoxBerry Pfade korrekt aufgelöst werden.)

### 3.7 Dateien an die richtigen LoxBerry-Orte kopieren

**Python (bin)**
```bash
sudo cp -r bin/plugins/goodwe_discharge/* /opt/loxberry/bin/plugins/goodwe_discharge/
```

**PHP (webfrontend)**
```bash
sudo cp -r webfrontend/html/plugins/goodwe_discharge /opt/loxberry/webfrontend/html/plugins/
```

### 3.8 Rechte setzen

```bash
sudo chmod +x /opt/loxberry/bin/plugins/goodwe_discharge/*.py
sudo chmod 644 /opt/loxberry/webfrontend/html/plugins/goodwe_discharge/*.php
```

---

## 4. Konfiguration (Inverter-IP)

Das Plugin benötigt die IP des Wechselrichters in einer Config-Datei:

```bash
sudo mkdir -p /opt/loxberry/config/plugins/goodwe_discharge
sudo nano /opt/loxberry/config/plugins/goodwe_discharge/config.json
```

Inhalt (IP anpassen!):

```json
{
  "InverterIP": "192.168.1.150"
}
```

---

## 5. API benutzen (Beispiele)

Basis-URL:

```
http://<LOXBERRY_IP>/plugins/goodwe_discharge/dod.php
```

### 5.1 DoD auslesen

```bash
curl "http://<LOXBERRY_IP>/plugins/goodwe_discharge/dod.php?action=get"
```

Beispiel-Response:
```json
{"status":"success","dod":30,"unit":"%"}
```

### 5.2 DoD setzen

```bash
curl "http://<LOXBERRY_IP>/plugins/goodwe_discharge/dod.php?action=set&value=80"
```

Beispiel-Response:
```json
{"status":"success","previous_dod":30,"requested_dod":80,"current_dod":80,"unit":"%","verified":true}
```

---

## 6. Node-RED Integration

In Node-RED nutzt du am Ende einfach einen **HTTP Request Node**:
Erstetze im letzten Function Node den Pluginnamen zu zum Beispiel
- URL (Beispiel):
  ```
  http://192.168.1.15/plugins/goodwe_discharge/dod.php?action=set&value=80
  ```

---

## 7. Troubleshooting

### „Inverter IP not configured“
- Prüfe, ob diese Datei existiert:
  ```
  /opt/loxberry/config/plugins/goodwe_discharge/config.json
  ```
- Inhalt muss `"InverterIP"` enthalten.
### „goodwe library not found“
- Installiere erneut:
  ```bash
  sudo pip3 install --upgrade goodwe
  ```
  oder:
  ```bash
  pip3 install -r requirements.txt
  ```

### Timeout / Connection refused
- Inverter-IP korrekt?
- Ping vom LoxBerry:
  ```bash
  ping <INVERTER_IP>
  ```
- Inverter im gleichen Netz & online?

---

## 8. Sicherheitshinweise

- Die API ist **ohne Authentifizierung** erreichbar.
- **Nicht** ins Internet forwarden!
- Zugriff im LAN einschränken (Firewall, VLAN, Reverse Proxy mit Auth).
