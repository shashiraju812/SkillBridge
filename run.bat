@echo off
title SkillBridge Server
cd /d "%~dp0"
echo ========================================================
echo     SkillBridge - Student Skills & Internship Platform
echo ========================================================
echo.

set PHP_BIN="C:\Users\LENOVO\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if not exist %PHP_BIN% (
    if exist "C:\xampp\php\php.exe" (
        set PHP_BIN="C:\xampp\php\php.exe"
    ) else (
        set PHP_BIN=php
    )
)

echo Using PHP: %PHP_BIN%
echo Opening browser at http://localhost:8000/wtproject/index.php ...
start http://localhost:8000/wtproject/index.php

echo Starting server on port 8000 (Press Ctrl+C to stop)...
echo.
%PHP_BIN% -S localhost:8000 router.php
pause

