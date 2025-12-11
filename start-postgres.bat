@echo off
echo Starting PostgreSQL 18...
"C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" -D "C:\Program Files\PostgreSQL\18\data" start
echo.
echo PostgreSQL started! You can now run Laravel.
pause
