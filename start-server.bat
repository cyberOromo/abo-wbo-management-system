@echo off
echo Starting ABO-WBO Management System Server...
echo.
echo Server will be available at: http://localhost:8080/landing.php
echo.
echo Press Ctrl+C to stop the server
echo.

cd "C:\xampp\htdocs\abo-wbo"
"C:\xampp\php\php.exe" -S localhost:8080

pause