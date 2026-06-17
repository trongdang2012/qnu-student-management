@echo off
title Cloudflare Tunnel - QNU Student Management
echo Dang khoi dong duong ham bao mat Cloudflare...
echo -------------------------------------------------------------
echo Chu y: Copy duong link "https://*.trycloudflare.com" tren man hinh de truy cap.
echo Them "/qnu-student-management/" vao cuoi link de vao web.
echo Vi du: https://xyz.trycloudflare.com/qnu-student-management/
echo -------------------------------------------------------------
echo BAM TO HOP PHIM "Ctrl + C" HOAC DONG CUA SO NAY DE TAT TUNNEL.
echo -------------------------------------------------------------
"%~dp0tools\cloudflared.exe" tunnel --url http://localhost
pause
