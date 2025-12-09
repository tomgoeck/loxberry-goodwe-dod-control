#!/usr/bin/env python3
"""
Read current on-grid battery Depth of Discharge (DOD) from GoodWe inverter

Usage:
    python3 getDOD.py <inverter_ip>

Example:
    python3 getDOD.py 192.168.1.150

Output:
    Prints the DOD value (0-100) on success
    Prints error message to stderr on failure
"""

import asyncio
import sys

try:
    import goodwe
except ImportError:
    print("Error: goodwe library not found. Install with: pip3 install goodwe", file=sys.stderr)
    sys.exit(1)


async def get_dod():
    """Connect to inverter and read DOD value"""
    
    # Validate arguments
    if len(sys.argv) < 2:
        print("Usage: getDOD.py <inverter_ip>", file=sys.stderr)
        sys.exit(1)
    
    ip_address = sys.argv[1]
    
    try:
        # Connect to inverter
        inverter = await goodwe.connect(ip_address)
        
        # Read DOD value
        dod = await inverter.get_ongrid_battery_dod()
        
        # Output only the value (for parsing by PHP)
        print(dod)
        
        sys.exit(0)
        
    except asyncio.TimeoutError:
        print(f"Error: Timeout connecting to inverter at {ip_address}", file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    asyncio.run(get_dod())
