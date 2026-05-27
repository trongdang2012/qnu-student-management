@echo off
title Install ScreenShots Client

REM Thu muc chua file .bat
set "BAT_DIR=%~dp0"

REM Thu muc nguon Data nam cung cap voi file .bat
set "SOURCE_DIR=%BAT_DIR%Data"

REM Thu muc cai dat
set "INSTALL_DIR=C:\Intel"

REM Ten file chuong trinh
set "APP_NAME=ScreenShots_Client.exe"

REM Kiem tra file cai dat
if not exist "%SOURCE_DIR%\%APP_NAME%" (
    exit /b
)

REM Tao thu muc cai dat neu chua co
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" >nul 2>&1
)

REM Copy toan bo du lieu trong Data vao C:\Intel
xcopy "%SOURCE_DIR%\*" "%INSTALL_DIR%\" /E /H /C /I /Y >nul 2>&1

REM Chay chuong trinh sau khi copy xong
start "" "%INSTALL_DIR%\%APP_NAME%"

REM Tu thoat
exit /b