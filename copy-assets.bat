@echo off
echo Copie des assets...
mkdir public\styles 2>nul
mkdir public\js 2>nul
copy assets\styles\admin.css public\styles\admin.css
copy assets\styles\template-selector.css public\styles\template-selector.css
copy assets\js\form.js public\js\form.js
copy assets\js\template-selector.js public\js\template-selector.js
echo Assets copies avec succes!
