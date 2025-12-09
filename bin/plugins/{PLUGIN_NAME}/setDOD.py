#!/usr/bin/env python3
"""
Set on-grid battery Depth of Discharge (DOD) on GoodWe inverter

Usage:
    python3 setDOD.py <inverter_ip> <dod_value>

Example:
    python3 setDOD.py 192.168.1.150 50

Parameters:
    - inverter_ip: IP address of the GoodWe inverter
    - dod_value: DOD percentage (0-100)

Output:
    Prints success message on success
    Prints error message to stderr on failure
"""

import asyncio
import sys

try:
    import goodwe
except ImportError:
    print("Error: goodwe library not found. Install with: pip3 install goodwe", file=sys.stderr)
    sys.exit(1)


async def set_dod():
    """Connect to inverter and set DOD value"""
    
    # Validate arguments
    if len(sys.argv) < 3:
        print("Usage: setDOD.py <inverter_ip> <dod_value>", file=sys.stderr)
        sys.exit(1)
    
    ip_address = sys.argv[1]
    
    try:
        dod_value = int(sys.argv[2])
    except ValueError:
        print(f"Error: DOD value must be a number (got '{sys.argv[2]}')", file=sys.stderr)
        sys.exit(1)
    
    # Validate range
    if dod_value < 0 or dod_value > 100:
        print(f"Error: DOD value must be between 0 and 100 (got {dod_value})", file=sys.stderr)
        sys.exit(1)
    
    try:
        # Connect to inverter
        inverter = await goodwe.connect(ip_address)
        
        # Set DOD value
        await inverter.set_ongrid_battery_dod(dod_value)
        
        # Output success message (for logging)
        print(f"DOD set to {dod_value}%")
        
        sys.exit(0)
        
    except asyncio.TimeoutError:
        print(f"Error: Timeout connecting to inverter at {ip_address}", file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    asyncio.run(set_dod())
