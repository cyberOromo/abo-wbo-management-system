#!/bin/bash
echo "=== ABO-WBO System Status Check ==="
echo

# Check if server is running
if curl -s http://localhost:8080/landing.php > /dev/null 2>&1; then
    echo "✅ Server: RUNNING on http://localhost:8080"
    echo "   Landing Page: http://localhost:8080/landing.php"
    echo "   Main Application: http://localhost:8080/index.php"
    echo "   Server Status: http://localhost:8080/server-status.php"
else
    echo "❌ Server: NOT RUNNING"
    echo "   To start server: cd 'C:/xampp/htdocs/abo-wbo' && 'C:/xampp/php/php.exe' -S localhost:8080"
fi

echo

# Check MySQL
if curl -s http://localhost:8080/api.php/system/status > /dev/null 2>&1; then
    echo "✅ MySQL: CONNECTED"
else
    echo "❌ MySQL: NOT CONNECTED"
fi

echo
echo "==================================="