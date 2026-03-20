@echo off
title Robot IA Cybersécurité

:boucle
echo ========================================
echo [%time%] Lancement d'un nouveau traitement...
echo ----------------------------------------

set dateclean=%date:/=-%
set fichier_log=C:\wamp64\www\veilys\logs\traitement_%dateclean%.log

"C:\wamp64\bin\php\php8.3.6\php.exe" -f "C:\wamp64\www\veilys\traitement_ia.php" >> logs/%dateclean%.log

echo.
echo ----------------------------------------
echo Traitement termine !
echo Attente de 30 secondes avant le prochain article...

timeout /t 30 /nobreak > NUL

goto boucle